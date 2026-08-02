<?php

namespace App\Models;

class OrganizerRequest extends Model
{
    public function latestForUser(int $userId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM organizer_requests WHERE user_id = ? ORDER BY created_at DESC LIMIT 1');
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: null;
    }

    public function byStatus(string $status = ''): array
    {
        $sql = 'SELECT r.*, u.name AS user_name, u.email AS user_email, reviewer.name AS reviewer_name
                FROM organizer_requests r
                JOIN users u ON u.id = r.user_id
                LEFT JOIN users reviewer ON reviewer.id = r.reviewed_by';
        $params = [];
        if ($status !== '') {
            $sql .= ' WHERE r.status = ?';
            $params[] = $status;
        }
        $sql .= ' ORDER BY r.created_at DESC LIMIT 100';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function create(array $data, int $userId, ?string $proofFile = null): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO organizer_requests
             (user_id, responsible_name, document_number, organization_name, organization_type, phone, city, state, description, proof_file)
             VALUES (:user_id, :responsible_name, :document_number, :organization_name, :organization_type, :phone, :city, :state, :description, :proof_file)
             RETURNING id'
        );
        $stmt->execute([
            'user_id' => $userId,
            'responsible_name' => trim($data['responsible_name'] ?? ''),
            'document_number' => preg_replace('/\D+/', '', $data['document_number'] ?? '') ?: '',
            'organization_name' => trim($data['organization_name'] ?? ''),
            'organization_type' => trim($data['organization_type'] ?? ''),
            'phone' => preg_replace('/\D+/', '', $data['phone'] ?? '') ?: '',
            'city' => trim($data['city'] ?? ''),
            'state' => strtoupper(substr(trim($data['state'] ?? ''), 0, 2)),
            'description' => trim($data['description'] ?? ''),
            'proof_file' => $proofFile,
        ]);
        return (int) $stmt->fetchColumn();
    }

    public function review(int $id, string $status, int $reviewerId, ?string $reason = null): ?array
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                'UPDATE organizer_requests
                 SET status = ?, rejection_reason = ?, reviewed_by = ?, reviewed_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP
                 WHERE id = ?
                 RETURNING *'
            );
            $stmt->execute([$status, $reason, $reviewerId, $id]);
            $request = $stmt->fetch() ?: null;
            if ($request && $status === 'approved') {
                $this->db->prepare('UPDATE users SET role = ?, suspended_at = NULL, suspension_reason = NULL WHERE id = ?')->execute(['organizer', (int) $request['user_id']]);
            }
            if ($request && $status === 'suspended') {
                $this->db->prepare('UPDATE users SET suspended_at = CURRENT_TIMESTAMP, suspension_reason = ? WHERE id = ?')->execute([$reason, (int) $request['user_id']]);
            }
            if ($request && $status === 'rejected') {
                $this->db->prepare('UPDATE users SET role = ? WHERE id = ? AND role <> ?')->execute(['athlete', (int) $request['user_id'], 'admin']);
            }
            $this->db->commit();
            return $request;
        } catch (\Throwable $exception) {
            $this->db->rollBack();
            error_log($exception->getMessage());
            throw $exception;
        }
    }

    public function counts(): array
    {
        $stmt = $this->db->query(
            "SELECT
             COALESCE(SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END), 0) AS pending,
             COALESCE(SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END), 0) AS approved,
             COALESCE(SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END), 0) AS rejected,
             COALESCE(SUM(CASE WHEN status = 'suspended' THEN 1 ELSE 0 END), 0) AS suspended
             FROM organizer_requests"
        );
        return $stmt->fetch() ?: [];
    }
}
