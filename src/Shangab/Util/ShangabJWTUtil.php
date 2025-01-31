<?php

declare(strict_types=1);

namespace Shangab\Util;

class ShangabJWTUtil
{
    protected int $tokenExpiry;
    protected string $signatureSecret;
    public function  __construct(string $singatureSecret = '',  int $tokenExpiry = 60 * 60 * 24 * 7)
    {
        $this->signatureSecret = $singatureSecret;
        $this->tokenExpiry = $tokenExpiry;
    }
    public function getHash256($text): string
    {
        return hash('sha256', $text);
    }
    public function getTempPassword($length = 5): string
    {
        return substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789'), 0, $length);
    }
    public function createToken($userData): string
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode([
            'user' => $userData,
            'exp' => time() + $this->tokenExpiry
        ]);

        $base64UrlHeader = base64_encode($header);
        $base64UrlPayload = base64_encode($payload);

        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $this->signatureSecret, true);
        $base64UrlSignature = base64_encode($signature);

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    protected function getTokenFromHeaders(): string
    {

        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            $header =  $headers['Authorization'];
            return trim(str_replace('Bearer', '', $header));
        }
        return '';
    }
    public function verifyToken(): mixed
    {
        $token = $this->getTokenFromHeaders();
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }

        $header = base64_decode($parts[0]);
        $payload = base64_decode($parts[1]);
        $signature = base64_decode($parts[2]);

        $expectedSignature = hash_hmac('sha256', $parts[0] . "." . $parts[1], $this->signatureSecret, true);
        if ($signature !== $expectedSignature) {
            return false;
        }

        $decodedPayload = json_decode($payload, true);
        if ($decodedPayload['exp'] < time()) {
            return false;
        }

        return $decodedPayload['user'];
    }
}
