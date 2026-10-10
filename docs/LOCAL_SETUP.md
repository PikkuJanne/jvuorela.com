# Reproducible local WordPress

Windows x64 with PowerShell 7: run `./scripts/setup-local.ps1` from the checkout. It downloads checksum-pinned PHP 8.3.35 x64, WordPress 7.1.3, WP-CLI 2.12.0 and WordPress Performance Team SQLite Database Integration 3.1.0 into ignored private storage. It preserves an existing database and does not seed or publish project Pages. It activates the local theme, disables indexing and selects pretty permalinks; those local settings are deliberate. Visual originals are unnecessary: the public clone contains approved derivatives.

Docker Desktop was found but its Linux daemon could not start here. This portable development environment uses the official SQLite drop-in solely for local testing. The production theme requires no plugin; IONOS database/runtime compatibility must be tested against the actual hosted MySQL/MariaDB and PHP version before launch. No database adapter is included in a deployment package.

Start the loopback preview from the checkout:

```powershell
$env:JVT_WP_ROOT=(Resolve-Path 'private/wordpress').Path
& private/php/php.exe -S 127.0.0.1:8088 -t private/wordpress scripts/router.php
```

Only 127.0.0.1 is bound. The preview is visible on this computer and is not a hosted private-preview verification. Development sign-in is held in ignored private/local-auth.json. For the initial manually provisioned instance, the account was rotated into that file. Use normal WordPress admin login; keep this test account local.

Generate review seeds with `python -X utf8 scripts/generate-content.py`; import instructions are in CONTENT_IMPORT.md. Native content is authoritative after import. Theme updates copy code only; explicit importer plan reports existing editorial conflicts without overwriting.

PHP syntax: `Get-ChildItem theme -Recurse -Filter *.php | ForEach-Object { & private/php/php.exe -l $_.FullName }`. WordPress debug is enabled on the disposable instance; review logs during tests. Record hosted versions separately.

Fresh setup and immediate rerun were executed on a separate disposable clone. The bootstrap now requires actual installation and checks every subsequent WP-CLI exit before reporting ready. Database bytes/tables/users and the private credentials remained identical on rerun; an explicit second installation was refused. See `evidence/local-setup-runtime.json`, which records the original failure and corrected source hashes.
