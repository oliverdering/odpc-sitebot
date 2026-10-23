"""Check research staging without changing or approving production recipes."""
import argparse
import json
import math
import re
from collections import Counter
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
# Atlas allergen arrays are incomplete; preserve well-defined ingredient identities.
IDENTITY_ALLERGENS = {
    'senfkorner': {'Senf'}, 'senfsaat-schwarz': {'Senf'},
    'senfsaat-gelb': {'Senf'}, 'senfpulver': {'Senf'},
    'selleriesaat': {'Sellerie'}, 'gerostete-erdnusse': {'Erdnüsse'},
    'sesam-wei': {'Sesam'}, 'sesam-schwarz': {'Sesam'},
}


def validate(package, atlas, extra):
    errors, blocked, seen = [], [], set()
    ingredient_map = {x['id']: x for x in atlas['ingredients']}
    countries = {x['code'] for x in atlas['countries']}
    active_ids = {x['id'] for x in atlas['recipes'] + extra['mixes']}
    proposals = {}
    for proposal in package.get('ingredientProposals', []):
        pid = proposal.get('id', '')
        bundled = proposal.get('status') == 'bundled'
        if not re.fullmatch(r'[a-z0-9]+(?:-[a-z0-9]+)*', pid) or pid in proposals or (pid in ingredient_map) != bundled:
            errors.append(f'{pid}: proposed ingredient ID invalid, duplicated, or already active')
        proposals[pid] = proposal
        if bundled and any(proposal.get(k) != ingredient_map.get(pid, {}).get(k) for k in ['name', 'group', 'form', 'allergens']):
            errors.append(f'{pid}: bundled proposal differs from catalog identity')
        if proposal.get('active') is not False or proposal.get('readyForImport') is not False or proposal.get('status') not in {'proposal_only', 'bundled'}:
            errors.append(f'{pid}: ingredient proposals must remain inactive and unapproved')
        for field in ['name', 'group', 'form', 'reason', 'allergenNote']:
            if not isinstance(proposal.get(field), str) or not proposal[field].strip():
                errors.append(f'{pid}: proposal missing {field}')
        if not isinstance(proposal.get('allergens'), list):
            errors.append(f'{pid}: explicit ingredient allergen assessment required')
        if not proposal.get('sources') or any(not s.get('url', '').startswith('https://') or not s.get('contentChecked') or not s.get('primarySource') for s in proposal['sources']):
            errors.append(f'{pid}: checked primary evidence required for ingredient proposal')
        if not proposal.get('notEquivalentTo') or any(i not in ingredient_map for i in proposal['notEquivalentTo']):
            errors.append(f'{pid}: rejected substitution must reference actual atlas ingredient(s)')
    if package.get('schemaVersion') != 1 or package.get('productionImport') is not False:
        errors.append('Research package must use schema 1 and productionImport=false')
    records = package.get('candidates', [])
    if not isinstance(records, list) or not records:
        return errors + ['Nonempty candidates array required'], blocked
    for record in records:
        rid = record.get('id', '')
        if not re.fullmatch(r'mk-research-[a-z0-9-]+', rid) or rid in seen or rid in active_ids:
            errors.append(f'{rid}: invalid, duplicate, or already active ID')
        seen.add(rid)
        for key in ['name', 'kind', 'description', 'editorialType', 'reviewStatus', 'application', 'serving']:
            if not isinstance(record.get(key), str) or not record[key].strip():
                errors.append(f'{rid}: missing {key}')
        if not record.get('countries') or any(c not in countries for c in record['countries']):
            errors.append(f'{rid}: unsupported country; regions are separate')
        if not record.get('ingredients'):
            errors.append(f'{rid}: no ingredients')
        unresolved = []
        derived_allergens = set()
        for part in record.get('ingredients', []):
            amount = part.get('amount')
            if isinstance(amount, bool) or not isinstance(amount, (int, float)) or not math.isfinite(amount) or amount <= 0:
                errors.append(f'{rid}: invalid quantity for {part.get("sourceName")}')
            if part.get('unit') not in {'g', 'TL', 'EL', 'ml', 'Stück', 'Tasse'}:
                errors.append(f'{rid}: unsupported or missing unit')
            iid = part.get('ingredient')
            proposed = part.get('proposedIngredient')
            if proposed is not None:
                if iid is not None or proposed not in proposals:
                    errors.append(f'{rid}: proposal must resolve to a staged ingredient and cannot masquerade as active')
                else:
                    derived_allergens.update(proposals[proposed].get('allergens', []))
            if iid is None:
                if not part.get('mappingIssue'):
                    errors.append(f'{rid}: unresolved ingredient needs an explicit mappingIssue')
                unresolved.append(part.get('sourceName'))
            elif iid not in ingredient_map:
                errors.append(f'{rid}: unknown ingredient ID {iid}; do not guess IDs')
            else:
                derived_allergens.update(ingredient_map[iid].get('allergens', []))
                derived_allergens.update(IDENTITY_ALLERGENS.get(iid, set()))
        assessment = record.get('allergenAssessment', {})
        if record.get('importStatus') == 'live_verified':
            imported_id = record.get('importedRecipeId')
            if imported_id not in active_ids or imported_id != record.get('bundledRecipeId'):
                errors.append(f'{rid}: imported recipe must resolve to its catalog ID')
        if not derived_allergens.issubset(set(assessment.get('known', []))):
            errors.append(f'{rid}: missing known ingredient allergen(s)')
        norm = record.get('normalization100g')
        all_grams = bool(record.get('ingredients')) and all(p.get('unit') == 'g' for p in record['ingredients'])
        if record.get('batchGrams') is not None:
            if not all_grams or not math.isclose(sum(p['amount'] for p in record['ingredients']), record['batchGrams'], abs_tol=1e-8):
                errors.append(f'{rid}: batchGrams must equal the selected weighed inputs')
        if norm is not None:
            if not all_grams:
                errors.append(f'{rid}: cannot normalize volumes to grams without evidence')
            else:
                inputs = record['ingredients']
                preview = norm.get('ingredients', [])
                expected_factor = 100 / sum(p['amount'] for p in inputs)
                if not math.isclose(norm.get('factor', 0), expected_factor, rel_tol=1e-10):
                    errors.append(f'{rid}: incorrect normalization factor')
                if len(preview) != len(inputs):
                    errors.append(f'{rid}: incomplete normalization')
                for original, scaled in zip(inputs, preview):
                    if scaled.get('sourceName') != original.get('sourceName') or scaled.get('ingredient') != original.get('ingredient') or scaled.get('proposedIngredient') != original.get('proposedIngredient') or scaled.get('unit') != 'g' or not math.isclose(scaled.get('amount', -1), original['amount'] * expected_factor, rel_tol=1e-10):
                        errors.append(f'{rid}: invalid normalized ingredient')
                if not math.isclose(sum(p.get('amount', 0) for p in preview), 100, abs_tol=1e-8):
                    errors.append(f'{rid}: normalized inputs must total 100 g')
        sources = record.get('sources', [])
        if not sources or any(not s.get('url', '').startswith('https://') or not s.get('title') or not s.get('author') or not re.fullmatch(r'\d{4}-\d{2}-\d{2}', s.get('accessedAt', '')) for s in sources):
            errors.append(f'{rid}: complete HTTPS source metadata required')
        if not record.get('methodSteps') or not record.get('storageNotes'):
            errors.append(f'{rid}: preparation and storage notes required')
        if unresolved or record.get('openIssues') or assessment.get('complete') is not True:
            if record.get('readyForImport') is not False:
                errors.append(f'{rid}: unresolved research cannot be marked import-ready')
        if record.get('readyForImport') is not True:
            blocked.append({'id': rid, 'unmapped': unresolved, 'issues': record.get('openIssues', []), 'reason': 'already_imported' if record.get('importStatus') == 'live_verified' else 'research_pending'})
    return errors, blocked


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('package', type=Path)
    parser.add_argument('--require-ready', action='store_true')
    args = parser.parse_args()
    content = REPO / 'site-deploy/meinkraut/content'
    atlas = json.loads((content / 'atlas-base.json').read_text())
    extra = json.loads((content / 'meinkraut-content.json').read_text())
    package = json.loads(args.package.read_text())
    errors, blocked = validate(package, atlas, extra)
    daily, duplicate_ids = Counter(), set()
    seen = set()
    for file in (REPO / 'research/meinkraut').glob('*/candidates.json'):
        batch = json.loads(file.read_text())
        for candidate in batch.get('candidates', []):
            key = candidate.get('id')
            if key in seen:
                duplicate_ids.add(key)
            seen.add(key)
            daily[batch.get('createdAt')] += 1
    if duplicate_ids:
        errors.append('Candidate IDs duplicated in research batches')
    if daily[package.get('createdAt')] > 10:
        errors.append('Daily research limit exceeded: more than ten candidates')
    report = {'status': 'FAIL' if errors else 'PASS_STAGING', 'productionImport': False, 'candidateCount': len(package.get('candidates', [])), 'createdToday': daily[package.get('createdAt')], 'errors': errors, 'blockedFromImport': blocked}
    print(json.dumps(report, ensure_ascii=False, indent=2))
    return 1 if errors else 2 if args.require_ready and blocked else 0


if __name__ == '__main__':
    raise SystemExit(main())
