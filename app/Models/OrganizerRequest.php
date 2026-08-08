<?php

namespace App\Models;

use PDO;
use PDOException;

class OrganizerRequest extends Model
{
    private array $validStatuses = ['pending', 'approved', 'rejected'];

    public function createForUser(int $userId): int
    {
        if ($this->hasPendingForUser($userId)) {
            return 0;
        }

        try {
            $stmt = $this->db->prepare(
                "INSERT INTO organizer_requests (user_id, status) VALUES (?, 'pending')"
            );
            $stmt->execute([$userId]);
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                return 0;
            }

            throw $exception;
        }

        return (int) $this->db->lastInsertId();
    }

    public function hasPendingForUser(int $userId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT id FROM organizer_requests WHERE user_id = ? AND status = 'pending' LIMIT 1"
        );
        $stmt->execute([$userId]);

        return (bool) $stmt->fetch();
    }

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

    public function pending(): array
    {
        return $this->byStatus('pending');
    }

    public function byStatus(string $status): array
    {
        if (!in_array($status, $this->validStatuses, true)) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT r.*, u.name, u.email, u.city, u.phone
             FROM organizer_requests r
             INNER JOIN users u ON u.id = r.user_id
             WHERE r.status = ?
             ORDER BY r.created_at DESC, r.id DESC'
        );
        $stmt->execute([$status]);

        return $stmt->fetchAll();
    }

    public function findWithUser(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT r.*, u.name, u.email, u.city, u.phone, u.role, approver.name AS approved_by_name
             FROM organizer_requests r
             INNER JOIN users u ON u.id = r.user_id
             LEFT JOIN users approver ON approver.id = r.approved_by
             WHERE r.id = ?
             LIMIT 1'
        );
        $stmt->execute([$id]);

        return $stmt->fetch() ?: null;
    }

    public function approve(int $id, int $adminId): bool
    {
        $this->db->beginTransaction();

        $request = $this->lockPending($id);
        if (!$request) {
            $this->db->rollBack();
            return false;
        }

        $this->db->prepare("UPDATE users SET role = 'organizer' WHERE id = ?")
            ->execute([(int) $request['user_id']]);

        $stmt = $this->db->prepare(
            "UPDATE organizer_requests
             SET status = 'approved', approved_by = ?, approved_at = NOW(), updated_at = NOW()
             WHERE id = ?"
        );
        $stmt->execute([$adminId, $id]);

        $this->db->commit();
        return true;
    }

    public function reject(int $id, string $reason): bool
    {
        $this->db->beginTransaction();

        if (!$this->lockPending($id)) {
            $this->db->rollBack();
            return false;
        }

        $stmt = $this->db->prepare(
            "UPDATE organizer_requests
             SET status = 'rejected', rejection_reason = ?, updated_at = NOW()
             WHERE id = ?"
        );
        $stmt->execute([$reason, $id]);

        $this->db->commit();
        return true;
    }

    private function lockPending(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM organizer_requests WHERE id = ? AND status = 'pending' LIMIT 1 FOR UPDATE"
        );
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
