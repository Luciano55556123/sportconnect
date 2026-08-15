BEGIN;

CREATE SEQUENCE IF NOT EXISTS registration_payments_id_seq;

SELECT setval(
    'registration_payments_id_seq',
    GREATEST(
        COALESCE((SELECT MAX(id) FROM registration_payments), 0),
        1
    ),
    true
);

ALTER TABLE registration_payments
    ALTER COLUMN id SET DEFAULT nextval('registration_payments_id_seq');

ALTER SEQUENCE registration_payments_id_seq
    OWNED BY registration_payments.id;

COMMIT;
