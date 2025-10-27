<?php

namespace App\Infrastructure\Security;

use App\Domain\Contracts\TokenGeneratorInterface;

class JwtTokenGenerator implements TokenGeneratorInterface
{
    private string $secret;
    private int $expiry;

    public function __construct(string $secret, int $expiry = 3600)
    {
        $this->secret = $secret;
        $this->expiry = $expiry;
    }

    public function generateToken(array $claims): string
    {
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $payload = array_merge($claims, ['exp' => time() + $this->expiry]);

        $base64UrlHeader = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');
        $base64UrlPayload = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');

        $signature = hash_hmac('sha256', "$base64UrlHeader.$base64UrlPayload", $this->secret, true);
        $base64UrlSignature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

        return "$base64UrlHeader.$base64UrlPayload.$base64UrlSignature";
    }
}
