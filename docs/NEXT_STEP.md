# Next-step handover

SPDX-License-Identifier: GPL-2.0-or-later

Updated 10 October 2026. The public [v0.1.1 candidate](https://github.com/PikkuJanne/jvuorela.com/releases/tag/v0.1.1) is **ready for deployment**. Use its fixed source `14cb3a924a1c1337ab7fcca9060bd4ed8003e801` and verified theme/content-media packages. This continuation does not change those packages or repeat their recorded runtime acceptance.

The next website step is owner review of the separate Privacy draft and factual tool copy using [CANDIDATE_REVIEW.md](CANDIDATE_REVIEW.md) and [the copy review](../content/COPY_REVIEW.md). Final Privacy wording needs actual hosting facts; obtain those during the separate deployment session. Keep the exact supplied Impressum text.

In parallel, the two tool projects need their stable public v1.0.0 releases: WinBookSplit and AudioFileForPhone. A fresh unauthenticated API check found HTTP 404 for both exact release endpoints and both exact tag endpoints; [the receipt](../evidence/next-step-release-gate.json) records the time, source and limited scope. T18 remains blocked. Publishing or developing those tool releases is separate tool-project work; this website continuation does not certify their applications or invent download links.

After those releases exist, run `python tools/verify-public-releases.py`, review all seven stable release records, downloaded ZIP/checksum comparisons and current/tagged README facts, then deliberately update reviewed native Page download links and copy through the conflict-aware procedure. Preserve hosted editorial changes. Record new evidence and publish any changed candidate through normal checks, commits and a new immutable preview release.

For the separate IONOS session, start with [DEPLOYMENT_HANDOFF.md](DEPLOYMENT_HANDOFF.md): inventory hosting and current routing, create an access-protected preview, install exact verified packages, finalize Privacy from observed facts, and test hosted backup/restore before cutover review. Live launch and final website v1.0.0 remain deferred until the release, owner-content and hosted acceptance gates pass.

Every subsequent handover should identify completed work, source/package identity, actual checks and limits, outstanding gates, and one concrete next step.

Continuation validation: Windows PowerShell, Python 3.14.6, starting clean source `c855aaae9bf892068070906ed87bf4ffe9e731f1`; `python scripts/check-source.py` passed with 47 source slots, 33 selected slots, 47 derivatives, eleven portable Pages and five verified tool releases. `git diff --check` passed. The website remote was verified as public `PikkuJanne/jvuorela.com`. These are source/documentation checks and a targeted release API observation; no new WordPress runtime or hosted acceptance is claimed.
