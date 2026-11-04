# Ponto Competitivo

Plataforma web para divulgacao, inscricoes e gerenciamento de campeonatos esportivos regionais. Projeto academico de Engenharia de Software (TCC).

## Tecnologias

- PHP 8.2+, HTML, CSS e JavaScript (arquitetura MVC propria).
- Bootstrap 5 e Font Awesome.
- PostgreSQL hospedado no Supabase; imagens e novos regulamentos no Supabase Storage.
- Hospedagem da aplicacao no Render.

## Perfis e funcionalidades

- **Atleta:** cadastro, login, perfil, busca e filtros, favoritos, inscricoes online, historico, notificacoes e recomendacoes por regras.
- **Organizador:** cadastro e edicao dos proprios campeonatos, analise de inscricoes, confirmacao **manual** de pagamentos PIX, cadastro de equipes e atletas, partidas, resultados, sets, classificacao e exportacao CSV.
- **Administrador:** solicitacoes de perfil organizador e consultas de usuarios, organizadores e campeonatos.
- **Publico:** informacoes dos campeonatos, calendario, equipes, partidas, resultados e regulamentos.

## Demonstracao do TCC

O Campeonato Arena Boot, realizado em Porto Uniao (SC), e utilizado para mostrar a aplicacao pratica **complementar** aos registros manuais dos organizadores. Atletas incluidos diretamente na gestao nao sao necessariamente inscricoes online nem possuem pagamento registrado.

**Preserve os dados desse campeonato.** Para testar cadastro, cancelamentos, pagamentos e resultados, crie um campeonato ficticio e contas exclusivas de teste.

## Instalacao e deploy

Configure as variaveis de ambiente do banco PostgreSQL (DB_DRIVER=pgsql, DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASSWORD e DB_SSLMODE=require) e a URL publica da aplicacao (APP_URL). Para uploads permanentes, configure SUPABASE_URL, SUPABASE_SERVICE_ROLE_KEY e SUPABASE_STORAGE_BUCKET com um bucket publico destinado a imagens e regulamentos.

Nao publique credenciais em codigo, README, prints ou demonstracoes. Mantenha APP_DEBUG=false em producao. Consulte `docs/CHECKLIST_DEPLOY_SEGURO.md` antes do deploy. O esquema inicial MySQL em `database/schema.sql` e historico; o banco do projeto publicado e PostgreSQL e suas migracoes devem ser aplicadas de acordo com o estado real do banco.

## Escopo e limitacoes

- O PIX e gerado para pagamento, mas a conciliacao e **manual pelo organizador**, sem integracao bancaria automatica.
- O sistema de recomendacoes utiliza pontuacao baseada em preferencias; nao e um modelo de IA treinado.
- A classificacao tem regras genericas. Formatos especificos de cada modalidade devem ser configurados e conferidos pelo organizador.
- O limite de vagas busca conciliar inscricoes online e gestao manual; cadastros duplicados sem vinculacao precisa podem exigir conferencia.
- Recuperacao de senha, testes automatizados de ponta a ponta e administracao completa de todos os recursos permanecem como evolucoes futuras.
- A plataforma e um prototipo academico: os textos de privacidade e termos precisam de revisao antes de uma operacao comercial.

## Testes antes da apresentacao

Consulte `docs/TESTES_PRE_BANCA.md` e utilize contas/eventos ficticios. O fluxo pratico deve ser validado no Render apos cada deploy.
