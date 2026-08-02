# Backup e importacao MySQL para Supabase PostgreSQL

## 1. Backup completo do MySQL

Nao apague o banco MySQL. Antes de importar no Supabase, gere um dump completo:

```powershell
cd C:\xampp\mysql\bin
.\mysqldump.exe --user=root --password --single-transaction --routines --triggers --events --databases sportconnect > C:\xampp\htdocs\sportconnect\database\backups\sportconnect_mysql_backup.sql
```

Se o usuario `root` nao tiver senha, remova `--password`.

## 2. Criar estrutura no Supabase

Abra o SQL Editor do Supabase e execute o conteudo de:

```text
C:\xampp\htdocs\sportconnect\database\migrations\mysql_para_postgresql.sql
```

## 3. Exportar e importar dados atuais

Use o MySQL como origem ate validar a migracao. Exporte os dados das tabelas existentes e importe no Supabase nesta ordem:

```text
users
sports
user_sports
championships
registrations
favorites
notifications
reviews
```

## 4. Ajustar sequences depois da importacao

Depois de importar registros com IDs existentes, execute no Supabase:

```sql
SELECT setval(pg_get_serial_sequence('users', 'id'), COALESCE((SELECT MAX(id) FROM users), 1), true);
SELECT setval(pg_get_serial_sequence('sports', 'id'), COALESCE((SELECT MAX(id) FROM sports), 1), true);
SELECT setval(pg_get_serial_sequence('championships', 'id'), COALESCE((SELECT MAX(id) FROM championships), 1), true);
SELECT setval(pg_get_serial_sequence('registrations', 'id'), COALESCE((SELECT MAX(id) FROM registrations), 1), true);
SELECT setval(pg_get_serial_sequence('favorites', 'id'), COALESCE((SELECT MAX(id) FROM favorites), 1), true);
SELECT setval(pg_get_serial_sequence('notifications', 'id'), COALESCE((SELECT MAX(id) FROM notifications), 1), true);
SELECT setval(pg_get_serial_sequence('reviews', 'id'), COALESCE((SELECT MAX(id) FROM reviews), 1), true);
```

## 5. Configurar a aplicacao

Crie `.env` na raiz do projeto a partir de `.env.example` e preencha `DB_PASSWORD` com a senha do Session Pooler do Supabase. Nao versionar `.env`.
