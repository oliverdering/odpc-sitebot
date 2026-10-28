#!/usr/bin/env python3
"""Deploy head/sitemap-only SEO fixes, freezing all existing homepages and bodies."""
import concurrent.futures,hashlib,json,posixpath,re,secrets,stat,time,urllib.request,urllib.error
from pathlib import Path
from xml.etree import ElementTree as ET
from deploy_bahnhof_grenzau_bootstrap import connect,read,atomic_write
from deploy_shared_footer import find_roots
from deploy_search_completion import content,expire_target_cache

SOURCE=Path('site-deploy/seo-maintenance/odpc-seo-maintenance.php')
BACKUP=Path('backup-scoped-seo-maintenance')
RESULTS=Path('scoped-seo-maintenance-results.json')
HOME=['https://odpc.de/','https://ai.odpc.de/','https://tante-lenchen.odpc.de/','https://atrium191.de/','https://atrium191.com/?lang=en','https://bahnhof-grenzau.de/','https://ra-justen.de/','https://guenana-bsv.de/']
PAGES=['https://odpc.de/initiq','https://odpc.de/ki-gestuetzte-dienstleistungen-automatisierung','https://odpc.de/leck-im-system','https://ai.odpc.de/connector-einrichten/','https://ai.odpc.de/connector-einrichten/?agent=website','https://ai.odpc.de/start/','https://tante-lenchen.odpc.de/speisekarte/','https://tante-lenchen.odpc.de/tisch-anfragen/','https://tante-lenchen.odpc.de/2026/10/17/hallo-welt/','https://tante-lenchen.odpc.de/author/lenchen_webmaster/','https://tante-lenchen.odpc.de/category/allgemein/']

def fetch(url):
    req=urllib.request.Request(url,headers={'User-Agent':'ODPC-Scoped-SEO-Release/1.0','Cache-Control':'no-cache'})
    try:r=urllib.request.urlopen(req,timeout=40)
    except urllib.error.HTTPError as e:r=e
    with r:return {'status':r.status,'url':r.url,'headers':dict(r.headers),'text':r.read(3000000).decode('utf-8','replace')}

def batch(urls):
    with concurrent.futures.ThreadPoolExecutor(max_workers=5)as ex:return dict(zip(urls,ex.map(fetch,urls)))

def metas(text,name):
    tags=re.findall(r'<meta\b[^>]*>',text,re.I)
    return [re.search(r'\bcontent\s*=\s*([\x22\x27])(.*?)\1',t,re.I|re.S).group(2) for t in tags if re.search(r'\bname\s*=\s*([\x22\x27])'+re.escape(name)+r'\1',t,re.I) and re.search(r'\bcontent\s*=\s*([\x22\x27])(.*?)\1',t,re.I|re.S)]

def canonical(text):
    tags=re.findall(r'<link\b[^>]*>',text,re.I)
    return [re.search(r'\bhref\s*=\s*([\x22\x27])(.*?)\1',t,re.I|re.S).group(2)for t in tags if re.search(r'\brel\s*=\s*([\x22\x27])canonical\1',t,re.I)and re.search(r'\bhref\s*=\s*([\x22\x27])(.*?)\1',t,re.I|re.S)]

def home_signature(text):
    # No theme/layout or homepage metadata may change.
    head=text.split('</head>',1)[0]
    title=re.search(r'<title\b[^>]*>.*?</title>',head,re.I|re.S)
    assets=re.findall(r'<(?:link|style|script)\b.*?(?:</(?:style|script)>|>)',head,re.I|re.S)
    return [content(text),title.group(0)if title else None,metas(head,'description'),canonical(head),assets]

def xml_urls(url):
    response=fetch(url)
    if response['status']!=200:raise RuntimeError('Sitemap unavailable: '+url)
    root=ET.fromstring(response['text'])
    return [el.text for el in root.iter()if el.tag.endswith('}loc')or el.tag=='loc']

def dish_inventory():
    url='https://tante-lenchen.odpc.de/wp-sitemap-posts-tl_dish-1.xml'
    response=fetch(url)
    if response['status']==200:
        root=ET.fromstring(response['text'])
        return [el.text for el in root.iter()if el.tag.endswith('}loc')or el.tag=='loc']
    if response['status'] not in (404,410):
        raise RuntimeError('Dish inventory unavailable')
    # Once this release excludes the provider, retain its observed inventory
    # for safe re-runs. Every URL is checked live before deploying.
    slugs='bayerischer-wurstsalat bunter-salat-mit-putenstreifen champignonschnitzel kaeseliebhaberteller kaesesalat-mit-mandarinen kleiner-salat ofenkartoffel-mit-fruehlingsquark ofenkartoffel-mit-gebratenen-scampi ofenkartoffel-mit-putenstreifen pikanter-rindfleischsalat reibekuchen-mit-lachs salat-mit-riesengarnelen salat-mit-schinken-und-ananas salat-mit-ziegenkaese schnitzel-teufelszauber schnitzel-wiener-art zwiebelschnitzel'
    return ['https://tante-lenchen.odpc.de/tl_dish/'+s+'/' for s in slugs.split()]

def find_primary_roots(sf,known):
    """Bind additional production domains to accessible roots by a temporary text probe.

    No configuration or credential files are read. The probe contains only a
    random verification string and is removed in every path.
    """
    found={};candidates={known['atrium191-odpc'],'/Bahnhof-Grenzau'}
    for root in candidates:
        try:sf.stat(root+'/wp-config.php')
        except FileNotFoundError:continue
        name='odpc-seo-origin-'+secrets.token_hex(12)+'.txt'
        marker='ODPC SEO origin '+secrets.token_hex(20)
        path=root+'/'+name
        try:
            atomic_write(sf,path,marker.encode())
            for host in ['atrium191.de','atrium191.com','bahnhof-grenzau.de']:
                r=fetch('https://'+host+'/'+name)
                if r['status']==200 and r['text']==marker:found[host]=root
        finally:
            try:sf.remove(path)
            except FileNotFoundError:pass
    return found

def verify(before,dishes,additional):
    after=batch(list(before))
    for url,old in before.items():
        new=after[url]
        if new['status']!=200:raise RuntimeError('Page unavailable: '+url)
        if content(new['text'])!=content(old['text']):raise RuntimeError('Visible content changed: '+url)
        if url in HOME and home_signature(new['text'])!=home_signature(old['text']):raise RuntimeError('Protected homepage changed: '+url)
    for url in ['https://odpc.de/leck-im-system','https://tante-lenchen.odpc.de/2026/10/17/hallo-welt/','https://tante-lenchen.odpc.de/author/lenchen_webmaster/','https://tante-lenchen.odpc.de/category/allgemein/']+dishes:
        if not any('noindex'in v for v in metas(after[url]['text'],'robots')):raise RuntimeError('Noindex missing: '+url)
    for url in ['https://odpc.de/initiq','https://odpc.de/ki-gestuetzte-dienstleistungen-automatisierung']:
        if len(metas(after[url]['text'],'description'))!=1 or 'Koblenz'not in after[url]['text'].split('</head>',1)[0]:raise RuntimeError('Service metadata missing: '+url)
    for url in ['https://ai.odpc.de/connector-einrichten/','https://ai.odpc.de/connector-einrichten/?agent=website']:
        if canonical(after[url]['text'])!=['https://ai.odpc.de/']:raise RuntimeError('Connector canonical missing: '+url)
    if 'noindex'in ' '.join(metas(after['https://tante-lenchen.odpc.de/speisekarte/']['text'],'robots')):raise RuntimeError('Menu accidentally excluded')
    site=xml_urls('https://tante-lenchen.odpc.de/wp-sitemap.xml')
    if any('/tl_dish' in u or 'tl_dish' in u or 'taxonomies' in u or 'users' in u for u in site):raise RuntimeError('Thin-view sitemap provider retained')
    pages=xml_urls('https://tante-lenchen.odpc.de/wp-sitemap-posts-page-1.xml')
    if 'https://tante-lenchen.odpc.de/'not in pages or 'https://tante-lenchen.odpc.de/speisekarte/'not in pages:raise RuntimeError('Useful menu or homepage removed from sitemap')
    for u in site:
        if 'posts-post-'in u and any('hallo-welt'in x for x in xml_urls(u)):raise RuntimeError('Test post remains in sitemap')
    if any('/leck-im-system'in u for u in xml_urls('https://odpc.de/page-sitemap.xml')):raise RuntimeError('Empty page retained in ODPC sitemap')
    for host in additional:
        if host not in ('atrium191.de','atrium191.com'):continue
        slug='/wohnungen/we01/'if host.endswith('.de')else'/apartments/we01/'
        row=after['https://'+host+slug]
        if len(metas(row['text'],'description'))!=1 or 'WE01'not in metas(row['text'],'description')[0]:raise RuntimeError('Specific apartment metadata missing')
        if 'hreflang='not in row['text'].split('</head>',1)[0]:raise RuntimeError('Language links lost')
    return {'protected_homepages_verified':len(HOME),'unchanged_bodies_verified':len(before),'dish_entries_preserved':len(dishes),'empty_search_views_noindex':len(dishes)+4,'connector_variants_verified':2,'sitemaps_verified':True}

def main():
    dishes=dish_inventory()
    if not 1<=len(dishes)<=40:raise RuntimeError('Unexpected dish inventory')
    targets=list(dict.fromkeys(HOME+PAGES+dishes))
    before=batch(targets)
    if any(r['status']!=200 for r in before.values()):raise RuntimeError('Existing page unavailable; no deployment')
    BACKUP.mkdir(exist_ok=True)
    (BACKUP/'public-before.json').write_text(json.dumps(before,ensure_ascii=False))
    transport,sf=connect();changed=[];roots={};additional={}
    try:
        known=find_roots(sf)
        roots={'odpc.de':'/clickandbuilds/Dering','ai.odpc.de':known['helpermonkey-business-v34-child'],'tante-lenchen.odpc.de':known['tante-lenchen']}
        additional=find_primary_roots(sf,known)
        roots.update(additional)
        if any(h in additional for h in ['atrium191.de','atrium191.com']):
            extra=['https://'+h+('/wohnungen/we01/'if h.endswith('.de')else'/apartments/we01/')for h in additional if h.startswith('atrium191.')]
            before.update(batch(extra));targets=list(before)
        for host,root in roots.items():
            if host=='bahnhof-grenzau.de':continue # No verified, scoped Bahnhof correction in this patch.
            sf.stat(root+'/wp-config.php')
            folder=root+'/wp-content/mu-plugins'
            try:sf.mkdir(folder)
            except OSError:
                if not stat.S_ISDIR(sf.lstat(folder).st_mode):raise
            path=folder+'/odpc-seo-maintenance.php'
            if any(p==path for p,o in changed):continue
            old=read(sf,path)
            backup=BACKUP/host;backup.mkdir(exist_ok=True)
            (backup/'previous-module.json').write_text(json.dumps({'path':path,'previous':old.decode()if old is not None else None}))
            atomic_write(sf,path,SOURCE.read_bytes());changed.append((path,old))
        for host,root in roots.items():
            if host=='bahnhof-grenzau.de':continue
            urls=[u for u in targets if u.startswith('https://'+host+'/')and u not in HOME]
            expire_target_cache(sf,root,urls)
        # Two warm reads avoid first-cache-build/minification fluctuations.
        batch(targets);result=verify(before,dishes,additional)
        result.update(status='verified',modules_written=len(changed),production_domains_bound=sorted(additional),production_domains_without_bound_access=sorted(set(['atrium191.de','atrium191.com'])-set(additional)),content_records_deleted=0,theme_files_changed=0)
        RESULTS.write_text(json.dumps(result,indent=2));print(json.dumps(result),flush=True)
    except BaseException as error:
        for path,old in reversed(changed):
            if old is None:sf.remove(path)
            else:atomic_write(sf,path,old)
        for host,root in roots.items():
            if host=='bahnhof-grenzau.de':continue
            try:expire_target_cache(sf,root,[u for u in targets if u.startswith('https://'+host+'/')and u not in HOME])
            except Exception:pass
        RESULTS.write_text(json.dumps({'status':'rolled_back','restored_modules':len(changed),'reason':str(error)},indent=2));raise
    finally:sf.close();transport.close()

if __name__=='__main__':main()
