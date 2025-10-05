#!/usr/bin/env bash
set -e

VERSION=$1

if [ -z "$VERSION" ]; then
  echo "Usage: ./bin/release.sh vX.Y.Z"
  exit 1
fi

echo ">>> Tagging version $VERSION"
git add .
git commit -m "chore: release $VERSION" || true
git tag $VERSION
git push origin main --tags

echo ">>> Release pushed. GitHub Actions will build, test and publish automatically."