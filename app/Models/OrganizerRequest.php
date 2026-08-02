<?php

namespace App\Models;

use PDO;
use PDOException;

class OrganizerRequest extends Model
{
    private array $validStatuses = ['pending', 'approved', 'rejected'];

    public function latestForUser(int $userId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT r.*, approver.name AS approved_by_name
             FROM organizer_requests r
             LEFT JOIN users approver ON approver.id = r.approved_by
             WHERE r.user_id = ?
             ORDER BY r.created_at DESC, r.id DESC
             LIMIT 1'
        );
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: null;
    }

    public function hasPendingForUser(int $userId): bool
    {
        $stmt = $this->db->prepare("SELECT id FROM organizer_requests WHERE user_id = ? AND status = 'pending' LIMIT 1");
        $stmt->execute([$userId]);
        return (bool) $stmt->fetch();
    }

    public function byStatus(string $status = ''): array
    {
        if ($status !== '' && !in_array($status, $this->validStatuses, true)) {
            return [];
        }

        $sql = 'SELECT r.*, u.name AS user_name, u.email AS user_email, u.name, u.email, u.city AS user_city, u.phone AS user_phone, approver.name AS approved_by_name
                FROM organizer_requests r
                JOIN users u ON u.id = r.user_id
                LEFT JOIN users approver ON approver.id = r.approved_by';
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
        if ($this->hasPendingForUser($userId)) {
            return 0;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO organizer_requests
             (user_id, responsible_name, document_number, organization_name, organization_type, phone, city, state, description, proof_file, status)
             VALUES (:user_id, :responsible_name, :document_number, :organization_name, :organization_type, :phone, :city, :state, :description, :proof_file, :status)
             RETURNING id'
        );
        try {
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
                'status' => 'pending',
            ]);
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23505') {
                return 0;
            }
            throw $exception;
        }

        return (int) $stmt->fetchColumn();
    }

    public function findWithUser(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT r.*, u.name AS user_name, u.email AS user_email, u.city AS user_city, u.phone AS user_phone, approver.name AS approved_by_name
             FROM organizer_requests r
             JOIN users u ON u.id = r.user_id
             LEFT JOIN users approver ON approver.id = r.approved_by
             WHERE r.id = ?
             LIMIT 1'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function approve(int $id, int $adminId): ?array
    {
        $this->db->beginTransaction();
        try {
            $request = $this->lockPending($id);
            if (!$request) {
                $this->db->rollBack();
                return null;
            }

            $this->db->prepare("UPDATE users SET role = 'organizer', suspended_at = NULL, suspension_reason = NULL WHERE id = ?")
                ->execute([(int) $request['user_id']]);

            $stmt = $this->db->prepare(
                'UPDATE organizer_requests
                 SET status = ?, rejection_reason = NULL, approved_by = ?, approved_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP
                 WHERE id = ?
                 RETURNING *'
            );
            $stmt->execute(['approved', $adminId, $id]);
            $request = $stmt->fetch() ?: $request;
            $this->db->commit();
            return $request;
        } catch (\Throwable $exception) {
            $this->db->rollBack();
            error_log($exception->getMessage());
            throw $exception;
        }
    }

    public function reject(int $id, string $reason): ?array
    {
        $this->db->beginTransaction();
        try {
            $request = $this->lockPending($id);
            if (!$request) {
                $this->db->rollBack();
                return null;
            }

            $stmt = $this->db->prepare(
                'UPDATE organizer_requests
                 SET status = ?, rejection_reason = ?, updated_at = CURRENT_TIMESTAMP
                 WHERE id = ?
                 RETURNING *'
            );
            $stmt->execute(['rejected', $reason, $id]);
            $request = $stmt->fetch() ?: $request;
            $this->db->prepare("UPDATE users SET role = 'athlete' WHERE id = ? AND role <> 'admin'")
                ->execute([(int) $request['user_id']]);
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
             COALESCE(SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END), 0) AS rejected
             FROM organizer_requests"
        );
        return $stmt->fetch() ?: [];
    }

    private function lockPending(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM organizer_requests WHERE id = ? AND status = 'pending' LIMIT 1 FOR UPDATE");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
