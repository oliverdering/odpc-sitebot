from pathlib import Path
import json,xml.etree.ElementTree as ET
from urllib.parse import quote
p=Path(__file__).resolve().parents[1]/'site-deploy/meinkraut';a=json.loads((p/'content/atlas-base.json').read_text());e=json.loads((p/'content/meinkraut-content.json').read_text());shop=json.loads((p/'content/shop-catalog.json').read_text())
paths=['/','/gewuerze/','/mischungen/','/laender/','/gewuerzshop/','/premium/','/glaeser-etiketten/','/nachfuellen/']
paths += ['/gewuerz/'+quote(i['id'])+'/' for i in a['ingredients']]
paths += ['/mischung/'+quote(i['id'])+'/' for i in e['mixes']+a['recipes']]
paths += ['/land/'+c+'/' for c in e['countries']]
paths += ['/produkt/'+i['slug']+'/' for i in shop['products']]
ET.register_namespace('','http://www.sitemaps.org/schemas/sitemap/0.9');root=ET.Element('{http://www.sitemaps.org/schemas/sitemap/0.9}urlset')
for path in paths:
 u=ET.SubElement(root,'url');ET.SubElement(u,'loc').text='https://meinkraut.odpc.de'+path
ET.ElementTree(root).write(p/'sitemap.xml',encoding='utf-8',xml_declaration=True)
print('Sitemap:',len(paths),'unique URLs')
