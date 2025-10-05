# Caching & ETag (v2.3)

## ENV
```
OBJECT_FOUNDATION_CACHE_TTL=3600
OBJECT_FOUNDATION_CACHE_DIR=var/cache/manifests
# optional Redis
REDIS_URL=redis://127.0.0.1:6379/0
```

## What is cached
- `manifestFor(FQCN)` results
- OQL query results (`oql:<hash>`)

## HTTP
All responses include:
```
ETag: "<sha1>"
Last-Modified: <date>
```
Clients may send `If-None-Match` or `If-Modified-Since` to get `304`.

## CLI
```bash
./bin/foundation foundation:cache:flush
```