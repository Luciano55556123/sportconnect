# Operacao em producao

## Supabase

- Usar PostgreSQL com SSL obrigatorio (`DB_SSLMODE=require`).
- Confirmar o modo do Session Pooler antes do deploy.
- Monitorar conexoes ativas no painel do Supabase.
- Rotacionar senha antes da abertura publica.
- Agendar backup do banco e testar restauracao periodicamente.
- Nao expor `service_role`, string de conexao completa ou `.env`.
- RLS pode fazer sentido futuramente para defesa em profundidade, mas nao foi ativado porque a aplicacao usa conexao PHP no backend.

## Backups

- Exportar banco antes de cada deploy.
- Guardar backup fora de `public`.
- Fazer backup separado de `storage` e uploads privados.
- Testar restauracao em ambiente local ou homologacao.
- Manter historico de migrations aplicadas.

## Dados demo

- Em `APP_ENV=production`, buscas publicas ocultam campeonatos com `is_demo = true`.
- O SQL `database/migrations/remover_dados_demo_opcional.sql` remove apenas registros marcados como demo.
- Nao execute SQL demo em producao.

## Arquivos publicados

Somente `public` deve ser exposto como document root. Pastas como `app`, `config`, `database`, `docs`, `storage`, backups e `.env` nao devem responder diretamente pela web.

## CDN e integridade

O projeto usa Bootstrap, Font Awesome e Chart.js via CDN. Nao foram adicionados hashes SRI inventados. Antes da abertura publica, baixe esses assets, hospede localmente em `public/assets/vendor` e ajuste a CSP para remover dominios externos quando possivel.
