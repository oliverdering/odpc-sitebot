"""List D.R.O.L. backup candidates without exposing names, paths, or database rows."""
import errno
import hashlib
import json
import re
import stat
from datetime import datetime, timezone

from drol_backup_restore import ARCHIVE_DIR


def inspect(sftp):
    roots = [("/drol", True), (ARCHIVE_DIR, True)]
    for entry in sftp.listdir_attr("/"):
        lower = entry.filename.lower()
        if stat.S_ISDIR(entry.st_mode) and not lower.startswith(".") and re.search("backup|sicherung|archiv", lower):
            roots.append(("/" + entry.filename, "drol" in lower))
    result = []
    visited = set()
    queue = [(path, scoped, 0) for path, scoped in roots]
    while queue:
        path, scoped, depth = queue.pop()
        if path in visited:
            continue
        visited.add(path)
        if len(visited) > 160:
            raise RuntimeError("Backup-directory inventory exceeded its reported scope")
        try:
            entries = sftp.listdir_attr(path)
        except OSError as exc:
            if exc.errno not in (errno.ENOENT, errno.EACCES):
                raise
            print("Unavailable scoped directory", hashlib.sha256(path.encode()).hexdigest()[:12])
            continue
        for entry in entries:
            child = path + "/" + entry.filename
            lower = entry.filename.lower()
            belongs = scoped or "drol" in lower
            if stat.S_ISDIR(entry.st_mode) and depth < 3 and (
                    ("backup" in path.lower() and (belongs or depth == 0)) or re.search("backup|sicherung|archiv|drol", lower)):
                queue.append((child, belongs, depth + 1))
            elif stat.S_ISREG(entry.st_mode) and belongs:
                protected = path == ARCHIVE_DIR and lower.endswith(".drol")
                if protected or re.search(r"(?:db|database|backup|dump|maria|sicherung).*\.(?:json|sql|gz|zip|tgz|bak)$|\.sql(?:\.gz)?$", lower):
                    if path == "/drol/database" and re.match(r"^\d{3}_", lower):
                        continue
                    result.append({"reference": hashlib.sha256(child.encode()).hexdigest()[:16],
                                   "protected_format": protected, "bytes": entry.st_size,
                                   "modified_utc": datetime.fromtimestamp(entry.st_mtime, timezone.utc).isoformat()})
    print("SFTP scoped backup inventory:", json.dumps(result, sort_keys=True))
    print("Scanned directories:", len(visited), "legacy candidates:", sum(not r["protected_format"] for r in result))
