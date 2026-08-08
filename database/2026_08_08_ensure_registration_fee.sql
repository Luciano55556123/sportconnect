BEGIN;

ALTER TABLE championships
    ADD COLUMN IF NOT EXISTS registration_fee NUMERIC(10,2) NOT NULL DEFAULT 0;

UPDATE championships
SET registration_fee = 0
WHERE registration_fee IS NULL;

COMMIT;
