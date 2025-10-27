<?php

namespace App\Infrastructure\Persistence;

use PDO;

abstract class BaseRepository
{
    protected PDO $db;
    protected string $table;

    public function __construct(DatabaseConnection $database)
    {
        $this->db = $database->getConnection();
    }

    public function all(): array
    {
        $stmt = $this->db->query("SELECT * FROM $this->table");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM $this->table WHERE id = :id");
        $stmt->execute(['id' => $id]);

        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        return $record ?: null;
    }

    public function findByField(string $field, $value): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM $this->table WHERE $field = :value LIMIT 1");
        $stmt->execute(['value' => $value]);

        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        return $record ?: null;
    }

    public function create(array $data): bool
    {
        $keys = array_keys($data);
        $columns = implode(',', $keys);
        $placeholders = ':' . implode(', :', $keys);

        $stmt = $this->db->prepare(
            "INSERT INTO $this->table ($columns) VALUES ($placeholders)"
        );

        return $stmt->execute($data);
    }

    public function update(int $id, array $data): bool
    {
        $fields = implode(', ', array_map(fn($key) => "$key = :$key", array_keys($data)));
        $data['id'] = $id;

        $stmt = $this->db->prepare(
            "UPDATE $this->table SET $fields WHERE id = :id"
        );

        return $stmt->execute($data);
    }


    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM $this->table WHERE id = :id");

        return $stmt->execute(['id' => $id]);
    }
}
