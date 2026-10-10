# Incremental implementation roadmap — final v2

Each task in TASKS.json has dependencies, specifications and acceptance cases. Inspect real project progress before applying initial todo statuses. Work in the smallest useful slices; commit and push passing checkpoints throughout.

| Milestone | Tasks | Required outcome |
| --- | --- | --- |
| M0 — Foundation and current inputs | T00–T01, T24–T26 | Preserved workspace, public GitHub setup, actual hosting/local inventory, 47-file approved intake and seven-repository cleanup audit |
| M1 — WordPress foundation | T02–T04 | Reproducible local WP, installable custom theme, conflict-aware editable draft seed workflow |
| M2 — Complete structure | T05–T07 | Home/seven tools/support/404 routes and exact supplied-asset mappings |
| M3 — Functional quality | T08–T10 | Editor survival, responsive/accessibility/no-JS and metadata/runtime checks |
| M4 — Deployment preparation and measurements | T11–T14 | Tested package/restore, actual private hosted preview where accessible, supplied-art layout measurements |
| M5 — Final art integration | T15–T17 | Pilot then all 33 selected website assets, 47 inputs accounted for, final CSS and public derivatives |
| M6 — Final candidate and cleanup | T18–T20, T27–T28 | Seven verified v1.0.0 tools, final copy/legal review, scoped cleanup, license/CI/clean clone and tested candidate |
| M7 — Publish and hand over | T21–T23 | Authorized IONOS cutover, live checks, public website v1.0.0 tag/release and synchronized source/owner guide |

T24/T25/T26 can proceed independently after T00. T14 depends on layout quality and local intake, not hosted preview; completed artwork can be integrated while access is resolved. T18/T19 do not require final art. T27 cleanup can proceed once classification/intake is available, independently of deployment. M6 is a grouping, not a rule to delay an otherwise ready task.

T20 requires actual hosted preview and public reconstruction checks. T23 requires live results plus public repository and cleanup outcomes. Missing credentials block only dependent tasks. A new creative session is not a planned milestone. Reference roadmap v1 is historical; this table and TASKS.json are authoritative.

Loop: read current state → inspect Git → choose next unblocked slice → implement → relevant checks → evidence/status → commit/push → continue. A public repository/clean local build does not by itself prove the live site. Completion is defined in docs/08_RELEASE_GATES.md.
