# API Auth, CORS, Rate Limiting (v2.2)

## Enable auth
Environment variables:
```
OBJECT_FOUNDATION_REQUIRE_AUTH=true
OBJECT_FOUNDATION_API_KEYS=key1,key2
# optional JWT (HS256)
OBJECT_FOUNDATION_JWT_SECRET=super_secret
# CORS
CORS_ORIGINS=*
# Rate limit (per IP)
OBJECT_FOUNDATION_RL_LIMIT=60
OBJECT_FOUNDATION_RL_WINDOW=60
```
Generate a key:
```bash
./bin/foundation foundation:api:key:generate
```
Use:
```
Authorization: ApiKey <key>
```
or
```
Authorization: Bearer <jwt>
```