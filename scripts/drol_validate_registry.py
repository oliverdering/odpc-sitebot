#!/usr/bin/env python3
"""Validate the editorial registry offline; does not certify factual accuracy."""
import json
import re
import sys
from pathlib import Path
from urllib.parse import urlsplit


def validate(data):
    errors = []
    if not isinstance(data, dict) or data.get('schema_version') != '1.0':
        return ['Expected registry object with schema_version 1.0']
    models = data.get('models')
    if not isinstance(models, list) or not models:
        return ['models must be a non-empty list']
    ids, identities = set(), set()
    for index, model in enumerate(models):
        label = f'model[{index}]'
        if not isinstance(model, dict):
            errors.append(f'{label}: expected object')
            continue
        ident = model.get('id')
        if not isinstance(ident, str) or not re.fullmatch(r'[a-z0-9]+(?:-[a-z0-9]+)*', ident):
            errors.append(f'{label}: invalid id')
        elif ident in ids:
            errors.append(f'{label}: duplicate id {ident}')
        else:
            ids.add(ident)
        for key in ('manufacturer', 'name', 'category'):
            if not isinstance(model.get(key), str) or not model[key].strip():
                errors.append(f'{label}: missing {key}')
        if all(isinstance(model.get(k), str) for k in ('manufacturer', 'name')):
            identity = tuple(model[k].strip().casefold() for k in ('manufacturer', 'name'))
            if identity in identities:
                errors.append(f'{label}: duplicate manufacturer/model identity; resolve variants explicitly')
            identities.add(identity)
        sources = model.get('sources')
        if not isinstance(sources, list) or not sources:
            errors.append(f'{label}: missing sources')
        else:
            for source in sources:
                if not isinstance(source, str):
                    errors.append(f'{label}: source must be an HTTPS URL')
                    continue
                try:
                    url = urlsplit(source)
                    valid = url.scheme == 'https' and bool(url.hostname) and not url.username and not url.password
                except ValueError:
                    valid = False
                if not valid:
                    errors.append(f'{label}: source must be an HTTPS URL without credentials')
        facts = model.get('facts')
        if not isinstance(facts, dict):
            errors.append(f'{label}: facts must be an object')
        elif facts:
            errors.append(f'{label}: populated facts require a reviewed evidence contract before publication')
        capabilities = model.get('capabilities')
        if not isinstance(capabilities, dict):
            errors.append(f'{label}: capabilities must be an object')
            continue
        for name, claim in capabilities.items():
            if not isinstance(claim, dict):
                errors.append(f'{label}: {name} must be an object')
                continue
            if claim.get('assessment') != 'UNKNOWN' or claim.get('evidence') != 'E0':
                errors.append(f'{label}: {name} requires a reviewed claim-level evidence contract before publication')
    return errors


def main():
    path = Path(sys.argv[1]) if len(sys.argv) == 2 else Path('site-deploy/drol.odpc.de/database/model-schema.json')
    try:
        data = json.loads(path.read_text(encoding='utf-8'))
        errors = validate(data)
    except (OSError, UnicodeError, json.JSONDecodeError) as exc:
        print(f'Registry could not be read: {exc}', file=sys.stderr)
        return 1
    for error in errors:
        print('ERROR:', error, file=sys.stderr)
    if errors:
        return 1
    print(f"Registry structure OK: {len(data['models'])} editorial seeds. Facts and compatibility are not verified.")
    return 0


if __name__ == '__main__':
    sys.exit(main())
