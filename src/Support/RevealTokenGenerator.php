<?php

namespace Rawand\FilamentReveal\Support;

use Illuminate\Support\Facades\Crypt;

class RevealTokenGenerator
{
    /**
     * Generate an encrypted token bound to the current session and user.
     *
     * The token contains:
     *   r  = record ID
     *   c  = column name
     *   m  = model class
     *   t  = issued-at timestamp (for expiry)
     *   s  = session ID fingerprint (prevents cross-session replay)
     *   u  = authenticated user ID (prevents cross-user replay)
     */
    public static function generate(string $recordId, string $columnName, string $modelClass): string
    {
        $payload = [
            'r' => $recordId,
            'c' => $columnName,
            'm' => $modelClass,
            't' => time(),
            's' => self::sessionFingerprint(),
            'u' => self::currentUserId(),
        ];

        return Crypt::encryptString(json_encode($payload));
    }

    /**
     * Decode and validate the token.
     * Returns null if invalid, expired, or bound to a different session/user.
     */
    public static function decode(string $token): ?array
    {
        try {
            $decrypted = Crypt::decryptString($token);
            $payload = json_decode($decrypted, true);

            if (!is_array($payload)) {
                return null;
            }

            // Expiry check
            $expiry = config('filament-reveal.token_expiry', 300);
            if (isset($payload['t']) && (time() - $payload['t']) > $expiry) {
                return null;
            }

            // Session binding check — token must belong to the session that created it
            if (isset($payload['s']) && !hash_equals($payload['s'], self::sessionFingerprint())) {
                return null;
            }

            // User binding check — token must belong to the user that created it
            if (isset($payload['u']) && $payload['u'] !== self::currentUserId()) {
                return null;
            }

            return [
                'record_id'   => $payload['r'] ?? null,
                'column_name' => $payload['c'] ?? null,
                'model'       => $payload['m'] ?? null,
            ];
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Generate obfuscated endpoint URL.
     */
    public static function generateEndpoint(): string
    {
        $hash = hash_hmac('sha256', 'filament-reveal', config('app.key'));

        return url('/x-fr-' . substr($hash, 0, 16));
    }

    /**
     * Verify the endpoint identifier.
     */
    public static function verifyEndpoint(string $identifier): bool
    {
        $hash = hash_hmac('sha256', 'filament-reveal', config('app.key'));
        $expected = substr($hash, 0, 16);

        return hash_equals($expected, $identifier);
    }

    // ── private helpers ───────────────────────────────────────────────────────

    /**
     * A stable fingerprint for the current session.
     * Hashed so the raw session ID is never embedded in the token.
     */
    private static function sessionFingerprint(): string
    {
        return hash_hmac('sha256', session()->getId(), config('app.key'));
    }

    /**
     * The ID of the currently authenticated user (any guard), or null.
     */
    private static function currentUserId(): int|string|null
    {
        foreach (array_keys(config('auth.guards', [])) as $guard) {
            if (($user = auth()->guard($guard)->user()) !== null) {
                return $user->getKey();
            }
        }

        return null;
    }
}
