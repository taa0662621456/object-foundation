# 🧠 Object Foundation — v2.5.0-branding-update

## 🚀 Overview
This release completes the branding and ecosystem unification of Object Foundation under Marketing America Corp,
introducing multilingual documentation, observability, metrics export, and a new REST API layer with OpenAPI and Swagger UI.

---

## ✨ New Features
- **Bilingual Docs** — English + Russian `README` with business context and support sections
- **Observability Layer** — structured request logging and metrics via `/api/metrics`
- **OpenAPI + Swagger UI**
  - `/api/openapi.json` — machine-readable OpenAPI 3.0 spec
  - `/api/docs` — interactive Swagger UI
- **Security Enhancements**
  - API Key / Bearer authentication
  - IP-based rate limiting (`X-RateLimit-*` headers)
  - CORS origin whitelisting
- **OQL Engine**
  - Query parser and executor with caching and outbox events
  - Supports JSON-LD and RDF ontology exports
- **Outbox Persistence**
  - Asynchronous storage of ontology and OQL reaction events
- **Prometheus Metrics Export**
  - Direct `/api/metrics?format=prometheus` integration

---

## 🔍 Swagger UI Preview
Once deployed, you can access live API documentation at:

```
https://your-domain.com/api/docs
```

**Example OpenAPI spec:**
```json
GET /api/openapi.json
→ 200 OK
{
  "openapi": "3.0.3",
  "info": { "title": "Object Foundation API", "version": "1.0.0" },
  "paths": { ... },
  "components": { "schemas": { ... } }
}
```

---

## ⚙️ Example Endpoints

### 🔹 Get Metrics Snapshot
```bash
GET /api/metrics
Accept: application/json
```
**Response:**
```json
{
  "uptime_seconds": 345600,
  "requests_total": 124,
  "avg_response_ms": 22.7,
  "cache_hit_ratio": 0.83
}
```

### 🔹 Execute OQL Query
```bash
POST /api/oql
Content-Type: application/json

{
  "query": "SELECT entity, traits WHERE has(ObjectAuditTrait)"
}
```
**Response:**
```json
[
  { "entity": "ObjectFoundation\\Entity\\User", "traits": ["ObjectAuditTrait", "LocaleAwareTrait"] }
]
```

### 🔹 Export as JSON-LD
```bash
GET /api/oql?query=SELECT%20entity,traits&format=jsonld
```

---

## 🧩 Developer Experience
- `.editorconfig`, `.gitattributes`, `.gitignore` for consistent environments
- Automated `CHANGELOG.md` updates via GitHub Actions
- ENV vars embedded in `phpunit.xml.dist`: `OBJECT_FOUNDATION_BRAND`, `OBJECT_FOUNDATION_OWNER`

---

## 🧭 Roadmap
| Version | Focus |
|:--|:--|
| **v2.6.x** | Extended Observability Dashboard |
| **v2.7.x** | Event Sourcing Layer |
| **v3.0.0** | Multi-language SDK Bridge (PHP / Node.js / Python) |

---

## 🪪 License
MIT License © 2025 **Marketing America Corp**  
Author — **Oleksandr Tishchenko**  
📧 taa0662621456@gmail.com | 📞 +1 (707) 867-5833  
🌐 [marketingamerica.us](https://marketingamerica.us) | [smartresponsor.com](https://smartresponsor.com) | [isponsor.dev](https://isponsor.dev)

---

### 🔗 Changelog
See full version details in [`CHANGELOG.md`](./CHANGELOG.md)