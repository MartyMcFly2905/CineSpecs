CREATE DATABASE IF NOT EXISTS cinespecs
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE cinespecs;

DROP TABLE IF EXISTS TAG_VOTI;
DROP TABLE IF EXISTS FRAME_VOTI;
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
    id_utente_creatore INT UNSIGNED NOT NULL,
    titolo VARCHAR(150) NOT NULL,
    anno_uscita YEAR NOT NULL,
    regista VARCHAR(100) NOT NULL,
    sinossi TEXT DEFAULT NULL,
    copertina_path VARCHAR(255) DEFAULT NULL,
    creato_il TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_film_utente_creatore
        FOREIGN KEY (id_utente_creatore)
        REFERENCES UTENTI (id_utente)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE HARDWARE (
    id_hardware INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_utente_creatore INT UNSIGNED NOT NULL,
    nome_modello VARCHAR(120) NOT NULL,
    produttore VARCHAR(120) NOT NULL,
    anno_rilascio SMALLINT DEFAULT NULL,
    descrizione TEXT DEFAULT NULL,
    curiosita TEXT DEFAULT NULL,
    prop_fittizio TINYINT(1) NOT NULL DEFAULT 0,
    immagine_path VARCHAR(255) DEFAULT NULL,
    creato_il TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_hardware_utente_creatore
        FOREIGN KEY (id_utente_creatore)
        REFERENCES UTENTI (id_utente)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE FRAME (
    id_frame INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_film INT UNSIGNED NOT NULL,
    id_utente_creatore INT UNSIGNED NOT NULL,
    immagine_path VARCHAR(255) NOT NULL,
    timestamp_frame TIME DEFAULT NULL,
    descrizione_scena VARCHAR(255) DEFAULT NULL,
    creato_il TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_frame_film
        FOREIGN KEY (id_film)
        REFERENCES FILM (id_film)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_frame_utente_creatore
        FOREIGN KEY (id_utente_creatore)
        REFERENCES UTENTI (id_utente)
        ON DELETE RESTRICT
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

CREATE TABLE TAG_VOTI (
    id_tag INT UNSIGNED NOT NULL,
    id_utente INT UNSIGNED NOT NULL,
    upvote TINYINT(1) NOT NULL DEFAULT 0,
    downvote TINYINT(1) NOT NULL DEFAULT 0,
    creato_il TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    aggiornato_il TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_tag, id_utente),
    CONSTRAINT chk_tag_voti_singolo CHECK (
        (upvote = 1 AND downvote = 0) OR
        (upvote = 0 AND downvote = 1)
    ),
    CONSTRAINT fk_tag_voti_tag
        FOREIGN KEY (id_tag)
        REFERENCES TAGS (id_tag)
        ON DELETE CASCADE,
    CONSTRAINT fk_tag_voti_utente
        FOREIGN KEY (id_utente)
        REFERENCES UTENTI (id_utente)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE FRAME_VOTI (
    id_frame INT UNSIGNED NOT NULL,
    id_utente INT UNSIGNED NOT NULL,
    upvote TINYINT(1) NOT NULL DEFAULT 0,
    downvote TINYINT(1) NOT NULL DEFAULT 0,
    creato_il TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    aggiornato_il TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_frame, id_utente),
    CONSTRAINT chk_frame_voti_singolo CHECK (
        (upvote = 1 AND downvote = 0) OR
        (upvote = 0 AND downvote = 1)
    ),
    CONSTRAINT fk_frame_voti_frame
        FOREIGN KEY (id_frame)
        REFERENCES FRAME (id_frame)
        ON DELETE CASCADE,
    CONSTRAINT fk_frame_voti_utente
        FOREIGN KEY (id_utente)
        REFERENCES UTENTI (id_utente)
        ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO UTENTI (username, email, password_hash, ruolo) VALUES
('admin', 'admin@cinespecs.local', '$2y$12$J7rMGw5Yq45TlX30pWqVVOIFIGsFCys2Xz2989Yljk0axFxxdPIkG', 'admin'),
('marta', 'marta@cinespecs.local', '$2y$12$J7rMGw5Yq45TlX30pWqVVOIFIGsFCys2Xz2989Yljk0axFxxdPIkG', 'contributor'),
('christian', 'Christian@cinespecs.local', '$2y$12$J7rMGw5Yq45TlX30pWqVVOIFIGsFCys2Xz2989Yljk0axFxxdPIkG', 'contributor');

INSERT INTO FILM (id_utente_creatore, titolo, anno_uscita, regista, sinossi, copertina_path) VALUES
(1, 'Alien', 1979, 'Ridley Scott', 'L''equipaggio della Nostromo affronta una minaccia aliena.', 'assets/covers/alien.jpg'),
(2, 'WarGames', 1983, 'John Badham', 'Un adolescente entra per errore in un sistema militare.', 'assets/covers/wargames.jpg'),
(2, 'Ritorno al futuro', 1985, 'Robert Zemeckis', 'Un ragazzo viaggia accidentalmente nel passato con una DeLorean modificata.', 'assets/covers/RAF1.jpg');

INSERT INTO HARDWARE (id_utente_creatore, nome_modello, produttore, anno_rilascio, descrizione, curiosita, prop_fittizio) VALUES
(1, 'DEC VT100', 'Digital Equipment Corporation', 1978, 'Terminale video molto usato come riferimento visivo nel cinema.', 'Appare in molte produzioni fantascientifiche e tecnologiche.', 0),
(2, 'IMSAI 8080', 'IMS Associates, Inc.', 1975, 'Microcomputer storico associato all''immaginario hacker degli anni 80.', 'Diventato iconico anche grazie al cinema e alla TV.', 0),
(1, 'JVC GR-C1', 'JVC', 1984, 'Videocamera portatile a cassette compatte, usata da Marty per registrare l''esperimento nel parcheggio.', 'E un esempio riconoscibile della tecnologia video consumer degli anni 80.', 0),
(1, 'Flux Capacitor', 'Emmett Brown', 1985, 'Dispositivo fittizio che rende possibile il viaggio nel tempo nella DeLorean.', 'Nel film viene presentato come l''invenzione decisiva di Doc Brown.', 1),
(2, 'Time Circuits Display', 'Emmett Brown', 1985, 'Pannello fittizio della DeLorean con destinazione, presente e ultima partenza.', 'Serve al viewer per testare un tag su un dettaglio elettronico ben visibile.', 1);

INSERT INTO FRAME (id_film, id_utente_creatore, immagine_path, timestamp_frame, descrizione_scena) VALUES
(1, 1, 'assets/frames/alien-console.jpg', '00:12:45', 'Console di bordo della Nostromo in primo piano.'),
(2, 2, 'assets/frames/wargames-imsai.jpg', '00:07:18', 'Postazione con microcomputer nella camera del protagonista.'),
(3, 1, 'assets/frames/bttf-camcorder.jpg', '00:21:30', 'Marty riprende Doc durante il primo esperimento nel parcheggio.'),
(3, 1, 'assets/frames/bttf-flux-capacitor.jpg', '00:25:05', 'Dettaglio del dispositivo installato nella DeLorean.'),
(3, 2, 'assets/frames/bttf-time-circuits.jpg', '00:27:42', 'Doc mostra i circuiti temporali della DeLorean.');

INSERT INTO TAGS (id_frame, id_hardware, id_utente, coord_x, coord_y) VALUES
(1, 1, 1, 46.50, 61.20),
(2, 2, 2, 38.00, 54.40),
(3, 3, 1, 28.00, 45.00),
(4, 4, 1, 48.50, 55.00),
(5, 5, 2, 49.00, 42.00);

INSERT INTO TAG_VOTI (id_tag, id_utente, upvote, downvote) VALUES
(1, 2, 1, 0),
(2, 1, 1, 0),
(3, 2, 1, 0),
(4, 2, 0, 1),
(5, 1, 1, 0);

INSERT INTO FRAME_VOTI (id_frame, id_utente, upvote, downvote) VALUES
(1, 2, 1, 0),
(2, 1, 1, 0),
(3, 2, 1, 0),
(4, 2, 0, 1),
(5, 1, 1, 0);
