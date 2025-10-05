<?php
namespace ObjectFoundation\Api\Security;

final class Auth
{
    /**
     * Validates API access using either:
     *  - API key via header "Authorization: ApiKey <key>"
     *  - JWT via header "Authorization: Bearer <jwt>" with HS256 and optional exp
     *
     * Allowed API keys are provided via env OBJECT_FOUNDATION_API_KEYS (comma-separated).
     * JWT secret via env OBJECT_FOUNDATION_JWT_SECRET.
     */
    public static function isAuthorized(array $server): bool
    {
        $auth = $server['HTTP_AUTHORIZATION'] ?? $server['Authorization'] ?? '';
        $auth = trim($auth);

        // If auth not required, allow public
        $require = getenv('OBJECT_FOUNDATION_REQUIRE_AUTH');
        $requireAuth = ($require === false) ? false : (strtolower($require) !== 'false' && $require !== '0');

        if (!$requireAuth && $auth === '') return true;

        if (stripos($auth, 'ApiKey ') === 0) {
            $key = trim(substr($auth, 7));
            return self::validateApiKey($key);
        }
        if (stripos($auth, 'Bearer ') === 0) {
            $jwt = trim(substr($auth, 7));
            return self::validateJwt($jwt);
        }
        return false;
    }

    public static function validateApiKey(string $key): bool
    {
        $keys = getenv('OBJECT_FOUNDATION_API_KEYS') ?: '';
        $allowed = array_filter(array_map('trim', explode(',', $keys)));
        if (empty($allowed)) return false;
        return in_array($key, $allowed, true);
    }

    public static function validateJwt(string $jwt): bool
    {
        $secret = getenv('OBJECT_FOUNDATION_JWT_SECRET') ?: '';
        if ($secret === '') return false;
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) return false;
        [$h64, $p64, $s64] = $parts;
        $sig = self::urlsafeB64Decode($s64);
        $header = json_decode(self::urlsafeB64Decode($h64), true);
        $payload = json_decode(self::urlsafeB64Decode($p64), true);
        if (!is_array($header) || !is_array($payload)) return false;
        if (($header['alg'] ?? '') !== 'HS256') return false;
        // verify signature
        $data = $h64 . '.' . $p64;
        $calc = hash_hmac('sha256', $data, $secret, true);
        if (!hash_equals($calc, $sig)) return false;
        // exp check
        if (isset($payload['exp']) && time() >= (int)$payload['exp']) return false;
        return true;
    }

    private static function urlsafeB64Decode(string $b64): string
    {
        $b64 = strtr($b64, '-_', '+/');
        $pad = strlen($b64) % 4;
        if ($pad > 0) $b64 .= str_repeat('=', 4 - $pad);
        return base64_decode($b64);
    }
}