<?php

namespace App\Http\Responses;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use App\Http\Requests\Stream;

class Response implements ResponseInterface
{
    private int $statusCode;
    private array $headers;
    private StreamInterface $body;
    private string $protocolVersion = '1.1';

    public function __construct(
        int $statusCode = 200,
        array $headers = [],
        string $body = ''
    ) {
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $body);
        rewind($stream);
        $this->body = new Stream($stream);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    public function withStatus($code, $reasonPhrase = ''): ResponseInterface
    {
        $clone = clone $this;
        $clone->statusCode = $code;
        return $clone;
    }

    public function withHeader($name, $value): ResponseInterface
    {
        $clone = clone $this;
        $clone->headers[$name] = (array) $value;
        return $clone;
    }

    public function withBody(StreamInterface $body): ResponseInterface
    {
        $clone = clone $this;
        $clone->body = $body;
        return $clone;
    }

    public function getReasonPhrase(): string { return ''; }
    public function hasHeader($name): bool { return isset($this->headers[$name]); }
    public function getHeader($name): array { return $this->headers[$name] ?? []; }
    public function getHeaderLine($name): string { return implode(', ', $this->getHeader($name)); }
    public function withProtocolVersion($version): ResponseInterface { $clone = clone $this; $clone->protocolVersion = $version; return $clone; }
    public function withAddedHeader($name, $value): ResponseInterface { $clone = clone $this; $clone->headers[$name][] = $value; return $clone; }
    public function withoutHeader($name): ResponseInterface { $clone = clone $this; unset($clone->headers[$name]); return $clone; }
}
