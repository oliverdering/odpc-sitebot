"""Regression: QA query strings in pagination must not mask genuine body changes."""
import ast
import re
import urllib.parse
from html import escape, unescape
from pathlib import Path

source=Path(__file__).with_name('deploy_search_completion.py').read_text()
function=next(n for n in ast.parse(source).body if isinstance(n,ast.FunctionDef) and n.name=='content')
env={'re':re,'urllib':urllib,'escape':escape,'unescape':unescape}
exec(compile(ast.Module(body=[function],type_ignores=[]),'content-regression','exec'),env)
normalize=env['content']
baseline='<body><a href="https://odpc.de/category/infos-news/page/2">2</a></body>'
probe=baseline.replace('/page/2','/page/2?odpc_search_snapshot=123')
assert normalize(baseline)==normalize(probe)
assert normalize(baseline)!=normalize(baseline.replace('/page/2','/page/3'))
assert normalize(baseline)!=normalize(baseline.replace('>2<','>3<'))
assert normalize(baseline)!=normalize(baseline.replace('/page/2','/page/2?actual_filter=x'))
print('PASS: QA pagination parameters only; real content and functional link changes still detected')
