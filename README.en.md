# 🧠 Object Foundation
**Atomic Core & Ontology Engine for PHP / Symfony / Laravel**

![Version](https://img.shields.io/badge/version-v2.5.x-blue)
![PHP](https://img.shields.io/badge/PHP-8.3+-brightgreen)
![License](https://img.shields.io/badge/license-MIT-lightgrey)

---

## 🌍 Overview
**Object Foundation** is an atomic core and ontology engine for self-describing PHP applications.  
Every entity in the system knows **who it is**, **what traits it has**, and **how it relates to others**.

It can serve as:
- a framework-independent core for PHP/Symfony/Laravel apps;  
- an introspection and manifest system for Doctrine entities;  
- an automatic **OpenAPI / GraphQL / JSON-LD** generator;  
- a reactive engine for **webhooks**, **outbox**, and **observability**.

---

## ⚙️ Core Principles

| Principle | Meaning |
|------------|----------|
| 🧩 **Atomicity** | Core is framework-agnostic (pure PHP) |
| 🔍 **Introspection** | Understands the nature and relations of entities |
| 🔄 **Portability** | All integrations are isolated in `Bridge/*` |
| 📡 **Exportability** | Everything can be exported as OpenAPI, GraphQL, RDF, JSON-LD |
| 📈 **Observability** | Outbox and Metrics provide event tracking |

---

## 🧱 Architecture
```
src/
 ├─ EntityTrait/           → atomic traits for entities
 ├─ Ontology/              → introspection, OQL, exporters
 ├─ Bridge/
 │   └─ Symfony/Command/   → console commands
 ├─ Events/                → outbox, metrics, webhooks
 └─ Tests/Atomic/          → integrity tests (no framework deps)
```

---

## 🔹 1. Traits Library
Common atomic traits for all PHP/Doctrine entities.

```php
use ObjectFoundation\EntityTrait\ObjectAuditTrait;

class Project {
    use ObjectAuditTrait;
}
```

Includes:
- universal properties: `id`, `uuid`, `slug`, `version`
- audit and lifecycle: `createdAt`, `updatedAt`, `deletedAt`
- config system (`config`, `configEncrypted`)
- metadata: `expiresAt`, `ipRestriction`, `token`, etc.

---

## 🔹 2. Ontology Engine
The semantic brain of Object Foundation.  
It scans your entities, builds manifests, and understands traits, interfaces, and relationships.

Main components:
- `ManifestCollector` — extracts metadata from PHP classes.  
- `OQL` — Ontology Query Language (like SQL, but for code).  
- `Exporter` — converts ontology into OpenAPI, GraphQL, JSON-LD, or RDF/Turtle.

Example commands:
```bash
php bin/foundation foundation:ontology:query "SELECT entity, traits WHERE has(SoftDeletableTrait)"
php bin/foundation foundation:ontology:openapi App\\Entity\\User
php bin/foundation foundation:ontology:graphql App\\Entity\\User
```

---

## 🔹 3. Bridge / Symfony
CLI and integration layer for Symfony Console.

| Command | Description |
|----------|--------------|
| `foundation:ontology:query` | Execute an OQL query |
| `foundation:ontology:openapi` | Generate OpenAPI YAML spec |
| `foundation:ontology:graphql` | Generate GraphQL schema |
| `foundation:outbox:dispatch` | Send queued webhooks |
| `foundation:outbox:purge` | Clear outbox queue |
| `foundation:metrics:report` | Display system metrics |
| `foundation:entity:init` | Create new Entity with traits |

---

## 🔹 4. OQL — Ontology Query Language
Object Foundation introduces **OQL** — a semantic query language for your code.

Examples:
```bash
SELECT entity, traits WHERE has(LocaleAwareTrait)
SELECT entity WHERE NOT implements(DeprecatedInterface)
SELECT entity WHERE has(ConfigurableTrait)
```

Output formats:
- `--format table`
- `--format json`
- `--format jsonld`

---

## 🔹 5. Exporters
| Format | Command | Usage |
|---------|----------|--------|
| **OpenAPI** | `foundation:ontology:openapi` | REST API documentation |
| **GraphQL** | `foundation:ontology:graphql` | GraphQL schema export |
| **JSON-LD** | `foundation:ontology:query` | Linked Data export |
| **RDF/Turtle** | `RdfExporter` | semantic web data |

---

## 🔹 6. Outbox & Webhooks
Event-based communication and delayed delivery system.

Example config:
```
OBJECT_FOUNDATION_WEBHOOKS=https://example.com/hook1,https://example.com/hook2
OBJECT_FOUNDATION_WEBHOOK_SECRET=super_secret
```

Send all pending events:
```bash
php bin/foundation foundation:outbox:dispatch
```

---

## 🔹 7. Metrics & Observability
Monitor performance and API activity:
```bash
php bin/foundation foundation:metrics:report
```

Integrations planned for Prometheus / Grafana dashboards.

---

## 🔹 8. Atomic Integrity Check
Ensures the core remains independent from frameworks.

```bash
vendor/bin/phpunit --testdox
```

Reports violations like `use Symfony\...` inside `/src/`.

---

## 🚀 Quick Start

```bash
composer install
php bin/foundation foundation:ontology:openapi App\\Entity\\User
php bin/foundation foundation:ontology:query "SELECT entity,traits"
vendor/bin/phpunit --testdox
```

---

## 🧭 Roadmap
- [x] Ontology Engine (v1.0)
- [x] GraphQL + OpenAPI exporters
- [x] OQL parser & executor
- [x] Outbox & Observability (v2.5)
- [ ] REST API Gateway (v3.x)
- [ ] Distributed Traits Registry
- [ ] Visual Ontology Graph

---

## 🧠 Philosophy
> “Every object is an atom of meaning.”

Object Foundation merges ontology, introspection, and transparency.  
It transforms a PHP codebase into a **self-describing semantic system**.

---

## 🪪 License
MIT © Object Foundation Contributors  
Maintained by [@Oleksandr Tishchenko](mailto:)
