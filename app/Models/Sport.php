<?php

namespace App\Models;

class Sport extends Model
{
    public function all(?int $limit = null): array
    {
        $sql = 'SELECT * FROM sports ORDER BY name';
        if ($limit !== null) {
            $sql .= ' LIMIT ' . max(1, min(100, $limit));
        }
        return $this->db->query($sql)->fetchAll();
    }
}
