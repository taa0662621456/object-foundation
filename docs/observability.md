# Observability (v2.4)

## ENV
```
OBJECT_FOUNDATION_LOG_FILE=var/log/api.log
OBJECT_FOUNDATION_LOG_LEVEL=info
OBJECT_FOUNDATION_METRICS_FILE=var/metrics/metrics.json
OBJECT_FOUNDATION_METRICS_EXPORT=true
# optional Redis via REDIS_URL (shared with cache)
```

## Endpoints
- `GET /api/metrics` — JSON snapshot
- `GET /api/metrics?format=prometheus` — Prometheus exposition format

## CLI
```bash
./bin/foundation foundation:metrics:report
```

## Log format (one JSON per line)
```json
{"ts":"2025-10-05T20:00:00+00:00","ip":"127.0.0.1","method":"GET","path":"/api/oql","status":200,"duration_ms":14.2,"user_agent":"...","auth":"ApiKey","cache_hit":true}
```