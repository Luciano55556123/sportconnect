# Revisao tecnica de seguranca do Ponto Competitivo

Esta lista e uma verificacao parcial de desenvolvimento, **nao** uma certificacao OWASP.

## Controles presentes

- Senhas geradas com `password_hash` e verificadas com `password_verify`.
- Consultas parametrizadas em rotas e modelos principais.
- CSRF exigido nas operacoes POST com tratamento de token invalido.
- Logout por POST, regenera o ID ao entrar; cookies de sessao HttpOnly e SameSite=Lax.
- Checagem de papel nas areas de atleta, organizador e admin, e de propriedade no formulario de edicao de campeonato.
- Rate limiting **por sessao** para login, cadastro, inscricao, avaliacoes e solicitacoes de organizador.
- Cabecalhos X-Content-Type-Options, X-Frame-Options, Referrer-Policy e Permissions-Policy.
- Upload de imagens com tipo validado; regulamentos PDF verificados por extensao, MIME, assinatura e tamanho.

## Lacunas conhecidas

- `Security::bootstrap` nao faz parte do ponto de entrada atualmente: mecanismos adicionais nessa classe (expiracao absoluta, CSP e manipuladores globais) nao devem ser apresentados como ativos.
- Limitacao por sessao pode ser contornada limpando cookies ou trocando navegador. Uma aplicacao aberta ao publico deve usar limites centralizados por conta/IP.
- Recuperacao de senha, revisao juridica de privacidade, auditoria global e testes de seguranca de ponta a ponta precisam de implementacao antes de operacao comercial.
- O fluxo PIX nao verifica pagamentos bancarios automaticamente.
- Backups automaticos e endurecimento do servidor devem ser confirmados na hospedagem.

## Verificacoes manuais

Testar acesso indevido a `/organizador/campeonatos/{id}/editar`, GET em `/logout`, POST sem CSRF, tentativas seguidas de login e uso de arquivo falso com extensao PDF. Nunca incluir segredos de producao em anexos, logs publicados ou prints.
