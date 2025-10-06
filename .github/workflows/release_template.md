# 🧠 Object Foundation — $VERSION

_Auto-generated on $RELEASE_DATE under Marketing America Corp_

---

## 🚀 Overview

This release delivers an enhanced **Object Foundation** platform — a modular, ontology-driven PHP framework
under **Marketing America Corp**, focused on maintainability, observability, and cross-domain automation.
It unifies CI/CD, documentation, and release workflows in one self-consistent system.

---

## ✨ Highlights

- 🧩 **Unified CI/CD Pipeline** — one YAML for Quality, Release, Docs, and Nightly
- 🕒 **Nightly Auto-Builds** — automatic `nightly-YYYY-MM-DD-build-N` tagging
- 🧮 **Build Auto-Increment** — ensures unique sequential nightly versions
- 🧹 **Nightly Cleanup** — keeps only 7 latest builds (auto-purges older)
- 🔐 **Composer Security Audit** — real-time advisory validation
- 🧠 **OpenAPI + Swagger UI** — `/api/openapi.json` & `/api/docs` auto-published
- 📊 **phpDocumentor Integration** — class and API docs deployed to GitHub Pages
- 🔄 **Packagist Auto-Trigger** — stable tags sync instantly with Packagist
- 🧱 **Release Archival** — release notes stored in `docs/releases/$VERSION.md`

---

## 🧪 Quality Layer


| Check             | Description                 | Status |
| :---------------- | :-------------------------- | :----: |
| 🧾 YAML Lint      | Workflow syntax validation  |   ✅   |
| 🧪 PHPUnit        | Test suite & coverage       |   ✅   |
| 🔍 PHPStan        | Static analysis (level max) |   ✅   |
| 🎨 PHP-CS-Fixer   | Coding-style dry-run        |   ✅   |
| 🔐 Composer Audit | Security advisories         |   ✅   |

---

## ⚙️ Compatibility & Requirements


| Component             | Minimum                                                        | Recommended    |
| :-------------------- | :------------------------------------------------------------- | :------------- |
| **PHP**               | 8.2                                                            | 8.3+           |
| **Composer**          | 2.5                                                            | Latest stable  |
| **Symfony Framework** | 6.2                                                            | 6.3 – 7.x     |
| **Database**          | PostgreSQL 13 / SQLite 3                                       | PostgreSQL 15+ |
| **Extensions**        | `mbstring`, `json`, `intl`, `pdo`, `xml`, `dom`, `curl`, `zip` | ✅             |
| **CLI Tools**         | `phpunit`, `phpstan`, `rector`, `php-cs-fixer`                 | ✅             |

> **Tip:** Run `composer install --no-dev --optimize-autoloader` in production environments.

---

## 🧰 Build Artifacts


| Artifact          | Location                              |
| :---------------- | :------------------------------------ |
| 📦 ZIP Package    | `dist/object-foundation-$VERSION.zip` |
| 📘 OpenAPI Docs   | `public/api/$VERSION/openapi.json`    |
| 📚 Developer Docs | `public/dev/$VERSION/index.html`      |

---

## 🔧 Developer Integration

Install the release via Composer:

```bash
composer require marketingamerica/object-foundation:"$VERSION"
```
