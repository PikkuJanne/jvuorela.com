# Public build and package verification

SPDX-License-Identifier: GPL-2.0-or-later

The current website version is **0.1.1, a preview**. This handoff prepares a tested local technical candidate, public source and deployment packages. The owner has deferred IONOS deployment to a separate session; hosting access is not required to finish this local handoff. Building a ZIP or passing CI does not establish a live site. The final website v1.0.0 must remain tied to its actually accepted and deployed source commit. Two tool releases, final Privacy content, hosted checks and launch review remain separate public-launch gates.

## Build from a public clone

Use Git and Python 3.12 or newer. Packaging and source checks use only the Python standard library. No private image masters, graphics software, Node, presentation plugins or tool dependencies are needed. The approved derivatives and their hashes are committed public inputs; do not regenerate artwork or fetch it from any PowerShell tool repository.

```sh
git clone https://github.com/PikkuJanne/jvuorela.com.git
cd jvuorela.com
git checkout SOURCE_COMMIT
python scripts/check-source.py
python scripts/package.py --release --expected-commit SOURCE_COMMIT --output dist
```

Replace `SOURCE_COMMIT` with the full reviewed checkpoint/release commit. `--release` refuses an uncommitted or dirty source tree. When the theme version becomes 1.0.0, it additionally requires all seven stable release records, exact tag commits, matching downloaded checksums and factual copy verification. It does not create a Git tag, publish to GitHub, upload to WordPress, reseed content or approve hosting cutover.

For active development, omit `--release` and `--expected-commit`. `BUILD.json` and `artifacts.json` then record the checked-out source commit plus `dirty: true` when applicable. A dirty preview is useful for local review; it cannot represent the exact final source release. The metadata distinguishes source code identity from the separate tool v1.0.0 identities.

## Outputs

At the current version, `dist/` contains:

- `jvuorela-tools-0.1.1.zip`: one `jvuorela-tools/` root, runtime theme files and traced background derivatives, GNU GPL text, license scope, separate artwork/third-party notices, runtime image provenance and build identity. No importer, handoff bundle, WordPress core, database adapter, development account or media masters are inside the theme.
- `jvuorela-tools-content-media-0.1.1.zip`: one `jvuorela-tools-setup/` root, eleven portable draft Page records, public factual source records, complete asset manifest/approved derivatives, import/export development scripts and setup/editing/deployment runbooks, including the backup and rollback procedure in `docs/DEPLOYMENT_HANDOFF.md`. Stable slot IDs and portable tokens are deliberate; actual WordPress attachment/page IDs and URLs are resolved at explicit import time. A raw hosted export is never selected automatically.
- `artifacts.json`: version/mode, source commit/dirty state, validation counts, archive SHA-256/byte sizes and input-tree digests.
- `SHA256SUMS`: checksums for both ZIPs and `artifacts.json`.

Root notices are copied during packaging so code and artwork terms accompany the distributable. Artwork retains its recorded separate rights; GPL source licensing does not grant a blanket sublicense for the visuals. WordPress and the local SQLite adapter keep their own terms and are not bundled into either website artifact.

The setup ZIP is an initialization/review deliverable. On an existing site, current native WordPress Pages and Media Library records remain authoritative. Use the importer in plan mode and review conflicts; ordinary theme updates install code only. The exported review script can output installation-local identifiers, so send its output to private storage and deliberately sanitize approved publication snapshots. See [CONTENT_IMPORT.md](CONTENT_IMPORT.md).

## Determinism and checksum checks

Entries are sorted by POSIX path. ZIP timestamps are fixed at 1980-01-01, permissions are ordinary 0644 files and text line endings are normalized to LF. GitHub HTTPS remote identity includes the optional `.git` suffix consistently in build metadata, including GitHub Actions checkouts that omit it. Already optimized image bytes are stored unchanged. `ZIP_STORED` avoids compressor-version differences between Windows and Linux; no build time, absolute workstation path or random identifier enters the artifacts.

Repeat a build from an unchanged source snapshot:

```sh
python scripts/package.py --output build/first
python scripts/package.py --output build/second
python -c "from pathlib import Path; import hashlib; a=Path('build/first'); b=Path('build/second'); names=['SHA256SUMS','artifacts.json']+[p.name for p in a.glob('*.zip')]; assert all(a.joinpath(n).read_bytes()==b.joinpath(n).read_bytes() for n in names); print('Byte-identical packages and metadata')"
```

On Linux/macOS with `sha256sum`, run `sha256sum -c SHA256SUMS` from the output directory. On Windows, compare `Get-FileHash -Algorithm SHA256` with each listed digest. Recheck the theme ZIP inventory: every entry starts with `jvuorela-tools/`, and it contains no private masters, credentials, IDs/database dumps or development dependencies. Do not attach production backups to a public release.

When rebuilding a published artifact, use the same public HTTPS clone URL and exact source commit. Keep the version header and approved content/media snapshot unchanged. The two generated input manifests identify any differing source file. A build from changed content or dirty code is a different candidate and must be recorded as such.

## Runtime tests and CI

`scripts/check-source.py` checks the 47/33 asset contract, source-to-derivative hashes, exact derivative sizes, unmapped imagery, all theme image/CSS references, license boundary, eleven portable core-block Pages, absence of local attachment IDs and fake links, seven canonical release records and public-source exclusions. `--require-launch` makes missing final tool releases fail explicitly. This is source validation, not browser/editor or IONOS runtime evidence.

The `Website checks` GitHub workflow uses read-only permissions and official actions pinned to commit SHAs resolved from their upstream tags on 2026-10-10:

| Action | Tag | Pinned commit |
|---|---|---|
| actions/checkout | v4 | `11d5960a326750d5838078e36cf38b85af677262` |
| actions/setup-python | v5 | `a26af69be951a213d495a4c3e4e4022e16d87065` |
| actions/upload-artifact | v4 | `ea165f8d65b6e75b540449e92b4886f43607fa02` |

CI runs the immutable handoff validator/tests from `docs/build-handoff-v2/`, actual source/provenance checks, committed Page regeneration comparison, repeated deterministic builds, PHP lint and the real importer tests in disposable loopback WordPress installations. PHP versions/extensions are printed from the Ubuntu runner. The WordPress matrix exercises the maintained 6.9 line and current 7.1 line; actual CI results must be recorded after the source is pushed.

Development-only downloads are pinned to these observed SHA-256 values:

| Download | Version | SHA-256 |
|---|---|---|
| Official WordPress ZIP | 6.9.10 | `4f18b9e25bd253b0c39009eafb651911e8de29465d6f62397369ff75a36fdb0e` |
| Official WordPress ZIP | 7.1.3 | `eb58a90ed55147f90f7bd55d0d3334960a69b7e8a4cc142c8ddfea8965218227` |
| SQLite Database Integration, local test only | 3.1.0 | `e260e3ada78676f1c0f4e09eb9ebcb9ecce4d4b85f0c6e5a70fb9a93f5b9847f` |
| Official WP-CLI PHAR | 2.12.0 | `ce34ddd838f7351d6759068d09793f26755463b4a4610a5a5c0a97b68220d85c` |

The matrix and checksummed URLs are saved in the workflow. This local database adapter is used to test real WordPress behavior without a database server; it is not a presentation dependency and is never part of the production theme or deployment package. IONOS MySQL/MariaDB, installed WordPress/PHP, real editor behavior and backup restoration require their own hosted evidence.

On the Windows local environment documented in [LOCAL_SETUP.md](LOCAL_SETUP.md), invoke the behavior suite from the checkout:

```powershell
& private/php/php.exe private/wp-cli.phar --path=private/wordpress --user=1 eval-file tests/importer.php
```

Use only the disposable loopback instance. The suite refuses other targets and validates planning, actual draft/media writes, idempotence, conflict/edit survival and preservation of unrelated data. Keep its logs and environment-specific exports private unless intentionally sanitized.

Official sources: [WordPress downloads](https://wordpress.org/download/), [SQLite integration](https://wordpress.org/plugins/sqlite-database-integration/), [WP-CLI](https://wordpress.org/cli/) and [WP-CLI config creation](https://developer.wordpress.org/cli/commands/config/create/).
