## Atomicity Contract — Cross-platform Guard

The Object Foundation Core stays framework-agnostic.  
All integrations with frameworks (Symfony, Laravel, etc.) live under `Bridge/`.

### Added in v2.5.3
- Cross-platform atomicity check via `composer test:atomicity`
- PHP version of atomicity test (works on Windows/macOS/Linux)
- Still includes bash version for CI
- `ramsey/uuid` integrated via `UuidGeneratorInterface`

**Run test:**
```bash
composer test:atomicity
```

Maintainer: **Oleksandr Tishchenko**, Marketing America Corp
