<?php

namespace App\Domain\Contracts;

interface TokenGeneratorInterface
{
    public function generateToken(array $claims): string;
}
