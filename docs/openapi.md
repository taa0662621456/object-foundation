# OpenAPI & Swagger UI (v2.1)

## Generate spec via CLI
```bash
./bin/foundation foundation:api:openapi:generate -f json -o var/export/openapi.json
./bin/foundation foundation:api:openapi:generate -f yaml -o var/export/openapi.yaml
```

## Serve `/api/docs`
Run local server:
```bash
php -S 127.0.0.1:8080 -t public public/index.php
```
Open: http://127.0.0.1:8080/api/docs

The UI pulls from `/api/openapi.json`.