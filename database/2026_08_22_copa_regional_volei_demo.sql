BEGIN;

INSERT INTO sports (name)
VALUES ('Voleibol')
ON CONFLICT (name) DO NOTHING;

INSERT INTO users (name, email, password, role, phone, city, is_demo)
VALUES (
    'Marcelo Fernandes',
    'marcelo.organizador@pontocompetitivo.test',
    '$2y$10$behjY865AARkSvWpA4plh.5alTGIT0.vcDwmLhH9IWiaqZjV4xjB.',
    'organizer',
    '42988881234',
    'Uniao da Vitoria',
    TRUE
)
ON CONFLICT (email) DO UPDATE
SET name = EXCLUDED.name,
    role = EXCLUDED.role,
    phone = EXCLUDED.phone,
    city = EXCLUDED.city,
    is_demo = TRUE;

DELETE FROM championships
WHERE name = 'Copa Regional de Volei 2026'
AND organizer_id = (
    SELECT id
    FROM users
    WHERE email = 'marcelo.organizador@pontocompetitivo.test'
);

INSERT INTO championships (
    organizer_id, sport_id, name, city, location, map_link, event_date, event_time,
    registration_fee, prize, max_participants, description, category, modality, status,
    image, views, whatsapp_contato, email_contato, requires_payment, pix_key,
    pix_key_type, pix_holder_name, pix_receiver_city, pix_instructions,
    competition_format, end_date, registration_deadline, registrations_open,
    address, neighborhood, state, zip_code, reference_point, court_or_field,
    rules, tiebreak_rules, qualification_rules, elimination_rules,
    required_documents, cancellation_policy, editorial_status, publication_requested_at,
    published_at, reviewed_by, reviewed_at, registration_type, maximum_registrations,
    minimum_team_members, maximum_team_members, requires_documents, payment_instructions,
    is_demo
)
VALUES (
    (SELECT id FROM users WHERE email = 'marcelo.organizador@pontocompetitivo.test'),
    (SELECT id FROM sports WHERE name = 'Voleibol'),
    'Copa Regional de Volei 2026',
    'Uniao da Vitoria',
    'Ginasio Municipal de Uniao da Vitoria - PR',
    'https://maps.google.com/?q=Ginasio+Municipal+Uniao+da+Vitoria',
    '2026-08-29',
    '14:00',
    250.00,
    'Trofeus e medalhas para 1o, 2o e 3o lugares',
    8,
    'A Copa Regional de Volei 2026 reune equipes da regiao em uma competicao de voleibol masculino adulto. Periodo: 15/08/2026 a 20/09/2026. Formato: volei de quadra 6x6, fase classificatoria, semifinais, disputa de terceiro lugar e final, com acompanhamento de resultados, classificacao, sets, pontuacao e estatisticas dos atletas. Organizacao: Associacao Regional de Esportes.',
    'Masculino Adulto - Volei de quadra 6x6',
    'masculino',
    'ativo',
    'assets/img/default-event.svg',
    426,
    '42988881234',
    'marcelo.organizador@pontocompetitivo.test',
    TRUE,
    'financeiro@pontocompetitivo.test',
    'email',
    'Associacao Regional de Esportes',
    'UNIAO DA VITORIA',
    'Taxa de inscricao de R$ 250,00 por equipe. Dados ficticios para demonstracao do TCC.',
    'todos_contra_todos_mata_mata',
    '2026-09-20',
    '2026-08-14',
    TRUE,
    'Rua Marechal Deodoro, 1000',
    'Centro',
    'PR',
    '84600-000',
    'Entrada principal do Ginasio Municipal',
    'Quadra principal',
    'Partidas em melhor de 5 sets. Sets 1 a 4 com 25 pontos e diferenca minima de 2. Tie-break com 15 pontos e diferenca minima de 2.',
    'Criterios: vitorias, pontos, razao de sets, razao de pontos e confronto direto.',
    'Os quatro melhores colocados avancam para as semifinais.',
    'Semifinal 1: 1o x 4o. Semifinal 2: 2o x 3o. Vencedores fazem a final; perdedores disputam o 3o lugar.',
    'Documento do responsavel e relacao nominal dos atletas.',
    'Dados ficticios para apresentacao academica.',
    'published',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP,
    (SELECT id FROM users WHERE email = 'marcelo.organizador@pontocompetitivo.test'),
    CURRENT_TIMESTAMP,
    'team',
    8,
    10,
    14,
    FALSE,
    'Pagamento ficticio via PIX para demonstracao.',
    TRUE
);

WITH c AS (
    SELECT id FROM championships WHERE name = 'Copa Regional de Volei 2026'
)
INSERT INTO teams (championship_id, name, city, responsible_name, responsible_phone, status, is_demo)
SELECT c.id, t.name, t.city, t.coach, t.phone, 'aprovado', TRUE
FROM c
CROSS JOIN (VALUES
    ('Uniao Volei Clube', 'Uniao da Vitoria', 'Marcelo Fernandes', '42988881234'),
    ('Porto Volei', 'Porto Uniao', 'Rodrigo Camargo', '42988882345'),
    ('Iguacu Volei', 'Uniao da Vitoria', 'Eduardo Pires', '42988883456'),
    ('Canoinhas Volei', 'Canoinhas', 'Sandro Nunes', '47988884567'),
    ('Bituruna Esporte Clube', 'Bituruna', 'Claudio Ferraz', '42988885678'),
    ('Sao Mateus Volei', 'Sao Mateus do Sul', 'Paulo Rezende', '42988886789'),
    ('General Carneiro Volei', 'General Carneiro', 'Marcos Antunes', '42988887890'),
    ('Irati Volei Clube', 'Irati', 'Vinicius Prado', '42988888901')
) AS t(name, city, coach, phone);

INSERT INTO users (name, email, password, role, phone, city, is_demo)
SELECT t.coach, t.email, '$2y$10$behjY865AARkSvWpA4plh.5alTGIT0.vcDwmLhH9IWiaqZjV4xjB.', 'athlete', t.phone, t.city, TRUE
FROM (VALUES
    ('Marcelo Fernandes', 'contato.uniao.volei@pontocompetitivo.test', '42988881234', 'Uniao da Vitoria'),
    ('Rodrigo Camargo', 'contato.porto.volei@pontocompetitivo.test', '42988882345', 'Porto Uniao'),
    ('Eduardo Pires', 'contato.iguacu.volei@pontocompetitivo.test', '42988883456', 'Uniao da Vitoria'),
    ('Sandro Nunes', 'contato.canoinhas.volei@pontocompetitivo.test', '47988884567', 'Canoinhas'),
    ('Claudio Ferraz', 'contato.bituruna.volei@pontocompetitivo.test', '42988885678', 'Bituruna'),
    ('Paulo Rezende', 'contato.sao.mateus.volei@pontocompetitivo.test', '42988886789', 'Sao Mateus do Sul'),
    ('Marcos Antunes', 'contato.general.carneiro.volei@pontocompetitivo.test', '42988887890', 'General Carneiro'),
    ('Vinicius Prado', 'contato.irati.volei@pontocompetitivo.test', '42988888901', 'Irati')
) AS t(coach, email, phone, city)
ON CONFLICT (email) DO UPDATE
SET name = EXCLUDED.name,
    phone = EXCLUDED.phone,
    city = EXCLUDED.city,
    is_demo = TRUE;

WITH c AS (
    SELECT id FROM championships WHERE name = 'Copa Regional de Volei 2026'
)
INSERT INTO registrations (
    championship_id, user_id, name, phone, email, team, category, city,
    status, registration_type, team_id, accepted_terms, terms_version,
    accepted_terms_at, is_demo
)
SELECT c.id, u.id, tm.name || ' - inscricao', u.phone, u.email, tm.name,
       'Masculino Adulto', tm.city, 'aprovado', 'team', tm.id,
       TRUE, 'demo-2026', CURRENT_TIMESTAMP, TRUE
FROM c
JOIN teams tm ON tm.championship_id = c.id
JOIN (VALUES
    ('Uniao Volei Clube', 'contato.uniao.volei@pontocompetitivo.test'),
    ('Porto Volei', 'contato.porto.volei@pontocompetitivo.test'),
    ('Iguacu Volei', 'contato.iguacu.volei@pontocompetitivo.test'),
    ('Canoinhas Volei', 'contato.canoinhas.volei@pontocompetitivo.test'),
    ('Bituruna Esporte Clube', 'contato.bituruna.volei@pontocompetitivo.test'),
    ('Sao Mateus Volei', 'contato.sao.mateus.volei@pontocompetitivo.test'),
    ('General Carneiro Volei', 'contato.general.carneiro.volei@pontocompetitivo.test'),
    ('Irati Volei Clube', 'contato.irati.volei@pontocompetitivo.test')
) AS r(team_name, email) ON r.team_name = tm.name
JOIN users u ON u.email = r.email;

WITH c AS (
    SELECT id FROM championships WHERE name = 'Copa Regional de Volei 2026'
)
INSERT INTO athletes (
    championship_id, team_id, name, city, shirt_number, position,
    category, status, is_demo
)
SELECT c.id, tm.id, p.name, tm.city, p.shirt_number, p.position,
       'Masculino Adulto', 'aprovado', TRUE
FROM c
JOIN teams tm ON tm.championship_id = c.id
JOIN (VALUES
    ('Uniao Volei Clube', 1, 'Lucas Martins', 'Levantador'), ('Uniao Volei Clube', 2, 'Gabriel Souza', 'Ponteiro'), ('Uniao Volei Clube', 4, 'Rafael Lima', 'Central'), ('Uniao Volei Clube', 5, 'Matheus Oliveira', 'Oposto'), ('Uniao Volei Clube', 7, 'Bruno Ferreira', 'Ponteiro'), ('Uniao Volei Clube', 8, 'Henrique Costa', 'Central'), ('Uniao Volei Clube', 10, 'Leonardo Alves', 'Libero'), ('Uniao Volei Clube', 11, 'Pedro Henrique', 'Levantador'), ('Uniao Volei Clube', 12, 'Gustavo Rocha', 'Ponteiro'), ('Uniao Volei Clube', 14, 'Felipe Santos', 'Central'), ('Uniao Volei Clube', 17, 'Joao Vitor', 'Oposto'), ('Uniao Volei Clube', 20, 'Andre Ribeiro', 'Libero'),
    ('Porto Volei', 1, 'Lucas Almeida', 'Ponteiro'), ('Porto Volei', 3, 'Caio Mendes', 'Levantador'), ('Porto Volei', 5, 'Thiago Ramos', 'Central'), ('Porto Volei', 6, 'Murilo Azevedo', 'Oposto'), ('Porto Volei', 8, 'Samuel Costa', 'Ponteiro'), ('Porto Volei', 9, 'Vitor Hugo', 'Central'), ('Porto Volei', 12, 'Igor Batista', 'Libero'), ('Porto Volei', 14, 'Daniel Freitas', 'Ponteiro'), ('Porto Volei', 17, 'Renan Lopes', 'Oposto'), ('Porto Volei', 19, 'Arthur Neves', 'Central'), ('Porto Volei', 21, 'Felipe Duarte', 'Levantador'), ('Porto Volei', 22, 'Brayan Teixeira', 'Libero'),
    ('Iguacu Volei', 2, 'Diego Martins', 'Central'), ('Iguacu Volei', 4, 'Marcos Vieira', 'Levantador'), ('Iguacu Volei', 5, 'Anderson Melo', 'Ponteiro'), ('Iguacu Volei', 7, 'Leandro Batista', 'Oposto'), ('Iguacu Volei', 9, 'Cesar Farias', 'Ponteiro'), ('Iguacu Volei', 10, 'Willian Moreira', 'Libero'), ('Iguacu Volei', 11, 'Joao Pedro Luz', 'Central'), ('Iguacu Volei', 13, 'Nathan Correa', 'Ponteiro'), ('Iguacu Volei', 15, 'Davi Silveira', 'Levantador'), ('Iguacu Volei', 18, 'Otavio Martins', 'Central'), ('Iguacu Volei', 20, 'Raul Fernandes', 'Oposto'), ('Iguacu Volei', 23, 'Mateus Borges', 'Libero'),
    ('Canoinhas Volei', 1, 'Gustavo Alves', 'Ponteiro'), ('Canoinhas Volei', 2, 'Andre Luiz', 'Levantador'), ('Canoinhas Volei', 4, 'Bruno Antunes', 'Central'), ('Canoinhas Volei', 6, 'Rian Souza', 'Oposto'), ('Canoinhas Volei', 7, 'Eduardo Lima', 'Ponteiro'), ('Canoinhas Volei', 8, 'Henrique Lopes', 'Central'), ('Canoinhas Volei', 10, 'Caio Rocha', 'Libero'), ('Canoinhas Volei', 12, 'Matheus Kuster', 'Ponteiro'), ('Canoinhas Volei', 14, 'Pedro Reis', 'Central'), ('Canoinhas Volei', 16, 'Luis Felipe', 'Levantador'), ('Canoinhas Volei', 18, 'Rafael Souza', 'Oposto'), ('Canoinhas Volei', 19, 'Vinicius Mayer', 'Libero'),
    ('Bituruna Esporte Clube', 1, 'Alan Ferreira', 'Levantador'), ('Bituruna Esporte Clube', 3, 'Cassio Ribeiro', 'Ponteiro'), ('Bituruna Esporte Clube', 5, 'Fernando Dias', 'Central'), ('Bituruna Esporte Clube', 6, 'Mario Henrique', 'Oposto'), ('Bituruna Esporte Clube', 8, 'Otavio Costa', 'Ponteiro'), ('Bituruna Esporte Clube', 9, 'Jean Moraes', 'Central'), ('Bituruna Esporte Clube', 11, 'Kevin Matos', 'Libero'), ('Bituruna Esporte Clube', 13, 'Luis Gustavo', 'Ponteiro'), ('Bituruna Esporte Clube', 15, 'Paulo Sergio', 'Central'), ('Bituruna Esporte Clube', 17, 'Robson Almeida', 'Levantador'), ('Bituruna Esporte Clube', 18, 'Tiago Vieira', 'Oposto'), ('Bituruna Esporte Clube', 20, 'Wesley Ramos', 'Libero'),
    ('Sao Mateus Volei', 1, 'Alex Moraes', 'Levantador'), ('Sao Mateus Volei', 2, 'Bernardo Salles', 'Ponteiro'), ('Sao Mateus Volei', 4, 'Carlos Eduardo', 'Central'), ('Sao Mateus Volei', 6, 'Douglas Pereira', 'Oposto'), ('Sao Mateus Volei', 7, 'Elias Cardoso', 'Ponteiro'), ('Sao Mateus Volei', 9, 'Fabio Henrique', 'Central'), ('Sao Mateus Volei', 10, 'Guilherme Pinto', 'Libero'), ('Sao Mateus Volei', 12, 'Heitor Cunha', 'Ponteiro'), ('Sao Mateus Volei', 14, 'Ivan Santos', 'Central'), ('Sao Mateus Volei', 16, 'Jorge Padilha', 'Levantador'), ('Sao Mateus Volei', 18, 'Kauan Ribeiro', 'Oposto'), ('Sao Mateus Volei', 21, 'Levi Machado', 'Libero'),
    ('General Carneiro Volei', 1, 'Adriano Neri', 'Levantador'), ('General Carneiro Volei', 3, 'Breno Campos', 'Ponteiro'), ('General Carneiro Volei', 4, 'Cristian Duarte', 'Central'), ('General Carneiro Volei', 6, 'Danilo Alves', 'Oposto'), ('General Carneiro Volei', 8, 'Emerson Castro', 'Ponteiro'), ('General Carneiro Volei', 9, 'Fabricio Lopes', 'Central'), ('General Carneiro Volei', 10, 'Gilberto Prado', 'Libero'), ('General Carneiro Volei', 12, 'Hugo Martins', 'Ponteiro'), ('General Carneiro Volei', 14, 'Italo Farias', 'Central'), ('General Carneiro Volei', 15, 'Jonas Ribeiro', 'Levantador'), ('General Carneiro Volei', 17, 'Kelvin Moreira', 'Oposto'), ('General Carneiro Volei', 20, 'Luiz Otavio', 'Libero'),
    ('Irati Volei Clube', 1, 'Antonio Barros', 'Levantador'), ('Irati Volei Clube', 2, 'Bruno Carvalho', 'Ponteiro'), ('Irati Volei Clube', 4, 'Claudio Matias', 'Central'), ('Irati Volei Clube', 5, 'Dener Martins', 'Oposto'), ('Irati Volei Clube', 7, 'Evandro Lima', 'Ponteiro'), ('Irati Volei Clube', 8, 'Flavio Ribeiro', 'Central'), ('Irati Volei Clube', 10, 'Gerson Lopes', 'Libero'), ('Irati Volei Clube', 12, 'Helio Duarte', 'Ponteiro'), ('Irati Volei Clube', 14, 'Iuri Machado', 'Central'), ('Irati Volei Clube', 16, 'Juliano Freitas', 'Levantador'), ('Irati Volei Clube', 18, 'Kleber Costa', 'Oposto'), ('Irati Volei Clube', 21, 'Leandro Nunes', 'Libero')
) AS p(team_name, shirt_number, name, position) ON p.team_name = tm.name;

WITH c AS (
    SELECT id FROM championships WHERE name = 'Copa Regional de Volei 2026'
),
match_rows AS (
    SELECT * FROM (VALUES
    (1,'Classificatoria','2026-08-15','14:00','Uniao Volei Clube','General Carneiro Volei',3,0,'finalizada','Vitoria consistente do Uniao na estreia.'),
    (1,'Classificatoria','2026-08-15','16:00','Porto Volei','Sao Mateus Volei',3,1,'finalizada','Porto aproveitou o saque forte no quarto set.'),
    (1,'Classificatoria','2026-08-15','18:00','Iguacu Volei','Bituruna Esporte Clube',3,0,'finalizada','Iguacu controlou os contra-ataques.'),
    (1,'Classificatoria','2026-08-15','20:00','Canoinhas Volei','Irati Volei Clube',3,0,'finalizada','Canoinhas iniciou com bom volume defensivo.'),
    (2,'Classificatoria','2026-08-16','14:00','Uniao Volei Clube','Bituruna Esporte Clube',3,0,'finalizada','Uniao manteve 100% de aproveitamento.'),
    (2,'Classificatoria','2026-08-16','16:00','Porto Volei','Irati Volei Clube',3,1,'finalizada','Porto virou o segundo set e fechou em quatro parciais.'),
    (2,'Classificatoria','2026-08-16','18:00','Iguacu Volei','Sao Mateus Volei',3,1,'finalizada','Iguacu cresceu no bloqueio.'),
    (2,'Classificatoria','2026-08-16','20:00','Canoinhas Volei','General Carneiro Volei',3,0,'finalizada','Canoinhas pontuou bem pelas pontas.'),
    (3,'Classificatoria','2026-08-22','14:00','Uniao Volei Clube','Sao Mateus Volei',3,1,'finalizada','Uniao perdeu o segundo set, mas retomou o ritmo.'),
    (3,'Classificatoria','2026-08-22','16:00','Porto Volei','Iguacu Volei',3,1,'finalizada','Confronto direto decidido no saque do Porto.'),
    (3,'Classificatoria','2026-08-22','18:00','Canoinhas Volei','Bituruna Esporte Clube',1,3,'finalizada','Bituruna conquistou sua primeira vitoria.'),
    (3,'Classificatoria','2026-08-22','20:00','Irati Volei Clube','General Carneiro Volei',3,0,'finalizada','Irati venceu com regularidade no passe.'),
    (4,'Classificatoria','2026-08-23','14:00','Uniao Volei Clube','Canoinhas Volei',3,1,'finalizada','Rodada 4: resultado obrigatorio da demonstracao.'),
    (4,'Classificatoria','2026-08-23','16:00','Porto Volei','Bituruna Esporte Clube',3,0,'finalizada','Rodada 4: Porto venceu em sets diretos.'),
    (4,'Classificatoria','2026-08-23','18:00','Iguacu Volei','Irati Volei Clube',3,2,'finalizada','Rodada 4: Iguacu venceu no tie-break.'),
    (4,'Classificatoria','2026-08-23','20:00','Sao Mateus Volei','General Carneiro Volei',3,0,'finalizada','Rodada 4: Sao Mateus reagiu na tabela.'),
    (5,'Classificatoria','2026-08-29','14:00','Uniao Volei Clube','Porto Volei',2,1,'em_andamento','Partida principal da apresentacao: 1o colocado x 2o colocado. Placar parcial demonstrativo: Uniao 2 x 1 Porto.'),
    (5,'Classificatoria','2026-08-29','16:00','Iguacu Volei','Canoinhas Volei',NULL,NULL,'agendada','Confronto direto pelo G4.'),
    (5,'Classificatoria','2026-08-29','18:00','Irati Volei Clube','Sao Mateus Volei',NULL,NULL,'agendada','Irati busca consolidar recuperacao.'),
    (5,'Classificatoria','2026-08-29','20:00','Bituruna Esporte Clube','General Carneiro Volei',NULL,NULL,'agendada','Jogo importante contra a parte baixa da tabela.'),
    (6,'Classificatoria','2026-09-05','14:00','Uniao Volei Clube','Irati Volei Clube',NULL,NULL,'agendada','Sexta rodada da fase classificatoria.'),
    (6,'Classificatoria','2026-09-05','16:00','Porto Volei','Canoinhas Volei',NULL,NULL,'agendada','Sexta rodada da fase classificatoria.'),
    (6,'Classificatoria','2026-09-05','18:00','Iguacu Volei','General Carneiro Volei',NULL,NULL,'agendada','Sexta rodada da fase classificatoria.'),
    (6,'Classificatoria','2026-09-05','20:00','Bituruna Esporte Clube','Sao Mateus Volei',NULL,NULL,'agendada','Sexta rodada da fase classificatoria.'),
    (7,'Classificatoria','2026-09-12','14:00','Uniao Volei Clube','Iguacu Volei',NULL,NULL,'agendada','Ultima rodada da fase classificatoria.'),
    (7,'Classificatoria','2026-09-12','16:00','Porto Volei','General Carneiro Volei',NULL,NULL,'agendada','Ultima rodada da fase classificatoria.'),
    (7,'Classificatoria','2026-09-12','18:00','Canoinhas Volei','Sao Mateus Volei',NULL,NULL,'agendada','Ultima rodada da fase classificatoria.'),
    (7,'Classificatoria','2026-09-12','20:00','Bituruna Esporte Clube','Irati Volei Clube',NULL,NULL,'agendada','Ultima rodada da fase classificatoria.'),
    (8,'Semifinal','2026-09-19','15:00',NULL,NULL,NULL,NULL,'agendada','Semifinal 1: 1o colocado x 4o colocado.'),
    (8,'Semifinal','2026-09-19','17:00',NULL,NULL,NULL,NULL,'agendada','Semifinal 2: 2o colocado x 3o colocado.'),
    (9,'Terceiro lugar','2026-09-20','15:00',NULL,NULL,NULL,NULL,'agendada','Perdedor SF1 x Perdedor SF2.'),
    (9,'Final','2026-09-20','17:00',NULL,NULL,NULL,NULL,'agendada','Vencedor SF1 x Vencedor SF2.')
    ) AS x(round_number, phase, match_date, match_time, home_team, away_team, home_score, away_score, status, notes)
)
INSERT INTO matches (
    championship_id, phase, group_name, round_number, home_team_id, away_team_id,
    match_date, match_time, venue, court_or_field, referee, home_score, away_score,
    status, winner_team_id, notes, is_demo
)
SELECT c.id, m.phase, 'Unico', m.round_number, ht.id, at.id,
       m.match_date::date, m.match_time::time,
       'Ginasio Municipal de Uniao da Vitoria - PR',
       CASE WHEN m.round_number % 2 = 0 THEN 'Quadra 2' ELSE 'Quadra 1' END,
       CASE WHEN m.phase = 'Classificatoria' THEN 'Arbitragem Regional' ELSE 'A definir' END,
       m.home_score, m.away_score,
       m.status,
       CASE WHEN m.home_score > m.away_score THEN ht.id WHEN m.away_score > m.home_score THEN at.id ELSE NULL END,
       m.notes,
       TRUE
FROM c
JOIN match_rows m ON TRUE
LEFT JOIN teams ht ON ht.championship_id = c.id AND ht.name = m.home_team
LEFT JOIN teams at ON at.championship_id = c.id AND at.name = m.away_team;

WITH c AS (
    SELECT id FROM championships WHERE name = 'Copa Regional de Volei 2026'
),
set_rows AS (
    SELECT * FROM (VALUES
    (1,'Uniao Volei Clube','General Carneiro Volei',1,25,16),(1,'Uniao Volei Clube','General Carneiro Volei',2,25,18),(1,'Uniao Volei Clube','General Carneiro Volei',3,25,20),
    (1,'Porto Volei','Sao Mateus Volei',1,25,21),(1,'Porto Volei','Sao Mateus Volei',2,22,25),(1,'Porto Volei','Sao Mateus Volei',3,25,18),(1,'Porto Volei','Sao Mateus Volei',4,25,19),
    (1,'Iguacu Volei','Bituruna Esporte Clube',1,25,19),(1,'Iguacu Volei','Bituruna Esporte Clube',2,25,17),(1,'Iguacu Volei','Bituruna Esporte Clube',3,25,21),
    (1,'Canoinhas Volei','Irati Volei Clube',1,25,20),(1,'Canoinhas Volei','Irati Volei Clube',2,25,22),(1,'Canoinhas Volei','Irati Volei Clube',3,25,18),
    (2,'Uniao Volei Clube','Bituruna Esporte Clube',1,25,18),(2,'Uniao Volei Clube','Bituruna Esporte Clube',2,25,15),(2,'Uniao Volei Clube','Bituruna Esporte Clube',3,25,19),
    (2,'Porto Volei','Irati Volei Clube',1,25,20),(2,'Porto Volei','Irati Volei Clube',2,23,25),(2,'Porto Volei','Irati Volei Clube',3,25,18),(2,'Porto Volei','Irati Volei Clube',4,25,21),
    (2,'Iguacu Volei','Sao Mateus Volei',1,25,19),(2,'Iguacu Volei','Sao Mateus Volei',2,25,21),(2,'Iguacu Volei','Sao Mateus Volei',3,22,25),(2,'Iguacu Volei','Sao Mateus Volei',4,25,18),
    (2,'Canoinhas Volei','General Carneiro Volei',1,25,17),(2,'Canoinhas Volei','General Carneiro Volei',2,25,16),(2,'Canoinhas Volei','General Carneiro Volei',3,25,19),
    (3,'Uniao Volei Clube','Sao Mateus Volei',1,25,18),(3,'Uniao Volei Clube','Sao Mateus Volei',2,23,25),(3,'Uniao Volei Clube','Sao Mateus Volei',3,25,20),(3,'Uniao Volei Clube','Sao Mateus Volei',4,25,17),
    (3,'Porto Volei','Iguacu Volei',1,25,21),(3,'Porto Volei','Iguacu Volei',2,25,23),(3,'Porto Volei','Iguacu Volei',3,20,25),(3,'Porto Volei','Iguacu Volei',4,25,22),
    (3,'Canoinhas Volei','Bituruna Esporte Clube',1,21,25),(3,'Canoinhas Volei','Bituruna Esporte Clube',2,25,23),(3,'Canoinhas Volei','Bituruna Esporte Clube',3,19,25),(3,'Canoinhas Volei','Bituruna Esporte Clube',4,22,25),
    (3,'Irati Volei Clube','General Carneiro Volei',1,25,18),(3,'Irati Volei Clube','General Carneiro Volei',2,25,20),(3,'Irati Volei Clube','General Carneiro Volei',3,25,21),
    (4,'Uniao Volei Clube','Canoinhas Volei',1,25,19),(4,'Uniao Volei Clube','Canoinhas Volei',2,23,25),(4,'Uniao Volei Clube','Canoinhas Volei',3,25,20),(4,'Uniao Volei Clube','Canoinhas Volei',4,25,18),
    (4,'Porto Volei','Bituruna Esporte Clube',1,25,17),(4,'Porto Volei','Bituruna Esporte Clube',2,25,21),(4,'Porto Volei','Bituruna Esporte Clube',3,25,16),
    (4,'Iguacu Volei','Irati Volei Clube',1,22,25),(4,'Iguacu Volei','Irati Volei Clube',2,25,20),(4,'Iguacu Volei','Irati Volei Clube',3,21,25),(4,'Iguacu Volei','Irati Volei Clube',4,25,19),(4,'Iguacu Volei','Irati Volei Clube',5,15,11),
    (4,'Sao Mateus Volei','General Carneiro Volei',1,25,18),(4,'Sao Mateus Volei','General Carneiro Volei',2,25,16),(4,'Sao Mateus Volei','General Carneiro Volei',3,25,22),
    (5,'Uniao Volei Clube','Porto Volei',1,25,21),(5,'Uniao Volei Clube','Porto Volei',2,22,25),(5,'Uniao Volei Clube','Porto Volei',3,25,19),(5,'Uniao Volei Clube','Porto Volei',4,0,0),(5,'Uniao Volei Clube','Porto Volei',5,0,0)
    ) AS x(round_number, home_team, away_team, set_number, home_score, away_score)
)
INSERT INTO match_sets (match_id, set_number, home_score, away_score, winner_team_id)
SELECT m.id, s.set_number, s.home_score, s.away_score,
       CASE WHEN s.home_score > s.away_score THEN m.home_team_id WHEN s.away_score > s.home_score THEN m.away_team_id ELSE NULL END
FROM c
JOIN set_rows s ON TRUE
JOIN teams ht ON ht.championship_id = c.id AND ht.name = s.home_team
JOIN teams at ON at.championship_id = c.id AND at.name = s.away_team
JOIN matches m ON m.championship_id = c.id
    AND m.round_number = s.round_number
    AND m.home_team_id = ht.id
    AND m.away_team_id = at.id;

WITH c AS (
    SELECT id FROM championships WHERE name = 'Copa Regional de Volei 2026'
)
INSERT INTO standings (
    championship_id, team_id, group_name, played, wins, draws, losses,
    score_for, score_against, score_difference, points
)
SELECT c.id, tm.id, 'Unico', s.played, s.wins, 0, s.losses,
       s.sets_for, s.sets_against, s.sets_for - s.sets_against, s.points
FROM c
JOIN (VALUES
    ('Uniao Volei Clube',4,4,0,12,2,12),
    ('Porto Volei',4,3,1,10,5,9),
    ('Iguacu Volei',4,3,1,10,6,8),
    ('Canoinhas Volei',4,2,2,8,7,6),
    ('Irati Volei Clube',4,2,2,7,8,5),
    ('Sao Mateus Volei',4,1,3,5,10,3),
    ('Bituruna Esporte Clube',4,1,3,4,10,3),
    ('General Carneiro Volei',4,0,4,2,12,0)
) AS s(team_name, played, wins, losses, sets_for, sets_against, points) ON TRUE
JOIN teams tm ON tm.championship_id = c.id AND tm.name = s.team_name;

WITH c AS (
    SELECT id FROM championships WHERE name = 'Copa Regional de Volei 2026'
)
INSERT INTO athlete_statistics (
    championship_id, team_id, athlete_id, matches_played, goals, assists,
    yellow_cards, red_cards, points, aces, blocks, wins
)
SELECT c.id, tm.id, a.id, 4, st.total_points, 0, 0, 0,
       st.attacks, st.aces, st.blocks, st.blocks
FROM c
JOIN (VALUES
    ('Uniao Volei Clube','Gabriel Souza',68,53,6,9),
    ('Porto Volei','Lucas Almeida',61,48,7,6),
    ('Iguacu Volei','Diego Martins',58,43,9,6),
    ('Uniao Volei Clube','Rafael Lima',51,35,13,3),
    ('Canoinhas Volei','Gustavo Alves',47,39,4,4),
    ('Uniao Volei Clube','Lucas Martins',24,9,3,4),
    ('Uniao Volei Clube','Leonardo Alves',12,2,0,1)
) AS st(team_name, athlete_name, total_points, attacks, blocks, aces) ON TRUE
JOIN teams tm ON tm.championship_id = c.id AND tm.name = st.team_name
JOIN athletes a ON a.championship_id = c.id AND a.team_id = tm.id AND a.name = st.athlete_name;

WITH c AS (
    SELECT id FROM championships WHERE name = 'Copa Regional de Volei 2026'
),
main_match AS (
    SELECT m.*
    FROM matches m
    JOIN teams ht ON ht.id = m.home_team_id
    JOIN teams at ON at.id = m.away_team_id
    JOIN c ON c.id = m.championship_id
    WHERE m.round_number = 5
    AND ht.name = 'Uniao Volei Clube'
    AND at.name = 'Porto Volei'
)
INSERT INTO match_events (match_id, team_id, athlete_id, event_type, minute, additional_time, value, description)
SELECT mm.id, tm.id, a.id, e.event_type, e.minute, NULL, e.value, e.description
FROM main_match mm
JOIN (VALUES
    ('Uniao Volei Clube','Gabriel Souza','ponto',3,1,'Partida iniciada com ataque de Gabriel pela entrada de rede.'),
    ('Uniao Volei Clube','Gabriel Souza','saque',12,1,'Ace de Gabriel Souza no fundo da quadra.'),
    ('Uniao Volei Clube','Rafael Lima','bloqueio',18,1,'Bloqueio simples de Rafael Lima.'),
    ('Uniao Volei Clube','Gabriel Souza','ponto',27,1,'Uniao vence o 1o set por 25x21.'),
    ('Porto Volei','Lucas Almeida','ponto',54,1,'Porto vence o 2o set por 25x22.'),
    ('Uniao Volei Clube','Lucas Martins','observacao',61,NULL,'Lucas Martins distribui bem as bolas pelo meio.'),
    ('Uniao Volei Clube','Rafael Lima','bloqueio',79,1,'Uniao vence o 3o set por 25x19.'),
    ('Uniao Volei Clube','Bruno Ferreira','substituicao',82,NULL,'Substituicao no Uniao Volei Clube para reforcar a recepcao.')
) AS e(team_name, athlete_name, event_type, minute, value, description) ON TRUE
JOIN teams tm ON tm.championship_id = mm.championship_id AND tm.name = e.team_name
LEFT JOIN athletes a ON a.championship_id = mm.championship_id AND a.team_id = tm.id AND a.name = e.athlete_name;

WITH c AS (
    SELECT id FROM championships WHERE name = 'Copa Regional de Volei 2026'
),
main_match AS (
    SELECT m.*
    FROM matches m
    JOIN teams ht ON ht.id = m.home_team_id
    JOIN teams at ON at.id = m.away_team_id
    JOIN c ON c.id = m.championship_id
    WHERE m.round_number = 5
    AND ht.name = 'Uniao Volei Clube'
    AND at.name = 'Porto Volei'
)
INSERT INTO match_lineups (match_id, team_id, athlete_id, is_starter, is_captain, shirt_number)
SELECT mm.id, tm.id, a.id, (a.shirt_number IN (1,2,4,5,7,10)), (a.name = 'Gabriel Souza'), a.shirt_number
FROM main_match mm
JOIN teams tm ON tm.championship_id = mm.championship_id AND tm.name = 'Uniao Volei Clube'
JOIN athletes a ON a.team_id = tm.id
WHERE a.shirt_number IN (1,2,4,5,7,8,10,11,12,14,17,20);

WITH c AS (
    SELECT id FROM championships WHERE name = 'Copa Regional de Volei 2026'
),
main_match AS (
    SELECT m.*
    FROM matches m
    JOIN teams ht ON ht.id = m.home_team_id
    JOIN teams at ON at.id = m.away_team_id
    JOIN c ON c.id = m.championship_id
    WHERE m.round_number = 5
    AND ht.name = 'Uniao Volei Clube'
    AND at.name = 'Porto Volei'
)
INSERT INTO match_reports (
    match_id, referee_name, summary, incidents,
    organizer_confirmation, home_team_confirmation, away_team_confirmation
)
SELECT id,
       'Arbitragem Regional',
       'Partida em andamento para demonstracao: Uniao Volei Clube lidera Porto Volei por 2 sets a 1; quarto set aberto para gestao visual.',
       'Sem incidentes disciplinares registrados.',
       TRUE,
       TRUE,
       FALSE
FROM main_match;

COMMIT;
