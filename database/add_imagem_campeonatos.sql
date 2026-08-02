ALTER TABLE championships
ADD COLUMN IF NOT EXISTS imagem VARCHAR(255);

UPDATE championships
SET imagem = image
WHERE (imagem IS NULL OR imagem = '')
  AND image IS NOT NULL
  AND image <> ''
  AND image <> 'assets/img/default-event.svg';
