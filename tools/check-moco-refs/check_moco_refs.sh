#!/usr/bin/env bash
# Usage: check_moco_refs.sh <responses_dir> <local_dir> <moco.json> [<moco.json> ...]
#
# Checks that:
# 1. All files referenced in any moco.json that live under responses_dir exist on disk
# 2. All files in responses_dir are referenced in at least one moco.json

set -euo pipefail

RESPONSES_DIR="${1}"
LOCAL_DIR="${2}"
shift 2
MOCO_FILES=("$@")
MOCO_PREFIX="/var/moco/"

errors=0

REFS_FILE=$(mktemp)
DISK_FILE=$(mktemp)
trap 'rm -f "$REFS_FILE" "$DISK_FILE"' EXIT

for moco_json in "${MOCO_FILES[@]}"; do
	jq -r '.[].response.file // empty' "$moco_json" | sed "s|${MOCO_PREFIX}|${LOCAL_DIR}/|g"
done | grep "^${RESPONSES_DIR}/" | LC_ALL=C sort -u > "$REFS_FILE"

if [ -d "$RESPONSES_DIR" ]; then
	find "$RESPONSES_DIR" -type f | LC_ALL=C sort > "$DISK_FILE"
else
	touch "$DISK_FILE"
fi

while IFS= read -r path; do
	echo "❌ Missing file referenced in moco.json: $path"
	errors=$((errors + 1))
done < <(LC_ALL=C comm -23 "$REFS_FILE" "$DISK_FILE")

while IFS= read -r file; do
	echo "❌ Unreferenced file in responses: $file"
	errors=$((errors + 1))
done < <(LC_ALL=C comm -13 "$REFS_FILE" "$DISK_FILE")

if [ "$errors" -gt 0 ]; then
	echo "Found $errors issue(s)."
	exit 1
fi

echo "✅ $(IFS=', '; echo "${MOCO_FILES[*]}") — all references OK."
