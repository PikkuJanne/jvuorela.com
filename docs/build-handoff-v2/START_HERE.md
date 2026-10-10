# Final Codex build bundle — jvuorela.com

Version 2.0 | 10 October 2026 | Complete implementation handoff

This replaces v1 and the separate asset addendum. Asset filenames, source restrictions, GitHub delivery and repository cleanup are integrated into the specifications, JSON records, tasks, acceptance cases and validation tools.

## Start

1. Extract this ZIP. Keep the owner's **Website Assets** and **PS Visuals** folders together in a local asset-parent directory. They can sit beside this extracted bundle. Their image bytes are intentionally supplied by the owner separately and are not included in this ZIP.
2. Open the website workspace in Codex. For an existing project, import this bundle under `docs/build-handoff-v2/`, merge AGENTS instructions, and map bundle-relative paths. Preserve current work and completed evidence; do not restart completed implementation.
3. Paste `CODEX_START_PROMPT.md`. Codex reads the current scope and works through the smallest useful tasks, committing and pushing passing checkpoints to the website's **public GitHub repository**.
4. The approved art is already created. Run the read-only asset intake against the two supplied folders, measure the real layouts and integrate a pilot followed by the remaining assets. Do not wait for another creative session.
5. Codex also audits the seven tool repositories and removes confirmed obsolete website-only assets through isolated, reviewable commits and the repositories' normal PR/check workflow. It must never source website imagery from those repositories.
6. Review the working candidate. IONOS public cutover follows the concrete launch review and any still-missing launch authorization. Public GitHub development is already requested and authorized.

## Mandatory reading

`PROJECT_BRIEF.md`, `DECISIONS.md`, `AGENTS.md`, `ROADMAP.md`, `TASKS.json`, `STATUS.md`, `docs/04_VISUAL_ASSET_CONTRACT.md`, `docs/11_PUBLIC_GITHUB.md`, `docs/12_TOOL_REPOSITORY_CLEANUP.md`, and `assets/manifest.json`.

The content audit is a dated starting point. All seven final public tool releases must be verified as v1.0.0 before website launch. Wording remains editable in WordPress. The website's first release is also planned as v1.0.0, independently of tool versions.

## Inputs still discovered during implementation

Existing website workspace/remote (if any), authenticated access to PikkuJanne's GitHub account, actual asset-parent path, IONOS/WordPress access, final public contact/privacy details and the eventual launch decision. These are concrete inputs, not a reason to stop all local work. The default new repository name is **jvuorela.com** under **PikkuJanne** if no existing intended website repository is found; this is a naming decision, not a claim that such a repository already exists.

## Useful files and commands

- `content/`: editable seed records and dated tool-release evidence.
- `assets/manifest.json`: exact 47-file inventory, aliases, source restrictions, intended uses and verification fields.
- `tools/inspect_assets.py`: read-only local intake; no copying, downloading, deletion or image edits.
- `docs/04_VISUAL_ASSET_CONTRACT.md`: complete source mapping and integration rules.
- `docs/11_PUBLIC_GITHUB.md`: public repository, licensing, CI, synchronization and release requirements.
- `docs/12_TOOL_REPOSITORY_CLEANUP.md`: scoped audit/removal procedure and limits.
- `tools/validate_bundle.py`: offline integrity and consistency checks for this handoff.
- `BUNDLE_VALIDATION.md`: actual bundle validation results and evidence limits.

From the extracted bundle root:

```text
python -B tools/validate_bundle.py
python -B -m unittest discover -s tools -p "test_*.py" -v
python -B tools/inspect_assets.py --asset-root "PATH_TO_ASSET_PARENT" --output "private/asset-intake.json"
```

The inspector expects `Website Assets` and `PS Visuals` beneath that parent. It checks names, byte sizes, hashes and PNG/ICO structures; Codex must also visually inspect the actual images. A successful handoff check does not prove the website has been implemented, tested or deployed.
