"""Activate a checked append-only catalog release through an ephemeral authenticated endpoint."""
import base64, hashlib, hmac, json, os, secrets, subprocess
from pathlib import Path
from urllib.error import HTTPError
from urllib.request import Request, urlopen
import paramiko
from deploy_helpermonkey_v4 import HOST, USER, PIN

REPO = Path(__file__).resolve().parents[1]
SITE = REPO / 'site-deploy/meinkraut'
ROOT = '/MeinKraut'
URL = 'https://meinkraut.odpc.de'


def release_payload():
    manifest = json.loads((REPO / 'deployment/meinkraut-catalog-release.json').read_text())
    baseline = manifest['baselineRevision']
    values = {}
    for key, file in [('Atlas', 'atlas-base.json'), ('Extra', 'meinkraut-content.json')]:
        rel = 'site-deploy/meinkraut/content/' + file
        values[key.lower()] = json.loads((REPO / rel).read_text())
        values['baseline' + key] = json.loads(subprocess.check_output(['git', 'show', baseline + ':' + rel], cwd=REPO))
    return dict(manifest=manifest, **values)


def main():
    data = release_payload()
    token = secrets.token_hex(32)
    endpoint = '/mk-catalog-' + secrets.token_hex(20) + '.php'
    guard = hashlib.sha256(token.encode()).hexdigest()
    library = (REPO / 'scripts/meinkraut_catalog_release.php').read_text().split('<?php', 1)[1]
    library = library.replace('declare(strict_types=1);', '', 1)
    wrapper = "<?php\ndeclare(strict_types=1);\n"
    wrapper += "if(($_SERVER['REQUEST_METHOD']??'')!=='POST'||!hash_equals('" + guard + "',hash('sha256',$_SERVER['HTTP_X_MK_RELEASE_TOKEN']??''))){http_response_code(404);exit;}\n"
    wrapper += "header('Content-Type: application/json');header('Cache-Control: no-store');define('MK_RELEASE_INTERNAL',true);define('MK_INTERNAL',true);require __DIR__.'/server/database.php';\n"
    wrapper += library
    wrapper += "\ntry{$q=json_decode(file_get_contents('php://input'),true,512,JSON_THROW_ON_ERROR);if(($q['action']??'')==='ping'){echo '{\"status\":\"ready\"}';exit;}$pdo=mk_database();if(!$pdo)throw new RuntimeException('database_unavailable');echo json_encode(mk_catalog_release($pdo,$q),JSON_THROW_ON_ERROR);}catch(Throwable $e){http_response_code(409);echo json_encode(['status'=>'rejected','code'=>in_array($e->getMessage(),['baseline_changed','release_changed','expected_one_active_release'],true)?$e->getMessage():'validation_or_database_error']);}\n"
    tr = paramiko.Transport((HOST, 22)); tr.start_client(timeout=25)
    try:
        actual = 'SHA256:' + base64.b64encode(hashlib.sha256(tr.get_remote_server_key().asbytes()).digest()).decode().rstrip('=')
        if not hmac.compare_digest(actual, PIN):
            raise RuntimeError('Host key mismatch')
        tr.auth_password(USER, os.environ['ODPC_SFTP_PASSWORD'])
        sf = paramiko.SFTPClient.from_transport(tr)
        uploaded = False
        try:
            with sf.open(ROOT + endpoint, 'wx') as remote:
                uploaded = True
                remote.write(wrapper)
            # The web server reads the entry script before PHP runs its token
            # guard. Database credentials stay in the existing private config.
            sf.chmod(ROOT + endpoint, 0o644)

            def request(payload):
                req = Request(URL + endpoint, data=json.dumps(payload, ensure_ascii=False).encode(), headers={'Content-Type': 'application/json', 'X-MK-Release-Token': token}, method='POST')
                try:
                    with urlopen(req, timeout=90) as r:
                        return json.load(r)
                except HTTPError as exc:
                    try:
                        body = json.loads(exc.read())
                    except (ValueError, UnicodeError):
                        body = {'code': 'http_' + str(exc.code)}
                    raise RuntimeError('Catalog operation rejected: ' + body.get('code', 'unknown')) from None

            # A token-less caller cannot read or activate releases.
            try:
                with urlopen(Request(URL + endpoint, data=b'{}', method='POST'), timeout=25) as r:
                    raise RuntimeError('Endpoint accepted unauthenticated request')
            except HTTPError as exc:
                if exc.code != 404:
                    raise RuntimeError('Unexpected unauthenticated response') from None
            if request({'action': 'ping'}).get('status') != 'ready':
                raise RuntimeError('Authenticated catalog endpoint unavailable')
            plan = request(dict(data, action='plan'))
            print('CATALOG PLAN', json.dumps(plan))
            activated = False
            try:
                result = request(dict(data, action='activate', expectedKey=plan['previousKey']))
                activated = result['status'] == 'activated'
                expected = data['manifest']['expectedTarget']
                with urlopen(URL + '/health.php?catalog=' + result['releaseKey'][:16], timeout=35) as r:
                    health = json.load(r)
                if health.get('catalog') != 'database' or any(health.get(k) != v for k, v in expected.items()):
                    raise RuntimeError('Live database catalog mismatch')
                from urllib.parse import quote
                for rid in data['manifest']['addedRecipes']:
                    with urlopen(URL + '/mischung/' + quote(rid) + '/', timeout=35) as r:
                        body = r.read().decode()
                    recipe = next(x for x in data['atlas']['recipes'] if x['id'] == rid)
                    if recipe['sources'][0]['url'] not in body or recipe['sources'][0]['author'] not in body:
                        raise RuntimeError('Published source attribution missing')
                for iid in data['manifest']['addedIngredients']:
                    with urlopen(URL + '/gewuerz/' + quote(iid) + '/', timeout=35) as r:
                        if b'<h1' not in r.read():
                            raise RuntimeError('New ingredient route missing')
                print('CATALOG VERIFIED', json.dumps(dict(result, catalog=health['catalog'])))
            except Exception:
                if activated or (plan['status'] == 'planned' and request(dict(data, action='plan'))['status'] == 'already_active'):
                    print('CATALOG ROLLBACK', json.dumps(request({'action': 'rollback', 'expectedKey': plan['releaseKey'], 'previousKey': plan['previousKey']})))
                raise
        finally:
            if uploaded:
                sf.remove(ROOT + endpoint)
            sf.close()
    finally:
        tr.close()


if __name__ == '__main__':
    main()
