<?php

namespace App\Models;

class Report extends Model
{
    public function allForAdmin(string $status = ''): array
    {
        $sql = "SELECT rp.*, c.name AS championship_name, u.name AS reporter_name, u.email AS reporter_email,
                reviewer.name AS reviewer_name
                FROM reports rp
                LEFT JOIN championships c ON c.id = rp.championship_id
                JOIN users u ON u.id = rp.reporter_user_id
                LEFT JOIN users reviewer ON reviewer.id = rp.reviewed_by";
        $params = [];
        if ($status !== '' && in_array($status, ['open','under_review','resolved','rejected','archived'], true)) {
            $sql .= ' WHERE rp.status = ?';
            $params[] = $status;
        }
        $sql .= ' ORDER BY rp.created_at DESC LIMIT 100';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function create(array $data, int $reporterId, int $championshipId): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO reports (reporter_user_id, championship_id, report_type, title, description)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $reporterId,
            $championshipId,
            in_array($data['report_type'] ?? '', ['fake_championship','incorrect_information','improper_charge','offensive_behavior','incorrect_result','fraud','other'], true) ? $data['report_type'] : 'other',
            trim($data['title'] ?? ''),
            trim($data['description'] ?? ''),
        ]);
    }

    public function review(int $id, string $status, int $adminId, ?string $notes = null): ?array
    {
        if (!in_array($status, ['under_review','resolved','rejected','archived'], true)) {
            return null;
        }

        $stmt = $this->db->prepare(
            'UPDATE reports
             SET status = ?, admin_notes = ?, reviewed_by = ?, reviewed_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP
             WHERE id = ?
             RETURNING *'
        );
        $stmt->execute([$status, $notes, $adminId, $id]);
        return $stmt->fetch() ?: null;
    }

    public function openCount(): int
    {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) FROM reports WHERE status IN ('open', 'under_review')");
            return (int) $stmt->fetchColumn();
        } catch (\Throwable) {
            return 0;
        }
    }
}
