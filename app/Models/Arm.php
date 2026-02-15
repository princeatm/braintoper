<?php
/**
 * Arm Model
 */

namespace App\Models;

class Arm extends BaseModel
{
    protected string $table = 'arms';

    /**
     * Get all arms with caching
     */
    public function getAllCached(): array
    {
        $cacheFile = __DIR__ . '/../../storage/cache/arms.cache';
        
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 86400) {
            return json_decode(file_get_contents($cacheFile), true);
        }

        $results = $this->all([], 'name ASC');
        file_put_contents($cacheFile, json_encode($results));
        
        return $results;
    }

    /**
     * Find by code
     */
    public function findByCode(string $code): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE code = ?");
        $stmt->execute([$code]);
        return $stmt->fetch() ?: null;
    }
}
