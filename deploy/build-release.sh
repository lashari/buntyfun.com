#!/usr/bin/env bash
# Builds a deployable release zip from the current committed HEAD, exactly as
# docs/DEPLOY.md describes: a clean export, production deps, built assets,
# nothing dev-only. Never touches the working copy's own vendor/node_modules —
# everything happens in a separate dist/ build directory.
#
# Usage: deploy/build-release.sh
# Output: dist/release-<short-hash>.zip

set -euo pipefail

cd "$(dirname "$0")/.."

if [ -n "$(git status --porcelain)" ]; then
    echo "Working tree has uncommitted changes — commit or stash before building a release." >&2
    exit 1
fi

HASH=$(git rev-parse --short HEAD)
BUILD_DIR="dist/release-${HASH}"
ZIP_NAME="release-${HASH}.zip"

echo "==> Building release ${HASH}"

rm -rf "$BUILD_DIR"
mkdir -p "$BUILD_DIR"

echo "==> Exporting committed tree at ${HASH} (untracked/gitignored files, including .env, never included)"
git archive HEAD | tar -x -C "$BUILD_DIR"

pushd "$BUILD_DIR" > /dev/null

echo "==> Installing production PHP dependencies"
COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader --no-interaction --quiet

echo "==> Building frontend assets"
npm ci --silent
npm run build --silent
rm -rf node_modules

echo "==> Removing dev-only paths that must never ship"
rm -rf tests .github deploy .styleci.yml phpunit.xml .editorconfig .gitattributes

popd > /dev/null

mkdir -p dist
rm -f "dist/${ZIP_NAME}"

echo "==> Zipping dist/${ZIP_NAME}"
(cd "$BUILD_DIR" && zip -rq "../${ZIP_NAME}" .)

SIZE=$(du -h "dist/${ZIP_NAME}" | cut -f1)

cat <<EOF

Release ready: dist/${ZIP_NAME} (${SIZE})

Next steps (full detail in docs/DEPLOY.md):
  1. Upload dist/${ZIP_NAME} via cPanel File Manager
  2. Extract into release/${HASH}/ on the server (never the live document root)
  3. Copy the server's existing .env into release/${HASH}/
  4. Symlink release/${HASH}/storage/app/public to the shared persistent upload folder
  5. Point the document root at release/${HASH}/public
  6. Run against the new release:
       php artisan migrate --force
       php artisan config:cache
       php artisan route:cache
       php artisan view:cache
  7. Confirm the site loads before deleting any old release folder
EOF
