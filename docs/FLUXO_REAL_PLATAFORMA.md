# Fluxo real do Ponto Competitivo

Este documento descreve as rotas implementadas, nao etapas futuras planejadas.

## Atleta

1. Faz cadastro em `/cadastro` e login em `/login`.
2. Pesquisa eventos em `/campeonatos` e consulta `/calendario`.
3. Abre detalhes, equipes, partidas e resultados, quando cadastrados.
4. Faz inscricao online no formulario do campeonato (se abertas as vagas) e consulta `/atleta/historico`.
5. Em campeonato pago, gera PIX e envia comprovante por WhatsApp. O organizador confirma manualmente o recebimento.
6. Pode solicitar perfil organizador em `/organizador/solicitar`.

## Organizador

1. Apos aprovacao de perfil, acessa `/organizador`.
2. Cria e edita seus campeonatos, incluindo valores, WhatsApp, PIX e regulamento.
3. Recebe e analisa inscricoes online em `/organizador/inscricoes`; ha secao separada de atletas incluidos manualmente.
4. Pode confirmar ou rejeitar pagamentos PIX de forma manual, depois de conferir o recebimento.
5. Cadastra equipes, atletas, partidas, eventos, sets e resultados; consulta classificacao em `/organizador/campeonatos/{id}/gerenciar`.
6. Exporta relatorios CSV. Alteracoes de resultados em fases eliminatorias nao sobrescrevem uma partida seguinte ja iniciada.

## Administrador

1. Acessa `/admin`.
2. Examina solicitacoes de organizador em `/admin/solicitacoes-organizador`.
3. Consulta usuarios, organizadores e campeonatos por meio de paginas somente de leitura.
4. Modulos administrativos completos de edicao e moderacao sao evolucoes futuras, nao recursos prontos.

## Limites da demonstracao

O campeonato Arena Boot foi acompanhado de maneira complementar aos registros manuais. A maioria dos participantes foi cadastrada pela gestao do organizador; eles nao aparecem automaticamente como inscricoes online de contas distintas.

O PIX nao e liquidado pelo sistema. Recomendacoes usam regras por preferencias; classificacao tem pontuacao generica, que precisa de revisao conforme o regulamento de cada modalidade. Teste novos resultados em um campeonato ficticio, nunca no historico do Arena Boot.
