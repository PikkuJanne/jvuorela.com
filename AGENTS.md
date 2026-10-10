# Codex operating instructions — final website plan

Read START_HERE.md, DECISIONS.md, STATUS.md and TASKS.json. Merge these instructions into an existing AGENTS.md without overwriting unrelated project guidance. Preserve existing files, worktrees and uncommitted changes. No subagents unless requested by the owner.

Work in small dependency-ordered slices, check actual behavior, update tasks/status/evidence, commit passing checkpoints and push to the verified PUBLIC website GitHub remote. Public GitHub development and ordinary repository publication are explicitly authorized; do not make synchronization optional or repeatedly request permission. Inspect source and outgoing history for private material before the first public push. Never force-push or rewrite published history.

Build the lightweight WordPress architecture. Keep page copy in native editable blocks. Never reseed on theme activation or overwrite hosted content from a stale local snapshot. Follow the explicit conflict-aware import and deployment procedure.

Only use source artwork listed in assets/manifest.json from the owner's Website Assets and PS Visuals folders. Inventory these locally, hash them and preserve source-to-derivative provenance. Thereafter verified derivatives in the website repository are permitted. Tool-repository preliminary website assets are forbidden under all circumstances, including references, placeholders, cache copies and hotlinks. Do not search for replacement art. A missing approved file blocks only its slot.

No image generation, drawing, retouching, redesign or new compositions. Neutral diagnostic rectangles are temporary development aids only. Proportional resizing, format optimization and explicitly approved crops are allowed. Keep source masters unchanged and private; publish the approved optimized derivatives required for a reproducible public build. Ordinary integration needs no renewed creative approval.

The owner's cleanup request is implemented by T26/T27 and docs/12_TOOL_REPOSITORY_CLEANUP.md. Audit the seven exact tool repositories and remove confirmed obsolete website-only files and stale direct references in isolated reviewable commits; synchronize through normal checks/PR rules. Do not alter tool behavior, application icons, runtime assets, published release archives/tags or Git history. Ask only about concrete ambiguous/shared files or access blockers, not a blanket reapproval of this scope.

Use GPL-2.0-or-later for new website code/docs and accurately document separate artwork/font/dependency rights. Include clean-clone build/setup instructions, CI appropriate to the implementation and a public source release matching the deployed candidate.

All copy can be refined before publication. Verify final tool release facts; audit snapshots do not prove current availability. Keep secrets, credentials, DB dumps, private inventories, backups and original masters out of Git/releases. Public configuration examples must use placeholders.

Record actual environment, commit, tests, results and limits. A green handoff validator is not runtime evidence. Continue local work around access blockers. Completion requires live IONOS acceptance, a public open-source GitHub release, and documented cleanup outcomes. IONOS public cutover still needs the concrete launch decision if not already authorized.
