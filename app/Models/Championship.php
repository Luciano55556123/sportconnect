<?php

namespace App\Models;

class Championship extends Model
{
    public function search(array $filters = [], int $limit = 50): array
    {
        $sql = 'SELECT c.*, s.name AS sport_name, u.name AS organizer_name,
                (SELECT COUNT(*) FROM registrations r WHERE r.championship_id = c.id) AS registrations_count
                FROM championships c
                JOIN sports s ON s.id = c.sport_id
                JOIN users u ON u.id = c.organizer_id
                WHERE 1 = 1';
        $params = [];

        foreach (['city', 'status', 'modality'] as $field) {
            if (!empty($filters[$field])) {
                $sql .= " AND c.$field = ?";
                $params[] = $filters[$field];
            }
        }
        if (!empty($filters['q'])) {
            $sql .= ' AND (c.name LIKE ? OR c.description LIKE ? OR u.name LIKE ?)';
            $term = '%' . $filters['q'] . '%';
            array_push($params, $term, $term, $term);
        }
        if (!empty($filters['sport_id'])) {
            $sql .= ' AND c.sport_id = ?';
            $params[] = (int) $filters['sport_id'];
        }
        if (!empty($filters['category'])) {
            $sql .= ' AND c.category LIKE ?';
            $params[] = '%' . $filters['category'] . '%';
        }
        if (!empty($filters['date'])) {
            $sql .= ' AND c.event_date = ?';
            $params[] = $filters['date'];
        }
        if ($filters['max_price'] ?? '' !== '') {
            $sql .= ' AND c.registration_fee <= ?';
            $params[] = (float) $filters['max_price'];
        }

        $sql .= ' ORDER BY c.event_date ASC LIMIT ' . (int) $limit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function featured(): array
    {
        return $this->search(['status' => 'ativo'], 6);
    }

    public function mostViewed(): array
    {
        return $this->db->query(
            'SELECT c.*, s.name AS sport_name, u.name AS organizer_name
             FROM championships c
             JOIN sports s ON s.id = c.sport_id
             JOIN users u ON u.id = c.organizer_id
             ORDER BY c.views DESC, c.event_date ASC LIMIT 6'
        )->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT c.*, s.name AS sport_name, u.name AS organizer_name, u.phone AS organizer_phone,
             (SELECT COUNT(*) FROM registrations r WHERE r.championship_id = c.id) AS registrations_count
             FROM championships c
             JOIN sports s ON s.id = c.sport_id
             JOIN users u ON u.id = c.organizer_id
             WHERE c.id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function incrementViews(int $id): void
    {
        $this->db->prepare('UPDATE championships SET views = views + 1 WHERE id = ?')->execute([$id]);
    }

    public function create(array $data, int $organizerId): int
    {
        $sql = 'INSERT INTO championships
            (organizer_id, sport_id, name, city, location, map_link, event_date, event_time, registration_fee,
             prize, max_participants, description, rules_file, category, modality, status, image,
             whatsapp_contato, email_contato, requires_payment, pix_key, pix_key_type, pix_holder_name, pix_instructions)
             VALUES (:organizer_id, :sport_id, :name, :city, :location, :map_link, :event_date, :event_time,
             :registration_fee, :prize, :max_participants, :description, :rules_file, :category, :modality, :status, :image,
             :whatsapp_contato, :email_contato, :requires_payment, :pix_key, :pix_key_type, :pix_holder_name, :pix_instructions)';
        if ($this->db->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'pgsql') {
            $sql .= ' RETURNING id';
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($this->payload($data, $organizerId));
        if ($this->db->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'pgsql') {
            return (int) $stmt->fetchColumn();
        }
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data, int $organizerId): void
    {
        $payload = $this->payload($data, $organizerId);
        $payload['id'] = $id;
        $stmt = $this->db->prepare(
            'UPDATE championships SET sport_id=:sport_id, name=:name, city=:city, location=:location,
            map_link=:map_link, event_date=:event_date, event_time=:event_time, registration_fee=:registration_fee,
            prize=:prize, max_participants=:max_participants, description=:description, rules_file=:rules_file,
            category=:category, modality=:modality, status=:status, image=:image, email_contato=:email_contato,
            whatsapp_contato=:whatsapp_contato, requires_payment=:requires_payment, pix_key=:pix_key, pix_key_type=:pix_key_type,
            pix_holder_name=:pix_holder_name, pix_instructions=:pix_instructions
            WHERE id=:id AND organizer_id=:organizer_id'
        );
        $stmt->execute($payload);
    }

    public function byOrganizer(int $organizerId): array
    {
        $stmt = $this->db->prepare(
            'SELECT c.*, s.name AS sport_name,
            (SELECT COUNT(*) FROM registrations r WHERE r.championship_id = c.id) AS registrations_count
            FROM championships c JOIN sports s ON s.id = c.sport_id
            WHERE c.organizer_id = ? ORDER BY c.event_date DESC'
        );
        $stmt->execute([$organizerId]);
        return $stmt->fetchAll();
    }

    public function calendar(): array
    {
        return $this->db->query(
            'SELECT c.id, c.name, c.city, c.event_date, s.name AS sport_name
             FROM championships c JOIN sports s ON s.id = c.sport_id
             WHERE c.status = \'ativo\' ORDER BY c.event_date ASC'
        )->fetchAll();
    }

    public function reviews(int $championshipId): array
    {
        $stmt = $this->db->prepare(
            'SELECT rv.*, u.name FROM reviews rv JOIN users u ON u.id = rv.user_id
             WHERE rv.championship_id = ? ORDER BY rv.created_at DESC'
        );
        $stmt->execute([$championshipId]);
        return $stmt->fetchAll();
    }

    private function payload(array $data, int $organizerId): array
    {
        return [
            'organizer_id' => $organizerId,
            'sport_id' => (int) $data['sport_id'],
            'name' => $data['name'],
            'city' => $data['city'],
            'location' => $data['location'],
            'map_link' => $data['map_link'] ?? null,
            'event_date' => $data['event_date'],
            'event_time' => $data['event_time'],
            'registration_fee' => (float) ($data['registration_fee'] ?? 0),
            'prize' => $data['prize'] ?? null,
            'max_participants' => (int) ($data['max_participants'] ?? 0),
            'description' => $data['description'],
            'rules_file' => $data['rules_file'] ?? null,
            'category' => $data['category'] ?? null,
            'modality' => $data['modality'] ?? 'misto',
            'status' => $data['status'] ?? 'ativo',
            'image' => $data['image'] ?? 'assets/img/default-event.svg',
            'whatsapp_contato' => preg_replace('/\D/', '', (string) ($data['whatsapp_contato'] ?? '')) ?: '00000000000',
            'email_contato' => trim((string) ($data['email_contato'] ?? '')) ?: null,
            'requires_payment' => !empty($data['requires_payment']) ? 1 : 0,
            'pix_key' => ($data['pix_key'] ?? '') ?: null,
            'pix_key_type' => ($data['pix_key_type'] ?? '') ?: null,
            'pix_holder_name' => ($data['pix_holder_name'] ?? '') ?: null,
            'pix_instructions' => ($data['pix_instructions'] ?? '') ?: null,
        ];
    }
}
