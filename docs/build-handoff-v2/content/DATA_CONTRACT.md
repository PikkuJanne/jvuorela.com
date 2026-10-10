# Handoff data contract

UTF-8 JSON. Most records retain schema_version 1; assets/manifest.json is schema_version 2 because the final source mappings/verification states extend the earlier slot contract.

| File | Contract |
| --- | --- |
| content/tools.json | Seven canonical tools, editable draft seeds, three benefits each; hero/avatar/social/icon/poster/application_icon mappings. Preserve runtime/repository naming aliases. |
| content/site.json | Intended canonical origin, native Pages, draft seeding, shared asset mappings and mandatory public/open-source repository policy. actual remote remains unknown until verified. |
| content/releases.json | Dated observations, not live evidence. Recheck exact stable v1.0.0 release packages before launch. |
| assets/manifest.json | 47 exact approved source filenames, 33 selected required website slots, 14 ancillary slots, safe relative paths, expected listed bytes and approved aliases. Source bytes are delivered separately. |
| repositories/tool-cleanup.json | Seven repositories, inspected commits, classified candidates/protected files, PR/merge/default-branch evidence. No blanket deletion or history rewrite. |
| TASKS.json / ACCEPTANCE.json | Dependency-ordered work and actual acceptance evidence; initial todo/not_run does not claim implementation. |

Asset state starts owner_listed_pending_local_inspection. Owner design selection is approved; actual file inspection, derivatives and integration are not. Record actual_metadata/source_sha256/verification when inspected; preserve source lineage and truthful separate license facts. Missing/changed/ambiguous files are review issues and never authorize repository substitutes. All source paths are relative to the explicit owner_asset_parent, not the bundle root or remote.

The asset inspector writes its output to an explicit local report; it does not mutate source files or grant verification to a manifest automatically. Review the findings and visual identity before updating records. Keep private absolute paths out of public evidence.

Validate the unmodified ZIP extraction with `python -B tools/validate_bundle.py`. After intentional implementation edits, use --structure-only; original BUNDLE_MANIFEST.json is a shipping snapshot, not a requirement to freeze evolving work. Tests: `python -B -m unittest discover -s tools -p "test_*.py" -v`. No checker here replaces actual WordPress/browser/hosting/cleanup validation.
