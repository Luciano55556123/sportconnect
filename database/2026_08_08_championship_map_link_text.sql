BEGIN;

ALTER TABLE championships
    ALTER COLUMN map_link TYPE TEXT;

COMMIT;
