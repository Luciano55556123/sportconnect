BEGIN;

INSERT INTO teams (id, championship_id, name, city, responsible_name, responsible_phone, status, is_demo) VALUES
(1001, 1, 'Uniao Futsal', 'Uniao da Vitoria', 'Marcos Lima', '42991000001', 'aprovado', true),
(1002, 1, 'Porto Arena', 'Porto Uniao', 'Rafael Costa', '42991000002', 'aprovado', true),
(1003, 1, 'Iguacu FC', 'Uniao da Vitoria', 'Eduardo Reis', '42991000003', 'aprovado', true),
(1004, 1, 'Mallet Esporte', 'Mallet', 'Tiago Duarte', '42991000004', 'aprovado', true),
(1005, 1, 'Vila Verde', 'Paula Freitas', 'Bruno Rocha', '42991000005', 'aprovado', true),
(1006, 1, 'Sao Cristovao', 'Uniao da Vitoria', 'Felipe Martins', '42991000006', 'aprovado', true),
(1007, 1, 'Panteras FC', 'Porto Uniao', 'Andre Nunes', '42991000007', 'aprovado', true),
(1008, 1, 'Elite da Serra', 'Cruz Machado', 'Lucas Freitas', '42991000008', 'aprovado', true)
ON CONFLICT (id) DO NOTHING;

INSERT INTO athletes (id, championship_id, team_id, name, city, shirt_number, position, category, status) VALUES
(10001,1,1001,'Joao Silva','Uniao da Vitoria',10,'Ala','Adulto','aprovado'),(10002,1,1001,'Pedro Lima','Uniao da Vitoria',9,'Pivo','Adulto','aprovado'),(10003,1,1001,'Caio Alves','Uniao da Vitoria',1,'Goleiro','Adulto','aprovado'),(10004,1,1001,'Mateus Rocha','Uniao da Vitoria',7,'Fixo','Adulto','aprovado'),(10005,1,1001,'Bruno Weiss','Uniao da Vitoria',11,'Ala','Adulto','aprovado'),
(10006,1,1002,'Carlos Souza','Porto Uniao',8,'Ala','Adulto','aprovado'),(10007,1,1002,'Ruan Pereira','Porto Uniao',12,'Goleiro','Adulto','aprovado'),(10008,1,1002,'Igor Mendes','Porto Uniao',5,'Fixo','Adulto','aprovado'),(10009,1,1002,'Diego Lopes','Porto Uniao',19,'Pivo','Adulto','aprovado'),(10010,1,1002,'Samuel Neri','Porto Uniao',6,'Ala','Adulto','aprovado'),
(10011,1,1003,'Felipe Santos','Uniao da Vitoria',20,'Pivo','Adulto','aprovado'),(10012,1,1003,'Lucas Almeida','Uniao da Vitoria',4,'Fixo','Adulto','aprovado'),(10013,1,1003,'Vitor Ramos','Uniao da Vitoria',17,'Ala','Adulto','aprovado'),(10014,1,1003,'Henrique Dias','Uniao da Vitoria',21,'Goleiro','Adulto','aprovado'),(10015,1,1003,'Gustavo Melo','Uniao da Vitoria',15,'Ala','Adulto','aprovado'),
(10016,1,1004,'Andre Martins','Mallet',3,'Fixo','Adulto','aprovado'),(10017,1,1004,'Leandro Cruz','Mallet',14,'Ala','Adulto','aprovado'),(10018,1,1004,'Rafael Pires','Mallet',18,'Pivo','Adulto','aprovado'),(10019,1,1004,'Nathan Oliveira','Mallet',22,'Goleiro','Adulto','aprovado'),(10020,1,1004,'Murilo Gomes','Mallet',13,'Ala','Adulto','aprovado'),
(10021,1,1005,'Otavio Ferraz','Paula Freitas',10,'Ala','Adulto','aprovado'),(10022,1,1005,'Daniel Barros','Paula Freitas',9,'Pivo','Adulto','aprovado'),(10023,1,1005,'Cesar Lopes','Paula Freitas',1,'Goleiro','Adulto','aprovado'),(10024,1,1005,'Thiago Simas','Paula Freitas',7,'Fixo','Adulto','aprovado'),(10025,1,1005,'Marcelo Vaz','Paula Freitas',11,'Ala','Adulto','aprovado'),
(10026,1,1006,'Renan Batista','Uniao da Vitoria',8,'Ala','Adulto','aprovado'),(10027,1,1006,'Alan Moraes','Uniao da Vitoria',12,'Goleiro','Adulto','aprovado'),(10028,1,1006,'Fabio Cardoso','Uniao da Vitoria',5,'Fixo','Adulto','aprovado'),(10029,1,1006,'Eduardo Klein','Uniao da Vitoria',19,'Pivo','Adulto','aprovado'),(10030,1,1006,'Sandro Farias','Uniao da Vitoria',6,'Ala','Adulto','aprovado'),
(10031,1,1007,'Paulo Vieira','Porto Uniao',20,'Pivo','Adulto','aprovado'),(10032,1,1007,'Alex Ribeiro','Porto Uniao',4,'Fixo','Adulto','aprovado'),(10033,1,1007,'Marcio Teixeira','Porto Uniao',17,'Ala','Adulto','aprovado'),(10034,1,1007,'Cleberson Maia','Porto Uniao',21,'Goleiro','Adulto','aprovado'),(10035,1,1007,'Willian Fogaça','Porto Uniao',15,'Ala','Adulto','aprovado'),
(10036,1,1008,'Rodrigo Antunes','Cruz Machado',3,'Fixo','Adulto','aprovado'),(10037,1,1008,'Jonas Correia','Cruz Machado',14,'Ala','Adulto','aprovado'),(10038,1,1008,'Nicolas Prado','Cruz Machado',18,'Pivo','Adulto','aprovado'),(10039,1,1008,'Edson Lara','Cruz Machado',22,'Goleiro','Adulto','aprovado'),(10040,1,1008,'Mauricio Tavares','Cruz Machado',13,'Ala','Adulto','aprovado')
ON CONFLICT (id) DO NOTHING;

INSERT INTO matches (id, championship_id, phase, group_name, round_number, home_team_id, away_team_id, match_date, match_time, venue, court_or_field, home_score, away_score, status, next_match_id, next_match_position, winner_team_id) VALUES
(11001,1,'Grupos','A',1,1001,1002,'2026-08-22','19:30','Ginasio Municipal','Quadra 1',3,2,'finalizada',NULL,NULL,1001),
(11002,1,'Grupos','A',1,1003,1004,'2026-08-22','20:30','Ginasio Municipal','Quadra 1',1,1,'finalizada',NULL,NULL,NULL),
(11003,1,'Grupos','B',1,1005,1006,'2026-08-23','18:00','Ginasio Municipal','Quadra 1',2,0,'finalizada',NULL,NULL,1005),
(11004,1,'Grupos','B',1,1007,1008,'2026-08-23','19:00','Ginasio Municipal','Quadra 1',4,3,'finalizada',NULL,NULL,1007),
(11005,1,'Grupos','A',2,1001,1003,'2026-08-24','19:30','Ginasio Municipal','Quadra 1',2,2,'finalizada',NULL,NULL,NULL),
(11006,1,'Grupos','A',2,1002,1004,'2026-08-24','20:30','Ginasio Municipal','Quadra 1',1,3,'finalizada',NULL,NULL,1004),
(11007,1,'Grupos','B',2,1005,1007,'2026-08-25','19:30','Ginasio Municipal','Quadra 1',2,1,'finalizada',NULL,NULL,1005),
(11008,1,'Grupos','B',2,1006,1008,'2026-08-25','20:30','Ginasio Municipal','Quadra 1',3,3,'finalizada',NULL,NULL,NULL),
(11009,1,'Quartas',NULL,3,1001,1008,'2026-08-27','19:00','Ginasio Municipal','Quadra 1',2,1,'finalizada',11013,'home',1001),
(11010,1,'Quartas',NULL,3,1005,1004,'2026-08-27','20:00','Ginasio Municipal','Quadra 1',1,2,'finalizada',11013,'away',1004),
(11011,1,'Quartas',NULL,3,1007,1003,'2026-08-28','19:00','Ginasio Municipal','Quadra 1',3,1,'finalizada',11014,'home',1007),
(11012,1,'Quartas',NULL,3,1006,1002,'2026-08-28','20:00','Ginasio Municipal','Quadra 1',NULL,NULL,'agendada',11014,'away',NULL),
(11013,1,'Semifinal',NULL,4,1001,1004,'2026-08-30','19:30','Ginasio Municipal','Quadra 1',NULL,NULL,'agendada',11015,'home',NULL),
(11014,1,'Semifinal',NULL,4,1007,NULL,'2026-08-30','20:30','Ginasio Municipal','Quadra 1',NULL,NULL,'agendada',11015,'away',NULL),
(11015,1,'Final',NULL,5,NULL,NULL,'2026-09-01','20:00','Ginasio Municipal','Quadra 1',NULL,NULL,'agendada',NULL,NULL,NULL)
ON CONFLICT (id) DO NOTHING;

INSERT INTO match_events (id, match_id, team_id, athlete_id, event_type, minute, additional_time, value, description) VALUES
(12001,11001,1001,10001,'gol',12,NULL,1,'Gol normal'),(12002,11001,1002,10006,'cartao_amarelo',28,NULL,NULL,'Reclamacao'),(12003,11001,1001,10002,'penalti_convertido',45,2,1,'Penalti convertido'),(12004,11001,1002,10009,'gol',36,NULL,1,'Finalizacao no canto'),
(12005,11003,1005,10021,'gol',8,NULL,1,'Contra-ataque'),(12006,11003,1005,10022,'gol',31,NULL,1,'Jogada ensaiada'),(12007,11004,1007,10031,'gol',6,NULL,1,'Pivo girou'),(12008,11004,1008,10038,'cartao_vermelho',39,NULL,NULL,'Falta dura'),
(12009,11007,1005,10021,'gol',18,NULL,1,'Gol normal'),(12010,11007,1007,10033,'cartao_amarelo',22,NULL,NULL,'Entrada atrasada'),(12011,11009,1001,10001,'gol',14,NULL,1,'Abriu o placar'),(12012,11010,1004,10018,'gol',33,NULL,1,'Gol da classificacao')
ON CONFLICT (id) DO NOTHING;

INSERT INTO standings (championship_id, team_id, group_name, played, wins, draws, losses, score_for, score_against, score_difference, points) VALUES
(1,1001,'A',2,1,1,0,5,4,1,4),(1,1004,'A',2,1,1,0,4,2,2,4),(1,1003,'A',2,0,2,0,3,3,0,2),(1,1002,'A',2,0,0,2,3,6,-3,0),
(1,1005,'B',2,2,0,0,4,1,3,6),(1,1007,'B',2,1,0,1,5,5,0,3),(1,1008,'B',2,0,1,1,6,7,-1,1),(1,1006,'B',2,0,1,1,3,5,-2,1)
ON CONFLICT DO NOTHING;

INSERT INTO competition_activity_logs (championship_id, user_id, action, description) VALUES
(1, 2, 'dados_demo', 'Dados de demonstracao da gestao completa carregados.')
ON CONFLICT DO NOTHING;

UPDATE championships SET is_demo = true WHERE id = 1;
UPDATE athletes SET is_demo = true WHERE id BETWEEN 10001 AND 10040;
UPDATE matches SET is_demo = true WHERE id BETWEEN 11001 AND 11015;

SELECT setval(pg_get_serial_sequence('teams', 'id'), COALESCE((SELECT MAX(id) FROM teams), 1), true);
SELECT setval(pg_get_serial_sequence('athletes', 'id'), COALESCE((SELECT MAX(id) FROM athletes), 1), true);
SELECT setval(pg_get_serial_sequence('matches', 'id'), COALESCE((SELECT MAX(id) FROM matches), 1), true);
SELECT setval(pg_get_serial_sequence('match_events', 'id'), COALESCE((SELECT MAX(id) FROM match_events), 1), true);
SELECT setval(pg_get_serial_sequence('standings', 'id'), COALESCE((SELECT MAX(id) FROM standings), 1), true);
SELECT setval(pg_get_serial_sequence('competition_activity_logs', 'id'), COALESCE((SELECT MAX(id) FROM competition_activity_logs), 1), true);

COMMIT;
