<?php
/**
 * Base Model Class
 * All models extend this class
 */

namespace App\Models;

use PDO;
use App\Helpers\Database;

abstract class BaseModel
{
    protected PDO $pdo;
    protected string $table;

    public function __construct()
    {
        $this->pdo = Database::connect();
    }

    /**
     * Find by ID
     */
    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Find with multiple conditions
     */
    public function findBy(array $conditions): ?array
    {
        $where = implode(' AND ', array_map(fn($key) => "$key = ?", array_keys($conditions)));
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE $where");
        $stmt->execute(array_values($conditions));
        return $stmt->fetch() ?: null;
    }

    /**
     * Get all records with optional conditions
     */
    public function all(array $conditions = [], string $orderBy = 'id DESC', int $limit = null): array
    {
        $query = "SELECT * FROM {$this->table}";
        
        if (!empty($conditions)) {
            $where = implode(' AND ', array_map(fn($key) => "$key = ?", array_keys($conditions)));
            $query .= " WHERE $where";
        }
        
        $query .= " ORDER BY $orderBy";
        
        if ($limit) {
            $query .= " LIMIT $limit";
        }

        $stmt = $this->pdo->prepare($query);
        $stmt->execute(array_values($conditions));
        return $stmt->fetchAll();
    }

    /**
     * Count records
     */
    public function count(array $conditions = []): int
    {
        $query = "SELECT COUNT(*) as total FROM {$this->table}";
        
        if (!empty($conditions)) {
            $where = implode(' AND ', array_map(fn($key) => "$key = ?", array_keys($conditions)));
            $query .= " WHERE $where";
        }

        $stmt = $this->pdo->prepare($query);
        $stmt->execute(array_values($conditions));
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Insert record
     */
    public function insert(array $data): int
    {
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');
        $query = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") 
                  VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(array_values($data));
        
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Update record
     */
    public function update(int $id, array $data): bool
    {
        $sets = implode(', ', array_map(fn($key) => "$key = ?", array_keys($data)));
        $query = "UPDATE {$this->table} SET $sets WHERE id = ?";
        
        $stmt = $this->pdo->prepare($query);
        $values = array_values($data);
        $values[] = $id;
        
        return $stmt->execute($values);
    }

    /**
     * Delete record
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Begin transaction
     */
    public function beginTransaction(): void
    {
        $this->pdo->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit(): void
    {
        $this->pdo->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback(): void
    {
        $this->pdo->rollBack();
    }
}
