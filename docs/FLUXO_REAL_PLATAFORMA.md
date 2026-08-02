# Fluxo Real da Plataforma SportConnect

## Atleta

1. Cria conta em `/cadastro`.
2. Acessa campeonatos publicados em `/campeonatos`.
3. Visualiza jogos, classificacao, chaveamento, estatisticas e regulamento.
4. Aceita o regulamento antes de solicitar inscricao.
5. Acompanha notificacoes em `/notificacoes`.
6. Caso queira organizar eventos, solicita permissao em `/solicitar-organizador`.

## Organizador

1. Aguarda aprovacao do admin.
2. Apos aprovado, acessa `/organizador`.
3. Cria campeonato em rascunho.
4. Completa checklist: informacoes, regulamento, local, inscricoes, equipes e tabela.
5. Envia campeonato para aprovacao.
6. Depois de publicado, recebe inscricoes e analisa participantes.
7. Gerencia equipes, atletas, jogos, resultados, eventos, sets e classificacao.
8. Usa a central da partida para registrar placar, gols, cartoes e observacoes.

Organizador suspenso nao pode criar, editar ou gerenciar campeonatos.

## Administrador

1. Acessa `/admin`.
2. Analisa organizadores em `/admin/solicitacoes-organizadores`.
3. Aprova, rejeita ou suspende solicitacoes.
4. Analisa campeonatos em `/admin/campeonatos-pendentes`.
5. Publica, rejeita, suspende ou cancela campeonatos.
6. Visualiza recursos administrativos e acompanha moderacao.

## Dados Demo x Dados Reais

Dados demo existem somente para testes e apresentacao do TCC. Eles podem ser identificados por campos `is_demo` nas tabelas principais preparadas pela migracao.

Para uso real:

- nao execute `dados_demonstracao_gestao_campeonato.sql`;
- cadastre organizadores pelo fluxo oficial;
- crie campeonatos pelo painel;
- publique somente apos revisao administrativa.

## Migracoes

Execute manualmente no Supabase SQL Editor. Nunca cole senhas ou credenciais em arquivos SQL.

Ordem recomendada:

1. `adicionar_gestao_completa_campeonatos.sql`
2. `melhorias_gestao_tcc.sql`
3. `profissionalizacao_fluxo_real.sql`

O SQL de demonstracao e opcional.
