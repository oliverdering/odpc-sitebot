"""Transaction adapter for reviewed metadata. Not wired to production."""
from drol_metadata_db_plan import build_plan, INSERT_SQL

def import_reviewed(connection, registry, records, *, execute=False):
    plan = build_plan(registry, records)
    result = {"eligible": len(plan["rows"]), "blocked": plan["blocked"],
              "rejected": plan["rejected"], "committed": False}
    if not execute or not plan["rows"]:
        return result
    cursor = connection.cursor()
    try:
        cursor.execute("START TRANSACTION")
        cursor.executemany(INSERT_SQL, plan["rows"])
        connection.commit()
        result["committed"] = True
    except Exception:
        connection.rollback()
        raise
    finally:
        cursor.close()
    return result
