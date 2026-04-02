<?php
// ============================================================
// MODEL DE BASE
// ============================================================

abstract class Model
{
    protected PDO $pdo;
    protected string $table;
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->pdo = db();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function findBy(string $col, mixed $val): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM {$this->table} WHERE {$col} = ? LIMIT 1"
        );
        $stmt->execute([$val]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function all(string $order = 'id DESC'): array
    {
        return $this->pdo
            ->query("SELECT * FROM {$this->table} ORDER BY {$order}")
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    public function count(string $where = '', array $params = []): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        if ($where) $sql .= " WHERE {$where}";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function insert(array $data): int
    {
        $cols   = implode(', ', array_keys($data));
        $places = implode(', ', array_fill(0, count($data), '?'));
        $stmt   = $this->pdo->prepare(
            "INSERT INTO {$this->table} ({$cols}) VALUES ({$places})"
        );
        $stmt->execute(array_values($data));
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $set  = implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($data)));
        $stmt = $this->pdo->prepare(
            "UPDATE {$this->table} SET {$set} WHERE {$this->primaryKey} = ?"
        );
        return $stmt->execute([...array_values($data), $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare(
            "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?"
        );
        return $stmt->execute([$id]);
    }

    public function paginate(string $where = '', array $params = [], int $limit = 12, int $offset = 0, string $order = 'id DESC'): array
    {
        $sql = "SELECT * FROM {$this->table}";
        if ($where) $sql .= " WHERE {$where}";
        $sql .= " ORDER BY {$order} LIMIT {$limit} OFFSET {$offset}";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function raw(string $sql, array $params = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
