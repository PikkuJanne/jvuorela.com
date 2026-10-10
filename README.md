# jvuorela.com

An editable WordPress website for Janne Vuorela’s seven Windows PowerShell tools. One classic PHP theme, native Pages and core blocks; downloads and detailed instructions remain on GitHub.

Public source: [PikkuJanne/jvuorela.com](https://github.com/PikkuJanne/jvuorela.com). The current version is **0.1.0, a local deployment candidate**. IONOS installation, hosting checks and live cutover are deferred to the owner's separate deployment session. This repository does not claim a live launch.

Development is incremental. See STATUS.md and TASKS.json for actual progress and blockers. The unmodified FINAL v2 handoff is preserved under docs/build-handoff-v2/. Original owner artwork remains private in the supplied folders; only traced optimized derivatives are public. Never source website imagery from tool repositories.

New code and documentation: GPL-2.0-or-later. Artwork has separate rights; see ARTWORK_LICENSE.md and LICENSE_SCOPE.md.

## Build, install and edit

See [BUILD.md](docs/BUILD.md) for a clean-clone build and SHA-256 verification, [LOCAL_SETUP.md](docs/LOCAL_SETUP.md) for the disposable Windows preview, [CONTENT_IMPORT.md](docs/CONTENT_IMPORT.md) for draft initialization and editor safety, and [DEPLOYMENT_HANDOFF.md](docs/DEPLOYMENT_HANDOFF.md) for installation, backups and rollback.

The theme ZIP contains presentation code only. A separate content/media ZIP contains eleven portable draft Pages, approved derivatives and the setup instructions. Page titles, taglines, benefits, requirements, buttons and support wording are native WordPress content. Home and navigation use native settings and menus. Ordinary theme updates preserve that content; the importer plans first, refuses conflicts and never publishes Pages automatically.

Five tool v1.0.0 runtime packages have verified public release/checksum evidence. WinBookSplit and AudioFileForPhone still lack those releases, so their Pages display “Release link pending.” Final Privacy wording and actual hosting acceptance are also required before a public website launch. See [release evidence](evidence/release-facts.md) and [STATUS.md](STATUS.md).

## Verification

Runtime evidence includes real WordPress importer/conflict checks, signed-out route/layout checks with JavaScript disabled, and an Editor account saving native blocks and retaining those edits through a theme ZIP reinstall. Evidence describes the actual environment and limits; the local SQLite development adapter is never shipped in either package.

From the repository root:

```powershell
python scripts/check-source.py
python scripts/package.py --output build/preview
```

For immutable packages use the clean source commit and `--release --expected-commit SOURCE_COMMIT`, as documented in BUILD.md. New website code/docs use GPL-2.0-or-later; [artwork terms](ARTWORK_LICENSE.md) and [third-party notices](THIRD_PARTY_NOTICES.md) remain separate.

## Original handoff validation

From docs/build-handoff-v2/:

```powershell
python -X utf8 -B tools/validate_bundle.py
python -X utf8 -B -m unittest discover -s tools -p 'test_*.py' -v
```

These checks validate the preserved supplied plan. Operational status and the owner's later deployment-scope decision are recorded in the root documents; the original handoff remains unchanged.
