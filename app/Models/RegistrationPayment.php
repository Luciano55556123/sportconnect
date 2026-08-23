<?php

namespace App\Models;

class RegistrationPayment extends Model
{
    public function createPending(int $registrationId, float $amount): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO registration_payments (registration_id, amount, payment_method, status)
             VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$registrationId, $amount, 'pix', 'awaiting_receipt']);
    }

    public function findForRegistration(int $registrationId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM registration_payments WHERE registration_id = ? LIMIT 1');
        $stmt->execute([$registrationId]);
        return $stmt->fetch() ?: null;
    }

    public function findForAthlete(int $registrationId, int $userId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT p.*, p.receipt_file AS receipt_path, p.rejection_reason AS review_notes, r.user_id, r.championship_id
             FROM registration_payments p
             JOIN registrations r ON r.id = p.registration_id
             WHERE p.registration_id = ? AND r.user_id = ? LIMIT 1'
        );
        $stmt->execute([$registrationId, $userId]);
        return $stmt->fetch() ?: null;
    }

    public function findForOrganizer(int $registrationId, int $organizerId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT p.*, p.receipt_file AS receipt_path, p.rejection_reason AS review_notes, r.user_id, r.championship_id
             FROM registration_payments p
             JOIN registrations r ON r.id = p.registration_id
             JOIN championships c ON c.id = r.championship_id
             WHERE p.registration_id = ? AND c.organizer_id = ? LIMIT 1'
        );
        $stmt->execute([$registrationId, $organizerId]);
        return $stmt->fetch() ?: null;
    }

    public function submitReceipt(int $registrationId, int $userId, string $receiptPath): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE registration_payments p
             SET receipt_file = ?, status = ?, updated_at = CURRENT_TIMESTAMP
             WHERE p.registration_id = ?
             AND EXISTS (SELECT 1 FROM registrations r WHERE r.id = p.registration_id AND r.user_id = ?)'
        );
        $stmt->execute([$receiptPath, 'under_review', $registrationId, $userId]);
        return $stmt->rowCount() > 0;
    }

    public function review(int $registrationId, int $organizerId, string $paymentStatus, string $registrationStatus, string $notes = ''): bool
    {
        $startedTransaction = !$this->db->inTransaction();
        if ($startedTransaction) {
            $this->db->beginTransaction();
        }
        try {
            $payment = $this->findForOrganizer($registrationId, $organizerId);
            if (!$payment) {
                if ($startedTransaction) {
                    $this->db->rollBack();
                }
                return false;
            }

            $stmt = $this->db->prepare(
                'UPDATE registration_payments
                 SET status = ?, reviewed_at = CURRENT_TIMESTAMP, reviewed_by = ?, rejection_reason = ?, updated_at = CURRENT_TIMESTAMP
                 WHERE registration_id = ?'
            );
            $stmt->execute([$paymentStatus, $organizerId, $notes ?: null, $registrationId]);

            $registration = $this->db->prepare(
                'UPDATE registrations
                 SET status = ?
                 WHERE id = ?
                 AND EXISTS (
                    SELECT 1 FROM championships c
                    WHERE c.id = registrations.championship_id AND c.organizer_id = ?
                 )'
            );
            $registration->execute([$registrationStatus, $registrationId, $organizerId]);

            if ($startedTransaction) {
                $this->db->commit();
            }
            return true;
        } catch (\Throwable $exception) {
            if ($startedTransaction) {
                $this->db->rollBack();
            }
            throw $exception;
        }
    }
}
