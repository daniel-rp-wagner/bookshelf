-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Erstellungszeit: 10. Mai 2025 um 20:28
-- Server-Version: 10.4.32-MariaDB
-- PHP-Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `shelf`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `biographies`
--

CREATE TABLE `biographies` (
  `id` int(11) NOT NULL,
  `person_id` int(11) NOT NULL,
  `lang` enum('de','en','fr','it','ru','es','nl','la') NOT NULL COMMENT 'language code (ISO-639 Set 1)',
  `bio` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cities`
--

CREATE TABLE `cities` (
  `id` int(11) NOT NULL COMMENT 'unique id for each city',
  `country_iso` char(2) DEFAULT NULL COMMENT 'iso code of the country the city belongs to',
  `parent_city_id` int(11) DEFAULT NULL COMMENT 'references the parent city, if applicable',
  `type` varchar(10) DEFAULT NULL COMMENT 'type of this place, e.g. city, island or region'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `city_coordinates`
--

CREATE TABLE `city_coordinates` (
  `city_id` int(11) NOT NULL COMMENT 'references the city',
  `latitude` double NOT NULL COMMENT 'latitude coordinate of the city',
  `longitude` double NOT NULL COMMENT 'longitude coordinate of the city'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `city_names`
--

CREATE TABLE `city_names` (
  `name_id` int(11) NOT NULL COMMENT 'unique id for each alternative name',
  `city_id` int(11) DEFAULT NULL COMMENT 'references the city this name belongs to, if applicable',
  `language_code` char(2) DEFAULT NULL COMMENT 'language code (iso 639-1) for an alternative name, on if the name is the official name',
  `name` varchar(255) NOT NULL COMMENT 'alternative name of the city'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `countries`
--

CREATE TABLE `countries` (
  `iso_code` char(2) NOT NULL COMMENT 'iso code of the country (e.g., de, fr)',
  `name_de` varchar(64) NOT NULL COMMENT 'country name in german',
  `name_fr` varchar(64) NOT NULL COMMENT 'country name in french',
  `name_la` varchar(64) NOT NULL COMMENT 'country name in latin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `i8n`
--

CREATE TABLE `i8n` (
  `id` int(11) NOT NULL COMMENT 'unique id for this key',
  `variable` varchar(10) NOT NULL COMMENT 'the key to be translated',
  `lang` enum('de','fr','la','') NOT NULL COMMENT 'language code (ISO-639 Set 1)',
  `translation` varchar(128) NOT NULL COMMENT 'the value to be displayed',
  `translation_alt` varchar(128) DEFAULT NULL COMMENT 'Variant of this word, e.g. female variants'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `organizations`
--

CREATE TABLE `organizations` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL COMMENT 'preferred display name',
  `established_year` smallint(6) DEFAULT NULL COMMENT 'year of establishment',
  `terminated_year` smallint(6) DEFAULT NULL COMMENT 'year of termination'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `organization_aliases`
--

CREATE TABLE `organization_aliases` (
  `org_id` int(11) NOT NULL COMMENT 'foreign key from table organizations',
  `name` varchar(255) NOT NULL COMMENT 'different notations of a company'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `organization_cities`
--

CREATE TABLE `organization_cities` (
  `org_id` int(11) NOT NULL COMMENT 'foreign key from table organizations',
  `city_id` int(11) NOT NULL COMMENT 'unique ID from https://www.geonames.org/'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `organization_description`
--

CREATE TABLE `organization_description` (
  `org_id` int(11) NOT NULL COMMENT 'foreign key from table organizations',
  `lang` enum('de','en','fr','it','ru','es','nl','la') NOT NULL COMMENT 'language code (ISO-639 Set 1)',
  `description` text DEFAULT NULL COMMENT 'description, mostly from https://lobid.org/gnd'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `organization_rels`
--

CREATE TABLE `organization_rels` (
  `org_id` int(11) NOT NULL COMMENT 'foreign key from table organizations',
  `child_org_id` int(11) NOT NULL COMMENT 'foreign key from table organizations',
  `type` enum('pre','suc') DEFAULT NULL COMMENT 'describes the kind of relationship, e.g. preceeding or succeeding'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `organization_sources`
--

CREATE TABLE `organization_sources` (
  `org_id` int(11) NOT NULL COMMENT 'foreign key from table organizations',
  `title` varchar(255) DEFAULT NULL COMMENT 'display title',
  `url` varchar(255) DEFAULT NULL COMMENT 'url to source (mostly lobid.org)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `organization_types`
--

CREATE TABLE `organization_types` (
  `org_id` int(11) NOT NULL COMMENT 'foreign key from table organizations',
  `type` varchar(64) DEFAULT NULL COMMENT 'keys which are translated from table i8n'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `persons`
--

CREATE TABLE `persons` (
  `id` int(11) NOT NULL,
  `honorificPrefix` varchar(10) DEFAULT NULL,
  `first_name` varchar(64) NOT NULL,
  `nobility_particle` varchar(10) DEFAULT NULL,
  `last_name` varchar(64) NOT NULL,
  `religion` varchar(1) DEFAULT NULL,
  `birth_city_id` int(11) DEFAULT NULL,
  `death_city_id` int(11) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `date_of_death` date DEFAULT NULL,
  `nationality` varchar(2) DEFAULT NULL,
  `gender` enum('M','F') DEFAULT NULL,
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tags`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `person_aliases`
--

CREATE TABLE `person_aliases` (
  `person_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL COMMENT 'e.g. abbrevation, complete, birth name, alias, etc.',
  `lang` enum('de','en','fr','it','ru','es','nl','la') DEFAULT NULL COMMENT 'language code (ISO-639 Set 1)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `person_professions`
--

CREATE TABLE `person_professions` (
  `person_id` int(11) NOT NULL COMMENT 'id of the person',
  `profession` varchar(10) NOT NULL COMMENT 'references i8n keys starting with prof'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `person_sources`
--

CREATE TABLE `person_sources` (
  `person_id` int(11) NOT NULL COMMENT 'foreign key from table persons',
  `title` varchar(255) DEFAULT NULL COMMENT 'display title',
  `url` varchar(1024) DEFAULT NULL COMMENT 'url to source'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `publications`
--

CREATE TABLE `publications` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `language` char(2) DEFAULT NULL,
  `refs` varchar(255) DEFAULT NULL,
  `summary` text DEFAULT NULL,
  `series_id` int(11) DEFAULT NULL,
  `series_note` varchar(255) DEFAULT NULL,
  `sort_index` tinyint(4) DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `tags` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `series`
--

CREATE TABLE `series` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `subtitle` varchar(255) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `series_organization`
--

CREATE TABLE `series_organization` (
  `series_id` int(11) NOT NULL,
  `org_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `series_person`
--

CREATE TABLE `series_person` (
  `series_id` int(11) NOT NULL,
  `person_id` int(11) NOT NULL,
  `role` enum('editor','compiler','other') NOT NULL DEFAULT 'editor'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `volumes`
--

CREATE TABLE `volumes` (
  `id` int(11) NOT NULL,
  `publication_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `edition` varchar(50) DEFAULT NULL,
  `year` smallint(6) DEFAULT NULL,
  `pages` int(11) DEFAULT NULL,
  `collation` varchar(255) DEFAULT NULL,
  `volume_number` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `volume_persons`
--

CREATE TABLE `volume_persons` (
  `volume_id` int(11) NOT NULL,
  `person_id` int(11) NOT NULL,
  `role` enum('author','contributor','editor','translator','illustrator','about','other') NOT NULL DEFAULT 'author'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `works`
--

CREATE TABLE `works` (
  `id` int(11) NOT NULL,
  `person_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `lang` enum('de','en','fr','it','ru','es','nl','la') NOT NULL COMMENT 'language code (ISO-639 Set 1)',
  `genre` varchar(100) DEFAULT NULL,
  `year` year(4) DEFAULT NULL,
  `medium` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `link` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `work_translations`
--

CREATE TABLE `work_translations` (
  `id` int(11) NOT NULL,
  `work_id` int(11) NOT NULL,
  `lang` char(2) NOT NULL,
  `title` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `biographies`
--
ALTER TABLE `biographies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `person_id` (`person_id`,`lang`);

--
-- Indizes für die Tabelle `cities`
--
ALTER TABLE `cities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `country_iso` (`country_iso`),
  ADD KEY `idx_parent_city_id` (`parent_city_id`);

--
-- Indizes für die Tabelle `city_coordinates`
--
ALTER TABLE `city_coordinates`
  ADD PRIMARY KEY (`city_id`);

--
-- Indizes für die Tabelle `city_names`
--
ALTER TABLE `city_names`
  ADD PRIMARY KEY (`name_id`),
  ADD KEY `idx_city_language` (`city_id`,`language_code`);

--
-- Indizes für die Tabelle `countries`
--
ALTER TABLE `countries`
  ADD PRIMARY KEY (`iso_code`);

--
-- Indizes für die Tabelle `i8n`
--
ALTER TABLE `i8n`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `organizations`
--
ALTER TABLE `organizations`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `organization_aliases`
--
ALTER TABLE `organization_aliases`
  ADD KEY `idx_organization_aliases_org_id` (`org_id`);

--
-- Indizes für die Tabelle `organization_cities`
--
ALTER TABLE `organization_cities`
  ADD PRIMARY KEY (`org_id`,`city_id`),
  ADD KEY `city_id` (`city_id`),
  ADD KEY `idx_organization_cities_org_id` (`org_id`);

--
-- Indizes für die Tabelle `organization_description`
--
ALTER TABLE `organization_description`
  ADD PRIMARY KEY (`org_id`,`lang`),
  ADD KEY `idx_organization_description_lang` (`lang`);

--
-- Indizes für die Tabelle `organization_rels`
--
ALTER TABLE `organization_rels`
  ADD KEY `child_org_id` (`child_org_id`),
  ADD KEY `idx_organization_rels_org_id` (`org_id`);

--
-- Indizes für die Tabelle `organization_sources`
--
ALTER TABLE `organization_sources`
  ADD KEY `idx_organization_sources_org_id` (`org_id`);

--
-- Indizes für die Tabelle `organization_types`
--
ALTER TABLE `organization_types`
  ADD KEY `idx_organization_types_org_id` (`org_id`);

--
-- Indizes für die Tabelle `persons`
--
ALTER TABLE `persons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `persons_ibfk_1` (`birth_city_id`),
  ADD KEY `persons_ibfk_2` (`death_city_id`);

--
-- Indizes für die Tabelle `person_aliases`
--
ALTER TABLE `person_aliases`
  ADD PRIMARY KEY (`person_id`,`name`),
  ADD KEY `idx_person_aliases_lang` (`lang`);

--
-- Indizes für die Tabelle `person_professions`
--
ALTER TABLE `person_professions`
  ADD PRIMARY KEY (`person_id`,`profession`),
  ADD KEY `idx_person_professions_person` (`person_id`);

--
-- Indizes für die Tabelle `person_sources`
--
ALTER TABLE `person_sources`
  ADD KEY `idx_person_sources_org_id` (`person_id`);

--
-- Indizes für die Tabelle `publications`
--
ALTER TABLE `publications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `series_id` (`series_id`);

--
-- Indizes für die Tabelle `series`
--
ALTER TABLE `series`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `series_organization`
--
ALTER TABLE `series_organization`
  ADD PRIMARY KEY (`series_id`,`org_id`),
  ADD KEY `org_id` (`org_id`);

--
-- Indizes für die Tabelle `series_person`
--
ALTER TABLE `series_person`
  ADD PRIMARY KEY (`series_id`,`person_id`),
  ADD KEY `person_id` (`person_id`);

--
-- Indizes für die Tabelle `volumes`
--
ALTER TABLE `volumes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `publication_id` (`publication_id`);

--
-- Indizes für die Tabelle `volume_persons`
--
ALTER TABLE `volume_persons`
  ADD PRIMARY KEY (`person_id`,`volume_id`),
  ADD KEY `volume_id` (`volume_id`);

--
-- Indizes für die Tabelle `works`
--
ALTER TABLE `works`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_works_person` (`person_id`);

--
-- Indizes für die Tabelle `work_translations`
--
ALTER TABLE `work_translations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_translation_work` (`work_id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `biographies`
--
ALTER TABLE `biographies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `cities`
--
ALTER TABLE `cities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'unique id for each city';

--
-- AUTO_INCREMENT für Tabelle `city_names`
--
ALTER TABLE `city_names`
  MODIFY `name_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'unique id for each alternative name';

--
-- AUTO_INCREMENT für Tabelle `i8n`
--
ALTER TABLE `i8n`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'unique id for this key';

--
-- AUTO_INCREMENT für Tabelle `organizations`
--
ALTER TABLE `organizations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `persons`
--
ALTER TABLE `persons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `series`
--
ALTER TABLE `series`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `works`
--
ALTER TABLE `works`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `work_translations`
--
ALTER TABLE `work_translations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `biographies`
--
ALTER TABLE `biographies`
  ADD CONSTRAINT `biographies_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `persons` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `cities`
--
ALTER TABLE `cities`
  ADD CONSTRAINT `cities_ibfk_1` FOREIGN KEY (`country_iso`) REFERENCES `countries` (`iso_code`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cities_ibfk_2` FOREIGN KEY (`parent_city_id`) REFERENCES `cities` (`id`) ON DELETE SET NULL;

--
-- Constraints der Tabelle `city_coordinates`
--
ALTER TABLE `city_coordinates`
  ADD CONSTRAINT `city_coordinates_ibfk_1` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `city_names`
--
ALTER TABLE `city_names`
  ADD CONSTRAINT `city_names_ibfk_1` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `organization_aliases`
--
ALTER TABLE `organization_aliases`
  ADD CONSTRAINT `organization_aliases_ibfk_1` FOREIGN KEY (`org_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `organization_cities`
--
ALTER TABLE `organization_cities`
  ADD CONSTRAINT `organization_cities_ibfk_1` FOREIGN KEY (`org_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `organization_cities_ibfk_2` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `organization_description`
--
ALTER TABLE `organization_description`
  ADD CONSTRAINT `organization_description_ibfk_1` FOREIGN KEY (`org_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `organization_rels`
--
ALTER TABLE `organization_rels`
  ADD CONSTRAINT `organization_rels_ibfk_1` FOREIGN KEY (`org_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `organization_rels_ibfk_2` FOREIGN KEY (`child_org_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `organization_sources`
--
ALTER TABLE `organization_sources`
  ADD CONSTRAINT `organization_sources_ibfk_1` FOREIGN KEY (`org_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `organization_types`
--
ALTER TABLE `organization_types`
  ADD CONSTRAINT `organization_types_ibfk_1` FOREIGN KEY (`org_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `persons`
--
ALTER TABLE `persons`
  ADD CONSTRAINT `persons_ibfk_1` FOREIGN KEY (`birth_city_id`) REFERENCES `cities` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `persons_ibfk_2` FOREIGN KEY (`death_city_id`) REFERENCES `cities` (`id`) ON DELETE SET NULL;

--
-- Constraints der Tabelle `person_aliases`
--
ALTER TABLE `person_aliases`
  ADD CONSTRAINT `person_aliases_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `persons` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `person_professions`
--
ALTER TABLE `person_professions`
  ADD CONSTRAINT `person_professions_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `persons` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `person_sources`
--
ALTER TABLE `person_sources`
  ADD CONSTRAINT `person_sources_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `persons` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `publications`
--
ALTER TABLE `publications`
  ADD CONSTRAINT `publications_ibfk_1` FOREIGN KEY (`series_id`) REFERENCES `series` (`id`);

--
-- Constraints der Tabelle `series_organization`
--
ALTER TABLE `series_organization`
  ADD CONSTRAINT `series_organization_ibfk_1` FOREIGN KEY (`series_id`) REFERENCES `series` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `series_organization_ibfk_2` FOREIGN KEY (`org_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `series_person`
--
ALTER TABLE `series_person`
  ADD CONSTRAINT `series_person_ibfk_1` FOREIGN KEY (`series_id`) REFERENCES `series` (`id`),
  ADD CONSTRAINT `series_person_ibfk_2` FOREIGN KEY (`person_id`) REFERENCES `persons` (`id`);

--
-- Constraints der Tabelle `volumes`
--
ALTER TABLE `volumes`
  ADD CONSTRAINT `volumes_ibfk_1` FOREIGN KEY (`publication_id`) REFERENCES `publications` (`id`);

--
-- Constraints der Tabelle `volume_persons`
--
ALTER TABLE `volume_persons`
  ADD CONSTRAINT `volume_persons_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `persons` (`id`),
  ADD CONSTRAINT `volume_persons_ibfk_2` FOREIGN KEY (`volume_id`) REFERENCES `volumes` (`id`);

--
-- Constraints der Tabelle `works`
--
ALTER TABLE `works`
  ADD CONSTRAINT `fk_works_person` FOREIGN KEY (`person_id`) REFERENCES `persons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `work_translations`
--
ALTER TABLE `work_translations`
  ADD CONSTRAINT `fk_translation_work` FOREIGN KEY (`work_id`) REFERENCES `works` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
