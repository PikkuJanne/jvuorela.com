# Remove obsolete website-only material from the seven tool repositories

## Scope and authorization

The owner reports that tool repositories may contain preliminary website assets, forbids using them for this website under any circumstance, and requests their removal. This task is part of the final plan. The repositories are enumerated exactly in repositories/tool-cleanup.json. They may contain no such files; a verified no-candidates outcome is valid. No inspection/deletion is claimed by the bundle itself.

Proceed with ordinary reversible Git removal commits when a file is clearly obsolete website-only material. This is not authorization for blanket asset-directory deletion, runtime changes, replacing released archives, deleting repositories/tags, purging Git history or changing tool licenses. Current approved application icons and runtime artwork can legitimately remain in tool repositories, but the website still sources its copies only from the owner's approved project folders.

## T26: evidence-based inventory

1. For each exact repository, inspect remote/default branch, current commit, worktree status and repository instructions. Fetch normally; preserve uncommitted changes and use a clean isolated branch/worktree.
2. Locate likely website directories, preliminary page designs, promotional mockups, obsolete banners/social previews and associated docs. Search tracked files first, then inspect references from source, launchers, packaging, tests, workflows, docs and README. Folder name, image extension or age alone does not prove obsolescence.
3. Classify each candidate: **confirmed obsolete website-only**, **active runtime/application icon**, **active documentation/shared**, or **ambiguous**. Record path, current blob/hash, reason, references and planned action in repositories/tool-cleanup.json and the evidence template. Do not import candidate images into the website.
4. Keep active runtime/shared assets. Ask only about specifically ambiguous files that cannot be resolved from evidence. Continue clearly scoped removals and independent website work. A preliminary README image may be removed along with its stale reference if it is confirmed website-only; do not break useful runtime documentation.

## T27: narrow removal and verification

1. Record the pre-cleanup commit and preserve a recoverable local copy/branch as appropriate. Normal Git history already retains removed tracked files; do not publish an extra archive of obsolete artwork into the website repository.
2. Remove only explicitly classified tracked paths with ordinary Git deletion. Remove/update only direct obsolete references needed to prevent broken links/builds. If deletion requires changing tool behavior or replacing runtime assets, stop that candidate and record the dependency instead.
3. Inspect the complete diff: only allowlisted paths/references, no accidental code/config/secret changes. Run relevant link/package checks and repository-required tests appropriate to the touched files. Verify launchers, runtime icons, packaged file lists and current releases are unaffected.
4. Commit on a focused cleanup branch, push to the correct tool remote and use the normal PR/check workflow. Merge clear scoped changes when required checks pass and existing repository rules permit. Never bypass branch protection. No repeated blanket approval is required for confirmed cleanup. If external access, a required human reviewer or a check blocks merging, record the real blocker; a pending PR is not a completed default-branch cleanup.
5. Re-fetch and verify the actual default branch after merge. Confirm the obsolete paths and their stale references are absent there, preserved assets still exist, and the relevant checks pass. Record PR/commit/evidence for every repository. If there were no candidates, record inspected commit and search/classification evidence.

## Historical copies and final report

An ordinary removal commit removes files from the current branch tree, not from historical commits or already published releases. Do not claim total erasure from GitHub. Old release attachments/history remain excluded as website sources regardless of cleanup. If historical erasure or replacement of published assets is later wanted, it needs a separately scoped migration because it can affect public references and release integrity.

Complete the cleanup work independently of website implementation where possible. The website may reach a reviewed candidate while a repository access issue remains, but report project completion only when cleanup outcomes are complete or the owner explicitly accepts a specific remaining blocker. Never fabricate a deletion, merge or no-candidates result.
