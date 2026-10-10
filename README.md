# jvuorela.com

SPDX-License-Identifier: GPL-2.0-or-later

An editable WordPress website for Janne Vuorela’s seven Windows PowerShell tools. One classic PHP theme, native Pages and core blocks; downloads and detailed instructions remain on GitHub.

**Ready for deployment:** the tested local preview [v0.1.1](https://github.com/PikkuJanne/jvuorela.com/releases/tag/v0.1.1) is public, with theme and content/media ZIPs at exact clean source [`14cb3a924a1c1337ab7fcca9060bd4ed8003e801`](https://github.com/PikkuJanne/jvuorela.com/tree/14cb3a924a1c1337ab7fcca9060bd4ed8003e801). It includes native Settings → Reading selection of editable 404 copy and decoded templates for setup without WP-CLI. IONOS installation, hosting checks and live cutover are deferred to the owner's separate deployment session. Final website v1.0.0 remains reserved for an accepted live launch. The initial [v0.1.0 preview](https://github.com/PikkuJanne/jvuorela.com/releases/tag/v0.1.0) and [its evidence](evidence/releases/v0.1.0.json) remain fixed.

Public remote: `https://github.com/PikkuJanne/jvuorela.com.git`. Development is incremental; [STATUS.md](STATUS.md), [TASKS.json](TASKS.json) and [checkpoints](evidence/checkpoints.json) record passing synchronization and remaining gates. The unmodified FINAL v2 handoff is preserved under `docs/build-handoff-v2/`. Original owner artwork remains private in the supplied folders; only traced optimized derivatives are public. Never source website imagery from tool repositories.

New code and documentation: GPL-2.0-or-later. Artwork has separate rights; see [ARTWORK_LICENSE.md](ARTWORK_LICENSE.md), [LICENSE_SCOPE.md](LICENSE_SCOPE.md) and [third-party notices](THIRD_PARTY_NOTICES.md).

## Build, install and edit

Use the [v0.1.1 release ZIPs and SHA256SUMS](https://github.com/PikkuJanne/jvuorela.com/releases/tag/v0.1.1), then follow [DEPLOYMENT_HANDOFF.md](docs/DEPLOYMENT_HANDOFF.md) for complete native UI setup, permalink/routing checks, private backups and rollback. [CANDIDATE_REVIEW.md](docs/CANDIDATE_REVIEW.md) records exact hashes, tests and remaining launch gates. Receipt/docs commits on `main` may be newer; rebuilding `main` produces different source metadata. To reproduce these released bytes, check out `v0.1.1` or its full source commit.

See [BUILD.md](docs/BUILD.md) for builds/checksum verification, [LOCAL_SETUP.md](docs/LOCAL_SETUP.md) for a disposable Windows preview and [CONTENT_IMPORT.md](docs/CONTENT_IMPORT.md) for draft initialization and editor safety.

The theme ZIP contains presentation code only. A separate content/media ZIP contains eleven portable draft Pages, decoded `page-templates/*.html`, approved derivatives and setup instructions. Resolve all template tokens locally before native code-editor paste. Page titles, taglines, benefits, requirements, buttons and support wording are native WordPress content. Home and navigation use native settings and menus. Ordinary theme updates preserve that content; the importer plans first, refuses conflicts and never publishes Pages automatically.

Five tool v1.0.0 runtime packages have verified public release/checksum evidence. WinBookSplit and AudioFileForPhone still lack those releases, so their Pages display “Release link pending.” The exact owner-supplied Impressum is editable native content with a site-wide footer link; Privacy remains a separate draft requiring final hosting-informed wording. These gates, final factual copy acceptance and hosted/live acceptance remain necessary before public website launch. See [release evidence](evidence/release-facts.md).

## Verification

The exact v0.1.1 clean packages passed 74 install/backup/fault/restore checks and retained four native Editor changes through a released theme ZIP reinstall. Native Reading save, signed-out HTTP 404 with an unmarked Page and exact ZIP reinstall passed. All eleven packaged templates resolve to 228 valid core blocks without Page writes. Three public CI jobs passed, including 37 importer and 20 native 404/export assertions on each of WordPress 6.9.10 and 7.1.3. Anonymous release downloads match Windows/Linux builds byte for byte. [Runtime evidence](evidence/README.md) also covers earlier signed-out Chrome/Edge/Firefox layouts and lab limits; the correction leaves presentation/artwork unchanged. The local SQLite adapter is never shipped in either package.

Reproduce the published candidate from a clean public clone using Python 3.12 or newer:

```sh
git clone https://github.com/PikkuJanne/jvuorela.com.git
cd jvuorela.com
git checkout v0.1.1
python scripts/check-source.py
python scripts/package.py --release --expected-commit 14cb3a924a1c1337ab7fcca9060bd4ed8003e801 --output dist
```

For active development, omit `--release` and `--expected-commit` and use another output directory. Development packages record their actual source/dirty state; they do not replace fixed published releases.

## Original handoff validation

From docs/build-handoff-v2/:

```powershell
python -X utf8 -B tools/validate_bundle.py
python -X utf8 -B -m unittest discover -s tools -p 'test_*.py' -v
```

These checks validate the preserved supplied plan. Operational status and the owner's later deployment-scope decision are recorded in the root documents; the original handoff remains unchanged.
