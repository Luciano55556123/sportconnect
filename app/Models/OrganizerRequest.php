<?php

namespace App\Models;

use PDO;
use PDOException;

class OrganizerRequest extends Model
{
    private array $validStatuses = ['pending', 'approved', 'rejected'];
    private ?array $columns = null;

    public function createForUser(int $userId, array $data = []): int
    {
        if ($this->hasPendingForUser($userId)) {
            return 0;
        }

        $fields = [
            'user_id' => $userId,
            'status' => 'pending',
        ];

        $this->putIfColumnExists($fields, 'responsible_name', $data['responsible_name'] ?? null);
        $this->putIfColumnExists($fields, 'cpf_cnpj', $data['document'] ?? null);
        $this->putIfColumnExists($fields, 'document', $data['document'] ?? null);
        $this->putIfColumnExists($fields, 'document_number', $data['document'] ?? null);
        $this->putIfColumnExists($fields, 'organization_name', $data['organization_name'] ?? null);
        $this->putIfColumnExists($fields, 'organization_type', $data['organization_type'] ?? null);
        $this->putIfColumnExists($fields, 'phone', $data['phone'] ?? null);
        $this->putIfColumnExists($fields, 'whatsapp', $data['whatsapp'] ?? null);
        $this->putIfColumnExists($fields, 'email', $data['contact_email'] ?? null);
        $this->putIfColumnExists($fields, 'contact_email', $data['contact_email'] ?? null);
        $this->putIfColumnExists($fields, 'city', $data['city'] ?? null);
        $this->putIfColumnExists($fields, 'state', $data['state'] ?? null);
        $this->putIfColumnExists($fields, 'uf', $data['state'] ?? null);
        $this->putIfColumnExists($fields, 'experience', $data['experience'] ?? null);
        $this->putIfColumnExists($fields, 'event_experience', $data['experience'] ?? null);
        $this->putIfColumnExists($fields, 'reason', $data['request_reason'] ?? null);
        $this->putIfColumnExists($fields, 'request_reason', $data['request_reason'] ?? null);
        $this->putIfColumnExists($fields, 'description', $data['request_reason'] ?? null);

        try {
            $columns = array_keys($fields);
            $placeholders = array_map(static fn(string $column): string => ':' . $column, $columns);
            $sql = 'INSERT INTO organizer_requests (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')';
            if ($this->db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql') {
                $sql .= ' RETURNING id';
            }

            $stmt = $this->db->prepare(
                $sql
            );
            $stmt->execute($fields);
        } catch (PDOException $exception) {
            if (in_array($exception->getCode(), ['23000', '23505'], true)) {
                return 0;
            }

            throw $exception;
        }

        $this->columns = [];

        if ($this->db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql') {
            return (int) $stmt->fetchColumn();
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
        $approverColumn = $this->reviewerColumn();
        $stmt = $this->db->prepare(
            'SELECT r.*, approver.name AS approved_by_name
             FROM organizer_requests r
             ' . ($approverColumn ? 'LEFT JOIN users approver ON approver.id = r.' . $approverColumn : 'LEFT JOIN users approver ON 1 = 0') . '
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

    public function all(): array
    {
        $stmt = $this->db->query(
            'SELECT r.*, u.name AS user_name, u.email AS user_email, u.city AS user_city, u.phone AS user_phone
             FROM organizer_requests r
             INNER JOIN users u ON u.id = r.user_id
             ORDER BY r.created_at DESC, r.id DESC'
        );

        return $stmt->fetchAll();
    }

    public function byStatus(string $status): array
    {
        if (!in_array($status, $this->validStatuses, true)) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT r.*, u.name AS user_name, u.email AS user_email, u.city AS user_city, u.phone AS user_phone
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
        $approverColumn = $this->reviewerColumn();
        $stmt = $this->db->prepare(
            'SELECT r.*, u.name AS user_name, u.email AS user_email, u.city AS user_city, u.phone AS user_phone, u.role, approver.name AS approved_by_name
             FROM organizer_requests r
             INNER JOIN users u ON u.id = r.user_id
             ' . ($approverColumn ? 'LEFT JOIN users approver ON approver.id = r.' . $approverColumn : 'LEFT JOIN users approver ON 1 = 0') . '
             WHERE r.id = ?
             LIMIT 1'
        );
        $stmt->execute([$id]);

        return $stmt->fetch() ?: null;
    }

    public function approve(int $id, int $adminId): bool
    {
        try {
            $this->db->beginTransaction();

            $request = $this->lockPending($id);
            if (!$request) {
                $this->db->rollBack();
                return false;
            }

            $this->db->prepare("UPDATE users SET role = CASE WHEN role = 'admin' THEN role ELSE 'organizer' END WHERE id = ?")
                ->execute([(int) $request['user_id']]);

            $fields = ['status' => 'approved'];
            $sets = ['status = :status'];
            $this->addTimestampSet($sets, 'approved_at');
            $this->addTimestampSet($sets, 'reviewed_at');
            $this->addValueSet($sets, $fields, 'approved_by', $adminId);
            $this->addValueSet($sets, $fields, 'reviewed_by', $adminId);
            $this->addTimestampSet($sets, 'updated_at');
            $fields['id'] = $id;

            $stmt = $this->db->prepare(
                'UPDATE organizer_requests SET ' . implode(', ', $sets) . ' WHERE id = :id'
            );
            $stmt->execute($fields);

            $this->db->commit();
            return true;
        } catch (PDOException $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $exception;
        }
    }

    public function reject(int $id, string $reason, int $adminId): bool
    {
        try {
            $this->db->beginTransaction();

            if (!$this->lockPending($id)) {
                $this->db->rollBack();
                return false;
            }

            $fields = ['status' => 'rejected'];
            $sets = ['status = :status'];
            $this->addValueSet($sets, $fields, 'rejection_reason', $reason);
            $this->addTimestampSet($sets, 'reviewed_at');
            $this->addValueSet($sets, $fields, 'reviewed_by', $adminId);
            $this->addTimestampSet($sets, 'updated_at');
            $fields['id'] = $id;

            $stmt = $this->db->prepare(
                'UPDATE organizer_requests SET ' . implode(', ', $sets) . ' WHERE id = :id'
            );
            $stmt->execute($fields);

            $this->db->commit();
            return true;
        } catch (PDOException $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $exception;
        }
    }

    private function lockPending(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM organizer_requests WHERE id = ? AND status = 'pending' LIMIT 1 FOR UPDATE"
        );
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function putIfColumnExists(array &$fields, string $column, ?string $value): void
    {
        if ($this->hasColumn($column)) {
            $fields[$column] = $value;
        }
    }

    private function addTimestampSet(array &$sets, string $column): void
    {
        if ($this->hasColumn($column)) {
            $sets[] = $column . ' = CURRENT_TIMESTAMP';
        }
    }

    private function addValueSet(array &$sets, array &$fields, string $column, int|string $value): void
    {
        if ($this->hasColumn($column)) {
            $sets[] = $column . ' = :' . $column;
            $fields[$column] = $value;
        }
    }

    private function reviewerColumn(): ?string
    {
        if ($this->hasColumn('reviewed_by')) {
            return 'reviewed_by';
        }

        return $this->hasColumn('approved_by') ? 'approved_by' : null;
    }

    private function hasColumn(string $column): bool
    {
        return in_array($column, $this->columns(), true);
    }

    private function columns(): array
    {
        if ($this->columns !== null) {
            return $this->columns;
        }

        if ($this->db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql') {
            $stmt = $this->db->prepare(
                "SELECT column_name
                 FROM information_schema.columns
                 WHERE table_name = 'organizer_requests'
                 AND table_schema = ANY (current_schemas(false))"
            );
            $stmt->execute();
            $this->columns = array_column($stmt->fetchAll(), 'column_name');
        }

        if ($this->columns === []) {
            $stmt = $this->db->prepare(
                "SELECT column_name
                 FROM information_schema.columns
                 WHERE table_name = 'organizer_requests'"
            );
            $stmt->execute();
            $this->columns = array_column($stmt->fetchAll(), 'column_name');
        }

        return $this->columns;
    }
}
