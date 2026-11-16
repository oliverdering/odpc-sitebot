# Workflow inventory (read-only)

Run locally from the repository root:

```bash
python3 operations/audit/workflow_inventory.py --output operations/audit/workflow-inventory.generated.json
```

The command reads all `.github/workflows/*.yml` and `*.yaml` files and writes JSON **inside the local checkout**. No GitHub token, network, SFTP, database access or credentials are needed. The generated JSON is a local analysis artifact, not a deployment manifest.

**Caution:** YAML trigger parsing, script extraction and concurrency extraction are explicitly heuristic. They cannot establish the effective remote target, whether a deployment is currently running, the correctness of a backup or safe mergeability. Never treat an inventory entry as approval to run a deployment.

After generating the JSON, review all deploy-like entries and manually complete the remote-target matrix against their scripts. Check GitHub Actions and deployment history separately before any production change. Do not commit generated output without checking it for sensitive data.
