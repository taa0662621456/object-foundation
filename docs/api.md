# REST API (v2.0)

Run dev server:
```bash
php -S 127.0.0.1:8080 -t public public/index.php
```

## OQL
`GET /api/oql?query=SELECT entity,traits WHERE has(LocaleAwareTrait)&classes[]=Examples\\SymfonyDemo\\Entity\\DemoEntity`

`POST /api/oql`:
```json
{ "query": "SELECT entity WHERE name like \"Demo\"", "classes": ["Examples\\SymfonyDemo\\Entity\\DemoEntity"], "format": "json" }
```

`format=jsonld` — returns JSON-LD.

## Ontology Introspection
- `GET /api/ontology/entities?classes[]=FQCN&classes[]=FQCN2`
- `GET /api/ontology/entity?name=FQCN`
- `GET /api/ontology/traits`