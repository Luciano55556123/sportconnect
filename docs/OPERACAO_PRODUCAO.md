# Operacao em producao

## Banco de dados

- PostgreSQL do Supabase com SSL (`DB_SSLMODE=require`).
- Guardar DB_PASSWORD e SUPABASE_SERVICE_ROLE_KEY apenas nas variaveis seguras da hospedagem.
- Monitorar conexoes e fazer backup antes de alteracoes importantes.
- **Nao** executar seeds ou migracoes sem conferir previamente as tabelas e dados existentes.

## Arquivos

- Imagens e novos regulamentos sao enviados ao Supabase Storage; o bucket configurado precisa permitir leitura publica dos arquivos publicados.
- PDFs historicos armazenados em `uploads/` podem nao ter sobrevivido a reinicializacoes/deploys do Render. Quando ausentes, reenvie-os pela edicao do campeonato.
- Config, storage, banco, backups, .env e chaves nao devem ser acessiveis publicamente pelo servidor web.

## Seguranca

- HTTPS no dominio publico; APP_DEBUG=false; credenciais fortes para todas as contas, incluindo demonstracao.
- CSRF nos formularios que alteram dados, cookies HttpOnly/SameSite e limitacao **por sessao** em acoes sensiveis. Limite por sessao nao protege contra todos os ataques distribuidos.
- Recuperacao de senha e rate limiting centralizado (por IP/conta em banco ou cache) sao melhorias futuras.
- Logout exige formulario POST; link GET nao deve ser usado.
- Nao usar senha padrao publicada no README.

## Dados da apresentacao

- Proteger o campeonato Arena Boot e nao alterar as partidas ou participantes historicos.
- Criar campeonato de testes separado para validar inscricoes, pagamentos, partidas e remocao de registros.
- A aplicacao nao oculta automaticamente todos os dados de demonstracao; revise e isole os dados manualmente antes da abertura a publico.

## Procedimento de deploy

1. Fazer backup e registrar o commit atual.
2. Publicar o commit escolhido no Render.
3. Conferir status Live e atualizar o navegador (Ctrl+F5).
4. Fazer login com perfis atleta, organizador e admin.
5. Executar `docs/TESTES_PRE_BANCA.md`.
6. Em caso de erro, consultar logs do Render e reverter para o ultimo commit estavel.
