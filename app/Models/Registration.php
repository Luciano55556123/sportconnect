<?php

namespace App\Models;

class Registration extends Model
{
    private ?array $championshipColumns = null;

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
        $pixReceiverCitySelect = $this->championshipHasColumn('pix_receiver_city')
            ? 'c.pix_receiver_city'
            : 'c.city AS pix_receiver_city';
        $stmt = $this->db->prepare(
            'SELECT r.*, c.name AS championship_name, c.registration_fee, c.requires_payment,
             c.registration_fee AS championship_registration_fee,
             c.requires_payment AS championship_requires_payment,
             c.pix_key, c.pix_key_type, c.pix_holder_name, ' . $pixReceiverCitySelect . ', c.pix_instructions, c.email_contato,
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
        $pixReceiverCitySelect = $this->championshipHasColumn('pix_receiver_city')
            ? 'championships.pix_receiver_city'
            : 'championships.city AS pix_receiver_city';
        $championshipWhatsappSelect = $this->championshipHasColumn('whatsapp_contato')
            ? 'championships.whatsapp_contato AS championship_whatsapp_contato'
            : 'NULL AS championship_whatsapp_contato';
        $stmt = $this->db->prepare(
            'SELECT r.*, championships.name AS championship_name, championships.event_date, championships.status AS championship_status,
             championships.requires_payment AS championship_requires_payment,
             championships.registration_fee AS championship_registration_fee,
             ' . $championshipWhatsappSelect . ',
             championships.pix_key, championships.pix_key_type, championships.pix_holder_name, ' . $pixReceiverCitySelect . ',
             championships.pix_instructions,
             s.name AS sport_name, p.status AS payment_status, p.amount AS payment_amount,
             p.receipt_file AS receipt_path, p.rejection_reason AS review_notes
             FROM registrations r
             JOIN championships ON championships.id = r.championship_id
             JOIN sports s ON s.id = championships.sport_id
             LEFT JOIN registration_payments p ON p.registration_id = r.id
             WHERE r.user_id = ? ORDER BY championships.event_date DESC'
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

    public function countPendingForChampionship(int $championshipId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*)
             FROM registrations
             WHERE championship_id = ?
             AND status IN ('pendente', 'aguardando_pagamento')"
        );
        $stmt->execute([$championshipId]);
        return (int) $stmt->fetchColumn();
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
             COUNT(DISTINCT CASE WHEN c.status IN (\'ativo\', \'registration_open\') THEN c.id END) AS active_events,
             COUNT(DISTINCT CASE WHEN c.status = \'encerrado\' THEN c.id END) AS closed_events,
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

    private function championshipHasColumn(string $column): bool
    {
        return in_array($column, $this->championshipColumns(), true);
    }

    private function championshipColumns(): array
    {
        if ($this->championshipColumns !== null) {
            return $this->championshipColumns;
        }

        $this->championshipColumns = [];

        if ($this->db->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'pgsql') {
            $stmt = $this->db->prepare(
                "SELECT column_name
                 FROM information_schema.columns
                 WHERE table_name = 'championships'
                 AND table_schema = ANY (current_schemas(false))"
            );
            $stmt->execute();
            $this->championshipColumns = array_column($stmt->fetchAll(), 'column_name');
        }

        if ($this->championshipColumns === []) {
            $stmt = $this->db->prepare(
                "SELECT column_name
                 FROM information_schema.columns
                 WHERE table_name = 'championships'"
            );
            $stmt->execute();
            $this->championshipColumns = array_column($stmt->fetchAll(), 'column_name');
        }

        return $this->championshipColumns;
    }
}
