BEGIN;

ALTER TABLE championships
    ADD COLUMN IF NOT EXISTS pix_receiver_city VARCHAR(60);

COMMIT;
