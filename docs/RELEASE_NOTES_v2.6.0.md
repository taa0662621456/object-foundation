
# ![Object Foundation Branding](docs/assets/method-draw-image.png)
# 🚀 Object Foundation v2.6.0 — Continuous Integration Suite

> Cross-platform automation. Atomic reliability. Visual transparency.

---

## ✨ Highlights
- 🧩 **Unified CI Pipeline** — full GitHub Actions workflows for testing, atomic validation, and releases.  
- 🧱 **Atomicity Guard (PHP + Bash)** — every push and PR now verified for atomic integrity.  
- 📘 **OpenAPI Auto-generation** — `/api/openapi.json` is generated and attached to each release.  
- 🔗 **GitHub → Packagist Bridge** — automatic package sync upon successful release.  
- 🖥️ **Cross-platform** — stable on Linux, macOS, and Windows.  

---

## ⚙️ Components
| File | Description |
|------|--------------|
| `.github/workflows/ci-tests.yml` | PHPUnit + atomicity validation |
| `.github/workflows/ci-release.yml` | Full release automation |
| `composer test:atomicity` | Unified atomic integrity check |
| `var/export/openapi.json` | Auto-generated OpenAPI 3.0 spec |
| `object-foundation-v2.6.0.zip` | Ready-to-distribute package |

---

## 🔄 Upgrade Path
If you're upgrading from **v2.5.x**, follow these steps:

1. **Remove old** `.github/workflows/release.yml`.  
2. **Add new workflows** from this release:  
   - `.github/workflows/ci-tests.yml`  
   - `.github/workflows/ci-release.yml`
3. **Update dependencies:**
   ```bash
   composer update
   composer test:atomicity
   ```
4. **Set GitHub secrets:**
   - `PACKAGIST_USERNAME`
   - `PACKAGIST_TOKEN`
   - *(optional)* `GH_PAT`
5. **Trigger a new release:**
   ```bash
   git tag v2.6.0
   git push origin v2.6.0
   ```
   ✅ Tests → ✅ Atomicity → ✅ OpenAPI → ✅ Release → ✅ Packagist sync

---

## 📊 Observability
- Request metrics and structured logs now supported in `/api/metrics`
- Automatic Prometheus exporter integration
- Event persistence via **Outbox Storage**
- Rate limiting headers:  
  `X-RateLimit-Limit`, `X-RateLimit-Remaining`, `X-RateLimit-Reset`

---

## 👤 Maintainer
**Oleksandr Tishchenko**  
Marketing America Corp LLC  
📞 +1 (707) 867-5833  
📧 taa0662621456@gmail.com  
Brands: *Smartresponsor*, *iSponsor*  
License: MIT  

---

## 🇷🇺 Релиз v2.6.0 — Комплекс CI/CD и атомарная гарантия целостности
> Полный автоматический конвейер сборки, тестирования и публикации.

### Основное:
- ✅ CI-конвейер GitHub Actions  
- 🧩 Проверка атомарности при каждом push  
- 🪄 Автоматическая генерация OpenAPI  
- 🔗 Интеграция с Packagist  
- 🧠 Метрики, логи, наблюдаемость (Observability API)

---
