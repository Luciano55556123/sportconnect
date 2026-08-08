BEGIN;

ALTER TABLE championships
    ADD COLUMN IF NOT EXISTS email_contato VARCHAR(160),
    ADD COLUMN IF NOT EXISTS requires_payment BOOLEAN NOT NULL DEFAULT FALSE,
    ADD COLUMN IF NOT EXISTS pix_key VARCHAR(180),
    ADD COLUMN IF NOT EXISTS pix_key_type VARCHAR(30),
    ADD COLUMN IF NOT EXISTS pix_holder_name VARCHAR(160),
    ADD COLUMN IF NOT EXISTS pix_instructions TEXT;

ALTER TABLE registrations
    ALTER COLUMN status TYPE VARCHAR(40) USING status::TEXT,
    ALTER COLUMN status SET DEFAULT 'pendente';

CREATE TABLE IF NOT EXISTS registration_payments (
    id BIGSERIAL PRIMARY KEY,
    registration_id INTEGER NOT NULL UNIQUE REFERENCES registrations(id) ON DELETE CASCADE,
    amount NUMERIC(10,2) NOT NULL DEFAULT 0,
    payment_method VARCHAR(30) NOT NULL DEFAULT 'pix',
    status VARCHAR(40) NOT NULL DEFAULT 'awaiting_receipt',
    pix_key VARCHAR(180),
    receipt_file VARCHAR(255),
    reviewed_at TIMESTAMP NULL,
    reviewed_by INTEGER NULL REFERENCES users(id) ON DELETE SET NULL,
    rejection_reason TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE UNIQUE INDEX IF NOT EXISTS unique_registration_per_athlete
    ON registrations (championship_id, user_id);

CREATE INDEX IF NOT EXISTS idx_registration_payments_status
    ON registration_payments (status);

COMMIT;
