<?php

namespace App\Http\Requests;


use Psr\Http\Message\UriInterface;

class Uri implements UriInterface
{
    private string $path;

    public function __construct(string $path)
    {
        $this->path = parse_url($path, PHP_URL_PATH) ?? '/';
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function __toString(): string
    {
        return $this->path;
    }

    public function getScheme(): string { return ''; }
    public function getAuthority(): string { return ''; }
    public function getUserInfo(): string { return ''; }
    public function getHost(): string { return ''; }
    public function getPort(): ?int { return null; }
    public function getQuery(): string { return ''; }
    public function getFragment(): string { return ''; }
    public function withScheme($scheme): UriInterface { return $this; }
    public function withUserInfo($user, $password = null): UriInterface { return $this; }
    public function withHost($host): UriInterface { return $this; }
    public function withPort($port): UriInterface { return $this; }
    public function withPath($path): UriInterface { return $this; }
    public function withQuery($query): UriInterface { return $this; }
    public function withFragment($fragment): UriInterface { return $this; }
}
