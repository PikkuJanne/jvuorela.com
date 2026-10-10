#!/usr/bin/env python3
"""Build deterministic theme and portable content/media packages.

SPDX-License-Identifier: GPL-2.0-or-later
No databases, WordPress IDs, private masters, credentials or core/plugins ship.
"""

import argparse
import hashlib
import importlib.util
import json
from pathlib import Path
import re
import subprocess
import sys
import zipfile

ROOT = Path(__file__).resolve().parents[1]
THEME = ROOT / "theme" / "jvuorela-tools"
LICENSES = ["LICENSE", "LICENSE_SCOPE.md", "ARTWORK_LICENSE.md", "THIRD_PARTY_NOTICES.md"]
ZIP_TIME = (1980, 1, 1, 0, 0, 0)
FORBIDDEN_SUFFIXES = {".sql", ".dump", ".bak", ".pem", ".key", ".pfx", ".p12", ".log", ".phar"}
FORBIDDEN_PARTS = {"private", "backups", "Website Assets", "PS Visuals", "node_modules", "vendor", ".git", ".runtime", "local-wordpress"}
TEXT_SUFFIXES = {".php", ".css", ".json", ".md", ".py", ".ps1", ".html", ".txt", ".yml", ".yaml"}
THEME_SUFFIXES = {".php", ".css", ".json", ".js", ".webp", ".png", ".ico", ".jpg", ".jpeg"}


def sha256(data):
    return hashlib.sha256(data).hexdigest()


def json_bytes(value):
    return (json.dumps(value, sort_keys=True, ensure_ascii=False, indent=2) + "\n").encode("utf-8")


def git(*args):
    result = subprocess.run(["git", *args], cwd=ROOT, capture_output=True, text=True, encoding="utf-8")
    return result.stdout.strip() if result.returncode == 0 else None


def source_context():
    remote = git("remote", "get-url", "origin")
    # checkout's HTTPS URL may omit Git's optional .git suffix. Keep the
    # public repository identity stable across GitHub CI and public clones.
    if remote and remote.startswith("https://github.com/"):
        remote = remote.rstrip("/")
        if not remote.endswith(".git"):
            remote += ".git"
    return {
        "commit": git("rev-parse", "HEAD"),
        "dirty": git("status", "--porcelain", "--untracked-files=all") != "",
        "remote": remote,
    }


def checked_file(path):
    if not path.is_file() or path.is_symlink():
        raise ValueError("Package input must be an ordinary file: " + str(path.relative_to(ROOT)))
    relative = path.relative_to(ROOT)
    if set(relative.parts) & FORBIDDEN_PARTS or path.suffix.lower() in FORBIDDEN_SUFFIXES or path.name == "wp-config.php" or path.name.startswith(".env"):
        raise ValueError("Private/dependency file cannot be packaged: " + relative.as_posix())
    data = path.read_bytes()
    # Git can check out text as CRLF on Windows. Packages use canonical LF so
    # the same clean source commit reproduces the same bytes on Linux.
    return data.replace(b"\r\n", b"\n") if path.suffix.lower() in TEXT_SUFFIXES or path.name == "LICENSE" else data


def inventory(entries):
    return [{"path": path, "sha256": sha256(data), "size_bytes": len(data)} for path, data in sorted(entries.items())]


def write_zip(path, entries):
    # Optimized PNG/WebP/ICO already compress well. ZIP_STORED also avoids
    # zlib-version differences when reproducing the artifact on another OS.
    with zipfile.ZipFile(path, "w", compression=zipfile.ZIP_STORED, allowZip64=True) as archive:
        for name, data in sorted(entries.items()):
            if name.startswith("/") or ".." in Path(name).parts or "\\" in name:
                raise ValueError("Unsafe ZIP entry")
            info = zipfile.ZipInfo(name, ZIP_TIME)
            info.create_system = 3
            info.external_attr = 0o100644 << 16
            info.compress_type = zipfile.ZIP_STORED
            archive.writestr(info, data)
    payload = path.read_bytes()
    return {"name": path.name, "sha256": sha256(payload), "size_bytes": len(payload), "entry_count": len(entries), "input_tree_sha256": sha256(json_bytes(inventory(entries)))}


def validate_source(require_launch):
    spec = importlib.util.spec_from_file_location("jvt_source_checks", ROOT / "scripts/check-source.py")
    module = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(module)
    result = module.validate(require_launch)
    if result["errors"]:
        raise ValueError("Source validation failed: " + "; ".join(result["errors"]))
    return result["counts"]


def build(output, release=False, expected_commit=None):
    style = (THEME / "style.css").read_text(encoding="utf-8")
    match = re.search(r"^Version:\s*([0-9]+\.[0-9]+\.[0-9]+(?:[-+][A-Za-z0-9.-]+)?)\s*$", style, re.M)
    if not match:
        raise ValueError("Theme Version header is missing or invalid")
    version = match[1]
    source = source_context()
    if expected_commit and source["commit"] != expected_commit:
        raise ValueError("Current source commit differs from --expected-commit")
    if release and (source["dirty"] or not source["commit"]):
        raise ValueError("An immutable release candidate requires an exact clean committed source tree")
    counts = validate_source(release and version == "1.0.0")
    # 0.1.0 remains a preview even when built from a clean checkpoint.
    mode = "preview" if not release or version.startswith("0.") else "release_candidate"
    metadata = {"schema_version": 1, "website_version": version, "mode": mode, "source": source,
                "delivery_scope": "local technical candidate and public source handoff; IONOS deployment is deferred to a separate session",
                "hosting_status": "not_deployed_by_this_build",
                "code_license": "GPL-2.0-or-later", "artwork_terms": "See ARTWORK_LICENSE.md and per-slot rights; artwork is excluded from the default code license.",
                "determinism": "Sorted entries; fixed 1980-01-01 ZIP timestamps; regular 0644 files; ZIP_STORED; canonical JSON metadata.",
                "validation_counts": counts}
    manifest = json.loads((ROOT / "assets/manifest.json").read_text(encoding="utf-8"))
    derivatives = {d["path"]: (s, d) for s in manifest["slots"] for d in s.get("derivatives", [])}
    theme_entries = {}
    for path in sorted(THEME.rglob("*")):
        if path.is_file():
            if path.suffix.lower() not in THEME_SUFFIXES and path.name not in LICENSES:
                raise ValueError("Unexpected nonruntime theme input: " + path.relative_to(THEME).as_posix())
            theme_entries["jvuorela-tools/" + path.relative_to(THEME).as_posix()] = checked_file(path)
    for name in LICENSES:
        theme_entries["jvuorela-tools/" + name] = checked_file(ROOT / name)
    provenance = []
    by_hash = {d["sha256"]: (s, d) for s, d in derivatives.values()}
    for path in sorted(THEME.rglob("*")):
        if path.is_file() and path.suffix.lower() in {".png", ".webp", ".ico", ".jpg", ".jpeg"}:
            slot, derivative = by_hash[sha256(path.read_bytes())]
            provenance.append({"runtime_path": path.relative_to(THEME).as_posix(), "slot_id": slot["slot_id"],
                "approved_derivative": derivative["path"], "sha256": derivative["sha256"], "source_sha256": slot["source_sha256"], "rights": slot["rights"]})
    theme_entries["jvuorela-tools/ASSET_PROVENANCE.json"] = json_bytes({"schema_version": 1, "runtime_images": provenance})
    theme_entries["jvuorela-tools/BUILD.json"] = json_bytes({**metadata, "artifact": "theme", "inputs": inventory(theme_entries)})

    setup_files = LICENSES + ["assets/manifest.json", "content/pages.json", "content/site.json", "content/tools.json", "content/releases.json",
        "scripts/import-content.php", "scripts/export-content.php", "scripts/generate-content.py", "docs/CONTENT_IMPORT.md", "docs/BUILD.md", "docs/DEPLOYMENT_HANDOFF.md"]
    optional_files = ["content/impressum.html", "content/privacy.html", "assets/approved/README.md", "docs/LOCAL_SETUP.md",
        "scripts/setup-local.ps1", "scripts/install-local.php", "scripts/local-credentials.php", "scripts/router.php",
        "evidence/release-verification.json", "evidence/release-facts.md"]
    setup_files += [name for name in optional_files if (ROOT / name).is_file()]
    setup_files += sorted(derivatives)
    setup_entries = {"jvuorela-tools-setup/" + name: checked_file(ROOT / name) for name in sorted(set(setup_files))}
    setup_entries["jvuorela-tools-setup/BUILD.json"] = json_bytes({**metadata, "artifact": "content_media_setup", "content_state": "draft_seed; hosted native content remains authoritative", "inputs": inventory(setup_entries)})

    # Catch concurrent edits before declaring a release candidate clean.
    if release and source_context() != source:
        raise ValueError("Source changed while building; repeat from the intended clean commit")
    output.mkdir(parents=True, exist_ok=True)
    artifacts = [write_zip(output / f"jvuorela-tools-{version}.zip", theme_entries),
                 write_zip(output / f"jvuorela-tools-content-media-{version}.zip", setup_entries)]
    index = {**metadata, "artifacts": artifacts}
    index_bytes = json_bytes(index)
    (output / "artifacts.json").write_bytes(index_bytes)
    checks = {a["name"]: a["sha256"] for a in artifacts}
    checks["artifacts.json"] = sha256(index_bytes)
    (output / "SHA256SUMS").write_text("".join(f"{digest}  {name}\n" for name, digest in sorted(checks.items())), encoding="utf-8", newline="\n")
    return index


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--output", type=Path, default=ROOT / "dist")
    parser.add_argument("--release", action="store_true", help="Require an exact clean source commit; v1.0.0 also requires all seven release records")
    parser.add_argument("--expected-commit", help="Require this exact local commit")
    args = parser.parse_args()
    try:
        result = build(args.output.resolve(), args.release, args.expected_commit)
        print(json.dumps(result, indent=2))
    except (OSError, ValueError, KeyError) as error:
        print("Package build failed: " + str(error), file=sys.stderr)
        sys.exit(1)


if __name__ == "__main__":
    main()
