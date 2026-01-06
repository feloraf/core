#!/usr/bin/env bash
set -e

# Dynamic paths
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"

SRC_PATH="$PROJECT_ROOT/src"
PHAR_PATH="$SCRIPT_DIR/bin/phpmd.phar"

# Ensure bin directory exists
mkdir -p "$SCRIPT_DIR/bin"

# Download phpmd.phar if it doesn't exist
if [[ ! -f "$PHAR_PATH" ]]; then
    echo "Downloading phpmd.phar..."
    wget -q https://phpmd.org/static/latest/phpmd.phar -O "$PHAR_PATH"
    chmod +x "$PHAR_PATH"
fi

# Set PHP error reporting without deprecated warnings
PHP_OPTS=(-d "error_reporting=E_ALL & ~E_DEPRECATED")

# Determine output format
# Use 'github' format in GitHub Actions, 'text' locally
if [[ "$GITHUB_ACTIONS" == "true" ]]; then
    FORMAT="github"
else
    FORMAT="text"
fi

# Run PHPMD
php "${PHP_OPTS[@]}" "$PHAR_PATH" "$SRC_PATH" "$FORMAT" codesize,naming,cleancode
