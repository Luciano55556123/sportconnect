<?php

namespace App\Models;

class Championship extends Model
{
    private const PUBLIC_STATUSES = ['published', 'registration_open', 'registration_closed', 'in_progress', 'finished'];

    public function search(array $filters = [], int $limit = 50, bool $publicOnly = true): array
    {
        $sql = 'SELECT c.*, s.name AS sport_name, u.name AS organizer_name,
                (SELECT COUNT(*) FROM registrations r WHERE r.championship_id = c.id) AS registrations_count
                FROM championships c
                JOIN sports s ON s.id = c.sport_id
                JOIN users u ON u.id = c.organizer_id
                WHERE 1 = 1';
        $params = [];

        if ($publicOnly) {
            $sql .= " AND c.status IN ('published', 'registration_open', 'registration_closed', 'in_progress', 'finished')";
            if ($this->appEnv() === 'production') {
                $sql .= ' AND COALESCE(c.is_demo, false) = false';
            }
        }

        foreach (['city', 'modality'] as $field) {
            if (!empty($filters[$field])) {
                $sql .= " AND c.$field = ?";
                $params[] = $filters[$field];
            }
        }
        if (!empty($filters['status']) && in_array($filters['status'], self::PUBLIC_STATUSES, true)) {
            $sql .= ' AND c.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['state'])) {
            $sql .= ' AND c.state = ?';
            $params[] = strtoupper(substr($filters['state'], 0, 2));
        }
        if (!empty($filters['registration_type'])) {
            $sql .= ' AND c.registration_type = ?';
            $params[] = $filters['registration_type'];
        }
        if (!empty($filters['q'])) {
            $sql .= ' AND (c.name ILIKE ? OR c.description ILIKE ? OR u.name ILIKE ?)';
            $term = '%' . $filters['q'] . '%';
            array_push($params, $term, $term, $term);
        }
        if (!empty($filters['sport_id'])) {
            $sql .= ' AND c.sport_id = ?';
            $params[] = (int) $filters['sport_id'];
        }
        if (!empty($filters['category'])) {
            $sql .= ' AND c.category ILIKE ?';
            $params[] = '%' . $filters['category'] . '%';
        }
        if (!empty($filters['date'])) {
            $sql .= ' AND c.event_date = ?';
            $params[] = $filters['date'];
        }
        if (!empty($filters['date_from'])) {
            $sql .= ' AND c.event_date >= ?';
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql .= ' AND c.event_date <= ?';
            $params[] = $filters['date_to'];
        }
        if (($filters['min_price'] ?? '') !== '') {
            $sql .= ' AND c.registration_fee >= ?';
            $params[] = (float) $filters['min_price'];
        }
        if ($filters['max_price'] ?? '' !== '') {
            $sql .= ' AND c.registration_fee <= ?';
            $params[] = (float) $filters['max_price'];
        }
        if (!empty($filters['free'])) {
            $sql .= ' AND c.registration_fee = 0';
        }
        if (!empty($filters['registrations_open'])) {
            $sql .= " AND c.status = 'registration_open'";
        }

        $order = match ($filters['sort'] ?? '') {
            'recentes' => 'c.created_at DESC',
            'menor_preco' => 'c.registration_fee ASC, c.event_date ASC',
            'maior_preco' => 'c.registration_fee DESC, c.event_date ASC',
            'mais_inscritos' => 'registrations_count DESC, c.event_date ASC',
            default => 'c.event_date ASC',
        };
        $sql .= ' ORDER BY ' . $order . ' LIMIT ' . (int) $limit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function featured(): array
    {
        return $this->search([], 6);
    }

    public function mostViewed(): array
    {
        $demoFilter = $this->appEnv() === 'production' ? ' AND COALESCE(c.is_demo, false) = false' : '';
        return $this->db->query(
            "SELECT c.*, s.name AS sport_name, u.name AS organizer_name
             FROM championships c
             JOIN sports s ON s.id = c.sport_id
             JOIN users u ON u.id = c.organizer_id
             WHERE c.status IN ('published', 'registration_open', 'registration_closed', 'in_progress', 'finished')" . $demoFilter . "
             ORDER BY c.views DESC, c.event_date ASC LIMIT 6"
        )->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT c.*, s.name AS sport_name, u.name AS organizer_name,
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
        $stmt = $this->db->prepare(
            'INSERT INTO championships
            (organizer_id, sport_id, name, city, location, map_link, event_date, event_time, registration_fee,
             prize, max_participants, description, rules_file, category, modality, status, whatsapp_contato, image, imagem, editorial_status)
             VALUES (:organizer_id, :sport_id, :name, :city, :location, :map_link, :event_date, :event_time,
             :registration_fee, :prize, :max_participants, :description, :rules_file, :category, :modality, :status,
             :whatsapp_contato, :image, :imagem, :editorial_status)
             RETURNING id'
        );
        $payload = $this->payload($data, $organizerId);
        unset($payload['is_admin']);
        $stmt->execute($payload);
        return (int) $stmt->fetchColumn();
    }

    public function update(int $id, array $data, int $organizerId): void
    {
        $payload = $this->payload($data, $organizerId);
        unset($payload['editorial_status']);
        $payload['id'] = $id;
        $stmt = $this->db->prepare(
            "UPDATE championships SET sport_id=:sport_id, name=:name, city=:city, location=:location,
            map_link=:map_link, event_date=:event_date, event_time=:event_time, registration_fee=:registration_fee,
            prize=:prize, max_participants=:max_participants, description=:description, rules_file=:rules_file,
            category=:category, modality=:modality, status=:status, whatsapp_contato=:whatsapp_contato,
            image=:image, imagem=:imagem
            WHERE id=:id AND (organizer_id=:organizer_id OR :is_admin = true)
            AND (COALESCE(editorial_status, 'draft') <> 'pending_review' OR :is_admin = true)"
        );
        $stmt->execute($payload);
    }

    public function updateCompetitionInfo(int $id, array $data, int $organizerId, bool $isAdmin = false): void
    {
        $stmt = $this->db->prepare(
            'UPDATE championships SET
                competition_format = :competition_format,
                end_date = :end_date,
                registration_deadline = :registration_deadline,
                registrations_open = :registrations_open,
                address = :address,
                neighborhood = :neighborhood,
                state = :state,
                zip_code = :zip_code,
                reference_point = :reference_point,
                court_or_field = :court_or_field,
                rules = :rules,
                tiebreak_rules = :tiebreak_rules,
                qualification_rules = :qualification_rules,
                elimination_rules = :elimination_rules,
                required_documents = :required_documents,
                cancellation_policy = :cancellation_policy
             WHERE id = :id AND (organizer_id = :organizer_id OR :is_admin = true)'
        );
        $stmt->execute([
            'competition_format' => trim($data['competition_format'] ?? '') ?: null,
            'end_date' => ($data['end_date'] ?? '') !== '' ? $data['end_date'] : null,
            'registration_deadline' => ($data['registration_deadline'] ?? '') !== '' ? $data['registration_deadline'] : null,
            'registrations_open' => !empty($data['registrations_open']),
            'address' => trim($data['address'] ?? '') ?: null,
            'neighborhood' => trim($data['neighborhood'] ?? '') ?: null,
            'state' => strtoupper(substr(trim($data['state'] ?? ''), 0, 2)) ?: null,
            'zip_code' => trim($data['zip_code'] ?? '') ?: null,
            'reference_point' => trim($data['reference_point'] ?? '') ?: null,
            'court_or_field' => trim($data['court_or_field'] ?? '') ?: null,
            'rules' => trim($data['rules'] ?? '') ?: null,
            'tiebreak_rules' => trim($data['tiebreak_rules'] ?? '') ?: null,
            'qualification_rules' => trim($data['qualification_rules'] ?? '') ?: null,
            'elimination_rules' => trim($data['elimination_rules'] ?? '') ?: null,
            'required_documents' => trim($data['required_documents'] ?? '') ?: null,
            'cancellation_policy' => trim($data['cancellation_policy'] ?? '') ?: null,
            'id' => $id,
            'organizer_id' => $organizerId,
            'is_admin' => $isAdmin,
        ]);
    }

    public function canManage(int $id, int $userId, bool $isAdmin = false): bool
    {
        if ($isAdmin) {
            return $this->find($id) !== null;
        }

        $stmt = $this->db->prepare('SELECT COUNT(*) FROM championships WHERE id = ? AND organizer_id = ?');
        $stmt->execute([$id, $userId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function pendingReview(): array
    {
        $stmt = $this->db->query(
            "SELECT c.*, s.name AS sport_name, u.name AS organizer_name, u.email AS organizer_email
             FROM championships c
             JOIN sports s ON s.id = c.sport_id
             JOIN users u ON u.id = c.organizer_id
             WHERE COALESCE(c.editorial_status, 'draft') = 'pending_review'
             ORDER BY c.publication_requested_at DESC NULLS LAST, c.created_at DESC
             LIMIT 100"
        );
        return $stmt->fetchAll();
    }

    public function sendToReview(int $id, int $organizerId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE championships
             SET editorial_status = 'pending_review', publication_requested_at = CURRENT_TIMESTAMP
             WHERE id = ? AND organizer_id = ? AND COALESCE(editorial_status, 'draft') IN ('draft', 'rejected')
             AND name <> '' AND city <> '' AND location <> '' AND description <> ''
             RETURNING id"
        );
        $stmt->execute([$id, $organizerId]);
        return (bool) $stmt->fetchColumn();
    }

    public function reviewPublication(int $id, string $status, int $adminId, ?string $reason = null): ?array
    {
        $publishedAt = in_array($status, ['published', 'registration_open'], true) ? 'CURRENT_TIMESTAMP' : 'published_at';
        $stmt = $this->db->prepare(
            "UPDATE championships
             SET editorial_status = ?, reviewed_by = ?, reviewed_at = CURRENT_TIMESTAMP,
                 rejection_reason = ?, published_at = {$publishedAt}
             WHERE id = ?
             RETURNING *"
        );
        $stmt->execute([$status, $adminId, $reason, $id]);
        return $stmt->fetch() ?: null;
    }

    public function editorialCounts(): array
    {
        $stmt = $this->db->query(
            "SELECT
             COALESCE(SUM(CASE WHEN editorial_status = 'pending_review' THEN 1 ELSE 0 END), 0) AS pending,
             COALESCE(SUM(CASE WHEN editorial_status IN ('published','registration_open','registration_closed','in_progress') THEN 1 ELSE 0 END), 0) AS published,
             COALESCE(SUM(CASE WHEN editorial_status = 'draft' THEN 1 ELSE 0 END), 0) AS draft
             FROM championships"
        );
        return $stmt->fetch() ?: [];
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
            "SELECT c.id, c.name, c.city, c.event_date, c.whatsapp_contato, s.name AS sport_name
             FROM championships c JOIN sports s ON s.id = c.sport_id
             WHERE c.status IN ('published', 'registration_open', 'registration_closed', 'in_progress', 'finished')
             ORDER BY c.event_date ASC"
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
            'whatsapp_contato' => $data['whatsapp_contato'],
            'image' => $data['image'] ?? 'assets/img/default-event.svg',
            'imagem' => $data['imagem'] ?? null,
            'is_admin' => !empty($data['is_admin']),
            'editorial_status' => $data['editorial_status'] ?? 'draft',
        ];
    }

    private function appEnv(): string
    {
        $env = getenv('APP_ENV');
        return $env === false || trim($env) === '' ? 'local' : trim($env);
    }
}
