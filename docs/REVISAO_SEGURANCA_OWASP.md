# Revisao de seguranca OWASP do SportConnect

Esta revisao usa OWASP ASVS como referencia de engenharia. Ela nao representa certificacao.

## Implementado

- Validacao e encoding: views usam `e()` para saida HTML; filtros de ordenacao usam whitelist no model.
- Autenticacao: login com `password_verify`, cadastro com `password_hash(PASSWORD_DEFAULT)`, rehash quando necessario e mensagens genericas.
- Sessao: cookies `HttpOnly`, `SameSite=Lax`, modo estrito, expiracao por inatividade e expiracao absoluta.
- CSRF: token validado com `hash_equals` em acoes `POST`.
- Autorizacao: controllers exigem perfil, admin acessa area administrativa e organizador valida ownership de campeonato.
- Upload: uploads revisados validam extensao, MIME com `finfo`, tamanho e nome aleatorio.
- Headers: CSP, `nosniff`, `X-Frame-Options`, `Referrer-Policy`, `Permissions-Policy` e HSTS em HTTPS/producao.
- Logs: falha de login, sucesso de login, CSRF invalido, rate limit, uploads rejeitados e acoes administrativas usam `error_log` sem senha/token.
- Comunicacao: conexao PostgreSQL/Supabase mantem `sslmode=require`.
- Erros: handler global evita stack trace em producao e exibe paginas amigaveis.

## Parcialmente implementado

- Rate limiting: implementado em sessao para login, cadastro, solicitacao de organizador, inscricao, avaliacao e denuncia. Para producao com multiplas instancias, recomenda-se persistir em banco ou cache compartilhado.
- Auditoria: existe log de competicao para acoes ligadas a campeonato; acoes globais ainda dependem de tabela propria de auditoria.
- Uploads privados: solicitacoes de organizador usam `storage`; comprovantes legados ainda dependem de rotas futuras de download autorizado.
- CSP: compativel com CDNs atuais; antes de producao, prefira hospedar Bootstrap, Font Awesome e Chart.js localmente.

## Dependente da hospedagem

- HTTPS real, certificado e redirecionamento definitivo.
- HSTS com dominio definitivo.
- Bloqueio de pastas fora de `public` pelo servidor.
- Configuracao de PHP-FPM/Apache/Nginx.
- Backup automatico do Supabase e dos uploads.
- Monitoramento de logs e alertas.

## Riscos restantes

- Sem recuperacao de senha, pois nao foi criado fluxo inseguro parcial.
- Sem RLS ativado automaticamente; o backend PHP centraliza autorizacao.
- Documentos pessoais exigem politica de retencao e revisao juridica antes de operacao publica.
- Rate limit em sessao pode ser contornado trocando navegador; usar tabela/cache em producao critica.
