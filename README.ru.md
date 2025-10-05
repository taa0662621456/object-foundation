# 🧠 Object Foundation  
**Атомарное ядро и онтологический движок для PHP / Symfony / Laravel**

![Версия](https://img.shields.io/badge/version-v2.5.x-blue)
![PHP](https://img.shields.io/badge/PHP-8.3+-brightgreen)
![Лицензия](https://img.shields.io/badge/license-MIT-lightgrey)

---

## 🌍 Обзор
**Object Foundation** — это атомарное ядро и онтологический движок, который делает код самодокументируемым.  
Каждый объект знает **кто он** и **какие у него связи**.

Используется как:
- независимое ядро для PHP-приложений;
- introspection-система для Doctrine сущностей;
- генератор **OpenAPI / GraphQL / JSON-LD** документации;
- реактивный слой для **webhooks**, **outbox** и **наблюдаемости (observability)**.

---

## ⚙️ Основные принципы

| Принцип | Значение |
|----------|-----------|
| 🧩 **Атомарность** | ядро не зависит от фреймворков |
| 🔍 **Интроспекция** | система понимает код и связи классов |
| 🔄 **Портируемость** | все интеграции вынесены в `Bridge/*` |
| 📡 **Экспортируемость** | всё можно экспортировать в OpenAPI, GraphQL, RDF |
| 📈 **Наблюдаемость** | через Outbox и Metrics |

---

## 🧱 Архитектура
```
src/
 ├─ EntityTrait/           → атомарные трейты
 ├─ Ontology/              → introspection, OQL, exporters
 ├─ Bridge/
 │   └─ Symfony/Command/   → CLI-команды
 ├─ Events/                → outbox, метрики, webhooks
 └─ Tests/Atomic/          → тесты атомарности
```

---

## 🔹 1. Traits Library
Набор общих свойств для всех сущностей:
- `id`, `uuid`, `slug`, `version`
- `createdAt`, `updatedAt`, `deletedAt`
- `config`, `expiresAt`, `ipRestriction`
- `published`, `token`, `softDelete`

Пример:
```php
use ObjectFoundation\EntityTrait\ObjectAuditTrait;

class Project {
    use ObjectAuditTrait;
}
```

---

## 🔹 2. Ontology Engine
Онтологическое ядро, которое анализирует код и строит карту связей.

Компоненты:
- `ManifestCollector` — сканирует классы и создаёт манифесты;
- `OQL` — язык запросов к коду (похож на SQL);
- `Exporter` — генерирует OpenAPI, GraphQL, JSON-LD, RDF.

Примеры:
```bash
php bin/foundation foundation:ontology:query "SELECT entity WHERE has(SoftDeletableTrait)"
php bin/foundation foundation:ontology:openapi App\\Entity\\User
php bin/foundation foundation:ontology:graphql App\\Entity\\User
```

---

## 🔹 3. Bridge / Symfony
CLI-команды и интеграция с Symfony Console.

| Команда | Назначение |
|----------|-------------|
| `foundation:ontology:query` | Выполнить OQL-запрос |
| `foundation:ontology:openapi` | Сгенерировать OpenAPI YAML |
| `foundation:ontology:graphql` | Сгенерировать GraphQL схему |
| `foundation:outbox:dispatch` | Отправить вебхуки |
| `foundation:metrics:report` | Показать метрики |

---

## 🔹 4. OQL — Ontology Query Language
Позволяет искать объекты по структурам кода.

```bash
SELECT entity, traits WHERE has(LocaleAwareTrait)
SELECT entity WHERE NOT implements(DeprecatedInterface)
```

Форматы вывода: `table`, `json`, `jsonld`.

---

## 🔹 5. Экспортеры
| Формат | Команда | Назначение |
|--------|----------|------------|
| **OpenAPI** | `foundation:ontology:openapi` | REST документация |
| **GraphQL** | `foundation:ontology:graphql` | introspection schema |
| **JSON-LD** | `foundation:ontology:query` | Linked Data |
| **RDF/Turtle** | `RdfExporter` | онтологические графы |

---

## 🚀 Быстрый старт
```bash
composer install
php bin/foundation foundation:ontology:openapi App\\Entity\\User
php bin/foundation foundation:ontology:query "SELECT entity,traits"
vendor/bin/phpunit --testdox
```

---

## 🧭 Дорожная карта
- [x] Ontology Engine
- [x] Exporters (OpenAPI, GraphQL)
- [x] Observability Layer
- [ ] REST Gateway
- [ ] Visual Ontology Graph

---

## 🧠 Философия
> “Каждый объект — атом знаний.”

**Object Foundation** объединяет онтологию, introspection и прозрачность.  
Это инструмент для понимания кода и его эволюции.

---

## 🪪 Лицензия
MIT © Object Foundation Contributors  
Maintained by [@Oleksandr Tishchenko](mailto:)
