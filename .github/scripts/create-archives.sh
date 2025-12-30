#!/bin/bash

# Exit on error
set -e

# Default values
VERSION=""

# Parse arguments
while [[ "$#" -gt 0 ]]; do
    case $1 in
        --version) VERSION="$2"; shift ;;
        *) echo "Unknown parameter passed: $1"; exit 1 ;;
    esac
    shift
done

if [ -z "$VERSION" ]; then
    echo "Error: Version argument is required. Usage: $0 --version <version>"
    exit 1
fi

# Resolve paths
PROJECT_ROOT=$(git rev-parse --show-toplevel)
OUTPUT_DIR="${PROJECT_ROOT}/workbench"
SOURCE_DIR="${PROJECT_ROOT}/resources/static"

# Check source directory
if [ ! -d "$SOURCE_DIR" ]; then
    echo "Error: Source directory $SOURCE_DIR does not exist."
    exit 1
fi

echo "Creating archives for version ${VERSION}..."

# Change to source directory to avoid including full path in zip
cd "$SOURCE_DIR"

# Create JSON archive
echo "Generating nusa.${VERSION}-json.zip..."
zip -q -r "${OUTPUT_DIR}/nusa.${VERSION}-json.zip" . -i "*.json"

# Create CSV archive
echo "Generating nusa.${VERSION}-csv.zip..."
zip -q -r "${OUTPUT_DIR}/nusa.${VERSION}-csv.zip" . -i "*.csv"

# Create GeoJSON archive
echo "Generating nusa.${VERSION}-geojson.zip..."
zip -q -r "${OUTPUT_DIR}/nusa.${VERSION}-geojson.zip" . -i "*.geojson"

# Create Combined archive
echo "Generating nusa.${VERSION}-static.zip..."
zip -q -r "${OUTPUT_DIR}/nusa.${VERSION}-static.zip" .

echo "Archives created successfully in ${OUTPUT_DIR}"
