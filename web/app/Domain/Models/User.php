<?php

namespace App\Domain\Models;

class User {
    public ?int $id;
    public string $username;
    public string $email;
    public string $password;
    public string $created_at;

    public function __construct(
        ?int $id,
        string $username,
        string $email,
        string $password,
        string $created_at = '',
    )
    {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->created_at = $created_at ?: date('Y-m-d H:i:s');
    }
}
