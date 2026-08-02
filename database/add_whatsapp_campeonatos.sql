ALTER TABLE championships
ADD COLUMN IF NOT EXISTS whatsapp_contato VARCHAR(20);

UPDATE championships
SET whatsapp_contato = ''
WHERE whatsapp_contato IS NULL;

ALTER TABLE championships
ALTER COLUMN whatsapp_contato SET DEFAULT '',
ALTER COLUMN whatsapp_contato SET NOT NULL;
