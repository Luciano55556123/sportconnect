# Testes antes da banca — Ponto Competitivo

**Use apenas campeonato e contas de demonstracao.** Nao altere os 10 times, os 20 atletas ou os 22 jogos historicos da Arena Boot. Faça backup do Supabase antes de testes que possam excluir/editar dados.

## 1. Visitante e navegacao

- [ ] Homepage sem erro HTTP e com quantidade de proximos eventos coerente.
- [ ] Busca por `arena`, `ARENA`, cidade e nome do organizador retorna resultados relevantes.
- [ ] Filtros de esporte, modalidade, status, data, valor e categoria funcionam e permanecem selecionados.
- [ ] Botao Limpar filtros volta a listagem completa.
- [ ] Calendario, detalhes, equipes, resultados e regulamento PDF abrem.
- [ ] Site usavel no celular e desktop; sem tabelas ou botoes cortados.

## 2. Atleta

- [ ] Criar uma conta de teste com senha forte, entrar e salvar preferencias.
- [ ] Campos vazios e e-mails duplicados mostram mensagem sem erro 500.
- [ ] Inscricao online aparece somente no historico da conta que a realizou.
- [ ] Evento encerrado ou sem vagas nao aceita inscricao.
- [ ] PIX gera QR e copia-e-cola; numero de WhatsApp aparece quando configurado.
- [ ] Apos pagamento confirmado pelo organizador, nao aparece botao para pagar de novo.
- [ ] Apos rejeicao ou cancelamento, nao aparece convite de pagamento.
- [ ] Favoritos e recomendacoes funcionam.

## 3. Organizador

- [ ] Outro organizador nao consegue abrir `/organizador/campeonatos/{id}/editar`.
- [ ] Campeonatos criados e editados pelo proprio organizador sao salvos.
- [ ] Formularios de imagem e regulamento PDF fazem upload e abrem posteriormente.
- [ ] Inscricoes online e atletas cadastrados manualmente aparecem em secoes separadas.
- [ ] Confirmar PIX de teste so apos conferir comprovante; status e arrecadacao atualizam.
- [ ] Limite de vagas funciona, inclusive ao tentar inscricoes simultaneas.
- [ ] Resultado da semifinal avanca vencedor em campeonato ficticio; ao corrigir, final agendada vazia e atualizada.
- [ ] Tentativa de alterar resultado que mudaria final ja disputada e bloqueada sem corromper os jogos.
- [ ] Salvar set e evento funciona no PostgreSQL; placares invalidos sao recusados.
- [ ] Exportar CSV de inscricoes e partidas abre corretamente.

## 4. Admin e seguranca

- [ ] Aprovar e rejeitar solicitacao de organizador (conta de teste).
- [ ] Consultas de usuarios, organizadores e campeonatos abrem sem botoes falsos.
- [ ] Atleta nao acessa rotas admin ou organizador.
- [ ] Logout por POST funciona; acesso a pagina privada apos logout exige novo login.
- [ ] POST sem CSRF exibe erro de sessao amigavel.
- [ ] Repetir tentativas de login em conta de teste ativa o bloqueio temporario.
- [ ] Erro 404 nao exibe stack trace, caminho do servidor ou senha.

## Evidencias de TCC

Salvar prints da homepage, busca, cadastro, inscricao/historico, perfil, painel do organizador, Arena Boot (somente leitura), painel admin e resultado/classificacao de campeonato ficticio. Registrar data, conta usada e resultado do teste. Nunca exibir senha, credencial Supabase ou informacao pessoal real dos participantes.
