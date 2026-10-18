#!/usr/bin/env python3
"""Check public metadata, schema and exact HTML body preservation."""
import html
from html.parser import HTMLParser
import json
import re
import time
from urllib.request import Request, urlopen

class Head(HTMLParser):
    def __init__(self):
        super().__init__()
        self.metas = []
        self.canonicals = []
    def handle_starttag(self, tag, attrs):
        attrs = dict(attrs)
        if tag == 'meta':
            self.metas.append(attrs)
        if tag == 'link' and attrs.get('rel') == 'canonical':
            self.canonicals.append(attrs.get('href'))

def fetch(url):
    request = Request(url+'?odpc_search_check='+str(time.time_ns()), headers={'Cache-Control':'no-cache', 'User-Agent':'ODPC-Search-Metadata-QA/1.0'})
    with urlopen(request, timeout=35) as response:
        if response.status != 200:
            raise AssertionError('Non-200 response')
        return response.read(1000000).decode('utf-8')

def body(source):
    return source[source.lower().index('<body'):]

def validate(url, source, previous=None):
    head_html = source[:source.lower().index('</head>')]
    head = Head()
    head.feed(head_html)
    assert head.canonicals == [url], (url, head.canonicals)
    assert len(re.findall(r'<title\b', head_html, re.I)) == 1
    descriptions = [m['content'] for m in head.metas if m.get('name') == 'description']
    assert len(descriptions) == 1 and 65 <= len(descriptions[0]) <= 180, descriptions
    assert not any('noindex' in m.get('content','') for m in head.metas if m.get('name') == 'robots')
    assert len([m for m in head.metas if m.get('property') == 'og:url' and m.get('content') == url]) == 1
    schema = re.findall(r'<script type="application/ld\+json" id="odpc-search-metadata-1">(.*?)</script>', head_html, re.S)
    assert len(schema) == 1
    graph = json.loads(schema[0])['@graph']
    assert {'Organization','Person','WebSite','WebPage'} <= {node['@type'] for node in graph}
    assert any(node['@type'] == 'WebPage' and node['url'] == url for node in graph)
    assert all(node.get('logo', {}).get('url', '').startswith('https://odpc.de/') for node in graph if node['@type'] == 'Organization')
    if previous is not None:
        assert body(source) == body(previous), 'Public body changed during metadata deployment'
