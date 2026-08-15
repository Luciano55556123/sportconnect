-- Supabase/PostgreSQL migration for organizer request details.
-- Keeps the existing table and fills required columns before enforcing constraints.

ALTER TABLE organizer_requests
    ADD COLUMN IF NOT EXISTS responsible_name VARCHAR(120),
    ADD COLUMN IF NOT EXISTS document_number VARCHAR(30),
    ADD COLUMN IF NOT EXISTS organization_name VARCHAR(160),
    ADD COLUMN IF NOT EXISTS organization_type VARCHAR(80),
    ADD COLUMN IF NOT EXISTS phone VARCHAR(30),
    ADD COLUMN IF NOT EXISTS city VARCHAR(100),
    ADD COLUMN IF NOT EXISTS state VARCHAR(2),
    ADD COLUMN IF NOT EXISTS description TEXT,
    ADD COLUMN IF NOT EXISTS whatsapp VARCHAR(30),
    ADD COLUMN IF NOT EXISTS contact_email VARCHAR(160),
    ADD COLUMN IF NOT EXISTS experience TEXT,
    ADD COLUMN IF NOT EXISTS request_reason TEXT,
    ADD COLUMN IF NOT EXISTS reviewed_at TIMESTAMP,
    ADD COLUMN IF NOT EXISTS reviewed_by BIGINT REFERENCES users(id) ON DELETE SET NULL;

UPDATE organizer_requests request
SET
    responsible_name = COALESCE(NULLIF(BTRIM(request.responsible_name), ''), users.name),
    phone = COALESCE(NULLIF(BTRIM(request.phone), ''), users.phone),
    city = COALESCE(NULLIF(BTRIM(request.city), ''), users.city),
    contact_email = COALESCE(NULLIF(BTRIM(request.contact_email), ''), users.email)
FROM users
WHERE users.id = request.user_id;

UPDATE organizer_requests
SET
    document_number = COALESCE(NULLIF(BTRIM(document_number), ''), 'nao informado'),
    organization_name = COALESCE(NULLIF(BTRIM(organization_name), ''), responsible_name),
    organization_type = COALESCE(NULLIF(BTRIM(organization_type), ''), 'nao informado'),
    phone = COALESCE(NULLIF(BTRIM(phone), ''), 'nao informado'),
    city = COALESCE(NULLIF(BTRIM(city), ''), 'nao informado'),
    state = COALESCE(NULLIF(BTRIM(state), ''), 'NA'),
    description = COALESCE(NULLIF(BTRIM(description), ''), 'nao informado');

ALTER TABLE organizer_requests
    ALTER COLUMN responsible_name SET NOT NULL,
    ALTER COLUMN document_number SET NOT NULL,
    ALTER COLUMN organization_name SET NOT NULL,
    ALTER COLUMN organization_type SET NOT NULL,
    ALTER COLUMN phone SET NOT NULL,
    ALTER COLUMN city SET NOT NULL,
    ALTER COLUMN state SET NOT NULL,
    ALTER COLUMN description SET NOT NULL;

CREATE UNIQUE INDEX IF NOT EXISTS unique_pending_organizer_request
    ON organizer_requests (user_id)
    WHERE status = 'pending';

CREATE INDEX IF NOT EXISTS idx_organizer_requests_status_created
    ON organizer_requests (status, created_at DESC);
