# IONOS preparation, deployment and rollback

## Discover the actual account

Use `templates/HOSTING_INVENTORY.md`. Confirm this is the jvuorela.com installation, its existing site/content, WordPress/PHP versions, theme install permissions, SFTP path, SSH/WP-CLI availability, backup/restore interface, domain mappings, TLS, upload limits, cache behavior and managed/MU plugins. Do not infer that a second installation, automatic staging, shell access or a particular rollback feature is included in “WordPress Start”.

IONOS documents an SFTP-user workflow, but account access and allowed filesystem scope must be observed. Record only nonsecret capability facts in Git. Credentials remain in the user's credential manager or local private environment.

## Private preview

Prefer an account-supported staging/private-preview feature if verified available. Otherwise use an isolated installation/path with access protection that the actual account supports. A protected preview requires a signed-out unauthorized request test; `noindex`, an obscure URL and WordPress's “discourage search engines” setting are not access control. Keep indexing disabled in private previews in addition to protection.

Do not overwrite a currently public site to create staging. If no separate hosted preview is possible without a purchase/change, continue local preview and package work, record the constraint and ask for the specific next hosting decision. Do not buy a plan or add a plugin automatically. Never weaken wp-admin authentication for a preview.

## Build the candidate

1. Freeze the tested source commit and export the currently reviewed content snapshot; confirm all required art and releases.
2. Build `jvuorela-tools-<website-version>.zip` with exactly one `jvuorela-tools/` root. Include only runtime theme files and necessary public licenses/assets. Exclude wp-config.php, database dumps, backups, test credentials, private masters, handoff docs and development dependencies.
3. Produce package SHA-256, included-file list, theme version/changelog and public third-party notices. Check the ZIP installs from WordPress's normal theme-upload screen.
4. Treat content/media setup as a separate deliverable: reviewed Page/block exports, media derivatives and mapping, menu/front-page/privacy-page settings, and a step-by-step initialization plan. Avoid shipping a full production database as a public release asset.
5. Exercise this exact package/setup on a disposable/private environment and retain evidence. A theme upload alone does not constitute deploying the website content.

## Before any public change

Inventory the existing target and preserve its state. Back up DB, uploads, theme and relevant configuration outside the public web root; record recovery steps and verify the backup can be read/restored. Confirm an emergency access route, previous theme/package, expected downtime and a safe rollback trigger. Public cutover needs owner authorization unless already explicitly given for the concrete action in the active session.

A launch review should show the candidate hash/commit, page and art screenshots, content approval, all seven v1.0.0 links, tests, exact target, proposed actions and rollback. Do all available preparation before requesting that decision.

## Deployment choices

Preferred code route: upload the tested ZIP through the authenticated WordPress admin when supported. If the theme already exists, back it up and use the confirmed update/replacement workflow deliberately. Alternative: authenticated SFTP of a fully prepared directory, using a temporary sibling directory and a safe switch only if the server permits and it has been rehearsed. Do not assume atomic rename semantics or overwrite a live directory file by file without a recovery plan.

Fresh-site content initialization uses the explicit importer or editor procedure. Once the owner has edited hosted content, deploy code only unless a narrow content change is separately planned with a diff and backup. Never replace current DB/uploads with stale development data. Domain URL changes require a serialization-aware WordPress migration procedure; use a dry-run first when WP-CLI search-replace is available. Avoid raw global SQL string replacement.

Keep public-site activation, Page publication, domain/DNS routing and preview protection removal as named steps. Preserve existing mail records and unrelated domain services. Confirm HTTPS works before enforcing canonical redirects. Do not remove a security control from unrelated sites in the account.

## Signed-out live checks

After cache invalidation, visit the apex HTTPS site and www variant; verify the intended single canonical route without loops. Inspect home, seven tool pages, Impressum, privacy, a random nonexistent route, headers/status codes, image URLs, downloads, metadata, social images, mobile layout and network requests. Confirm preview noindex/protection is removed only from production and remains on staging. Confirm WordPress admin and updates remain available.

## Rollback

Triggers include inaccessible pages/admin, broken asset paths, data loss, severe layout failure or unresolved redirects. Restore the previous theme/package first for a code-only fault; do not roll back newer editorial data unnecessarily. For a first-install content migration failure, restore the backed-up content/media only with the documented scope and awareness of any newer writes. Reapply privacy protection if the candidate should no longer be public. Restore only domain changes made by this deployment, keeping mail and unrelated records intact. Recheck the previous site's behavior and record the incident.

## After success

Record the actual deployed version/hash, timestamp, approved content snapshot and live checks. Publish the public website tag/release and synchronize the exact deployed commit to the verified GitHub remote under docs/11_PUBLIC_GITHUB.md. Public GitHub work is already authorized; verify a clean public clone and record all repository-cleanup outcomes. Keep a previous working package and the private backup references. Provide the owner editing/maintenance guide. No GitHub-to-production auto-deployment is required for this project.
