<?php

namespace App\Models;

class Registration extends Model
{
    public function create(array $data): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO registrations
            (championship_id, user_id, name, phone, email, team, category, city, cpf, notes, proof_file, status, accepted_terms, terms_version, accepted_terms_at, accepted_terms_ip)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ' . $this->db->quote('pendente') . ', ?, ?, CURRENT_TIMESTAMP, ?)'
        );
        $stmt->execute([
            $data['championship_id'], $data['user_id'], $data['name'], $data['phone'], $data['email'],
            $data['team'], $data['category'], $data['city'], $data['cpf'], $data['notes'], $data['proof_file'],
            !empty($data['accepted_terms']), 'v1', $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    }

    public function byUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT r.*, c.name AS championship_name, c.event_date, c.status AS championship_status, s.name AS sport_name
             FROM registrations r
             JOIN championships c ON c.id = r.championship_id
             JOIN sports s ON s.id = c.sport_id
             WHERE r.user_id = ? ORDER BY c.event_date DESC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function byOrganizer(int $organizerId, bool $isAdmin = false): array
    {
        $sql = 'SELECT r.*, c.name AS championship_name, c.registration_fee
                FROM registrations r
                JOIN championships c ON c.id = r.championship_id';
        $params = [];
        if (!$isAdmin) {
            $sql .= ' WHERE c.organizer_id = ?';
            $params[] = $organizerId;
        }
        $sql .= ' ORDER BY r.created_at DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function setStatus(int $id, string $status, int $organizerId, bool $isAdmin = false): void
    {
        $stmt = $this->db->prepare(
            'UPDATE registrations r
             SET status = ?
             FROM championships c
             WHERE c.id = r.championship_id AND r.id = ? AND (c.organizer_id = ? OR ? = true)'
        );
        $stmt->execute([$status, $id, $organizerId, $isAdmin]);
    }

    public function countPendingForChampionship(int $championshipId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM registrations WHERE championship_id = ? AND status = ?');
        $stmt->execute([$championshipId, 'pendente']);
        return (int) $stmt->fetchColumn();
    }

    public function countActiveForChampionship(int $championshipId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM registrations
             WHERE championship_id = ?
             AND status NOT IN ('cancelled', 'cancelado', 'rejected', 'rejeitado')"
        );
        $stmt->execute([$championshipId]);
        return (int) $stmt->fetchColumn();
    }

    public function statsForOrganizer(int $organizerId): array
    {
        $stmt = $this->db->prepare(
            'SELECT
             COUNT(DISTINCT c.id) AS total_championships,
             SUM(CASE WHEN c.status = ' . $this->db->quote('ativo') . ' THEN 1 ELSE 0 END) AS active_events,
             SUM(CASE WHEN c.status = ' . $this->db->quote('encerrado') . ' THEN 1 ELSE 0 END) AS closed_events,
             COUNT(r.id) AS total_registrations,
             SUM(CASE WHEN r.status = ' . $this->db->quote('aprovado') . ' THEN 1 ELSE 0 END) AS confirmed,
             COALESCE(SUM(CASE WHEN r.status = ' . $this->db->quote('aprovado') . ' THEN c.registration_fee ELSE 0 END), 0) AS revenue
             FROM championships c
             LEFT JOIN registrations r ON r.championship_id = c.id
             WHERE c.organizer_id = ?'
        );
        $stmt->execute([$organizerId]);
        return $stmt->fetch() ?: [];
    }
}
