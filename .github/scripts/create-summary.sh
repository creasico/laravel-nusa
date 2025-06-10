#!/bin/bash
set -e

echo "📊 Creating workflow summary..."

# Determine workflow type
WORKFLOW_TYPE="📦 Distribution Database Update"

# Start summary
echo "## $WORKFLOW_TYPE Summary" >> $GITHUB_STEP_SUMMARY
echo "" >> $GITHUB_STEP_SUMMARY

# Basic information
echo "### 📋 Workflow Information" >> $GITHUB_STEP_SUMMARY
echo "- **Trigger:** $COMMIT_MESSAGE" >> $GITHUB_STEP_SUMMARY
echo "- **Author:** $COMMIT_AUTHOR" >> $GITHUB_STEP_SUMMARY
echo "- **Branch:** $GITHUB_REF_NAME" >> $GITHUB_STEP_SUMMARY
echo "- **Commit:** [\`${GITHUB_SHA:0:7}\`](https://github.com/$GITHUB_REPOSITORY/commit/$GITHUB_SHA)" >> $GITHUB_STEP_SUMMARY
echo "" >> $GITHUB_STEP_SUMMARY

# Database status
echo "### 💾 Database Status" >> $GITHUB_STEP_SUMMARY
if [[ "$DIST_CHANGED" == "true" ]]; then
    echo "- 🔄 Database changes committed and pushed" >> $GITHUB_STEP_SUMMARY
else
    echo "- ℹ️ No database changes detected" >> $GITHUB_STEP_SUMMARY
fi
echo "" >> $GITHUB_STEP_SUMMARY

# Database file sizes (if available)
if [[ -f "database/nusa.sqlite" ]]; then
    DIST_SIZE=$(ls -lh database/nusa.sqlite | awk '{print $5}')
    echo "### 📏 Database Information" >> $GITHUB_STEP_SUMMARY
    echo "- **Distribution database size:** $DIST_SIZE" >> $GITHUB_STEP_SUMMARY

    # Count records if possible
    if command -v sqlite3 >/dev/null 2>&1; then
        PROVINCE_COUNT=$(sqlite3 database/nusa.sqlite "SELECT COUNT(*) FROM provinces;" 2>/dev/null || echo "N/A")
        REGENCY_COUNT=$(sqlite3 database/nusa.sqlite "SELECT COUNT(*) FROM regencies;" 2>/dev/null || echo "N/A")
        DISTRICT_COUNT=$(sqlite3 database/nusa.sqlite "SELECT COUNT(*) FROM districts;" 2>/dev/null || echo "N/A")
        VILLAGE_COUNT=$(sqlite3 database/nusa.sqlite "SELECT COUNT(*) FROM villages;" 2>/dev/null || echo "N/A")

        echo "- **Record counts:**" >> $GITHUB_STEP_SUMMARY
        echo "  - Provinces: $PROVINCE_COUNT" >> $GITHUB_STEP_SUMMARY
        echo "  - Regencies: $REGENCY_COUNT" >> $GITHUB_STEP_SUMMARY
        echo "  - Districts: $DISTRICT_COUNT" >> $GITHUB_STEP_SUMMARY
        echo "  - Villages: $VILLAGE_COUNT" >> $GITHUB_STEP_SUMMARY
    fi
    echo "" >> $GITHUB_STEP_SUMMARY
fi

# Footer
echo "---" >> $GITHUB_STEP_SUMMARY
echo "*Automated by GitHub Actions* 📦" >> $GITHUB_STEP_SUMMARY

echo "✅ Workflow summary created successfully!"
