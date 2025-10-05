# Outbox & Webhooks (v2.5)

## ENV
```
OBJECT_FOUNDATION_OUTBOX_DRIVER=file   # file|redis
OBJECT_FOUNDATION_OUTBOX_FILE=var/outbox/events.json
OBJECT_FOUNDATION_WEBHOOKS=https://example.com/hook1,https://example.com/hook2
OBJECT_FOUNDATION_WEBHOOK_SECRET=super_secret
```

## CLI
```bash
./bin/foundation foundation:outbox:dispatch
./bin/foundation foundation:outbox:purge
```

## Event format
```json
{
  "id": "uuid",
  "event": "OQLQueryExecuted",
  "payload": {"query":"...","classes":["FQCN"],"rows":12},
  "created_at": "2025-10-05T23:00:00Z",
  "dispatched": false,
  "attempts": 0,
  "last_error": null
}
```

## Verify webhook signature (PHP)
```php
$payload = file_get_contents('php://input');
$sig = $_SERVER['HTTP_X_SIGNATURE'] ?? '';
$ok = hash_equals(hash_hmac('sha256', $payload, 'super_secret'), $sig);
```