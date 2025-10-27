<?php

namespace App\Domain\Contracts;

use App\Domain\Models\User;

interface UserRepositoryInterface
{
    public function findByUsername(string $username): ?User;
    public function findByEmail(string $email): ?User;
    public function save(User $user): void;
}
