<?php

namespace App\Http\Requests;

use BadMethodCallException;

use Psr\Http\Message\UriInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

class ServerRequest implements ServerRequestInterface
{
    private string $method;
    private UriInterface $uri;
    private array $headers;
    private StreamInterface $body;
    private array $queryParams;
    private array $parsedBody;
    private array $cookies;
    private array $uploadedFiles;
    private array $attributes = [];
    private string $protocolVersion = '1.1';

    public function __construct(
        string          $method,
        UriInterface    $uri,
        array           $headers,
        StreamInterface $body,
        array           $queryParams = [],
        array           $parsedBody = [],
        array           $cookies = [],
        array           $uploadedFiles = []
    ) {
        $this->method = $method;
        $this->uri = $uri;
        $this->headers = $headers;
        $this->body = $body;
        $this->queryParams = $queryParams;
        $this->parsedBody = $parsedBody;
        $this->cookies = $cookies;
        $this->uploadedFiles = $uploadedFiles;
    }

    public function getMethod(): string { return $this->method; }
    public function getUri(): UriInterface { return $this->uri; }
    public function getHeaders(): array { return $this->headers; }
    public function getBody(): StreamInterface { return $this->body; }
    public function getQueryParams(): array { return $this->queryParams; }
    public function getParsedBody(): array { return $this->parsedBody; }
    public function getCookieParams(): array { return $this->cookies; }
    public function getUploadedFiles(): array { return $this->uploadedFiles; }
    public function getAttributes(): array { return $this->attributes; }
    public function getAttribute($name, $default = null) {
        return $this->attributes[$name] ?? $default;
    }
    public function withAttribute($name, $value): ServerRequestInterface {
        $clone = clone $this;
        $clone->attributes[$name] = $value;
        return $clone;
    }
    public function withoutAttribute($name): ServerRequestInterface {
        $clone = clone $this;
        unset($clone->attributes[$name]);
        return $clone;
    }

    public function getProtocolVersion(): string { return $this->protocolVersion; }

    public function getServerParams(): array
    {
        throw new BadMethodCallException('Not implemented');
    }
    public function getRequestTarget(): string
    {
        throw new BadMethodCallException('Not implemented');
    }
    public function withRequestTarget($requestTarget): ServerRequestInterface { throw new BadMethodCallException('Not implemented'); }
    public function withMethod($method): ServerRequestInterface { throw new BadMethodCallException('Not implemented'); }
    public function withUri(UriInterface $uri, $preserveHost = false): ServerRequestInterface { throw new BadMethodCallException('Not implemented'); }
    public function withProtocolVersion($version): ServerRequestInterface { throw new BadMethodCallException('Not implemented'); }
    public function hasHeader(string $name): bool
    {
        throw new BadMethodCallException('Not implemented');
    }
    public function getHeader(string $name): array
    {
        throw new BadMethodCallException('Not implemented');
    }

    public function getHeaderLine(string $name): string
    {
        throw new BadMethodCallException('Not implemented');
    }
    public function withHeader($name, $value): ServerRequestInterface { throw new BadMethodCallException('Not implemented'); }
    public function withAddedHeader($name, $value): ServerRequestInterface { throw new BadMethodCallException('Not implemented'); }
    public function withoutHeader($name): ServerRequestInterface { throw new BadMethodCallException('Not implemented'); }
    public function withBody(StreamInterface $body): ServerRequestInterface { throw new BadMethodCallException('Not implemented'); }
    public function withCookieParams(array $cookies): ServerRequestInterface { throw new BadMethodCallException('Not implemented'); }
    public function withQueryParams(array $query): ServerRequestInterface { throw new BadMethodCallException('Not implemented'); }
    public function withUploadedFiles(array $uploadedFiles): ServerRequestInterface { throw new BadMethodCallException('Not implemented'); }
    public function withParsedBody($data): ServerRequestInterface { throw new BadMethodCallException('Not implemented'); }
}
