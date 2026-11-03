#!/usr/bin/env bash

set -u

ZED_MAIN_GIT_WORKTREE=${ZED_MAIN_GIT_WORKTREE:-}
ZED_WORKTREE_ROOT=${ZED_WORKTREE_ROOT:-}

if [ -z "$ZED_MAIN_GIT_WORKTREE" ] || [ -z "$ZED_WORKTREE_ROOT" ]; then
    echo "This file should only be called from Zed create worktree" >&2
    exit 1
fi

files=(
    "workbench/.env"
)

dir_links=(
    ".agents"
)

for file in "${files[@]}"; do
    [ -f "$ZED_MAIN_GIT_WORKTREE/$file" ] && cp -n "$ZED_MAIN_GIT_WORKTREE/$file" "$ZED_WORKTREE_ROOT/$file"
done

for dir in "${dir_links[@]}"; do
    [ -d "$ZED_MAIN_GIT_WORKTREE/$dir" ] && ln -s "$ZED_MAIN_GIT_WORKTREE/$dir" "$ZED_WORKTREE_ROOT/$dir"
done

cd "$ZED_WORKTREE_ROOT" || exit 0

composer install --no-interaction
pnpm i
