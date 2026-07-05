<?php

namespace App\Support;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TwoFactorAuthenticator
{
    private string $issuer;

    public function __construct(?string $issuer = null)
    {
        $this->issuer = $issuer ?: config('app.name', 'Foto 636');
    }

    public function generateSecretKey(int $bytes = 20): string
    {
        return $this->base32Encode(random_bytes($bytes));
    }

    public function generateRecoveryCodes(int $count = 8, int $length = 12): array
    {
        $codes = [];

        for ($index = 0; $index < $count; $index++) {
            $codes[] = Str::upper(Str::random($length));
        }

        return $codes;
    }

    public function hashRecoveryCodes(array $codes): array
    {
        return array_values(array_map(
            fn (string $code): string => Hash::make($this->normalizeRecoveryCode($code)),
            $codes
        ));
    }

    public function verifyRecoveryCode(string $candidate, array $hashedCodes): ?int
    {
        $normalized = $this->normalizeRecoveryCode($candidate);

        foreach ($hashedCodes as $index => $hash) {
            if (Hash::check($normalized, $hash)) {
                return (int) $index;
            }
        }

        return null;
    }

    public function verifyCode(string $secret, string $code, int $window = 1, ?int $timestamp = null): bool
    {
        $normalized = preg_replace('/\s+/', '', $code) ?? '';

        if (! preg_match('/^\d{6}$/', $normalized)) {
            return false;
        }

        $timestamp ??= time();

        for ($offset = -$window; $offset <= $window; $offset++) {
            if (hash_equals($this->totp($secret, $timestamp + ($offset * 30)), $normalized)) {
                return true;
            }
        }

        return false;
    }

    public function otpauthUri(string $accountName, string $secret): string
    {
        $label = rawurlencode($this->issuer . ':' . $accountName);

        return 'otpauth://totp/' . $label
            . '?secret=' . rawurlencode($secret)
            . '&issuer=' . rawurlencode($this->issuer)
            . '&algorithm=SHA1&digits=6&period=30';
    }

    private function totp(string $secret, int $timestamp): string
    {
        $counter = intdiv(max(0, $timestamp), 30);
        $binaryCounter = pack('N2', 0, $counter);
        $key = $this->base32Decode($secret);
        $hash = hash_hmac('sha1', $binaryCounter, $key, true);
        $offset = ord(substr($hash, -1)) & 0x0f;
        $binary =
            ((ord($hash[$offset]) & 0x7f) << 24)
            | ((ord($hash[$offset + 1]) & 0xff) << 16)
            | ((ord($hash[$offset + 2]) & 0xff) << 8)
            | (ord($hash[$offset + 3]) & 0xff);

        return str_pad((string) ($binary % 1000000), 6, '0', STR_PAD_LEFT);
    }

    private function normalizeRecoveryCode(string $code): string
    {
        return Str::upper(preg_replace('/[^A-Za-z0-9]/', '', $code) ?? '');
    }

    private function base32Encode(string $binary): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $buffer = 0;
        $bitsLeft = 0;
        $output = '';

        foreach (str_split($binary) as $character) {
            $buffer = ($buffer << 8) | ord($character);
            $bitsLeft += 8;

            while ($bitsLeft >= 5) {
                $bitsLeft -= 5;
                $output .= $alphabet[($buffer >> $bitsLeft) & 31];
            }
        }

        if ($bitsLeft > 0) {
            $output .= $alphabet[($buffer << (5 - $bitsLeft)) & 31];
        }

        return $output;
    }

    private function base32Decode(string $input): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $input = strtoupper(preg_replace('/[^A-Z2-7]/', '', $input) ?? '');
        $buffer = 0;
        $bitsLeft = 0;
        $output = '';

        foreach (str_split($input) as $character) {
            $value = strpos($alphabet, $character);

            if ($value === false) {
                continue;
            }

            $buffer = ($buffer << 5) | $value;
            $bitsLeft += 5;

            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $output .= chr(($buffer >> $bitsLeft) & 0xff);
            }
        }

        return $output;
    }
}
