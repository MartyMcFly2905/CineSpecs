-- Progettazione Web 
DROP DATABASE if exists cantavenera_673569; 
CREATE DATABASE cantavenera_673569; 
USE cantavenera_673569; 
-- MySQL dump 10.13  Distrib 5.7.28, for Win64 (x86_64)
--
-- Host: localhost    Database: cantavenera_673569
-- ------------------------------------------------------
-- Server version	5.7.28

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `film`
--

DROP TABLE IF EXISTS `film`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `film` (
  `id_film` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_utente_creatore` int(10) unsigned NOT NULL,
  `titolo` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `anno_uscita` year(4) NOT NULL,
  `regista` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sinossi` text COLLATE utf8mb4_unicode_ci,
  `copertina_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `creato_il` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_film`),
  KEY `fk_film_utente_creatore` (`id_utente_creatore`),
  CONSTRAINT `fk_film_utente_creatore` FOREIGN KEY (`id_utente_creatore`) REFERENCES `utenti` (`id_utente`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `film`
--

LOCK TABLES `film` WRITE;
/*!40000 ALTER TABLE `film` DISABLE KEYS */;
INSERT INTO `film` VALUES (1,1,'Matrix',1999,'Lana Wachowski, Lilly Wachowski','Un programmatore di giorno e hacker di notte scopre che il mondo in cui vive non è altro che una simulazione informatica controllata da macchine senzienti, e viene reclutato per combatterle.','assets/covers/matrix.jpg','2026-07-18 15:29:11'),(2,2,'Ritorno al Futuro',1985,'Robert Zemeckis','Lo studente Marty McFly viene catapultato per errore nel 1955 a bordo di una DeLorean modificata dall\'eccentrico scienziato Doc Brown e deve trovare il modo di tornare al presente senza alterare il corso della storia.','assets/covers/ritorno_al_futuro.jpg','2026-07-18 15:29:11');
/*!40000 ALTER TABLE `film` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `frame`
--

DROP TABLE IF EXISTS `frame`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `frame` (
  `id_frame` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_film` int(10) unsigned NOT NULL,
  `id_utente_creatore` int(10) unsigned NOT NULL,
  `immagine_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `timestamp_frame` time DEFAULT NULL,
  `descrizione_scena` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `creato_il` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_frame`),
  KEY `fk_frame_film` (`id_film`),
  KEY `fk_frame_utente_creatore` (`id_utente_creatore`),
  CONSTRAINT `fk_frame_film` FOREIGN KEY (`id_film`) REFERENCES `film` (`id_film`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_frame_utente_creatore` FOREIGN KEY (`id_utente_creatore`) REFERENCES `utenti` (`id_utente`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `frame`
--

LOCK TABLES `frame` WRITE;
/*!40000 ALTER TABLE `frame` DISABLE KEYS */;
INSERT INTO `frame` VALUES (1,1,1,'assets/frames/matrix_neo_pc.jpg','00:08:15','Neo dorme alla scrivania: lo schermo del computer mostra un terminale attivo con testo verde su sfondo nero, mentre riceve il messaggio che lo invita a seguire il coniglio bianco.','2026-07-18 15:29:11'),(2,1,2,'assets/frames/matrix_neb_operators.jpg','01:02:30','Sala operatori della Nabucodonosor: Tank alla postazione principale, monitorando il codice di Matrix sui vari schermi per assistere l\'equipaggio durante una connessione.','2026-07-18 15:29:11'),(3,1,1,'assets/frames/matrix_construct.jpg','01:18:45','Morpheus mostra a Neo la realtà attraverso un vecchio televisore a tubo catodico, illustrando come la Matrix sia una prigione per la mente.','2026-07-18 15:29:11'),(4,2,1,'assets/frames/bttf_camcorder.jpg','00:21:30','Marty riprende Doc Brown con la JVC GR-C1 nel parcheggio del Twin Pines Mall, documentando il primo esperimento di viaggio nel tempo.','2026-07-18 15:29:11'),(5,2,2,'assets/frames/bttf_flux_capacitor.jpg','00:25:05','Doc mostra a Marty il Flux Capacitor installato nella DeLorean, spiegando che è ciò che rende possibile il viaggio nel tempo.','2026-07-18 15:29:11');
/*!40000 ALTER TABLE `frame` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `frame_voti`
--

DROP TABLE IF EXISTS `frame_voti`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `frame_voti` (
  `id_frame` int(10) unsigned NOT NULL,
  `id_utente` int(10) unsigned NOT NULL,
  `upvote` tinyint(1) NOT NULL DEFAULT '0',
  `downvote` tinyint(1) NOT NULL DEFAULT '0',
  `creato_il` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `aggiornato_il` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_frame`,`id_utente`),
  KEY `fk_frame_voti_utente` (`id_utente`),
  CONSTRAINT `fk_frame_voti_frame` FOREIGN KEY (`id_frame`) REFERENCES `frame` (`id_frame`) ON DELETE CASCADE,
  CONSTRAINT `fk_frame_voti_utente` FOREIGN KEY (`id_utente`) REFERENCES `utenti` (`id_utente`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `frame_voti`
--

LOCK TABLES `frame_voti` WRITE;
/*!40000 ALTER TABLE `frame_voti` DISABLE KEYS */;
INSERT INTO `frame_voti` VALUES (1,2,1,0,'2026-07-18 15:29:12','2026-07-18 15:29:12'),(1,3,1,0,'2026-07-18 15:29:12','2026-07-18 15:29:12'),(2,1,1,0,'2026-07-18 15:29:12','2026-07-18 15:29:12'),(2,2,0,1,'2026-07-18 15:29:12','2026-07-18 15:29:12'),(2,3,0,1,'2026-07-18 15:29:12','2026-07-18 15:29:12'),(3,1,1,0,'2026-07-18 15:29:12','2026-07-18 15:29:12'),(3,2,1,0,'2026-07-18 15:29:12','2026-07-18 15:29:12'),(3,3,1,0,'2026-07-18 15:29:12','2026-07-18 15:29:12'),(4,1,1,0,'2026-07-18 15:29:12','2026-07-18 15:29:12'),(4,2,0,1,'2026-07-18 15:29:12','2026-07-18 15:29:12'),(5,1,0,1,'2026-07-18 15:29:12','2026-07-18 15:29:12'),(5,3,0,1,'2026-07-18 15:29:12','2026-07-18 15:29:12');
/*!40000 ALTER TABLE `frame_voti` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hardware`
--

DROP TABLE IF EXISTS `hardware`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hardware` (
  `id_hardware` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_utente_creatore` int(10) unsigned NOT NULL,
  `nome_modello` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `produttore` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `anno_rilascio` smallint(6) DEFAULT NULL,
  `descrizione` text COLLATE utf8mb4_unicode_ci,
  `curiosita` text COLLATE utf8mb4_unicode_ci,
  `prop_fittizio` tinyint(1) NOT NULL DEFAULT '0',
  `immagine_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `creato_il` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_hardware`),
  KEY `fk_hardware_utente_creatore` (`id_utente_creatore`),
  CONSTRAINT `fk_hardware_utente_creatore` FOREIGN KEY (`id_utente_creatore`) REFERENCES `utenti` (`id_utente`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hardware`
--

LOCK TABLES `hardware` WRITE;
/*!40000 ALTER TABLE `hardware` DISABLE KEYS */;
INSERT INTO `hardware` VALUES (1,2,'Sun Ultra 5','Sun Microsystems',1998,'Workstation Unix professionale riconoscibile per il case beige e il logo Sun. Nel film appare sulla scrivania di Neo come terminale di sviluppo, coerente con il profilo di programmatore del personaggio.','La Sun Ultra 5 era una macchina diffusa nei dipartimenti universitari e nelle aziende tech a fine anni \'90, il che la rende un dettaglio di background credibile e preciso.',0,NULL,'2026-07-18 15:29:11'),(2,2,'Console dell\'Operatore','AMX / Digitron',1999,'Postazione multi-monitor della Nabucodonosor. I display sono privati della scocca originale (bezel) per esporre telai metallici e cavi a vista, creando un look hacker di recupero. Sugli schermi scorre il \"raw data\" di Matrix: la celebre pioggia digitale verde fosforo.','Per evitare i problemi di sfarfallio (flicker) dei vecchi monitor a tubo catodico ripresi dalla cinepresa, la produzione usò rari e costosi pannelli piatti. Questo permise riprese molto angolate senza mai far sbiadire i colori del codice.',1,NULL,'2026-07-18 15:29:11'),(3,1,'Vintage Deep Image CRT TV','AWA Radiola',1958,'Vecchio televisore a tubo catodico posizionato su un carrello. Morpheus lo accende all\'interno del Costrutto usando un telecomando per mostrare a Neo il vero aspetto del mondo reale.','Le Wachowski scelsero un televisore volutamente retrò e analogico per contrastare visivamente l\'iper-tecnologia digitale del mondo di Matrix.',0,NULL,'2026-07-18 15:29:11'),(4,1,'JVC GR-C1','JVC',1984,'Videocamera portatile consumer a cassette VHS-C. Marty la usa per documentare l\'esperimento del viaggio nel tempo di Doc Brown nel parcheggio del centro commerciale Twin Pines Mall.','La JVC GR-C1 fu una delle prime camcorder all-in-one sul mercato consumer. La sua comparsa in Ritorno al Futuro nel 1985 la rese un simbolo tecnologico dell\'era VHS.',0,NULL,'2026-07-18 15:29:11'),(5,1,'Flux Capacitor','Emmett Brown',1985,'Dispositivo fittizio a forma di Y installato nell\'abitacolo della DeLorean, composto da tre cilindri luminosi che pulsano quando il veicolo raggiunge le 88 miglia orarie necessarie al viaggio nel tempo.','Secondo il racconto di Doc Brown nel film, l\'idea del Flux Capacitor gli venne battendo la testa sul water il 5 novembre 1955. È l\'oggetto di scena più iconico dell\'intera trilogia.',1,NULL,'2026-07-18 15:29:11'),(6,2,'Time Circuits Display','Emmett Brown',1985,'Pannello digitale nel cruscotto della DeLorean che mostra tre date: destinazione, data attuale e ultima partenza. I display a LED rossi, verdi e gialli ne rendono la lettura immediata e scenografica.','I display furono realizzati con componenti elettronici reali dell\'epoca, inclusi segmenti LED da calcolatrice. Il pannello originale fu venduto all\'asta per oltre 500.000 dollari nel 2022.',1,NULL,'2026-07-18 15:29:11');
/*!40000 ALTER TABLE `hardware` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tag_voti`
--

DROP TABLE IF EXISTS `tag_voti`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tag_voti` (
  `id_tag` int(10) unsigned NOT NULL,
  `id_utente` int(10) unsigned NOT NULL,
  `upvote` tinyint(1) NOT NULL DEFAULT '0',
  `downvote` tinyint(1) NOT NULL DEFAULT '0',
  `creato_il` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `aggiornato_il` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_tag`,`id_utente`),
  KEY `fk_tag_voti_utente` (`id_utente`),
  CONSTRAINT `fk_tag_voti_tag` FOREIGN KEY (`id_tag`) REFERENCES `tags` (`id_tag`) ON DELETE CASCADE,
  CONSTRAINT `fk_tag_voti_utente` FOREIGN KEY (`id_utente`) REFERENCES `utenti` (`id_utente`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tag_voti`
--

LOCK TABLES `tag_voti` WRITE;
/*!40000 ALTER TABLE `tag_voti` DISABLE KEYS */;
INSERT INTO `tag_voti` VALUES (1,1,1,0,'2026-07-18 15:29:12','2026-07-18 15:29:12'),(1,2,1,0,'2026-07-18 15:29:12','2026-07-18 15:29:12'),(1,3,1,0,'2026-07-18 15:29:12','2026-07-18 15:29:12'),(2,1,0,1,'2026-07-18 15:29:12','2026-07-18 15:29:12'),(2,3,0,1,'2026-07-18 15:29:12','2026-07-18 15:29:12'),(3,1,1,0,'2026-07-18 15:29:12','2026-07-18 15:29:12'),(3,2,1,0,'2026-07-18 15:29:12','2026-07-18 15:29:12'),(3,3,0,1,'2026-07-18 15:29:12','2026-07-18 15:29:12'),(4,1,0,1,'2026-07-18 15:29:12','2026-07-18 15:29:12'),(4,2,1,0,'2026-07-18 15:29:12','2026-07-18 15:29:12'),(4,3,0,1,'2026-07-18 15:29:12','2026-07-18 15:29:12'),(5,2,1,0,'2026-07-18 15:29:12','2026-07-18 15:29:12'),(5,3,0,1,'2026-07-18 15:29:12','2026-07-18 15:29:12');
/*!40000 ALTER TABLE `tag_voti` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tags`
--

DROP TABLE IF EXISTS `tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tags` (
  `id_tag` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_frame` int(10) unsigned NOT NULL,
  `id_hardware` int(10) unsigned NOT NULL,
  `id_utente` int(10) unsigned NOT NULL,
  `coord_x` decimal(5,2) NOT NULL,
  `coord_y` decimal(5,2) NOT NULL,
  `creato_il` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_tag`),
  UNIQUE KEY `uq_tags_frame_hardware` (`id_frame`,`id_hardware`),
  KEY `fk_tags_hardware` (`id_hardware`),
  KEY `fk_tags_utente` (`id_utente`),
  CONSTRAINT `fk_tags_frame` FOREIGN KEY (`id_frame`) REFERENCES `frame` (`id_frame`) ON DELETE CASCADE,
  CONSTRAINT `fk_tags_hardware` FOREIGN KEY (`id_hardware`) REFERENCES `hardware` (`id_hardware`) ON DELETE CASCADE,
  CONSTRAINT `fk_tags_utente` FOREIGN KEY (`id_utente`) REFERENCES `utenti` (`id_utente`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tags`
--

LOCK TABLES `tags` WRITE;
/*!40000 ALTER TABLE `tags` DISABLE KEYS */;
INSERT INTO `tags` VALUES (1,1,1,1,50.00,43.00,'2026-07-18 15:29:11'),(2,2,2,2,50.00,22.00,'2026-07-18 15:29:11'),(3,3,3,1,48.00,60.00,'2026-07-18 15:29:11'),(4,4,4,1,20.00,45.00,'2026-07-18 15:29:11'),(5,5,5,2,55.00,44.00,'2026-07-18 15:29:11');
/*!40000 ALTER TABLE `tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `utenti`
--

DROP TABLE IF EXISTS `utenti`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `utenti` (
  `id_utente` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ruolo` enum('contributor','admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'contributor',
  `creato_il` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_utente`),
  UNIQUE KEY `uq_utenti_username` (`username`),
  UNIQUE KEY `uq_utenti_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `utenti`
--

LOCK TABLES `utenti` WRITE;
/*!40000 ALTER TABLE `utenti` DISABLE KEYS */;
INSERT INTO `utenti` VALUES (1,'admin','admin@cinespecs.local','$2y$12$J7rMGw5Yq45TlX30pWqVVOIFIGsFCys2Xz2989Yljk0axFxxdPIkG','admin','2026-07-18 15:29:11'),(2,'contributor1','contributor1@cinespecs.local','$2y$12$J7rMGw5Yq45TlX30pWqVVOIFIGsFCys2Xz2989Yljk0axFxxdPIkG','contributor','2026-07-18 15:29:11'),(3,'contributor2','contributor2@cinespecs.local','$2y$12$J7rMGw5Yq45TlX30pWqVVOIFIGsFCys2Xz2989Yljk0axFxxdPIkG','contributor','2026-07-18 15:29:11');
/*!40000 ALTER TABLE `utenti` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-18 17:29:35
