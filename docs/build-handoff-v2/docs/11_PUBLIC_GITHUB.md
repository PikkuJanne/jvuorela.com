# Public GitHub development and open-source delivery

## Owner authorization and identity

The owner explicitly requires ongoing GitHub development and a final PUBLIC open-source website repository, like the tools. Normal repository creation/publication, commits, pushes, PRs and the final public website source release are in scope. Do not convert this into optional remote synchronization or ask for the same authorization at each checkpoint.

Inspect the local remote and GitHub identity without printing credentials. The owner namespace is PikkuJanne. Reuse the existing intended website repository if established. If no intended repository exists, create a new public **jvuorela.com** repository in that namespace after verifying availability and access. Do not assume a repository exists from its proposed name. If the name belongs to unrelated work or authentication/ownership is unclear, report that concrete issue and keep local work moving. Do not use another namespace silently.

For an intended existing private website repository, inspect its entire outgoing history and contents for secrets/private material before the authorized public visibility change. A .gitignore only protects untracked future additions. If sensitive history exists, preserve it privately and prepare a clean public source import in a separate repository; do not rewrite/delete history or expose secrets. Resolve only the specific repository-name/access choice needed.

## Working loop

Use the repository's established default branch and protection rules. Keep changes in focused milestone/task branches as appropriate. For every passing slice: update status/tasks/evidence, inspect the diff, commit, fetch/reconcile remote changes, run relevant checks and push. Create/merge PRs when required by repository rules after checks pass. Do not bypass protections, force-push, rewrite history or overwrite someone else's work. Record the exact local and remote commit, branch and PR when relevant. Pending network/authentication is a synchronization blocker, not a reason to abandon local work.

GitHub is the durable source of code and public delivery material. Keep a root README with purpose, pages, prerequisites, reproducible local setup, build/test commands, asset-source rules, licensing, contribution path and deployment instructions. Add a public source link in the website footer to the actual repository URL once resolved.

## License and repository contents

The new website code and project documentation use **GPL-2.0-or-later**, as specified in LICENSE and LICENSE_SCOPE.md. Carry the same license identifier in theme metadata/new source headers where appropriate. Preserve existing compatible third-party notices; inventory any imported dependency. Do not relicense the seven tools.

Artwork, fonts and other third-party material keep their actual separate rights. ARTWORK_LICENSE.md explains the boundary; populate THIRD_PARTY_NOTICES.md and per-asset records from supplied facts. The owner has authorized public website/GitHub delivery of the approved derivative assets. Do not infer an unconfirmed blanket GPL/CC license, guarantee copyright status or relabel franchise material. If an observed asset has a concrete incompatible restriction, identify that file and resolve it without replacing it from a tool repository.

Commit all source needed to build the theme, scripts, tests, documentation, public-safe content seeds/exports, exact dependency locks where used, and optimized approved media with provenance. A clean signed-out clone must reproduce the installable theme and documented public content/media setup without private image masters or credentials. WordPress-generated environment-specific attachment IDs stay private; map assets with stable IDs. Hosted editable content remains authoritative, so refresh only an intentional public-safe snapshot for a website release. Never commit production databases, users, credentials, backups or private contact drafts.

## Continuous integration

The supplied .github/workflows/handoff-checks.yml validates the instructions/data and inspector behavior. It is not website runtime CI. During implementation, add checks for the actual PHP/theme environment, importer conflict/edit-survival behavior and reproducible release packaging. Use GitHub-hosted runners with read-only permissions by default. Do not add IONOS credentials or automatic production deployment to CI. Pin action dependencies deliberately when creating runtime workflows and document actual tool versions.

## Final release acceptance

Publish a public website tag/release **v1.0.0** for the verified deployed source commit, separate from the seven tool versions. Include release notes, checksummed installable theme ZIP and the sanitized public content/media setup package where applicable. Never attach a raw WordPress database. If a website v1.0.0 tag already exists for unrelated/previous work, do not move it; reconcile the correct project/version explicitly.

Verify repository visibility and LICENSE from a signed-out/public request. Clone the public repository into a clean directory, resolve the tag commit, reproduce the build with its documented public assets, and compare artifacts or explain legitimate nondeterministic fields. Confirm the deployed version and recorded source commit match. Leave no required website source/assets only on the development computer. Record the actual repository URL, default branch, tag, commit, release URL, checksums and CI results in the final owner handover.

Public source release is authorized by this plan. IONOS public cutover follows the separate concrete launch review in docs/07_IONOS_DEPLOYMENT.md.
