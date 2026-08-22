# Checklist de deploy seguro do Ponto Competitivo

## Antes do deploy

- Dominio real configurado.
- HTTPS valido e testado.
- `APP_ENV=production`.
- `APP_DEBUG=false`.
- `APP_URL=https://dominio-real.com`.
- `HEALTH_TOKEN` longo e aleatorio definido.
- `TRUSTED_PROXIES` preenchido somente se houver proxy reverso confiavel.
- Senha do banco Supabase rotacionada antes de producao.
- `.env` fora do Git e fora de `public`.
- Document root apontando somente para `public`.
- Migrations executadas manualmente no Supabase.
- Dados demo removidos ou ocultos por `APP_ENV=production`.
- Contas de teste removidas ou desativadas.
- Conta admin com senha forte.

## Servidor

- `display_errors=Off`.
- `log_errors=On`.
- `expose_php=Off`.
- `allow_url_include=Off`.
- Cookies de sessao `Secure`, `HttpOnly` e `SameSite=Lax`.
- Headers de seguranca verificados.
- HSTS habilitado apenas depois do HTTPS funcionar.
- Uploads privados fora de `public`.
- Listagem de diretorios desativada.
- Acesso direto a `.env`, `config`, `app`, `database`, `docs`, backups e SQL bloqueado.

## Testes finais

- `/health` responde apenas com token em producao.
- Atleta nao acessa `/admin`.
- Atleta nao acessa `/organizador`.
- Organizador nao altera campeonato de outro organizador.
- POST sem CSRF retorna erro amigavel.
- Upload `.php` e extensao falsa sao rejeitados.
- Login repetido aciona rate limiting.
- Rotas inexistentes mostram 404 profissional.
- Erro interno mostra 500 sem stack trace.
- Logout remove cookie de sessao.
- Busca publica nao mostra dados demo em producao.
- Backup do Supabase e dos uploads foi criado e restauracao foi testada.
