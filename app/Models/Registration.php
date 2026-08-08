<?php

namespace App\Models;

class Registration extends Model
{
    public function create(array $data): int
    {
        $sql = 'INSERT INTO registrations
            (championship_id, user_id, name, phone, email, team, category, city, cpf, notes, proof_file, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        if ($this->db->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'pgsql') {
            $sql .= ' RETURNING id';
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['championship_id'], $data['user_id'], $data['name'], $data['phone'], $data['email'],
            $data['team'], $data['category'], $data['city'], $data['cpf'], $data['notes'], $data['proof_file'],
            $data['status'] ?? 'pendente',
        ]);
        if ($this->db->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'pgsql') {
            return (int) $stmt->fetchColumn();
        }
        return (int) $this->db->lastInsertId();
    }

    public function existsForUser(int $championshipId, int $userId): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM registrations WHERE championship_id = ? AND user_id = ? LIMIT 1');
        $stmt->execute([$championshipId, $userId]);
        return (bool) $stmt->fetch();
    }

    public function findDetails(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT r.*, c.name AS championship_name, c.registration_fee, c.requires_payment,
             c.pix_key, c.pix_key_type, c.pix_holder_name, c.pix_instructions, c.email_contato,
             c.modality, c.category AS championship_category, c.organizer_id, s.name AS sport_name,
             p.status AS payment_status, p.amount AS payment_amount, p.receipt_file AS receipt_path,
             p.rejection_reason AS review_notes
             FROM registrations r
             JOIN championships c ON c.id = r.championship_id
             JOIN sports s ON s.id = c.sport_id
             LEFT JOIN registration_payments p ON p.registration_id = r.id
             WHERE r.id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function byUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT r.*, c.name AS championship_name, c.event_date, c.status AS championship_status,
             c.registration_fee, c.requires_payment, c.pix_key, c.pix_key_type, c.pix_holder_name,
             c.pix_instructions, s.name AS sport_name, p.status AS payment_status, p.amount AS payment_amount,
             p.receipt_file AS receipt_path, p.rejection_reason AS review_notes
             FROM registrations r
             JOIN championships c ON c.id = r.championship_id
             JOIN sports s ON s.id = c.sport_id
             LEFT JOIN registration_payments p ON p.registration_id = r.id
             WHERE r.user_id = ? ORDER BY c.event_date DESC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function byOrganizer(int $organizerId): array
    {
        $stmt = $this->db->prepare(
            'SELECT r.*, c.name AS championship_name, c.registration_fee, c.requires_payment,
             p.status AS payment_status, p.amount AS payment_amount, p.receipt_file AS receipt_path,
             p.updated_at AS submitted_at, p.reviewed_at, p.rejection_reason AS review_notes
             FROM registrations r
             JOIN championships c ON c.id = r.championship_id
             LEFT JOIN registration_payments p ON p.registration_id = r.id
             WHERE c.organizer_id = ? ORDER BY r.created_at DESC'
        );
        $stmt->execute([$organizerId]);
        return $stmt->fetchAll();
    }

    public function setStatus(int $id, string $status, int $organizerId): void
    {
        $stmt = $this->db->prepare(
            'UPDATE registrations
             SET status = ?
             WHERE id = ?
             AND EXISTS (
                SELECT 1 FROM championships c
                WHERE c.id = registrations.championship_id AND c.organizer_id = ?
             )'
        );
        $stmt->execute([$status, $id, $organizerId]);
    }

    public function statsForOrganizer(int $organizerId): array
    {
        $stmt = $this->db->prepare(
            'SELECT
             COUNT(DISTINCT c.id) AS total_championships,
             SUM(CASE WHEN c.status = \'ativo\' THEN 1 ELSE 0 END) AS active_events,
             SUM(CASE WHEN c.status = \'encerrado\' THEN 1 ELSE 0 END) AS closed_events,
             COUNT(r.id) AS total_registrations,
             SUM(CASE WHEN r.status IN (\'aprovado\', \'confirmada\') THEN 1 ELSE 0 END) AS confirmed,
             COALESCE(SUM(CASE WHEN r.status IN (\'aprovado\', \'confirmada\') THEN c.registration_fee ELSE 0 END), 0) AS revenue
             FROM championships c
             LEFT JOIN registrations r ON r.championship_id = c.id
             WHERE c.organizer_id = ?'
        );
        $stmt->execute([$organizerId]);
        return $stmt->fetch() ?: [];
    }
}
