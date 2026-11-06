import os, base64, hashlib, hmac, pathlib, subprocess, tempfile
import paramiko

HOST = 'home119567219.1and1-data.host'
USER = 'acc1461323326'
PIN = 'SHA256:1gx2w8Rtv3wCgi7Jh8myf/KVd72cRQbow03UP8P095Q'
THEME = '/Lenchen/wp-content/themes/tante-lenchen'
LINK = 'https://www.google.com/maps/place/Caf%C3%A9+Tante+Lenchen/@50.3092538,7.6404574,3a,75y,90t/data=!3m8!1e2!3m6!1sCIABIhAi1Yl0fAfTCBuMyisXuCtw!2e10!3e12!6shttps:%2F%2Flh3.googleusercontent.com%2Fgps-cs-s%2FANWiy9RYuLVSs3lixq6AXJWnAiitl9MLP2l7O5jt8HQEyNpsiXSdmRcjHbmWcHKmqXiQKj9QEymiTza0PvPoAY27VqkKx-VXu72tU2-WsXb4cY8v5cG58byHfZ95MMRFLfiPCat5zOJss2PM7HBw%3Dw224-h298-k-no!7i720!8i960!4m9!3m8!1s0x47be7b5a8e5aee9d:0x541c2d6c7141d5d7!8m2!3d50.3092538!4d7.6404574!10e5!14m1!1BCgIgAQ!16s%2Fg%2F11zzqv_j3z?entry=ttu&g_ep=EgoyMDI2MTEwMy4wIKXMDSoASAFQAw%3D%3D'
images = ['01.webp', '03.webp', '04.webp', '05.webp']
imgs = ''.join('<img src="'+"<?php echo esc_url(get_stylesheet_directory_uri()); ?>"+'/tl-photo-'+p+'" alt="" loading="lazy" decoding="async">' for p in images)
banner = '''<!-- TL-PHOTO-BANNER-v1 -->
<style>.tl-photo-banner{display:block;position:relative;width:min(900px,100%);overflow:hidden;border:1px solid #d7c9b8;border-radius:12px;background:#f6efe7;text-decoration:none}.tl-photo-track{display:flex;width:max-content;animation:tl-photo-scroll 28s linear infinite}.tl-photo-group{display:flex;gap:4px;padding-right:4px}.tl-photo-group img{display:block;width:150px;height:72px;object-fit:cover}.tl-photo-label{position:absolute;right:8px;bottom:7px;padding:5px 10px;border-radius:8px;background:rgba(90,58,46,.9);color:#fff;font:600 13px system-ui,sans-serif}.tl-photo-banner:hover .tl-photo-track,.tl-photo-banner:focus .tl-photo-track{animation-play-state:paused}.tl-photo-banner:focus-visible{outline:3px solid #2d5a82;outline-offset:3px}@keyframes tl-photo-scroll{to{transform:translateX(-50%)}}@media(max-width:600px){.tl-photo-group img{width:118px;height:62px}}@media(prefers-reduced-motion:reduce){.tl-photo-track{animation:none}}</style>
<a class="tl-photo-banner" href="LINK" target="_blank" rel="noopener noreferrer" aria-label="Fotos von Café Tante Lenchen bei Google ansehen"><span class="tl-photo-track"><span class="tl-photo-group">IMAGES</span><span class="tl-photo-group" aria-hidden="true">IMAGES</span></span><span class="tl-photo-label">Fotos bei Google ansehen ↗</span></a>
<!-- /TL-PHOTO-BANNER-v1 -->'''.replace('LINK', LINK.replace('&', '&amp;')).replace('IMAGES', imgs)

tr = paramiko.Transport((HOST, 22)); tr.start_client(timeout=30)
try:
    fp = 'SHA256:' + base64.b64encode(hashlib.sha256(tr.get_remote_server_key().asbytes()).digest()).decode().rstrip('=')
    if not hmac.compare_digest(fp, PIN): raise RuntimeError('Server fingerprint mismatch')
    tr.auth_password(USER, os.environ['ODPC_SFTP_PASSWORD'])
    s = paramiko.SFTPClient.from_transport(tr)
    try:
        with s.open(THEME+'/header.php', 'rb') as f: old = f.read()
        pathlib.Path('header-before-banner.php').write_bytes(old)
        text = old.decode('utf-8')
        marker = '</div><div id="tl-google-translate"'
        if 'TL-PHOTO-BANNER-v1' in text: raise RuntimeError('Banner already installed; refusing duplicate')
        if text.count(marker) != 1: raise RuntimeError('Header anchor missing or ambiguous')
        result = text.replace(marker, '</div>'+banner+'<div id="tl-google-translate"', 1)
        with tempfile.NamedTemporaryFile(suffix='.php') as f:
            f.write(result.encode()); f.flush()
            subprocess.run(['php', '-l', f.name], check=True)
        for name in images:
            target = THEME+'/tl-photo-'+name
            tmp = target+'.new'
            with s.open(tmp, 'wb') as f: f.write(pathlib.Path('lenchen-banner-assets/'+name).read_bytes())
            s.posix_rename(tmp, target)
        with s.open(THEME+'/header.php.banner-new', 'wb') as f: f.write(result.encode())
        s.posix_rename(THEME+'/header.php.banner-new', THEME+'/header.php')
        print('Banner deployed; previous header stored as workflow artifact')
    finally: s.close()
finally: tr.close()
