<?php

namespace App\Http\Requests;

use Exception;
use InvalidArgumentException;

use Psr\Http\Message\StreamInterface;

class Stream implements StreamInterface
{
    private $resource;

    public function __construct($resource)
    {
        if (!is_resource($resource)) {
            throw new InvalidArgumentException('Invalid stream resource');
        }

        $this->resource = $resource;
    }

    public function __toString(): string
    {
        try {
            $this->rewind();
            return stream_get_contents($this->resource) ?: '';
        } catch (Exception) {
            return '';
        }
    }

    public function getContents(): string
    {
        return stream_get_contents($this->resource) ?: '';
    }

    public function rewind(): void
    {
        rewind($this->resource);
    }

    public function close(): void
    {
        fclose($this->resource);
    }

    public function detach()
    {
        $res = $this->resource;
        $this->resource = null;
        return $res;
    }

    public function getSize(): ?int
    {
        $stats = fstat($this->resource);
        return $stats['size'] ?? null;
    }

    public function tell(): int
    {
        return ftell($this->resource);
    }

    public function eof(): bool
    {
        return feof($this->resource);
    }

    public function isSeekable(): bool
    {
        $meta = stream_get_meta_data($this->resource);
        return $meta['seekable'];
    }

    public function seek($offset, $whence = SEEK_SET): void
    {
        fseek($this->resource, $offset, $whence);
    }

    public function isWritable(): bool
    {
        $meta = stream_get_meta_data($this->resource);
        return str_contains($meta['mode'], 'w') || str_contains($meta['mode'], '+');
    }

    public function write($string): int
    {
        return fwrite($this->resource, $string);
    }

    public function isReadable(): bool
    {
        $meta = stream_get_meta_data($this->resource);
        return str_contains($meta['mode'], 'r') || str_contains($meta['mode'], '+');
    }

    public function read($length): string
    {
        return fread($this->resource, $length);
    }

    public function getMetadata($key = null)
    {
        $meta = stream_get_meta_data($this->resource);
        return $key ? ($meta[$key] ?? null) : $meta;
    }
}
