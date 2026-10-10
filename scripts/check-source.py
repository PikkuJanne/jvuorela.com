#!/usr/bin/env python3
"""Check public source, artwork provenance and portable content.

SPDX-License-Identifier: GPL-2.0-or-later
This check needs committed derivatives, not private image masters or a server.
"""

import argparse
import hashlib
import json
from pathlib import Path, PurePosixPath
import re
import subprocess
import sys

ROOT = Path(__file__).resolve().parents[1]
THEME = ROOT / "theme" / "jvuorela-tools"
TOOLS = {"winbooksplit", "winpdfmerger", "winvidcompress", "universalpodcastdownloader", "winaudioclean", "winimagenormalizer", "audiofileforphone"}
IMAGE_EXTENSIONS = {".webp", ".png", ".ico", ".jpg", ".jpeg"}


def read_json(path):
    return json.loads(path.read_text(encoding="utf-8"))


def sha256(path):
    return hashlib.sha256(path.read_bytes()).hexdigest()


def git(*args):
    result = subprocess.run(["git", *args], cwd=ROOT, capture_output=True, text=True, encoding="utf-8")
    return result.stdout.strip() if result.returncode == 0 else None


def safe_relative(value):
    if not isinstance(value, str) or not value or "\\" in value or ":" in value:
        return False
    path = PurePosixPath(value)
    return not path.is_absolute() and ".." not in path.parts and value == path.as_posix()


def validate(require_launch=False):
    errors = []
    counts = {}
    def need(condition, message):
        if not condition:
            errors.append(message)
    for name in ["LICENSE", "LICENSE_SCOPE.md", "ARTWORK_LICENSE.md", "THIRD_PARTY_NOTICES.md"]:
        need((ROOT / name).is_file(), "Missing license/rights file: " + name)
    license_text = (ROOT / "LICENSE").read_text(encoding="utf-8") if (ROOT / "LICENSE").is_file() else ""
    need("GNU GENERAL PUBLIC LICENSE" in license_text and "Version 2" in license_text, "LICENSE must contain GNU GPL version 2")
    scope = (ROOT / "LICENSE_SCOPE.md").read_text(encoding="utf-8") if (ROOT / "LICENSE_SCOPE.md").is_file() else ""
    need("GPL-2.0-or-later" in scope and "Artwork" in scope, "License scope must distinguish code and visual material")
    style = (THEME / "style.css").read_text(encoding="utf-8") if (THEME / "style.css").is_file() else ""
    need("GNU General Public License v2 or later" in style, "Theme metadata must state GPL v2 or later")
    need(bool(re.search(r"^Version:\s*\d+\.\d+\.\d+", style, re.M)), "Theme needs an explicit semantic version")
    for name in ["index.php", "functions.php", "header.php", "footer.php", "page.php", "404.php", "theme.json"]:
        need((THEME / name).is_file(), "Missing theme runtime file: " + name)

    manifest = read_json(ROOT / "assets/manifest.json")
    slots = manifest.get("slots", [])
    counts["source_slots"] = len(slots)
    counts["selected_slots"] = sum(bool(s.get("required_for_launch")) for s in slots)
    need(len(slots) == 47 and len({s.get("slot_id") for s in slots}) == 47, "Artwork inventory must contain 47 unique slots")
    need(counts["selected_slots"] == manifest.get("required_slot_count") == 33, "Artwork must have 33 selected website slots")
    need(manifest.get("artwork_creation_by_codex") is False, "Artwork creation policy changed")
    derivatives = {}
    for slot in slots:
        sid = slot.get("slot_id", "<missing>")
        source = slot.get("actual_source_file", slot.get("source_file", ""))
        need(safe_relative(source) and PurePosixPath(source).parts[0] in ("Website Assets", "PS Visuals"), sid + ": source must be an owner-supplied relative project path")
        need(bool(re.fullmatch(r"[0-9a-f]{64}", slot.get("source_sha256") or "")), sid + ": missing source SHA-256")
        rights = slot.get("rights", {})
        need(bool(rights.get("creator_or_source")) and bool(rights.get("publication_basis")), sid + ": source/publication rights must be recorded separately")
        if slot.get("required_for_launch"):
            need(bool(slot.get("derivatives")), sid + ": selected slot needs an approved derivative")
        for derivative in slot.get("derivatives", []):
            path = derivative.get("path", "")
            is_safe = safe_relative(path) and path.startswith("assets/approved/") and Path(path).suffix.lower() in IMAGE_EXTENSIONS
            need(is_safe, sid + ": unsafe derivative path")
            if not is_safe:
                continue
            need(path not in derivatives, "Derivative path is mapped more than once: " + path)
            derivatives[path] = derivative
            target = ROOT / path
            need(target.is_file() and not target.is_symlink(), "Derivative missing or linked: " + path)
            if target.is_file() and not target.is_symlink():
                need(sha256(target) == derivative.get("sha256"), "Derivative bytes differ from provenance: " + path)
                need(target.stat().st_size == derivative.get("size_bytes"), "Derivative size differs from provenance: " + path)
            need(derivative.get("source_sha256") == slot.get("source_sha256"), "Derivative/source hash mismatch: " + path)
            need(derivative.get("width", 0) > 0 and derivative.get("height", 0) > 0, "Derivative dimensions missing: " + path)
    counts["derivatives"] = len(derivatives)
    approved = ROOT / "assets/approved"
    for path in approved.rglob("*") if approved.is_dir() else []:
        if path.is_file() and path.suffix.lower() in IMAGE_EXTENSIONS:
            need(path.relative_to(ROOT).as_posix() in derivatives, "Unmapped approved image: " + path.relative_to(ROOT).as_posix())

    approved_hashes = {d.get("sha256") for d in derivatives.values()}
    for path in THEME.rglob("*"):
        if path.is_symlink():
            errors.append("Theme symlinks are not portable: " + path.relative_to(ROOT).as_posix())
        if not path.is_file():
            continue
        relative = path.relative_to(ROOT).as_posix()
        if path.suffix.lower() in IMAGE_EXTENSIONS:
            need(sha256(path) in approved_hashes, "Theme image is not a traced approved derivative: " + relative)
        if path.suffix.lower() in {".php", ".css"}:
            text = path.read_text(encoding="utf-8")
            need("GPL-2.0-or-later" in text or path.name == "style.css", "Source GPL notice missing: " + relative)
            need("api.github.com" not in text, "Live GitHub API use is forbidden in theme: " + relative)
            if path.suffix == ".php":
                need(not re.search(r"\b(shell_exec|passthru|proc_open|system)\s*\(", text), "Server-side execution is forbidden in theme: " + relative)
            if path.suffix == ".css":
                for operand in re.findall(r"url\(\s*['\"]?([^'\")]+)['\"]?\s*\)", text):
                    operand = operand.strip()
                    if operand.startswith("#"):
                        continue
                    need(not re.match(r"(?:https?:|//|data:)", operand, re.I), "CSS image/font must be a traced local file: " + relative)
                    target = (path.parent / operand.split("?", 1)[0]).resolve()
                    need(target.is_relative_to(THEME.resolve()) and target.is_file(), "CSS referenced file missing or outside theme: " + operand)
                    if target.is_file() and target.suffix.lower() in IMAGE_EXTENSIONS:
                        need(sha256(target) in approved_hashes, "CSS imagery is not traced to owner delivery: " + operand)

    pages = read_json(ROOT / "content/pages.json") if (ROOT / "content/pages.json").is_file() else {"pages": []}
    entries = pages.get("pages", [])
    counts["portable_pages"] = len(entries)
    ids = {p.get("id") for p in entries}
    need(ids == TOOLS | {"home", "impressum", "privacy", "not-found"}, "Portable setup must contain Home, seven tools, two support Pages and 404 content")
    need(len(entries) == len(ids), "Duplicate portable page ID")
    selected_ids = {s["slot_id"] for s in slots if s.get("required_for_launch")}
    for page in entries:
        content = page.get("content", "")
        need(isinstance(content, str) and "<!-- wp:" in content, "Page requires editable core blocks: " + str(page.get("id")))
        for sid in re.findall(r"\{\{(?:asset|attachment):([^}]+)\}\}", content):
            need(sid in selected_ids, "Page references unknown/nonselected media slot: " + sid)
        need(not re.search(r'"id"\s*:\s*\d+', content), "Portable page contains environment-specific attachment ID: " + str(page.get("id")))
        need(not re.search(r"href=['\"]#['\"]", content), "Page has a fake actionable link: " + str(page.get("id")))

    releases = read_json(ROOT / "content/releases.json").get("records", [])
    counts["verified_tool_releases"] = sum(r.get("launch_verification", {}).get("status") == "passed" for r in releases)
    need({r.get("tool_id") for r in releases} == TOOLS, "Release records must name exactly seven canonical tools")
    if require_launch:
        for record in releases:
            v = record.get("launch_verification", {})
            need(v.get("status") == "passed" and v.get("release_is_stable") is True and v.get("checksum_matches") is True and v.get("copy_matches_release") is True and bool(v.get("tag_commit")), "Launch release gate incomplete: " + record["tool_id"])

    tracked = git("ls-files", "-z")
    if tracked:
        forbidden = {"Website Assets", "PS Visuals", "private", "backups", "local-wordpress", "node_modules", "vendor", ".runtime"}
        for name in tracked.split("\0"):
            if not name:
                continue
            parts = PurePosixPath(name).parts
            need(not (set(parts) & forbidden), "Private/master/dependency path is tracked: " + name)
            need(Path(name).name != "wp-config.php" and not name.endswith((".sql", ".sql.gz", ".dump", ".pem", ".pfx", ".p12")), "Private configuration/backup is tracked: " + name)
            candidate = ROOT / name
            if candidate.is_file() and candidate.suffix.lower() in {".py", ".php", ".js", ".json", ".yml", ".yaml", ".md", ".txt"}:
                text = candidate.read_text(encoding="utf-8", errors="replace")
                need(not re.search(r"-----BEGIN (?:RSA |EC |OPENSSH )?PRIVATE KEY-----|gh[pousr]_[A-Za-z0-9]{36,}", text), "Possible private key/token in tracked text: " + name)
    return {"status": "FAIL" if errors else "PASS", "scope": "public source/provenance/portable setup; not hosting or final launch acceptance", "counts": counts, "errors": errors}


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--require-launch", action="store_true", help="Require all seven stable, checksummed release records")
    args = parser.parse_args()
    try:
        result = validate(args.require_launch)
    except (OSError, ValueError, KeyError, TypeError) as error:
        result = {"status": "FAIL", "errors": [str(error)]}
    print(json.dumps(result, indent=2))
    sys.exit(result["status"] != "PASS")


if __name__ == "__main__":
    main()
