# jvuorela.com

SPDX-License-Identifier: GPL-2.0-or-later

An editable WordPress website for Janne Vuorela’s seven Windows PowerShell tools. One classic PHP theme, native Pages and core blocks; downloads and detailed instructions remain on GitHub.

The tested initial local preview [v0.1.0](https://github.com/PikkuJanne/jvuorela.com/releases/tag/v0.1.0) is public, with theme and content/media ZIPs at exact source [`0f8a50b6ba6bfb39ef97e7d7a976108a9e33c738`](https://github.com/PikkuJanne/jvuorela.com/tree/0f8a50b6ba6bfb39ef97e7d7a976108a9e33c738). A corrected **v0.1.1 candidate is in progress**: native Settings → Reading selection of editable 404 copy will complete the manual setup route. Initial release bytes stay fixed; final local readiness awaits the replacement's tests/publication. IONOS installation, hosting checks and live cutover are deferred to the owner's separate deployment session. Final website v1.0.0 remains reserved for an accepted live launch.

Public remote: `https://github.com/PikkuJanne/jvuorela.com.git`. Development is incremental; [STATUS.md](STATUS.md), [TASKS.json](TASKS.json) and [checkpoints](evidence/checkpoints.json) record passing synchronization and remaining gates. The unmodified FINAL v2 handoff is preserved under `docs/build-handoff-v2/`. Original owner artwork remains private in the supplied folders; only traced optimized derivatives are public. Never source website imagery from tool repositories.

New code and documentation: GPL-2.0-or-later. Artwork has separate rights; see [ARTWORK_LICENSE.md](ARTWORK_LICENSE.md), [LICENSE_SCOPE.md](LICENSE_SCOPE.md) and [third-party notices](THIRD_PARTY_NOTICES.md).

## Build, install and edit

The [initial release ZIPs and SHA256SUMS](https://github.com/PikkuJanne/jvuorela.com/releases/tag/v0.1.0) remain available for review/reproduction. [CANDIDATE_REVIEW.md](docs/CANDIDATE_REVIEW.md) records their exact identity and the pending correction; use the replacement candidate only once its tested release identity is recorded. Current [DEPLOYMENT_HANDOFF.md](docs/DEPLOYMENT_HANDOFF.md) supplements the frozen instructions with complete UI setup, permalink/routing checks, private backups and rollback. Receipt/docs commits on `main` may be newer; rebuilding `main` produces different source metadata. To reproduce the initial released bytes, check out `v0.1.0` or its full source commit.

See [BUILD.md](docs/BUILD.md) for builds/checksum verification, [LOCAL_SETUP.md](docs/LOCAL_SETUP.md) for a disposable Windows preview and [CONTENT_IMPORT.md](docs/CONTENT_IMPORT.md) for draft initialization and editor safety.

The theme ZIP contains presentation code only. A separate content/media ZIP contains eleven portable draft Pages, approved derivatives and the setup instructions. Page titles, taglines, benefits, requirements, buttons and support wording are native WordPress content. Home and navigation use native settings and menus. Ordinary theme updates preserve that content; the importer plans first, refuses conflicts and never publishes Pages automatically.

Five tool v1.0.0 runtime packages have verified public release/checksum evidence. WinBookSplit and AudioFileForPhone still lack those releases, so their Pages display “Release link pending.” The exact owner-supplied Impressum is editable native content with a site-wide footer link; Privacy remains a separate draft requiring final hosting-informed wording. These gates, final factual copy acceptance and hosted/live acceptance remain necessary before public website launch. See [release evidence](evidence/release-facts.md).

## Verification

The exact v0.1.0 clean packages passed 74 install/backup/fault/restore checks and retained four native Editor changes through a released theme ZIP reinstall. All eleven Pages contain 228 valid core blocks. Three public CI jobs passed, including 37 importer assertions on each of WordPress 6.9.10 and 7.1.3. Anonymous release downloads match Windows/Linux builds byte for byte. These observations belong to the initial preview; the 404 UI correction needs replacement-candidate acceptance. [Runtime evidence](evidence/README.md) also covers signed-out Chrome/Edge/Firefox layouts, no-JavaScript behavior and the actual test limits. The local SQLite adapter is never shipped in either package.

Reproduce the published candidate from a clean public clone using Python 3.12 or newer:

```sh
git clone https://github.com/PikkuJanne/jvuorela.com.git
cd jvuorela.com
git checkout v0.1.0
python scripts/check-source.py
python scripts/package.py --release --expected-commit 0f8a50b6ba6bfb39ef97e7d7a976108a9e33c738 --output dist
```

For active development, omit `--release` and `--expected-commit` and use another output directory. Development packages record their actual source/dirty state; they do not replace the fixed v0.1.0 release.

## Original handoff validation

From docs/build-handoff-v2/:

```powershell
python -X utf8 -B tools/validate_bundle.py
python -X utf8 -B -m unittest discover -s tools -p 'test_*.py' -v
```

These checks validate the preserved supplied plan. Operational status and the owner's later deployment-scope decision are recorded in the root documents; the original handoff remains unchanged.
