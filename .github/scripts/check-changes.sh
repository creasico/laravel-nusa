#!/bin/bash
set -e

echo "::group::🔍 Checking for database changes..."

rm database/nusa.sqlite
cp database/nusa.$GIT_BRANCH.sqlite database/nusa.sqlite

# Check distribution database changes
if git diff --quiet database/nusa.sqlite; then
    echo "dist-changed=false" >> $GITHUB_OUTPUT
    echo "ℹ️ No changes to distribution database"
else
    echo "dist-changed=true" >> $GITHUB_OUTPUT
    echo "✅ Distribution database has changes"
fi

echo "::endgroup::"
echo "🏁 Change detection completed!"
