#!/bin/bash
set -e

# Configure git
git config --local user.email "41898282+github-actions[bot]@users.noreply.github.com"
git config --local user.name "github-actions[bot]"

echo "::group::📋 Files to be committed:"

git status --porcelain database/nusa.sqlite || echo "No database changes to commit"

# Add any changed database files
git add database/nusa.sqlite

# Create commit message based on trigger type
COMMIT_MSG="chore(deps): update distribution database [skip ci]

This automated update includes:
- Fresh import of upstream submodule data

Author: $COMMIT_AUTHOR"

echo "::endgroup::"
echo "::group::💬 Committing with message:"
echo "$COMMIT_MSG"
echo "---"

# Commit and push
git commit -m "$COMMIT_MSG" && git push

echo "::endgroup::"
echo "✅ Changes committed and pushed successfully!"
