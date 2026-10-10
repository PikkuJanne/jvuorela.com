#!/usr/bin/env python3
"""Verify public release metadata and byte integrity without using tool artwork.

SPDX-License-Identifier: GPL-2.0-or-later

Uses unauthenticated HTTPS for public release pages, API metadata, README text,
checksum text and archive bytes. Archive bytes are hashed in memory and never
extracted. This is not tool execution or application compatibility testing.
"""

import concurrent.futures
import hashlib
import json
from pathlib import Path
import re
import subprocess
import urllib.error
import urllib.request
from datetime import datetime, timezone

ROOT = Path(__file__).resolve().parents[1]
REPOS = [
    ("winbooksplit", "WinBookSplit"),
    ("winpdfmerger", "WinPDFMerger"),
    ("winvidcompress", "WinVidCompress"),
    ("universalpodcastdownloader", "UniversalPodcastDownloader"),
    ("winaudioclean", "WinAudioClean"),
    ("winimagenormalizer", "WinImgNormalizer"),
    ("audiofileforphone", "AudioFileForPhone"),
]
HEADERS = {"User-Agent": "jvuorela-website-release-verification", "Accept": "application/vnd.github+json"}


def public_get(url, accept=None, method="GET"):
    headers = dict(HEADERS)
    if accept:
        headers["Accept"] = accept
    request = urllib.request.Request(url, headers=headers, method=method)
    try:
        with urllib.request.urlopen(request, timeout=60) as response:
            return {"status": response.status, "url": response.url, "bytes": response.read()}
    except urllib.error.HTTPError as error:
        return {"status": error.code, "url": url, "bytes": b""}


def gh_json(endpoint):
    result = subprocess.run(["gh", "api", endpoint], capture_output=True, text=True, encoding="utf-8")
    if result.returncode:
        return None
    return json.loads(result.stdout)


def tag_commit(repo):
    ref = gh_json(f"repos/PikkuJanne/{repo}/git/ref/tags/v1.0.0")
    if not ref:
        return None
    obj = ref["object"]
    for _ in range(5):
        if obj["type"] == "commit":
            return obj["sha"]
        if obj["type"] != "tag":
            return None
        tag = gh_json(f"repos/PikkuJanne/{repo}/git/tags/{obj['sha']}")
        if not tag:
            return None
        obj = tag["object"]
    return None


def checksum_matches(checksum_texts, name, sha256):
    if not sha256:
        return False
    for check in checksum_texts:
        for line in check["text"].splitlines():
            match = re.fullmatch(r"([a-fA-F0-9]{64})\s+\*?(.+)", line.strip())
            if match and match[1].lower() == sha256.lower() and match[2] == name:
                return True
    return False


def verify(item):
    tool_id, repo = item
    page_url = f"https://github.com/PikkuJanne/{repo}/releases/tag/v1.0.0"
    api_url = f"https://api.github.com/repos/PikkuJanne/{repo}/releases/tags/v1.0.0"
    observed_at = datetime.now(timezone.utc).isoformat(timespec="seconds").replace("+00:00", "Z")
    response = public_get(api_url)
    page = public_get(page_url, "text/html")
    data = json.loads(response["bytes"]) if response["status"] == 200 else None
    repo_data = gh_json(f"repos/PikkuJanne/{repo}")
    record = {
        "tool_id": tool_id,
        "repo": f"PikkuJanne/{repo}",
        "expected_tag": "v1.0.0",
        "checked_at": observed_at,
        "release_api_url": api_url,
        "unauthenticated_api_http_status": response["status"],
        "unauthenticated_release_page_http_status": page["status"],
        "public_repo": None if not repo_data else not repo_data["private"],
        "repository_license_spdx": None if not repo_data or not repo_data.get("license") else repo_data["license"]["spdx_id"],
        "observed_status": "not_published" if response["status"] == 404 else "unverified",
        "release_page_url": None,
        "tag_commit": tag_commit(repo),
        "assets": [],
        "checksum_texts": [],
        "runtime_verification": None,
        "readme_sources": [],
        "application_execution": "not_performed",
        "artwork_policy": "No image file was requested, rendered, extracted or used. Runtime ZIP bytes were hashed in memory only.",
    }
    if response["status"] == 404:
        listing = public_get(f"https://api.github.com/repos/PikkuJanne/{repo}/releases")
        record["unauthenticated_release_listing_http_status"] = listing["status"]
        record["public_release_listing_count"] = len(json.loads(listing["bytes"])) if listing["status"] == 200 else None
    if data:
        record.update({
            "release_id": data["id"], "release_page_url": data["html_url"], "tag_name": data["tag_name"],
            "name": data["name"], "draft": data["draft"], "prerelease": data["prerelease"],
            "created_at": data["created_at"], "published_at": data["published_at"],
            "updated_at": data["updated_at"], "target_commitish": data["target_commitish"],
            "release_is_stable": data["tag_name"] == "v1.0.0" and not data["draft"] and not data["prerelease"],
        })
        record["observed_status"] = "published" if record["release_is_stable"] and page["status"] == 200 else "unverified"
        for asset in data["assets"]:
            mapped = {"id": asset["id"], "name": asset["name"], "url": asset["browser_download_url"],
                      "size_bytes": asset["size"], "download_count_at_check": asset["download_count"],
                      "api_digest": asset.get("digest"), "state": asset["state"], "content_type": asset["content_type"]}
            record["assets"].append(mapped)
            if "sha256" in asset["name"].lower():
                fetched = public_get(asset["browser_download_url"], "application/octet-stream")
                record["checksum_texts"].append({"name": asset["name"], "url": asset["browser_download_url"],
                    "http_status": fetched["status"], "text": fetched["bytes"].decode("utf-8-sig").strip(),
                    "sha256": hashlib.sha256(fetched["bytes"]).hexdigest() if fetched["status"] == 200 else None})
        for asset in record["assets"]:
            if not asset["name"].lower().endswith(".zip"):
                continue
            downloaded = public_get(asset["url"], "application/octet-stream")
            actual_hash = hashlib.sha256(downloaded["bytes"]).hexdigest() if downloaded["status"] == 200 else None
            checksum_match = checksum_matches(record["checksum_texts"], asset["name"], actual_hash)
            record["runtime_verification"] = {
                "asset_name": asset["name"], "url": asset["url"], "http_status": downloaded["status"],
                "downloaded_size_bytes": len(downloaded["bytes"]) if downloaded["status"] == 200 else None,
                "sha256": actual_hash, "size_matches_api": downloaded["status"] == 200 and len(downloaded["bytes"]) == asset["size_bytes"],
                "digest_matches_api": actual_hash is not None and asset["api_digest"] == "sha256:" + actual_hash,
                "checksum_matches": checksum_match, "extracted": False,
            }
    readme_refs = ["main"] + (["v1.0.0"] if data else [])
    for ref in readme_refs:
        url = f"https://raw.githubusercontent.com/PikkuJanne/{repo}/{ref}/README.md"
        page_url = f"https://github.com/PikkuJanne/{repo}/blob/{ref}/README.md"
        readme = public_get(url, "text/plain")
        documentation_page = public_get(page_url, "text/html", "HEAD")
        commit = gh_json(f"repos/PikkuJanne/{repo}/commits/{ref}")
        record["readme_sources"].append({"ref": ref, "url": page_url, "raw_url": url,
            "source_commit": commit["sha"] if commit else None,
            "http_status": documentation_page["status"], "raw_http_status": readme["status"],
            "documentation_http_checked_at": datetime.now(timezone.utc).isoformat(timespec="seconds").replace("+00:00", "Z"),
            "sha256": hashlib.sha256(readme["bytes"]).hexdigest() if readme["status"] == 200 else None})
    return record


def main():
    with concurrent.futures.ThreadPoolExecutor(max_workers=3) as pool:
        records = list(pool.map(verify, REPOS))
    evidence = {
        "schema_version": 1,
        "checked_at": datetime.now(timezone.utc).isoformat(timespec="seconds").replace("+00:00", "Z"),
        "scope": "Exact tag/public visibility/stable flags, resolved tag commit, direct runtime download byte integrity and current/tagged README text retrieval. No tool execution or application compatibility certification.",
        "all_seven_public_stable": all(r.get("release_is_stable") and r["unauthenticated_release_page_http_status"] == 200 for r in records),
        "records": records,
    }
    target = ROOT / "evidence" / "release-verification.json"
    target.parent.mkdir(parents=True, exist_ok=True)
    target.write_text(json.dumps(evidence, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    print(json.dumps({"evidence": str(target), "all_seven_public_stable": evidence["all_seven_public_stable"], "results": [
        {"repo": r["repo"], "status": r["observed_status"], "page_http": r["unauthenticated_release_page_http_status"], "tag_commit": r["tag_commit"], "runtime": r["runtime_verification"]} for r in records
    ]}, indent=2))


if __name__ == "__main__":
    main()
