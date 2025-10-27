<?php

namespace App\Domain\Contracts;

interface PasswordHasherInterface
{
    public function hash(string $plain): string;

    public function verify(string $plain, string $hashed): bool;
}
