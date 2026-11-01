# VOX candidate – 2ix2

Candidate page: https://www.2ix2.com/vox/
Status: **Research only – not production-ready**

User confirmed that VOX plays when visiting this page on their Android device.

Public HTML/search results confirm the page exists but do not expose an independently verified, embeddable and authorized HLS/DASH media URL. Direct HTML fetch from the current runtime failed DNS resolution. The provider's copyright notice says it embeds third-party streams and does not own the streams; it does not grant ODPC redistribution or embedding rights. See https://www.2ix2.com/copyright/.

Other publicly listed VOX candidate `https://viamotionhsi.netplus.ch/live/eds/vox/browser-HLS8/vox.m3u8` is geo-blocked according to a third-party playlist and is **not validated for German users or reuse**.

Next steps before adding VOX to the native ODPC TV channel grid:
1. Identify legitimate provider-authorized player/embed URL or publicly licensed direct HLS stream.
2. Check embed permissions (frame-ancestors/X-Frame-Options), CORS, DRM, tokens, geo-restrictions, playback, and legal terms.
3. Test on an isolated branch and browser; do not edit the 15-channel stable release.
4. If direct playback cannot be authorized, consider a separately labelled external link to the 2ix2 page, without implying it is hosted by ODPC.

**Frozen baseline:** release/odpc-tv-v0.9.8-stable. No changes to `odpc-tv-lab.php` made in this research step.
