<?php

namespace App\Http\Responses;

use Psr\Http\Message\ResponseInterface;

class JsonResponse
{
    public static function create(array $data, int $status = 200): ResponseInterface
    {
        return new Response(
            $status,
            ['Content-Type' => ['application/json']],
            json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );
    }
}
