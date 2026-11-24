"""Load migration 005 and the reviewed E0 graph seed from the checked-out revision."""
import json
from drol_validate_knowledge_graph import PATH, validate


def load_graph_payload():
    graph = json.loads(PATH.read_text(encoding="utf-8"))
    validate(graph)
    sql = PATH.with_name("005_uav_relationships.sql").read_text(encoding="utf-8")
    stripped = "\n".join(line for line in sql.splitlines() if not line.lstrip().startswith("--"))
    statements = [part.strip() for part in stripped.split(";") if part.strip()]
    return {"entities": graph["entities"], "statements": statements}
