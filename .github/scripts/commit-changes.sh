#!/bin/bash
set -e

echo "::group::🔍 Checking for database changes..."

ls -lah database/*.sqlite

rm database/nusa.sqlite
cp database/nusa.$GIT_BRANCH.sqlite database/nusa.sqlite

DB_CHANGED=0

# Check distribution database changes
if git diff --quiet database/nusa.sqlite; then
    echo "dist-changed=false" >> $GITHUB_OUTPUT
    echo "ℹ️ No changes to distribution database"
else
    DB_CHANGED=1
    echo "dist-changed=true" >> $GITHUB_OUTPUT
    echo "✅ Distribution database has changes"
fi

echo "::endgroup::"

[[ "$DB_CHANGED" == 0 ]] && exit 0;

# Configure git
git config --local user.email "41898282+github-actions[bot]@users.noreply.github.com"
git config --local user.name "github-actions[bot]"

echo "::group::📋 Files to be committed:"

git status --porcelain database/nusa.sqlite

# Add any changed database files
git add database/nusa.sqlite

echo "::endgroup::"
echo "::group::💬 Committing with message:"

# Create commit message based on trigger type
COMMIT_MSG="chore(deps): update distribution database [skip ci]

This automated update includes:
- Fresh import of upstream submodule data

Author: $COMMIT_AUTHOR"

# Commit and push
git commit -m "$COMMIT_MSG" && git push

echo "::endgroup::"
echo "✅ Changes committed and pushed successfully!"
