"""Protect an IONOS snapshot and prove a complete restore on an isolated MariaDB."""
import datetime
import errno
import gzip
import hashlib
import hmac
import json
import os
from pathlib import Path
import re
import secrets
import stat
import subprocess
from graphlib import TopologicalSorter

from cryptography.hazmat.primitives.ciphers.aead import AESGCM
from cryptography.hazmat.primitives.kdf.scrypt import Scrypt


ARCHIVE_DIR = "/.drol-private-backups"
HEADER = b"DROL-DB-AESGCM1\0"
REQUIRED = {
    "drol_attributes", "drol_candidate_facts", "drol_capability_evidence",
    "drol_change_log", "drol_entity_aliases", "drol_firmware_releases",
    "drol_hardware", "drol_hardware_sources", "drol_manufacturers",
    "drol_model_facts", "drol_release_batches", "drol_source_snapshots",
    "drol_source_watch", "drol_sources", "drol_metadata_inbox",
}


def identifier(value):
    if not isinstance(value, str) or not re.fullmatch(r"[a-zA-Z0-9_]+", value):
        raise ValueError("Unsupported SQL identifier")
    return f"`{value}`"


def ordered_tables(snapshot, required=REQUIRED):
    tables = snapshot["backup"]
    indexed = {table["name"]: table for table in tables}
    if len(indexed) != len(tables) or not required.issubset(indexed):
        raise ValueError("Duplicate or missing production table")
    deps = {}
    for name, table in indexed.items():
        quoted = identifier(name)
        ddl = table["ddl"]
        if not ddl.startswith(f"CREATE TABLE {quoted} ("):
            raise ValueError("Unexpected table DDL")
        columns = table["columns"]
        if not columns or len(columns) != len(set(columns)):
            raise ValueError("Duplicate or missing columns")
        for column in columns:
            identifier(column)
        for row in table["rows"]:
            if len(row) != len(columns) or any(
                value is not None and not re.fullmatch(r"(?:[0-9A-F]{2})*", value)
                for value in row
            ):
                raise ValueError("Invalid snapshot row")
        references = re.findall(r"\bREFERENCES\s+`([^`]+)`", ddl, re.I)
        if not set(references).issubset(indexed):
            raise ValueError("Missing foreign key target")
        deps[name] = references
    return [indexed[name] for name in TopologicalSorter(deps).static_order()]


def key_for(salt, db_password, sftp_password):
    material = json.dumps([db_password, sftp_password], separators=(",", ":")).encode()
    return Scrypt(salt=salt, length=32, n=2**16, r=8, p=1).derive(material)


def seal(snapshot, db_password, sftp_password):
    salt, nonce = secrets.token_bytes(16), secrets.token_bytes(12)
    header = HEADER + salt + nonce
    plaintext = gzip.compress(json.dumps(snapshot, ensure_ascii=False).encode(), mtime=0)
    return header + AESGCM(key_for(salt, db_password, sftp_password)).encrypt(nonce, plaintext, header)


def unseal(blob, db_password, sftp_password):
    i = len(HEADER)
    if not blob.startswith(HEADER) or len(blob) < i + 44:
        raise ValueError("Unsupported archive")
    header, ciphertext = blob[:i+28], blob[i+28:]
    key = key_for(blob[i:i+16], db_password, sftp_password)
    return json.loads(gzip.decompress(AESGCM(key).decrypt(blob[i+16:i+28], ciphertext, header)))


def store_archive(sf, name, data):
    try:
        sf.mkdir(ARCHIVE_DIR, mode=0o700)
    except OSError as exc:
        # Some SFTP servers use generic failure for an existing directory.
        if exc.errno not in (None, errno.EEXIST):
            raise
    mode = sf.lstat(ARCHIVE_DIR).st_mode
    if not stat.S_ISDIR(mode) or stat.S_IMODE(mode) != 0o700:
        raise RuntimeError("Backup directory must be private and outside the D.R.O.L. webroot")
    path = ARCHIVE_DIR + "/" + name
    # Exclusive random name; never replace an existing backup.
    with sf.open(path, "wx") as output:
        sf.chmod(path, 0o600)
        output.write(data)
    if stat.S_IMODE(sf.lstat(path).st_mode) != 0o600:
        raise RuntimeError("Unprotected backup file")
    with sf.open(path, "rb") as stored:
        retrieved = stored.read()
    if not hmac.compare_digest(hashlib.sha256(data).digest(), hashlib.sha256(retrieved).digest()):
        raise RuntimeError("Stored backup digest mismatch")
    return retrieved


def schema_statement(snapshot):
    db_ident = identifier(snapshot["database"])
    ddl = snapshot["database_ddl"]
    if not re.match(r"^CREATE DATABASE (?:/\*![0-9]+ IF NOT EXISTS \*/ )?" + re.escape(db_ident), ddl):
        raise ValueError("Unexpected database DDL")
    return ddl


def table_statements(table):
    name_sql = identifier(table["name"])
    cols = ", ".join(identifier(c) for c in table["columns"])
    yield table["ddl"] + ";"
    for row in table["rows"]:
        # UNHEX returns the exact source bytes as a SQL string, including NUL.
        vals = ", ".join("NULL" if v is None else f"UNHEX('{v}')" for v in row)
        yield f"INSERT INTO {name_sql} ({cols}) VALUES ({vals});"


def session_sql():
    return "SET time_zone='+00:00';\nSET SESSION sql_mode='STRICT_ALL_TABLES,NO_AUTO_VALUE_ON_ZERO';\n"


def verify_restore(snapshot):
    import time
    tables = ordered_tables(snapshot)
    if not re.match(r"^11\.8\.\d+-MariaDB", snapshot["version"]):
        raise RuntimeError("No compatible isolated restore image for this MariaDB series")
    image = "mariadb:11.8@sha256:e1d5495f445654134f226f55a41ce0da227116f6f27f283101f405c794d841fc"
    database = snapshot["database"]
    ddl = schema_statement(snapshot)
    name = "drol-restore-" + secrets.token_hex(8)

    def cli(statement, database_name=None):
        command = ["docker", "exec", "-i", name, "mariadb", "--no-defaults", "--protocol=socket",
                   "--batch", "--raw", "--skip-column-names", "--binary-mode", "--default-character-set=utf8mb4"]
        if database_name:
            command.append(database_name)
        # Never copy SQL or database values into Actions error logs.
        result = subprocess.run(command, input=statement.encode(), capture_output=True, timeout=100)
        if result.returncode:
            raise RuntimeError("Isolated MariaDB restore query failed")
        return result.stdout.decode()

    subprocess.run(["docker", "pull", image], check=True, stdout=subprocess.DEVNULL, timeout=200)
    try:
        subprocess.run(["docker", "run", "--detach", "--network", "none", "--name", name,
                        "--env", "MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=yes", image],
                       check=True, stdout=subprocess.DEVNULL, timeout=60)
        for _ in range(90):
            # Verify PID 1 is the final server, not the entrypoint's temporary init server.
            ready = subprocess.run(["docker", "exec", name, "sh", "-c",
                                    'test "$(cat /proc/1/comm)" = mariadbd && mariadb-admin --protocol=socket ping'],
                                   capture_output=True, timeout=8)
            if ready.returncode == 0:
                break
            time.sleep(1)
        else:
            raise RuntimeError("Disposable database did not start")
        cli(ddl + ";")
        restore_version = cli("SELECT VERSION();").strip()
        schema_copy = cli(f"SHOW CREATE DATABASE {identifier(database)};").split("\t", 1)[1].rstrip("\n")
        if schema_copy != ddl:
            raise RuntimeError("Restored database definition differs")
        for table in tables:
            name_sql = identifier(table["name"])
            cli(session_sql() + "\n".join(table_statements(table)), database)
            restored_ddl = cli(f"SHOW CREATE TABLE {name_sql};", database).split("\t", 1)[1].rstrip("\n")
            if restored_ddl != table["ddl"]:
                raise RuntimeError("Restored table definition differs: " + name_sql)
            projections = ", ".join(
                f"IFNULL(HEX(CAST({identifier(c)} AS BINARY)), '-')" for c in table["columns"]
            )
            restored = cli(session_sql() + f"SELECT {projections} FROM {name_sql};", database)
            expected = ["\t".join("-" if value is None else value for value in row) for row in table["rows"]]
            lines = restored[:-1].split("\n") if restored else []
            if sorted(lines) != sorted(expected):
                raise RuntimeError("Restored data differs: " + name_sql)
        return restore_version
    finally:
        subprocess.run(["docker", "rm", "--force", "--volumes", name], check=True, stdout=subprocess.DEVNULL)


def protect_and_verify(sf, snapshot, db_password, sftp_password):
    tables = ordered_tables(snapshot)
    created = datetime.datetime.now(datetime.timezone.utc)
    snapshot["created_utc"] = created.isoformat()
    archive = f"{created:%Y%m%dT%H%M%SZ}-{secrets.token_hex(12)}.drol"
    stored = store_archive(sf, archive, seal(snapshot, db_password, sftp_password))
    reread = unseal(stored, db_password, sftp_password)
    restore_version = verify_restore(reread)
    manifest = {
        "created_utc": snapshot["created_utc"], "version": snapshot["version"],
        "archive": archive, "sha256": hashlib.sha256(stored).hexdigest(),
        "counts": {t["name"]: len(t["rows"]) for t in tables}, "restore_verified": True,
        "restore_version": restore_version,
    }
    Path("drol-backup-manifest.json").write_text(json.dumps(manifest, indent=2) + "\n")
    print("Verified isolated restore:", json.dumps(manifest))


def export_recovery_sql(archive, output, db_password, sftp_password):
    """Recover SQL locally in a private file; never connect to a database."""
    snapshot = unseal(Path(archive).read_bytes(), db_password, sftp_password)
    tables = ordered_tables(snapshot)
    ddl = schema_statement(snapshot)
    # Exclusive creation at 0600 ensures plaintext is never world readable.
    fd = os.open(output, os.O_CREAT | os.O_EXCL | os.O_WRONLY, 0o600)
    try:
        with os.fdopen(fd, "w", encoding="utf-8") as stream:
            stream.write(ddl + ";\nUSE " + identifier(snapshot["database"]) + ";\n" + session_sql())
            for table in tables:
                for statement in table_statements(table):
                    stream.write(statement + "\n")
    except BaseException:
        os.unlink(output)
        raise


if __name__ == "__main__":
    import argparse
    parser = argparse.ArgumentParser(description="Decode a protected D.R.O.L. archive into a new local SQL file")
    parser.add_argument("archive")
    parser.add_argument("output")
    args = parser.parse_args()
    export_recovery_sql(args.archive, args.output, os.environ["DROL_DB_PASSWORD"], os.environ["ODPC_SFTP_PASSWORD"])
