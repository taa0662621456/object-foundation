#!/usr/bin/env bash
set -euo pipefail

violations_core=$(grep -RIn --include="*.php" -E "^\s*use\s+(Symfony\\|Illuminate\\)" src | grep -v "src/Bridge/" || true)
if [[ -n "$violations_core" ]]; then
  echo "❌ Atomicity violation: Symfony/Laravel imports in core:"
  echo "$violations_core"
  exit 1
fi

violations_cmd=$(grep -RIn --include="*.php" -E "(AsCommand\s*\(|extends\s+Command\b)" src | grep -v "src/Bridge/Symfony/Command" || true)
if [[ -n "$violations_cmd" ]]; then
  echo "❌ Atomicity violation: Console command outside src/Bridge/Symfony/Command:"
  echo "$violations_cmd"
  exit 1
fi

echo "✅ Atomicity OK for src/ and Bridge/"
