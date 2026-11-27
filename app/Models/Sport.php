<?php

namespace App\Models;

class Sport extends Model
{
    public function all(): array
    {
        return $this->db->query("SELECT * FROM sports WHERE id <> 15 OR NOT EXISTS (SELECT 1 FROM sports WHERE id = 3 AND LOWER(name) IN ('volei','vôlei','voleibol')) ORDER BY name")->fetchAll();
    }
}
