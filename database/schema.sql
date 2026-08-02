BEGIN;

DO $$ BEGIN
    CREATE TYPE user_role AS ENUM ('athlete', 'organizer', 'admin');
EXCEPTION
    WHEN duplicate_object THEN NULL;
END $$;

DO $$ BEGIN
    CREATE TYPE championship_modality AS ENUM ('masculino', 'feminino', 'misto');
EXCEPTION
    WHEN duplicate_object THEN NULL;
END $$;

DO $$ BEGIN
    CREATE TYPE championship_status AS ENUM ('ativo', 'encerrado', 'cancelado');
EXCEPTION
    WHEN duplicate_object THEN NULL;
END $$;

DO $$ BEGIN
    CREATE TYPE registration_status AS ENUM ('pendente', 'aprovado', 'rejeitado', 'cancelado');
EXCEPTION
    WHEN duplicate_object THEN NULL;
END $$;

CREATE TABLE IF NOT EXISTS users (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(160) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role user_role NOT NULL DEFAULT 'athlete',
    phone VARCHAR(30),
    city VARCHAR(100),
    birth_date DATE,
    preferred_price_max NUMERIC(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS sports (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(80) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS user_sports (
    user_id BIGINT NOT NULL,
    sport_id BIGINT NOT NULL,
    PRIMARY KEY (user_id, sport_id),
    CONSTRAINT fk_user_sports_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_user_sports_sport FOREIGN KEY (sport_id) REFERENCES sports(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS championships (
    id BIGSERIAL PRIMARY KEY,
    organizer_id BIGINT NOT NULL,
    sport_id BIGINT NOT NULL,
    name VARCHAR(160) NOT NULL,
    city VARCHAR(100) NOT NULL,
    location VARCHAR(180) NOT NULL,
    map_link VARCHAR(255),
    event_date DATE NOT NULL,
    event_time TIME NOT NULL,
    registration_fee NUMERIC(10,2) NOT NULL DEFAULT 0,
    prize VARCHAR(180),
    max_participants INTEGER NOT NULL DEFAULT 0,
    description TEXT NOT NULL,
    rules_file VARCHAR(255),
    category VARCHAR(100),
    modality championship_modality NOT NULL DEFAULT 'misto',
    status championship_status NOT NULL DEFAULT 'ativo',
    whatsapp_contato VARCHAR(20) NOT NULL,
    image VARCHAR(255) NOT NULL DEFAULT 'assets/img/default-event.svg',
    imagem VARCHAR(255),
    views INTEGER NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_championship_organizer FOREIGN KEY (organizer_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_championship_sport FOREIGN KEY (sport_id) REFERENCES sports(id)
);

CREATE TABLE IF NOT EXISTS registrations (
    id BIGSERIAL PRIMARY KEY,
    championship_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    name VARCHAR(120) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    email VARCHAR(160) NOT NULL,
    team VARCHAR(120),
    category VARCHAR(100),
    city VARCHAR(100) NOT NULL,
    cpf VARCHAR(20),
    notes TEXT,
    proof_file VARCHAR(255),
    status registration_status NOT NULL DEFAULT 'pendente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_registration_championship FOREIGN KEY (championship_id) REFERENCES championships(id) ON DELETE CASCADE,
    CONSTRAINT fk_registration_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS favorites (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    championship_id BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT unique_favorite UNIQUE (user_id, championship_id),
    CONSTRAINT fk_favorite_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_favorite_championship FOREIGN KEY (championship_id) REFERENCES championships(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS notifications (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    message VARCHAR(255) NOT NULL,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notification_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS reviews (
    id BIGSERIAL PRIMARY KEY,
    championship_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    rating SMALLINT NOT NULL,
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_review_championship FOREIGN KEY (championship_id) REFERENCES championships(id) ON DELETE CASCADE,
    CONSTRAINT fk_review_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT chk_reviews_rating CHECK (rating BETWEEN 1 AND 5)
);

CREATE INDEX IF NOT EXISTS idx_championships_organizer_id ON championships (organizer_id);
CREATE INDEX IF NOT EXISTS idx_championships_sport_id ON championships (sport_id);
CREATE INDEX IF NOT EXISTS idx_championships_status_date ON championships (status, event_date);
CREATE INDEX IF NOT EXISTS idx_registrations_championship_id ON registrations (championship_id);
CREATE INDEX IF NOT EXISTS idx_registrations_user_id ON registrations (user_id);
CREATE INDEX IF NOT EXISTS idx_registrations_status ON registrations (status);
CREATE INDEX IF NOT EXISTS idx_favorites_user_id ON favorites (user_id);
CREATE INDEX IF NOT EXISTS idx_notifications_user_id ON notifications (user_id);
CREATE INDEX IF NOT EXISTS idx_reviews_championship_id ON reviews (championship_id);

INSERT INTO sports (name) VALUES
('Futebol'), ('Futsal'), ('Volei'), ('Basquete'), ('Handebol'), ('Tenis de Mesa'),
('Beach Tennis'), ('Ciclismo'), ('Corrida'), ('Xadrez'), ('Pesca Esportiva'), ('Outros')
ON CONFLICT (name) DO NOTHING;

INSERT INTO users (name, email, password, role, phone, city, birth_date, preferred_price_max) VALUES
('Ana Atleta', 'atleta@sportconnect.test', '$2y$10$behjY865AARkSvWpA4plh.5alTGIT0.vcDwmLhH9IWiaqZjV4xjB.', 'athlete', '42999990000', 'Uniao da Vitoria', '2001-04-12', 90.00),
('Carlos Organizador', 'organizador@sportconnect.test', '$2y$10$behjY865AARkSvWpA4plh.5alTGIT0.vcDwmLhH9IWiaqZjV4xjB.', 'organizer', '42988887777', 'Uniao da Vitoria', NULL, NULL),
('Admin SportConnect', 'admin@sportconnect.test', '$2y$10$behjY865AARkSvWpA4plh.5alTGIT0.vcDwmLhH9IWiaqZjV4xjB.', 'admin', NULL, 'Uniao da Vitoria', NULL, NULL)
ON CONFLICT (email) DO NOTHING;

INSERT INTO user_sports (user_id, sport_id) VALUES (1, 2), (1, 4), (1, 9)
ON CONFLICT (user_id, sport_id) DO NOTHING;

INSERT INTO championships
(organizer_id, sport_id, name, city, location, map_link, event_date, event_time, registration_fee, prize, max_participants, description, category, modality, status, whatsapp_contato, views)
VALUES
(2, 2, 'Campeonato Regional de Futsal', 'Uniao da Vitoria', 'Ginasio Municipal', 'https://maps.google.com', '2026-08-22', '19:30', 70.00, 'Trofeu e R$ 1500', 16, 'Torneio regional com fase de grupos e eliminatorias.', 'Adulto', 'masculino', 'ativo', '42988887777', 230),
(2, 9, 'Corrida 5K Vale do Iguacu', 'Porto Uniao', 'Parque Linear', 'https://maps.google.com', '2026-09-06', '08:00', 45.00, 'Medalhas por categoria', 300, 'Prova de rua para atletas iniciantes e avancados.', 'Livre', 'misto', 'ativo', '42977776666', 188),
(2, 4, 'Copa Basquete 3x3', 'Uniao da Vitoria', 'Praca Esportiva Central', 'https://maps.google.com', '2026-08-30', '14:00', 60.00, 'Kit esportivo e trofeu', 24, 'Competicao dinamica em quadra aberta.', 'Sub-23', 'misto', 'ativo', '42966665555', 122),
(2, 10, 'Aberto Regional de Xadrez', 'Mallet', 'Centro Cultural', 'https://maps.google.com', '2026-10-12', '09:00', 30.00, 'Medalhas e rating interno', 80, 'Sistema suico em seis rodadas.', 'Livre', 'misto', 'ativo', '42955554444', 91)
ON CONFLICT DO NOTHING;

INSERT INTO registrations (championship_id, user_id, name, phone, email, team, category, city, status)
VALUES (1, 1, 'Ana Atleta', '42999990000', 'atleta@sportconnect.test', 'Feras FC', 'Adulto', 'Uniao da Vitoria', 'aprovado')
ON CONFLICT DO NOTHING;

INSERT INTO favorites (user_id, championship_id) VALUES (1, 3)
ON CONFLICT (user_id, championship_id) DO NOTHING;

INSERT INTO notifications (user_id, message) VALUES
(1, 'Novo campeonato de Futsal disponivel em Uniao da Vitoria.');

SELECT setval(pg_get_serial_sequence('users', 'id'), COALESCE((SELECT MAX(id) FROM users), 1), true);
SELECT setval(pg_get_serial_sequence('sports', 'id'), COALESCE((SELECT MAX(id) FROM sports), 1), true);
SELECT setval(pg_get_serial_sequence('championships', 'id'), COALESCE((SELECT MAX(id) FROM championships), 1), true);
SELECT setval(pg_get_serial_sequence('registrations', 'id'), COALESCE((SELECT MAX(id) FROM registrations), 1), true);
SELECT setval(pg_get_serial_sequence('favorites', 'id'), COALESCE((SELECT MAX(id) FROM favorites), 1), true);
SELECT setval(pg_get_serial_sequence('notifications', 'id'), COALESCE((SELECT MAX(id) FROM notifications), 1), true);
SELECT setval(pg_get_serial_sequence('reviews', 'id'), COALESCE((SELECT MAX(id) FROM reviews), 1), true);

COMMIT;
