# 🧬 Object Foundation  
**Digital Ontology Layer for PHP Entities**  
*(Doctrine / Symfony / Laravel compatible)*

## 📖 About

**Object Foundation** is an atomic library of traits and interfaces describing the **ontology of entities** in PHP.  
Every domain entity — `Product`, `Vendor`, `Order`, `User`, etc. — shares universal attributes of existence: identity, time, state, visibility, version, configuration, authorship.

This library formalizes those universal aspects as **atomic traits**, freely composable and framework-agnostic.  
It’s not an ORM layer — it’s a **digital ontology foundation** for all PHP applications, from microservices to large DDD systems.

## 🧩 Core Features

| Domain | Description |
|---------|--------------|
| **Identity** | `id`, `uuid`, `slug` – base identity fields |
| **Audit** | automatic timestamps `createdAt`, `updatedAt` |
| **Publication** | publishing state and expiration date |
| **Soft Delete** | logical deletion with `deletedAt`, `deletedBy` |
| **Versioning** | Doctrine’s `@Version` field for optimistic locking |
| **Lockable** | `lockedAt`, `lockedBy` for exclusive access |
| **Configurable** | persistent configuration (`config`, `decryptedConfig`) |
| **Restrictable** | IP restriction and `isIpAllowed()` control |
| **Workflow** | lightweight state machine (`submitted`, `approved`, etc.) |
| **Creator** | `createdBy`, `modifiedBy` tracking |
| **Coded / Tokenized** | unique codes and secure tokens |

## 🧠 Philosophy

> “Every entity is a reflection of its environment.”

**Object Foundation** views every object not as isolated data, but as a **unit of existence** within a digital ecosystem.  
Just as atoms form matter, traits form entities.  
This library defines the *ontological grammar* of your domain.

## ⚙️ Installation

```bash
composer require isponsor/object-foundation
```

## 🚀 Quick Start

```php
use Doctrine\ORM\Mapping as ORM;
use ObjectFoundation\Traits\EntityFoundationTrait;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class Product
{
    use EntityFoundationTrait;
}
```

🔹 After inclusion:
- Fields like `id`, `uuid`, `createdAt`, `updatedAt`, `slug`, `published`, `version`, `workflow` work out of the box.  
- Doctrine lifecycle hooks (`PrePersist`, `PreUpdate`) initialize timestamps and UUIDs automatically.

## 🧩 Atomic Architecture

Each trait is a standalone building block:

```php
use ObjectFoundation\Traits\IdentityTrait;
use ObjectFoundation\Traits\AuditTrait;
use ObjectFoundation\Traits\SoftDeleteTrait;

class MinimalEntity {
    use IdentityTrait, AuditTrait, SoftDeleteTrait;
}
```

## 🧪 Tests

```bash
composer install
vendor/bin/phpunit --testdox
```

## 🤖 Automated Releases

GitHub Actions workflow included:

- On tag push (`v1.0.0`)  
  → installs dependencies  
  → runs PHPUnit  
  → creates GitHub Release  
  → notifies Packagist via API  

Secrets required:
- `PACKAGIST_USER`
- `PACKAGIST_TOKEN`

To release:

```bash
./bin/release.sh v1.0.0
```

## 📘 Example Entity

[`examples/SymfonyDemo/src/Entity/DemoEntity.php`](examples/SymfonyDemo/src/Entity/DemoEntity.php)

```php
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class DemoEntity {
    use EntityFoundationTrait;
}
```

## 📜 License

MIT License © 2025 Oleksandr Tishchenko  

## 🗺️ Roadmap

| Version | Milestone |
|----------|------------|
| `1.1.x` | Doctrine lifecycle events (`OnConfigChange`, `OnSoftDelete`) |
| `1.2.x` | AES configuration encryption with key rotation |
| `1.3.x` | `LocaleAwareTrait` and i18n support |
| `1.4.x` | CLI utilities (`foundation:entity:init`, `foundation:entity:info`) |
| `2.x` | Introspection API — the “Ontology Engine” for PHP entities |

> *“Ontology meets code. Foundation meets entity.”*