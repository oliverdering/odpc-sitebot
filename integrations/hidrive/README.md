# ODPC HiDrive storage

Status 2026-10-23: browser upload and byte-identical download verified on IONOS HiDrive. The authenticated WebDAV client below is prepared and locally tested, **not connected to the live account**. No background job or public storage API is enabled.

## Simplest deployment

Use the existing GitHub Actions runner with the IONOS HTTPS WebDAV endpoint, if WebDAV is already included and active. Do not purchase a protocol upgrade. The public share link is for uncritical exchange and is **not** a private backup destination. Authenticating to a publicly shared folder does not make that folder private.

Before enabling writes, the owner must verify a separate, unshared target ending in `ODPC-AI-STORAGE` and containing `07_BACKUPS`, or explicitly change the existing sharing arrangement. Check inherited parent shares as well. This implementation does not change shares or create directories outside its configured root. The private target has not been verified yet.

Configure these repository Actions secrets through GitHub's secure settings:

| Secret | Meaning |
| --- | --- |
| HIDRIVE_USERNAME | Actual HiDrive protocol username, as shown in the account; do not assume the email alias is accepted |
| HIDRIVE_PASSWORD | Existing authorized credential; never put it in a command, repository file, artifact or log |
| HIDRIVE_ROOT_PATH | Exact owner-approved WebDAV path of the unshared ODPC-AI-STORAGE root; do not derive it from a browser fragment |

Set the repository variable `HIDRIVE_PRIVATE_TARGET_VERIFIED=true` **only after the owner-side sharing review**. It is an administrative gate, not an automatic technical proof of privacy. No secrets or variables were configured as part of preparing these files.

The current tool connection cannot write GitHub Actions secrets. A secure owner action is required for that step. Existing IONOS webhosting SFTP secrets do not authenticate to HiDrive.

## Verification and operation

Python 3.12 or newer is sufficient; no dependencies or model API calls are needed.

```sh
python3 -m unittest discover -s integrations/hidrive -p 'test_*.py' -v
python3 integrations/hidrive/hidrive_webdav.py plan README.md
python3 integrations/hidrive/hidrive_webdav.py probe
python3 integrations/hidrive/hidrive_webdav.py test
python3 integrations/hidrive/hidrive_webdav.py backup /approved/local/project-export.zip
```

Set credentials with an approved secret injection mechanism, never shell command arguments. The `plan` mode uses no credentials and no network. `probe` reads root directory metadata with PROPFIND. `test` creates a unique test run. `backup` requires explicit local file selection. Each upload is downloaded again and compared with SHA-256. Only a fully verified run gets `COMPLETE.json`; a failed partial run remains for review. No automatic deletion or retention policy is implemented.

The workflow is manual only (`workflow_dispatch`), defaults to `plan`, and accepts remote operations only from `main`. Merge is not required to inspect the prepared source; live execution requires the reviewed workflow to be present on the default branch. It has no cron, push or pull-request trigger. The initial backup selection is deliberately limited to two public source files: the repository README and this transfer client. Expand the explicit file allowlist after reviewing the desired project exports. No entire-repository archive, WordPress database or private document is selected automatically.

## Security and boundaries

- TLS certificate validation is mandatory. Fixed IONOS host and no redirects prevent forwarding credentials to another server.
- The approved root is validated. Relative traversal, URL injection and arbitrary hosts are refused.
- New files use `If-None-Match: *`; unique run folders prevent collision with existing backups. Correct conditional-write handling still needs a live WebDAV test against the target service.
- Input size is limited to 100 MiB per file and 20 explicitly selected files per run. This is an implementation limit; no Windows WebDAV client is used.
- Secret values, response headers and provider error bodies are not logged. No cached Git credentials, public download links, deletion operations or automatic retries after ambiguous writes.
- Username/password over validated HTTPS is supported by IONOS WebDAV. The credential may grant broader account access than the script's path restriction; the restriction is application-level, not a provider-enforced OAuth directory scope.
- Repository is public. No personal, customer or account data belongs in source files or GitHub Actions artifacts. No artifact/cache upload is used by this workflow.
- Standard GitHub-hosted runners for public repositories are free under current GitHub policy. No larger runner, paid service, model API or new storage contract is configured.

## Other integration routes evaluated

| Route | Assessment |
| --- | --- |
| Browser share | Actually tested: upload, listing, ZIP download, byte comparison, folder creation. No login needed. Unsuitable for private content. Browser availability is session-dependent. |
| WebDAV | Simplest prepared route for automated selected-file transfers. Account login, enabled protocol, verified private root and secure secret injection still required. |
| HiDrive REST + OAuth2 | Official API supports file operations; requires registered application client credentials and owner consent. Request minimum `user,rw`, not administrative scope. Provider role/data scope does not by itself isolate one project directory. Token lifecycle and secure refresh-token storage must be implemented before deployment. No application was registered and no OAuth consent was granted. |
| SFTP | IONOS documentation requires HiDrive Pro or the additional-protocol option. Actual existing entitlement is unknown. No upgrade ordered. Host-key verification and account credentials would be required. |
| IONOS webhosting | Existing repository uses Python/Paramiko to deploy WordPress assets to webhosting. A server-side transfer could run there if the existing package supports a suitable runtime/scheduler and outbound HTTPS. That runtime/cron capability was not inspected live. |
| Google Drive | Can remain an editing and exchange surface; there is no proven automatic HiDrive mirror. Do not synchronize whole accounts. No Drive files were read or copied. |
| ODPC agent gateway | Optional later: authenticated MCP adapter backed by this transfer layer or HiDrive OAuth. Define only list/read/create-directory/write-new-file tools, restrict project roots, enforce limits and keep secrets outside the webroot. No delete, overwrite, arbitrary URL fetch or anonymous endpoint. Existing hosting suitability and end-to-end authentication must be established before implementation. No gateway deployed. |

## ChatGPT availability

Browser access, a transfer script and a connected ChatGPT tool are three separate things. A working share link is not a permanent ChatGPT drive mount. GitHub Actions transfers do not automatically make HiDrive files visible to ChatGPT.

The current OpenAI documentation supports connecting a remote MCP server as a ChatGPT plugin with read/write tools, subject to account/workspace restrictions and authentication. The storage adapter would need to be implemented, deployed with authenticated HTTPS, added as a plugin, and tested by invoking an actual read and write from ChatGPT. None of that has been proven for this account. No always-on ChatGPT HiDrive access is claimed.

## Sources checked 2026-10-23

- IONOS WebDAV: https://www.ionos.de/hilfe/cloud-speicher/einrichtung-von-hidrive/verbinden-per-webdav-windows-10/11/
- IONOS SFTP entitlements: https://www.ionos.de/hilfe/cloud-speicher/verwendung-von-hidrive/hidrive-mit-ftps-ssl/tls-und-sftp-ssh/
- HiDrive API registration: https://developer.hidrive.com/get-api-key/
- HiDrive OAuth2: https://developer.hidrive.com/oauth2-endpoints/
- GitHub Actions billing: https://docs.github.com/en/billing/concepts/product-billing/github-actions
- OpenAI custom MCP: https://developers.openai.com/api/docs/guides/custom-mcp-server
