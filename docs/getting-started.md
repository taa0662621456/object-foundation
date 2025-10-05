# Getting Started

Install:
```bash
composer require isponsor/object-foundation
```

Try CLI:
```bash
./bin/foundation foundation:entity:info "Examples\\SymfonyDemo\\Entity\\DemoEntity"
```

Create your first entity:
```bash
./bin/foundation foundation:entity:init Product --namespace="App\\Entity" --traits="EntityFoundationTrait,LocaleAwareTrait,TranslatableTrait"
```