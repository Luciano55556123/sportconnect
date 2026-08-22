# Ponto Competitivo

Sistema web MVC em PHP 8+, MySQL, Bootstrap 5 e JavaScript para divulgacao e gerenciamento de campeonatos esportivos regionais.

## Instalar no XAMPP

1. Copie a pasta para `htdocs`.
2. Abra o phpMyAdmin e importe `database/schema.sql`.
3. Ajuste `config/app.php` se o nome da pasta ou credenciais MySQL forem diferentes.
4. Acesse `http://localhost/TCC%20CAMPEONATO/public`.

## Acessos de demonstracao

- Atleta: `atleta@sportconnect.test` / `123456`
- Organizador: `organizador@sportconnect.test` / `123456`
- Admin: `admin@sportconnect.test` / `123456`

## Recursos implementados

- Arquitetura MVC sem Laravel ou outro framework.
- Login seguro com hash de senha, sessoes, CSRF e PDO.
- Home moderna com pesquisa, categorias, destaques e eventos mais procurados.
- Pesquisa por nome, cidade, esporte, categoria, data, valor, status e modalidade.
- Pagina completa do campeonato com inscricao, favorito, WhatsApp, compartilhar, avaliacoes e comentarios.
- Painel do atleta com perfil, esportes favoritos, notificacoes, favoritos, historico e recomendacoes.
- Sistema inteligente de recomendacao com porcentagem e motivos de compatibilidade.
- Painel do organizador com indicadores, CRUD de campeonatos, gestao de inscricoes e relatorio CSV.
- Painel administrativo com modulos para usuarios, organizadores, esportes, categorias, campeonatos, inscricoes, avaliacoes, comentarios, notificacoes e relatorios.
- Banco normalizado com chaves estrangeiras e integridade referencial.
