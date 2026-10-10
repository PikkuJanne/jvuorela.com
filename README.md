# jvuorela.com

An editable WordPress website for Janne Vuorela’s seven Windows PowerShell tools. One classic PHP theme, native Pages and core blocks; downloads and detailed instructions remain on GitHub.

Public source: https://github.com/PikkuJanne/jvuorela.com

Development is incremental. See STATUS.md and TASKS.json for actual progress and blockers. The unmodified FINAL v2 handoff is preserved under docs/build-handoff-v2/. Original owner artwork remains private in the supplied folders; only traced optimized derivatives are public. Never source website imagery from tool repositories.

New code and documentation: GPL-2.0-or-later. Artwork has separate rights; see ARTWORK_LICENSE.md and LICENSE_SCOPE.md.

## Handoff validation

From docs/build-handoff-v2/:

```powershell
python -X utf8 -B tools/validate_bundle.py
python -X utf8 -B -m unittest discover -s tools -p 'test_*.py' -v
```

These checks validate the supplied plan, not a deployed website. Runtime setup, testing, packaging and deployment instructions will be recorded as implementation checkpoints pass.
