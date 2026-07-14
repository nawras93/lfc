<?php

namespace App\Services;

use App\Models\ParentAccount;
use Carbon\Carbon;

class ScanTokenService
{
    /**
     * A blank or non-numeric config value casts to 0, which would expire every QR
     * the instant it is issued. Guard here too, so the TTL is safe whatever its source.
     */
    private function ttl(): int
    {
        $ttl = (int) config('scan.token_ttl', 60);

        return $ttl > 0 ? $ttl : 60;
    }

    public function issue(ParentAccount $parent, ?Carbon $at = null): array
    {
        $at ??= now();
        $ttl = $this->ttl();
        $secret = config('scan.qr_secret');

        $payload = [
            'pid' => $parent->id,
            'iat' => $at->getTimestamp(),
            'nonce' => bin2hex(random_bytes(16)),
        ];

        $encoded = $this->base64urlEncode(json_encode($payload, JSON_UNESCAPED_SLASHES));
        $hmac = hash_hmac('sha256', $encoded, $secret);

        return [
            'token' => $encoded . '.' . $hmac,
            'expires_at' => $at->copy()->addSeconds($ttl)->toIso8601String(),
        ];
    }

    public function verify(string $token, ?Carbon $at = null): ?int
    {
        $at ??= now();
        $ttl = $this->ttl();
        $secret = config('scan.qr_secret');

        $parts = explode('.', $token, 2);

        if (count($parts) !== 2) {
            return null;
        }

        [$encoded, $signature] = $parts;

        if (! $this->isValidBase64url($encoded)) {
            return null;
        }

        $expected = hash_hmac('sha256', $encoded, $secret);

        if (! hash_equals($expected, $signature)) {
            return null;
        }

        $decoded = json_decode($this->base64urlDecode($encoded), true);

        if (! is_array($decoded) || ! isset($decoded['pid'], $decoded['iat'])) {
            return null;
        }

        $iat = $decoded['iat'];
        $now = $at->getTimestamp();
        $skew = 5;

        if ($iat > $now + $skew) {
            return null;
        }

        if ($now > $iat + $ttl) {
            return null;
        }

        return (int) $decoded['pid'];
    }

    private function base64urlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64urlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;

        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }

        return base64_decode(strtr($data, '-_', '+/'));
    }

    private function isValidBase64url(string $data): bool
    {
        return preg_match('/^[A-Za-z0-9_-]+$/', $data) === 1;
    }
}
