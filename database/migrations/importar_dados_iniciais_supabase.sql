BEGIN;

INSERT INTO sports (id, name) VALUES
(1, 'Futebol'),
(2, 'Futsal'),
(3, 'Volei'),
(4, 'Basquete'),
(5, 'Handebol'),
(6, 'Tenis de Mesa'),
(7, 'Beach Tennis'),
(8, 'Ciclismo'),
(9, 'Corrida'),
(10, 'Xadrez'),
(11, 'Pesca Esportiva'),
(12, 'Outros')
ON CONFLICT DO NOTHING;

INSERT INTO users (id, name, email, password, role, phone, city, birth_date, preferred_price_max) VALUES
(1, 'Ana Atleta', 'atleta@sportconnect.test', '$2y$10$behjY865AARkSvWpA4plh.5alTGIT0.vcDwmLhH9IWiaqZjV4xjB.', 'athlete', '42999990000', 'Uniao da Vitoria', '2001-04-12', 90.00),
(2, 'Carlos Organizador', 'organizador@sportconnect.test', '$2y$10$behjY865AARkSvWpA4plh.5alTGIT0.vcDwmLhH9IWiaqZjV4xjB.', 'organizer', '42988887777', 'Uniao da Vitoria', NULL, NULL),
(3, 'Admin SportConnect', 'admin@sportconnect.test', '$2y$10$behjY865AARkSvWpA4plh.5alTGIT0.vcDwmLhH9IWiaqZjV4xjB.', 'admin', NULL, 'Uniao da Vitoria', NULL, NULL)
ON CONFLICT DO NOTHING;

INSERT INTO championships
(id, organizer_id, sport_id, name, city, location, map_link, event_date, event_time, registration_fee, prize, max_participants, description, category, modality, status, whatsapp_contato, views)
VALUES
(1, 2, 2, 'Campeonato Regional de Futsal', 'Uniao da Vitoria', 'Ginasio Municipal', 'https://maps.google.com', '2026-08-22', '19:30', 70.00, 'Trofeu e R$ 1500', 16, 'Torneio regional com fase de grupos e eliminatorias.', 'Adulto', 'masculino', 'ativo', '42988887777', 230),
(2, 2, 9, 'Corrida 5K Vale do Iguacu', 'Porto Uniao', 'Parque Linear', 'https://maps.google.com', '2026-09-06', '08:00', 45.00, 'Medalhas por categoria', 300, 'Prova de rua para atletas iniciantes e avancados.', 'Livre', 'misto', 'ativo', '42977776666', 188),
(3, 2, 4, 'Copa Basquete 3x3', 'Uniao da Vitoria', 'Praca Esportiva Central', 'https://maps.google.com', '2026-08-30', '14:00', 60.00, 'Kit esportivo e trofeu', 24, 'Competicao dinamica em quadra aberta.', 'Sub-23', 'misto', 'ativo', '42966665555', 122),
(4, 2, 10, 'Aberto Regional de Xadrez', 'Mallet', 'Centro Cultural', 'https://maps.google.com', '2026-10-12', '09:00', 30.00, 'Medalhas e rating interno', 80, 'Sistema suico em seis rodadas.', 'Livre', 'misto', 'ativo', '42955554444', 91)
ON CONFLICT DO NOTHING;

INSERT INTO registrations (id, championship_id, user_id, name, phone, email, team, category, city, status) VALUES
(1, 1, 1, 'Ana Atleta', '42999990000', 'atleta@sportconnect.test', 'Feras FC', 'Adulto', 'Uniao da Vitoria', 'aprovado')
ON CONFLICT DO NOTHING;

INSERT INTO favorites (id, user_id, championship_id) VALUES
(1, 1, 3)
ON CONFLICT DO NOTHING;

INSERT INTO notifications (id, user_id, message) VALUES
(1, 1, 'Novo campeonato de Futsal disponivel em Uniao da Vitoria.')
ON CONFLICT DO NOTHING;

INSERT INTO user_sports (user_id, sport_id) VALUES
(1, 2),
(1, 4),
(1, 9)
ON CONFLICT DO NOTHING;

SELECT setval(pg_get_serial_sequence('sports', 'id'), COALESCE((SELECT MAX(id) FROM sports), 1), true);
SELECT setval(pg_get_serial_sequence('users', 'id'), COALESCE((SELECT MAX(id) FROM users), 1), true);
SELECT setval(pg_get_serial_sequence('championships', 'id'), COALESCE((SELECT MAX(id) FROM championships), 1), true);
SELECT setval(pg_get_serial_sequence('registrations', 'id'), COALESCE((SELECT MAX(id) FROM registrations), 1), true);
SELECT setval(pg_get_serial_sequence('favorites', 'id'), COALESCE((SELECT MAX(id) FROM favorites), 1), true);
SELECT setval(pg_get_serial_sequence('notifications', 'id'), COALESCE((SELECT MAX(id) FROM notifications), 1), true);
SELECT setval(pg_get_serial_sequence('reviews', 'id'), COALESCE((SELECT MAX(id) FROM reviews), 1), true);

COMMIT;
