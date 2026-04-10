CREATE DATABASE IF NOT EXISTS cinespecs
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE cinespecs;

DROP TABLE IF EXISTS TAGS;
DROP TABLE IF EXISTS FRAME;
DROP TABLE IF EXISTS HARDWARE;
DROP TABLE IF EXISTS FILM;
DROP TABLE IF EXISTS UTENTI;

CREATE TABLE UTENTI (
    id_utente INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    ruolo ENUM('contributor', 'admin') NOT NULL DEFAULT 'contributor',
    creato_il TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_utenti_username (username),
    UNIQUE KEY uq_utenti_email (email)
) ENGINE=InnoDB;

CREATE TABLE FILM (
    id_film INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titolo VARCHAR(150) NOT NULL,
    anno_uscita YEAR NOT NULL,
    regista VARCHAR(100) NOT NULL,
    sinossi TEXT DEFAULT NULL,
    copertina_path VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB;

CREATE TABLE HARDWARE (
    id_hardware INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome_modello VARCHAR(120) NOT NULL,
    produttore VARCHAR(120) NOT NULL,
    anno_rilascio YEAR DEFAULT NULL,
    descrizione TEXT DEFAULT NULL,
    curiosita TEXT DEFAULT NULL,
    prop_fittizio TINYINT(1) NOT NULL DEFAULT 0,
    immagine_path VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB;

CREATE TABLE FRAME (
    id_frame INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_film INT UNSIGNED NOT NULL,
    immagine_path VARCHAR(255) NOT NULL,
    timestamp_frame TIME DEFAULT NULL,
    descrizione_scena VARCHAR(255) DEFAULT NULL,
    CONSTRAINT fk_frame_film
        FOREIGN KEY (id_film)
        REFERENCES FILM (id_film)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE TAGS (
    id_tag INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_frame INT UNSIGNED NOT NULL,
    id_hardware INT UNSIGNED NOT NULL,
    id_utente INT UNSIGNED NOT NULL,
    coord_x DECIMAL(5,2) NOT NULL,
    coord_y DECIMAL(5,2) NOT NULL,
    creato_il TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_tags_coord_x CHECK (coord_x >= 0 AND coord_x <= 100),
    CONSTRAINT chk_tags_coord_y CHECK (coord_y >= 0 AND coord_y <= 100),
    CONSTRAINT fk_tags_frame
        FOREIGN KEY (id_frame)
        REFERENCES FRAME (id_frame)
        ON DELETE CASCADE,
    CONSTRAINT fk_tags_hardware
        FOREIGN KEY (id_hardware)
        REFERENCES HARDWARE (id_hardware)
        ON DELETE CASCADE,
    CONSTRAINT fk_tags_utente
        FOREIGN KEY (id_utente)
        REFERENCES UTENTI (id_utente)
        ON DELETE CASCADE,
    UNIQUE KEY uq_tags_frame_hardware (id_frame, id_hardware)
) ENGINE=InnoDB;

INSERT INTO UTENTI (username, email, password_hash, ruolo) VALUES
('admin', 'admin@cinespecs.local', '$2y$10$wH0N3xQeJ8mP4Q0xM1yRne9L7kJQ2m8f2n4jJc7sJb3rR8A1VtL8O', 'admin'),
('marta', 'marta@cinespecs.local', '$2y$10$wH0N3xQeJ8mP4Q0xM1yRne9L7kJQ2m8f2n4jJc7sJb3rR8A1VtL8O', 'contributor');

INSERT INTO FILM (titolo, anno_uscita, regista, sinossi, copertina_path) VALUES
('Alien', 1979, 'Ridley Scott', 'L''equipaggio della Nostromo affronta una minaccia aliena.', 'assets/covers/alien.jpg'),
('WarGames', 1983, 'John Badham', 'Un adolescente entra per errore in un sistema militare.', 'assets/covers/wargames.jpg');

INSERT INTO HARDWARE (nome_modello, produttore, anno_rilascio, descrizione, curiosita, prop_fittizio, immagine_path) VALUES
('DEC VT100', 'Digital Equipment Corporation', 1978, 'Terminale video molto usato come riferimento visivo nel cinema.', 'Appare in molte produzioni fantascientifiche e tecnologiche.', 0, 'assets/icons/vt100.png'),
('IMSAI 8080', 'IMS Associates, Inc.', 1975, 'Microcomputer storico associato all''immaginario hacker degli anni 80.', 'Diventato iconico anche grazie al cinema e alla TV.', 0, 'assets/icons/imsai-8080.png');

INSERT INTO FRAME (id_film, immagine_path, timestamp_frame, descrizione_scena) VALUES
(1, 'assets/frames/alien-console.jpg', '00:12:45', 'Console di bordo della Nostromo in primo piano.'),
(2, 'assets/frames/wargames-imsai.jpg', '00:07:18', 'Postazione con microcomputer nella camera del protagonista.');

INSERT INTO TAGS (id_frame, id_hardware, id_utente, coord_x, coord_y) VALUES
(1, 1, 1, 46.50, 61.20),
(2, 2, 2, 38.00, 54.40);
