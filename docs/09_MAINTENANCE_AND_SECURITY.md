# Maintenance and security scope

Keep WordPress and retained hosting components maintained using the account's actual update workflow. Do not install extra security/caching/SEO plugins by default. Document each retained plugin's purpose and any removal test rather than deleting IONOS components wholesale.

Use named least-privilege accounts where available: owner Administrator for setup, ordinary editorial access for content. Keep credentials out of Git, packages, screenshots and terminal transcripts. Do not commit wp-config.php, databases, backups or private asset masters. Review a staged diff before a first public push and scan the actual release ZIP for excluded paths.

Implement standard WordPress escaping for output, sanitization for saved values and capabilities/nonces for state changes. Avoid arbitrary remote fetchers, front-end upload endpoints and dynamic inclusion of user-provided paths. The site has no need to execute PowerShell, accept user media or proxy GitHub files. Metadata and asset selection must not permit script injection.

Hosting logs, admin cookies and provider services may exist even without analytics. Determine the actual processing and necessary public details before the owner approves privacy/Impressum text. Do not invent legal compliance claims or a fake contact address. A cookie-consent system is not automatically required or automatically unnecessary merely because the theme has no analytics; review actual behavior and applicable requirements if needed.

Owner maintenance routine: preview content/link edits, review release facts when a tool changes, back up before code/core changes, apply updates through the documented route, check representative pages afterward and verify periodic recovery. Preserve the public version label until a deliberate content update adopts a newer tool version. Tool projects retain their own release and security responsibilities.

After launch, WordPress DB/uploads are authoritative for content. Theme changes are code-only by default. Keep approved source assets durable and retain derivative hashes/rights. Review stored logs/backups according to the account's actual retention and owner needs.

Public GitHub stays the maintained source of website code, reviewed public content snapshots and permitted derivatives. Commit/push tested updates, keep release tags tied to deployed commits and retain public license/provenance files. Tool-repository preliminary imagery remains forbidden even after cleanup or future tool updates.
