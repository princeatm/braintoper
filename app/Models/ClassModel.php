<?php
/**
 * Class Model
 */

namespace App\Models;

class ClassModel extends BaseModel
{
    protected string $table = 'classes';

    /**
     * Get all classes with caching
     */
    public function getAllCached(): array
    {
        $cacheFile = __DIR__ . '/../../storage/cache/classes.cache';
        
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 86400) {
            return json_decode(file_get_contents($cacheFile), true);
        }

        $results = $this->all([], 'name ASC');
        file_put_contents($cacheFile, json_encode($results));
        
        return $results;
    }
}
