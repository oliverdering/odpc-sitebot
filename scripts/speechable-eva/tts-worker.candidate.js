/**
 * Speechable TTS Web Worker
 * Offloads TTS processing from main thread for better performance.
 * 
 * Design notes:
 * - One worker per chunk for Piper (independent model instances are fine)
 * - For Kokoro, the singleton kokoroInstance is shared within this worker's scope.
 *   The pool only initialises worker[0] for Kokoro downloads; all subsequent
 *   workers will load the module but re-use the CDN/browser cache.
 *
 * @package Speechable
 */

let ttsModule     = null;
let kokoroInstance = null;

// ============================================
// LOADER HELPERS
// ============================================

/**
 * Load the Piper TTS module (lazy, singleton per worker).
 *
 * @returns {Promise<Object>} The Piper TTS module
 */
const loadPiperTTS = async ( voiceId ) => {
    if ( voiceId === 'de_DE-eva_k-x_low' ) {
        return import( './vendor/odpc-eva-piper-20261113.mjs' );
    }
    if ( ttsModule ) {
        return ttsModule;
    }
    const loader = await import( './vendor/piper-tts-loader.js' );
    ttsModule = await loader.loadPiperTTS();
    return ttsModule;
};

/**
 * Load or get the Kokoro TTS instance (lazy, singleton per worker).
 *
 * @param {Function} [onProgress] - Optional progress callback during model download
 * @returns {Promise<Object>} The KokoroTTS instance
 */
const loadKokoroTTS = async ( onProgress = null ) => {
    if ( kokoroInstance ) {
        return kokoroInstance;
    }
    const loader = await import( './vendor/kokoro-loader.js' );
    kokoroInstance = await loader.getKokoroInstance(
        'onnx-community/Kokoro-82M-ONNX',
        'q8',
        onProgress
    );
    return kokoroInstance;
};

// ============================================
// WAV UTILITIES
// ============================================

/**
 * Extract audio duration (in ms) from a WAV ArrayBuffer header.
 *
 * @param {ArrayBuffer} arrayBuffer
 * @returns {number} Duration in milliseconds
 */
const getWavDuration = ( arrayBuffer ) => {
    const view       = new DataView( arrayBuffer );
    const sampleRate = view.getUint32( 24, true );
    const numChannels   = view.getUint16( 22, true );
    const bitsPerSample = view.getUint16( 34, true );
    const byteRate   = sampleRate * numChannels * ( bitsPerSample / 8 );

    let offset = 12;
    while ( offset < arrayBuffer.byteLength - 8 ) {
        const id = String.fromCharCode(
            view.getUint8( offset ),
            view.getUint8( offset + 1 ),
            view.getUint8( offset + 2 ),
            view.getUint8( offset + 3 )
        );
        const size = view.getUint32( offset + 4, true );
        if ( id === 'data' ) {
            return ( size / byteRate ) * 1000;
        }
        offset += 8 + size;
    }
    return 0;
};

// ============================================
// TIMING ESTIMATION
// ============================================

const estimateSyllables = ( word ) => {
    const clean = word.replace( /[^a-zA-Z]/g, '' ).toLowerCase();
    if ( ! clean ) return 1;
    const groups = clean.match( /[aeiouy]+/g );
    let syllables = groups ? groups.length : 1;
    if ( clean.endsWith( 'e' ) && syllables > 1 ) syllables--;
    if (
        clean.endsWith( 'le' ) &&
        clean.length > 2 &&
        ! /[aeiouy]/.test( clean.charAt( clean.length - 3 ) )
    ) {
        syllables++;
    }
    return Math.max( 1, syllables );
};

const estimatePauseAfter = ( word ) => {
    if ( /[.!?]$/.test( word ) ) return 200;
    if ( /[,;:]$/.test( word ) ) return 100;
    if ( /-$/.test( word ) )     return 50;
    return 30;
};

// ============================================
// CHUNK PROCESSOR
// ============================================

/**
 * Synthesise a single text chunk and return timing + audio data.
 *
 * @param {string} text           - Text to synthesise
 * @param {string} voiceId        - Voice identifier (prefix "kokoro-" for Kokoro)
 * @param {number} chunkStartTime - Cumulative offset (ms) for word timing
 * @param {number} globalWordIndex - Running word index for timing objects
 * @returns {Promise<{audioBuffer: ArrayBuffer, timings: Array, duration: number, wordCount: number}>}
 */
const processChunk = async ( text, voiceId, chunkStartTime, globalWordIndex ) => {
    const words = text.split( /\s+/ ).filter( w => w.length > 0 );

    let wavBlob;
    if ( voiceId.startsWith( 'kokoro-' ) ) {
        const voiceKey = voiceId.replace( 'kokoro-', '' );
        const tts      = await loadKokoroTTS();
        const audioObj = await tts.generate( text, { voice: voiceKey } );
        wavBlob        = audioObj.toBlob();
    } else {
        const tts = await loadPiperTTS( voiceId );
        wavBlob   = await tts.predict( { text, voiceId } );
    }

    const audioBuffer = await wavBlob.arrayBuffer();
    const duration    = getWavDuration( audioBuffer );

    // Proportional word-timing estimation
    const totalSyllables = words.reduce( ( s, w ) => s + estimateSyllables( w ), 0 );
    const totalPauseTime = words.reduce( ( s, w ) => s + estimatePauseAfter( w ), 0 );
    const speakingTime   = Math.max( 0, duration - totalPauseTime );

    const timings = [];
    let currentOffset = chunkStartTime;
    let idx           = globalWordIndex;

    for ( const word of words ) {
        const syllables   = estimateSyllables( word );
        const wordDuration = totalSyllables > 0
            ? ( syllables / totalSyllables ) * speakingTime
            : 0;
        const pauseAfter = estimatePauseAfter( word );

        timings.push( {
            word,
            index: idx++,
            start: currentOffset,
            end: currentOffset + wordDuration
        } );
        currentOffset += wordDuration + pauseAfter;
    }

    return {
        audioBuffer,
        timings,
        duration,
        wordCount: words.length
    };
};

// ============================================
// MESSAGE HANDLER
// ============================================

self.onmessage = async ( e ) => {
    const { type, data } = e.data;

    try {
        switch ( type ) {
            case 'init': {
                const isKokoro = data?.voiceId?.startsWith( 'kokoro-' );

                if ( isKokoro ) {
                    await loadKokoroTTS( ( prog ) => {
                        if ( prog.status === 'progress' ) {
                            self.postMessage( {
                                type: 'downloadProgress',
                                progress: {
                                    status:   'progress',
                                    file:     prog.file,
                                    progress: prog.progress
                                }
                            } );
                        } else if ( prog.status === 'ready' ) {
                            self.postMessage( {
                                type:     'downloadProgress',
                                progress: { status: 'ready' }
                            } );
                        }
                    } );
                } else {
                    // Piper: download the voice model so it's cached
                    const tts = await loadPiperTTS( data.voiceId );
                    if ( data.voiceId !== 'de_DE-eva_k-x_low' ) await tts.download( data.voiceId, ( prog ) => {
                        if ( prog.total ) {
                            self.postMessage( {
                                type: 'downloadProgress',
                                progress: {
                                    status:   'progress',
                                    progress: ( prog.loaded / prog.total ) * 100
                                }
                            } );
                        }
                    } );
                }

                self.postMessage( { type: 'ready' } );
                break;
            }

            case 'processChunk': {
                const result = await processChunk(
                    data.text,
                    data.voiceId,
                    data.chunkStartTime   || 0,
                    data.globalWordIndex  || 0
                );
                // Transfer the ArrayBuffer to avoid copying (~2x speedup)
                self.postMessage(
                    { type: 'chunkComplete', result, chunkIndex: data.chunkIndex },
                    [ result.audioBuffer ]
                );
                break;
            }

            default:
                self.postMessage( { type: 'error', error: `Unknown message type: ${type}` } );
        }
    } catch ( error ) {
        self.postMessage( { type: 'error', error: error.message } );
    }
};
