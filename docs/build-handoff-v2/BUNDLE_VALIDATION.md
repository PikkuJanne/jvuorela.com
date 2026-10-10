# Final bundle validation

Prepared 10 October 2026. Scope: instructions, data, local intake checker and package integrity. These results do not certify a WordPress implementation or live website.

## Performed checks

- Seven unique tools/routes with correct display/repository/launcher aliases and seven dated release records.
- 29 implementation tasks, valid specification paths, acyclic dependencies and 44 mapped acceptance cases. Actual implementation checks remain not_run.
- All 47 exact owner-listed visual sources mapped: 26 in Website Assets and 21 in PS Visuals. One documented WinPDFMerger filename alias; reference text separately recorded.
- 33 selected website-use slots and 14 ancillary slots classified consistently across manifest, page data, tasks and completion gates.
- Mandatory public GitHub visibility, GPL-2.0-or-later code, ongoing sync and final public source release are integrated into the project policy and task graph.
- Seven repository cleanup records mapped to the correct tools, with forbidden source reuse and no history/release rewrite scope.
- 27 regression tests passed: source traversal/symlink boundaries, corrupt PNG CRCs and ICO bounds, missing/conflicting aliases, source preservation, protected report output, byte-size review, dependency cycles, wrong tool mapping, missing cases, tampered hashes, public GitHub policy, forbidden art, cleanup limits and false verification.
- Operational text reviewed for superseded artwork-pending/optional-GitHub instructions; old snapshots are clearly historical.
- Final file hashes, ZIP CRC and re-extracted bundle integrity validated during packaging.

## Reproduce

```text
python -B tools/validate_bundle.py
python -B -m unittest discover -s tools -p "test_*.py" -v
python -B tools/inspect_assets.py --asset-root "PATH_TO_ASSET_PARENT" --output "private/asset-intake.json"
```

The inspector's checks use the actual separately supplied files when Codex runs it. Its unit tests use synthetic image fixtures; no project artwork was substituted or validated by those tests. Structure checks cannot establish visual identity, actual visible alpha, copyright status or correct page rendering.

The default bundle validator checks shipped-file hashes. Use --structure-only after intentional implementation edits. CI starts with handoff/inspector checks; add actual theme/importer/browser/package validation during implementation.

## Not performed by this bundle preparation

No website code implementation, WordPress/browser runtime testing, IONOS inspection/deployment, GitHub repository creation/visibility change/push/cleanup, final tool-release download verification or actual owner-folder image inspection. Source image bytes are supplied separately by the owner. Existing release audit facts remain dated. No cleanup deletion or historical erasure is claimed.
