"""Run reviewed additive import packages on the verified IONOS database."""
import argparse
import json
from pathlib import Path

from drol_firmware import load_documents
from drol_graph_payload import load_graph_payload
from drol_reviewed_facts import DATABASE, load_facts

SCRIPTS = Path(__file__).resolve().parent
MODULES = ("drol_graph_import.php", "drol_reviewed_facts.php", "drol_firmware_import.php", "drol_reviewed_links.php")
HEADER = """<?php
declare(strict_types=1);
header('Cache-Control: no-store');
header('Content-Type: application/json');
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' ||
    !hash_equals('__TOKEN_HASH__', hash('sha256', $_SERVER['HTTP_X_DROL_PROBE'] ?? ''))) {
    http_response_code(404);
    exit;
}
"""
BODY = """
try {
    $in = json_decode(file_get_contents('php://input', false, null, 0, 1000000), true, 64, JSON_THROW_ON_ERROR);
    $pdo = new PDO('mysql:host='.$in['host'].';dbname='.$in['database'].';charset=utf8mb4',
        $in['username'], $in['password'], [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES=>false]);
    if ((int)$pdo->query("SELECT GET_LOCK('drol_reviewed_import', 5)")->fetchColumn() !== 1) {
        throw new RuntimeException('import_busy');
    }
    $apply = $in['mode'] === 'apply';
    $graph = $in['graph'];
    if ($in['mode'] === 'schema') {
        drol_prepare_graph($pdo, $graph['statements']);
        $result = ['schema'=>'ready'];
    } else {
        $result = [];
        $result['facts'] = drol_reviewed_facts($pdo, $in['facts'], $apply);
        $result['graph'] = drol_import_graph($pdo, $graph['entities'], $apply);
        $pdo->beginTransaction();
        $result['firmware'] = importFirmware($pdo, $in['firmware']);
        $result['reviewed_links'] = drol_reviewed_links($pdo, $in['links'], $in['revision']);
        if ($apply) {
            $pdo->commit();
        } else {
            $pdo->rollBack();
        }
    }
    echo json_encode(['ok'=>true,'result'=>$result], JSON_THROW_ON_ERROR);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    // Never return connection details, SQL or values from a database error.
    $reason = $e instanceof PDOException ? 'database_error_'.$e->getCode() : get_class($e);
    echo json_encode(['ok'=>false,'reason'=>$reason]);
}
"""


def endpoint():
    parts = [HEADER]
    for name in MODULES:
        code = (SCRIPTS / name).read_text().strip()
        if not code.startswith("<?php") or "?>" in code:
            raise ValueError(f"Unexpected PHP module format: {name}")
        parts.append(code.removeprefix("<?php").strip().removeprefix("declare(strict_types=1);"))
    return "\n".join(parts + [BODY])


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--check", action="store_true")
    args = parser.parse_args()
    payload = dict(facts=load_facts(), graph=load_graph_payload(), firmware=load_documents())
    import subprocess
    payload["revision"] = subprocess.check_output(["git","rev-parse","HEAD"], text=True).strip()
    payload["links"] = json.loads((DATABASE / "reviewed-links.json").read_text())
    for item in payload["links"]:
        if item.get("evidence") != "E1" or not item.get("locator") or not item["url"].startswith("https://"):
            raise ValueError("Missing primary-source review")
    php = endpoint()
    if args.check:
        import subprocess
        subprocess.run(["php", "-l"], input=php, text=True, check=True)
        return
    from datetime import datetime, timedelta, timezone
    manifest = json.loads(Path("drol-backup-manifest.json").read_text())
    age = datetime.now(timezone.utc) - datetime.fromisoformat(manifest["created_utc"])
    if manifest.get("restore_verified") is not True or not timedelta(0) <= age < timedelta(minutes=45):
        raise RuntimeError("A fresh, restore-verified backup is required")
    from drol_db_bridge import invoke
    for mode in ("schema", "preview", "apply", "repeat-preview"):
        result = invoke(php, dict(payload, mode=mode))
        if not result.get("ok"):
            raise RuntimeError(f"{mode}: {result.get('reason')}")
        totals = result["result"]
        print(mode, json.dumps(totals), flush=True)
        if mode == "repeat-preview" and (totals["facts"]["planned"] or totals["graph"]["planned"]
                or totals["firmware"]["inserted"] or totals["firmware"]["corroborated"] or totals["reviewed_links"]):
            raise RuntimeError("Repeat preview found outstanding changes")


if __name__ == "__main__":
    main()
