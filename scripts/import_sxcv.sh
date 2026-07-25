#!/usr/bin/env bash
set -euo pipefail

SRC_REPO="https://github.com/midland722-dev/sxcv.git"
TMP_DIR=$(mktemp -d)

echo "Cloning $SRC_REPO into $TMP_DIR"
git clone --depth=1 "$SRC_REPO" "$TMP_DIR"

echo "Copying files from $TMP_DIR to repo root (skipping existing files)..."
# Use rsync to copy files, excluding .git and skipping existing files to honor the "Default (skip)" choice
rsync -a --exclude='.git' --ignore-existing "$TMP_DIR"/ ./

# Show a summary of what changed
echo "Changes after copy:"
git status --porcelain || true

if [ -n "$(git status --porcelain)" ]; then
  git config user.name "github-actions[bot]"
  git config user.email "41898282+github-actions[bot]@users.noreply.github.com"
  git add -A
  git commit -m "Import files from midland722-dev/sxcv (skip conflicts)"
  echo "Pushing commit to origin"
  git push origin HEAD
else
  echo "No files were added (all would have conflicted or no new files)."
fi
