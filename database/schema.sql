CREATE DATABASE IF NOT EXISTS sportconnect CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sportconnect;

DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS favorites;
DROP TABLE IF EXISTS registrations;
DROP TABLE IF EXISTS user_sports;
DROP TABLE IF EXISTS championships;
DROP TABLE IF EXISTS sports;
DROP TABLE IF EXISTS organizer_requests;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(160) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('athlete','organizer','admin') NOT NULL DEFAULT 'athlete',
    phone VARCHAR(30),
    city VARCHAR(100),
    birth_date DATE,
    preferred_price_max DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE organizer_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    approved_by INT NULL,
    approved_at TIMESTAMP NULL,
    rejection_reason TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    pending_user_id INT GENERATED ALWAYS AS (CASE WHEN status = 'pending' THEN user_id ELSE NULL END) STORED,
    UNIQUE KEY unique_pending_organizer_request (pending_user_id),
    INDEX idx_organizer_requests_status_created (status, created_at),
    CONSTRAINT fk_organizer_request_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_organizer_request_approver FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE sports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE user_sports (
    user_id INT NOT NULL,
    sport_id INT NOT NULL,
    PRIMARY KEY (user_id, sport_id),
    CONSTRAINT fk_user_sports_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_user_sports_sport FOREIGN KEY (sport_id) REFERENCES sports(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE championships (
    id INT AUTO_INCREMENT PRIMARY KEY,
    organizer_id INT NOT NULL,
    sport_id INT NOT NULL,
    name VARCHAR(160) NOT NULL,
    city VARCHAR(100) NOT NULL,
    location VARCHAR(180) NOT NULL,
    map_link VARCHAR(255),
    event_date DATE NOT NULL,
    event_time TIME NOT NULL,
    registration_fee DECIMAL(10,2) NOT NULL DEFAULT 0,
    prize VARCHAR(180),
    max_participants INT NOT NULL DEFAULT 0,
    description TEXT NOT NULL,
    rules_file VARCHAR(255),
    category VARCHAR(100),
    modality ENUM('masculino','feminino','misto') NOT NULL DEFAULT 'misto',
    status ENUM('ativo','encerrado','cancelado') NOT NULL DEFAULT 'ativo',
    image VARCHAR(255) NOT NULL DEFAULT 'assets/img/default-event.svg',
    views INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_championship_organizer FOREIGN KEY (organizer_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_championship_sport FOREIGN KEY (sport_id) REFERENCES sports(id)
) ENGINE=InnoDB;

CREATE TABLE registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    championship_id INT NOT NULL,
    user_id INT NOT NULL,
    name VARCHAR(120) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    email VARCHAR(160) NOT NULL,
    team VARCHAR(120),
    category VARCHAR(100),
    city VARCHAR(100) NOT NULL,
    cpf VARCHAR(20),
    notes TEXT,
    proof_file VARCHAR(255),
    status ENUM('pendente','aprovado','rejeitado','cancelado') NOT NULL DEFAULT 'pendente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_registration_championship FOREIGN KEY (championship_id) REFERENCES championships(id) ON DELETE CASCADE,
    CONSTRAINT fk_registration_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    championship_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_favorite (user_id, championship_id),
    CONSTRAINT fk_favorite_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_favorite_championship FOREIGN KEY (championship_id) REFERENCES championships(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message VARCHAR(255) NOT NULL,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notification_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    championship_id INT NOT NULL,
    user_id INT NOT NULL,
    rating TINYINT NOT NULL,
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_review_championship FOREIGN KEY (championship_id) REFERENCES championships(id) ON DELETE CASCADE,
    CONSTRAINT fk_review_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO sports (name) VALUES
('Futebol'),('Futsal'),('Volei'),('Basquete'),('Handebol'),('Tenis de Mesa'),
('Beach Tennis'),('Ciclismo'),('Corrida'),('Xadrez'),('Pesca Esportiva'),('Outros');

INSERT INTO users (name, email, password, role, phone, city, birth_date, preferred_price_max) VALUES
('Ana Atleta', 'atleta@sportconnect.test', '$2y$10$behjY865AARkSvWpA4plh.5alTGIT0.vcDwmLhH9IWiaqZjV4xjB.', 'athlete', '42999990000', 'Uniao da Vitoria', '2001-04-12', 90.00),
('Carlos Organizador', 'organizador@sportconnect.test', '$2y$10$behjY865AARkSvWpA4plh.5alTGIT0.vcDwmLhH9IWiaqZjV4xjB.', 'organizer', '42988887777', 'Uniao da Vitoria', NULL, NULL),
('Admin SportConnect', 'admin@sportconnect.test', '$2y$10$behjY865AARkSvWpA4plh.5alTGIT0.vcDwmLhH9IWiaqZjV4xjB.', 'admin', NULL, 'Uniao da Vitoria', NULL, NULL);

INSERT INTO user_sports (user_id, sport_id) VALUES (1,2),(1,4),(1,9);

INSERT INTO championships
(organizer_id, sport_id, name, city, location, map_link, event_date, event_time, registration_fee, prize, max_participants, description, category, modality, status, views)
VALUES
(2, 2, 'Campeonato Regional de Futsal', 'Uniao da Vitoria', 'Ginasio Municipal', 'https://maps.google.com', '2026-08-22', '19:30', 70.00, 'Trofeu e R$ 1500', 16, 'Torneio regional com fase de grupos e eliminatorias.', 'Adulto', 'masculino', 'ativo', 230),
(2, 9, 'Corrida 5K Vale do Iguacu', 'Porto Uniao', 'Parque Linear', 'https://maps.google.com', '2026-09-06', '08:00', 45.00, 'Medalhas por categoria', 300, 'Prova de rua para atletas iniciantes e avancados.', 'Livre', 'misto', 'ativo', 188),
(2, 4, 'Copa Basquete 3x3', 'Uniao da Vitoria', 'Praca Esportiva Central', 'https://maps.google.com', '2026-08-30', '14:00', 60.00, 'Kit esportivo e trofeu', 24, 'Competicao dinamica em quadra aberta.', 'Sub-23', 'misto', 'ativo', 122),
(2, 10, 'Aberto Regional de Xadrez', 'Mallet', 'Centro Cultural', 'https://maps.google.com', '2026-10-12', '09:00', 30.00, 'Medalhas e rating interno', 80, 'Sistema suico em seis rodadas.', 'Livre', 'misto', 'ativo', 91);

INSERT INTO registrations (championship_id, user_id, name, phone, email, team, category, city, status)
VALUES (1, 1, 'Ana Atleta', '42999990000', 'atleta@sportconnect.test', 'Feras FC', 'Adulto', 'Uniao da Vitoria', 'aprovado');

INSERT INTO favorites (user_id, championship_id) VALUES (1, 3);

INSERT INTO notifications (user_id, message) VALUES
(1, 'Novo campeonato de Futsal disponivel em Uniao da Vitoria.');
