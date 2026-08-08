<?php

namespace App\Models;

class Sport extends Model
{
    public function all(): array
    {
        return $this->db->query('SELECT * FROM sports ORDER BY name')->fetchAll();
    }
}
