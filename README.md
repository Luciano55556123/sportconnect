# SportConnect

SportConnect e uma plataforma MVC em PHP puro para divulgacao e gestao de campeonatos esportivos regionais.

## Tecnologias

- PHP puro, sem Composer
- PDO
- PostgreSQL no Supabase
- HTML, CSS, JavaScript
- Bootstrap 5
- Chart.js
- Sessoes PHP e CSRF

## Banco Supabase

A conexao e lida exclusivamente do `.env`. Nao grave senha ou credenciais no codigo.

Execute as migracoes manualmente no SQL Editor do Supabase:

1. `database/migrations/adicionar_gestao_completa_campeonatos.sql`
2. `database/migrations/melhorias_gestao_tcc.sql`
3. `database/migrations/profissionalizacao_fluxo_real.sql`

Dados de demonstracao:

- `database/migrations/dados_demonstracao_gestao_campeonato.sql`
- `database/migrations/remover_dados_demo_opcional.sql` remove somente dados com `is_demo = true`.

Use dados demo apenas para testes e apresentacao do TCC. Nao execute esse SQL em producao.

## Perfis

- Atleta: visualiza campeonatos, acompanha inscricoes, solicita perfil de organizador.
- Organizador: gerencia apenas campeonatos proprios e somente se aprovado.
- Admin: aprova organizadores, publica campeonatos e controla a plataforma.

## Fluxo Real

1. Usuario cria conta como atleta.
2. Solicita perfil de organizador em `/solicitar-organizador`.
3. Admin analisa em `/admin/solicitacoes-organizadores`.
4. Organizador aprovado cria campeonato em rascunho.
5. Organizador envia para aprovacao.
6. Admin revisa em `/admin/campeonatos-pendentes`.
7. Somente campeonatos publicados aparecem na busca publica.
8. Atletas/equipes solicitam inscricao e aceitam regulamento.
9. Organizador analisa inscricoes, documentos e pagamento manual.
10. Partidas, sumulas, resultados, classificacao e chaveamento sao gerenciados no painel.

## Pagamento

O projeto esta preparado para pagamento manual por PIX, dinheiro, transferencia ou isencao. Nao ha gateway bancario integrado nesta etapa.

## Partidas e Sumula

A gestao de partidas fica em:

- `/organizador/campeonatos/{id}/gerenciar`
- `/organizador/campeonatos/{id}/partidas/{matchId}/gerenciar`

O banco esta preparado para lineups, relatorios de sumula, reagendamentos e auditoria.

## URLs Principais

- `/campeonatos`
- `/campeonatos/{id}`
- `/solicitar-organizador`
- `/notificacoes`
- `/atleta`
- `/organizador`
- `/admin`
- `/admin/solicitacoes-organizadores`
- `/admin/campeonatos-pendentes`
- `/admin/denuncias`
- `/health`

## Iniciar

```powershell
cd C:\xampp\htdocs\sportconnect
php -S 127.0.0.1:8081 -t public
```

## Seguranca

- Prepared statements em PDO
- CSRF nos formularios
- Validacao de sessao e role
- Organizadores so acessam campeonatos proprios
- Admin pode gerenciar toda a plataforma
- Uploads validam extensao, MIME type e tamanho
- Dados sensiveis nao sao gravados em logs
- Headers HTTP de seguranca sao enviados pela aplicacao.
- Em producao, erros tecnicos vao para `error_log` e o usuario recebe pagina amigavel.
- Sessao usa cookies `HttpOnly`, `SameSite=Lax`, modo estrito e expiracao por inatividade.
- Rate limiting simples protege login, cadastro, inscricoes, denuncias e uploads sensiveis.

## Producao

Nao altere o `.env` real no repositorio. Use `.env.example` como referencia:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://dominio-real.com
DB_SSLMODE=require
```

Somente a pasta `public` deve ser configurada como document root. As pastas `app`, `config`, `database`, `docs`, `storage`, backups e o arquivo `.env` nao devem ser expostos pela web.

Arquivos de referencia:

- `deploy/apache-vhost.example.conf`
- `deploy/nginx-site.example.conf`
- `deploy/php-production.example.ini`
- `docs/CHECKLIST_DEPLOY_SEGURO.md`
- `docs/REVISAO_SEGURANCA_OWASP.md`
- `docs/OPERACAO_PRODUCAO.md`

Em `APP_ENV=production`, campeonatos marcados com `is_demo = true` ficam ocultos nas buscas publicas.

## Limitacoes Atuais

- Pagamento e manual.
- PDF de sumula deve ser impresso pelo navegador.
- Geolocalizacao por distancia ainda nao foi implementada.
- Dados demo sao opcionais e devem ficar fora de ambientes reais.
- Rate limit em sessao deve ser substituido por banco ou cache compartilhado em hospedagens com multiplas instancias.
- Revisao juridica de termos e politica de privacidade ainda e necessaria antes de uso publico real.
