-- TudásBázis - Adatbázis Séma
-- Gyakorikerdesek.hu Clone

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Adatbázis létrehozása
CREATE DATABASE IF NOT EXISTS `tudasbazis` 
    DEFAULT CHARACTER SET utf8mb4 
    COLLATE utf8mb4_hungarian_ci;

USE `tudasbazis`;

-- ========================================
-- USERS (Felhasználók) tábla
-- ========================================
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `felhasznalonev` VARCHAR(50) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `jelszo_hash` VARCHAR(255) NOT NULL,
    `szerep` ENUM('user', 'moderator', 'admin') DEFAULT 'user',
    `reputacio_szazalek` DECIMAL(5,2) DEFAULT 0.00,
    `nev_latszik` TINYINT(1) DEFAULT 0 COMMENT 'Látsszon-e a név a kérdéseknél',
    `letrehozva` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `utolso_belepes` DATETIME NULL,
    `tiltva` TINYINT(1) DEFAULT 0,
    `tiltas_oka` VARCHAR(255) NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_felhasznalonev` (`felhasznalonev`),
    UNIQUE KEY `uk_email` (`email`),
    INDEX `idx_szerep` (`szerep`),
    INDEX `idx_reputacio` (`reputacio_szazalek`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

-- ========================================
-- CATEGORIES (Kategóriák) tábla - Rekurzív fa szerkezet
-- ========================================
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nev` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL,
    `szulo_id` INT UNSIGNED NULL COMMENT 'Szülő kategória (NULL = főkategória)',
    `sorrend` INT DEFAULT 0,
    `aktiv` TINYINT(1) DEFAULT 1,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_slug` (`slug`),
    INDEX `idx_szulo` (`szulo_id`),
    CONSTRAINT `fk_categories_szulo` 
        FOREIGN KEY (`szulo_id`) REFERENCES `categories` (`id`) 
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

-- ========================================
-- QUESTIONS (Kérdések) tábla
-- ========================================
DROP TABLE IF EXISTS `questions`;
CREATE TABLE `questions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `felhasznalo_id` INT UNSIGNED NOT NULL,
    `kategoria_id` INT UNSIGNED NOT NULL,
    `cim` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `tartalom` TEXT NOT NULL,
    `valaszok_szama` INT UNSIGNED DEFAULT 0,
    `megtekintesek` INT UNSIGNED DEFAULT 0,
    `letrehozva` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `frissitve` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    `lezarva` TINYINT(1) DEFAULT 0,
    PRIMARY KEY (`id`),
    INDEX `idx_felhasznalo` (`felhasznalo_id`),
    INDEX `idx_kategoria` (`kategoria_id`),
    INDEX `idx_letrehozva` (`letrehozva`),
    FULLTEXT INDEX `ft_kereses` (`cim`, `tartalom`),
    CONSTRAINT `fk_questions_felhasznalo` 
        FOREIGN KEY (`felhasznalo_id`) REFERENCES `users` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_questions_kategoria` 
        FOREIGN KEY (`kategoria_id`) REFERENCES `categories` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

-- ========================================
-- ANSWERS (Válaszok) tábla
-- ========================================
DROP TABLE IF EXISTS `answers`;
CREATE TABLE `answers` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `kerdes_id` INT UNSIGNED NOT NULL,
    `felhasznalo_id` INT UNSIGNED NOT NULL,
    `tartalom` TEXT NOT NULL,
    `hasznos_szavazat` INT UNSIGNED DEFAULT 0,
    `nem_hasznos_szavazat` INT UNSIGNED DEFAULT 0,
    `letrehozva` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `frissitve` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_kerdes` (`kerdes_id`),
    INDEX `idx_felhasznalo` (`felhasznalo_id`),
    INDEX `idx_letrehozva` (`letrehozva`),
    CONSTRAINT `fk_answers_kerdes` 
        FOREIGN KEY (`kerdes_id`) REFERENCES `questions` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_answers_felhasznalo` 
        FOREIGN KEY (`felhasznalo_id`) REFERENCES `users` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

-- ========================================
-- VOTES (Szavazatok) tábla
-- ========================================
DROP TABLE IF EXISTS `votes`;
CREATE TABLE `votes` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `felhasznalo_id` INT UNSIGNED NOT NULL,
    `valasz_id` INT UNSIGNED NOT NULL,
    `irany` TINYINT NOT NULL COMMENT '+1 = hasznos, -1 = nem hasznos',
    `letrehozva` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_felhasznalo_valasz` (`felhasznalo_id`, `valasz_id`),
    INDEX `idx_valasz` (`valasz_id`),
    CONSTRAINT `fk_votes_felhasznalo` 
        FOREIGN KEY (`felhasznalo_id`) REFERENCES `users` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_votes_valasz` 
        FOREIGN KEY (`valasz_id`) REFERENCES `answers` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

-- ========================================
-- Alapértelmezett kategóriák (a gyakorikerdesek.hu alapján)
-- ========================================
INSERT INTO `categories` (`nev`, `slug`, `szulo_id`, `sorrend`) VALUES
('Állatok', 'allatok', NULL, 1),
('Családi kapcsolatok', 'csaladi-kapcsolatok', NULL, 2),
('Egészség', 'egeszseg', NULL, 3),
('Elektronikus eszközök', 'elektronikus-eszkozok', NULL, 4),
('Emberek', 'emberek', NULL, 5),
('Ételek, italok', 'etelek-italok', NULL, 6),
('Ezotéria', 'ezoteria', NULL, 7),
('Felnőtt párkapcsolatok', 'felnott-parkapcsolatok', NULL, 8),
('Fogyókúrák', 'fogyokurak', NULL, 9),
('Gyerekvállalás, nevelés', 'gyerekvallalas-neveles', NULL, 10),
('Ismerkedés', 'ismerkedes', NULL, 11),
('Közlekedés', 'kozlekedes', NULL, 12),
('Közoktatás, tanfolyamok', 'kozoktatas-tanfolyamok', NULL, 13),
('Kultúra és közösség', 'kultura-es-kozosseg', NULL, 14),
('Otthon', 'otthon', NULL, 15),
('Politika', 'politika', NULL, 16),
('Sport, mozgás', 'sport-mozgas', NULL, 17),
('Számítástechnika', 'szamitastechnika', NULL, 18),
('Szépség és divat', 'szepseg-es-divat', NULL, 19),
('Szexualitás', 'szexualitas', NULL, 20),
('Szórakozás', 'szorakozas', NULL, 21),
('Tini párkapcsolatok', 'tini-parkapcsolatok', NULL, 22),
('Tudományok', 'tudomanyok', NULL, 23),
('Utazás', 'utazas', NULL, 24),
('Ünnepek', 'unnepek', NULL, 25),
('Üzlet és pénzügyek', 'uzlet-es-penzugyek', NULL, 26),
('Egyéb kérdések', 'egyeb-kerdesek', NULL, 27);

-- Alkategóriák (példa)
INSERT INTO `categories` (`nev`, `slug`, `szulo_id`, `sorrend`) VALUES
-- Állatok alkategóriák
('Kutyák', 'kutyak', 1, 1),
('Macskák', 'macskak', 1, 2),
('Madarak', 'madarak', 1, 3),
('Halak', 'halak', 1, 4),
('Egyéb állatok', 'egyeb-allatok', 1, 5),

-- Számítástechnika alkategóriák
('Programozás', 'programozas', 18, 1),
('Weblapkészítés', 'weblapkeszites', 18, 2),
('Windows', 'windows', 18, 3),
('Hardver', 'hardver', 18, 4),
('Internet', 'internet', 18, 5),

-- Egészség alkategóriák
('Mentális egészség', 'mentalis-egeszseg', 3, 1),
('Betegségek', 'betegsegek', 3, 2),
('Gyógyszerek', 'gyogyszerek', 3, 3),
('Egyéb egészség', 'egyeb-egeszseg', 3, 4),

-- Politika alkategóriák
('Magyar politika', 'magyar-politika', 16, 1),
('Nemzetközi politika', 'nemzetkozi-politika', 16, 2),

-- Családi kapcsolatok alkategóriák
('Szülő-gyermek kapcsolat', 'szulo-gyermek-kapcsolat', 2, 1),
('Anyós, após', 'anyos-apos', 2, 2),
('Testvérek', 'testverek', 2, 3),
('Egyéb családi', 'egyeb-csaladi', 2, 4);

SET FOREIGN_KEY_CHECKS = 1;
