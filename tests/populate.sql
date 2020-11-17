-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Feb 02, 2019 at 07:55 PM
-- Server version: 5.7.23
-- PHP Version: 7.2.10

SET NAMES 'utf8';
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `mesurelocal`
--

-- --------------------------------------------------------

--
-- Table structure for table `a_c_l`
--

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `a_c_l`;
CREATE TABLE IF NOT EXISTS `a_c_l` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_doc` int(11) DEFAULT NULL,
  `id_evaluator` int(11) DEFAULT NULL,
  `id_patient` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_C2A8AF8518E5153E` (`id_doc`),
  KEY `IDX_C2A8AF85F53E2428` (`id_evaluator`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `a_c_l`
--

INSERT INTO `a_c_l` (`id`, `id_doc`, `id_evaluator`, `id_patient`, `date`) VALUES
(3, 2, 6, 'pp_sDNYEhvn', NULL),
(4, 1, 5, 'pp_Qm16OsEi', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `crypto`
--

DROP TABLE IF EXISTS `crypto`;
CREATE TABLE IF NOT EXISTS `crypto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `encryptionKey` varchar(16) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `crypto`
--

INSERT INTO `crypto` (`id`, `encryptionKey`) VALUES
(1, 'AFX8963ZzxywVpA6');

-- --------------------------------------------------------

--
-- Table structure for table `healthCare`
--

DROP TABLE IF EXISTS `healthCare`;
CREATE TABLE IF NOT EXISTS `healthCare` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8_unicode_ci,
  `sessionCount` int(11) NOT NULL,
  `closed` tinyint(1) NOT NULL,
  `idPatient` int(11) DEFAULT NULL,
  `idSpeciality` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_BE8DC591A63BC19` (`idPatient`),
  KEY `IDX_BE8DC5917123BE64` (`idSpeciality`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Table de soin';

--
-- Dumping data for table `healthCare`
--

INSERT INTO `healthCare` (`id`, `name`, `description`, `sessionCount`, `closed`, `idPatient`, `idSpeciality`) VALUES
(3, 'Mon soin', 'Test description soin', 2, 0, 1, 3),
(4, 'Soin pat3 par par1', 'Test du soin pour un paramédical', 2, 0, 3, 3);

-- --------------------------------------------------------

--
-- Table structure for table `healthSession`
--

DROP TABLE IF EXISTS `healthSession`;
CREATE TABLE IF NOT EXISTS `healthSession` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idhealthcare` int(11) DEFAULT NULL,
  `date` datetime NOT NULL,
  `comment` longtext COLLATE utf8_unicode_ci COMMENT 'Commentaire du médical quand il valide le soin',
  `idCaregiver` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_9D20E34ED3167795` (`idhealthcare`),
  KEY `IDX_9D20E34EDB43F593` (`idCaregiver`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `healthSession`
--

INSERT INTO `healthSession` (`id`, `idhealthcare`, `date`, `comment`, `idCaregiver`) VALUES
(4, 3, '2019-02-02 13:34:00', 'Test commentaire séance', 1);

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

DROP TABLE IF EXISTS `notification`;
CREATE TABLE IF NOT EXISTS `notification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `source` int(11) DEFAULT NULL,
  `target` int(11) DEFAULT NULL,
  `patient` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `date` datetime NOT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_BF5476CA5F8A7F73` (`source`),
  KEY `IDX_BF5476CA466F2FFC` (`target`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `notification`
--

INSERT INTO `notification` (`id`, `source`, `target`, `patient`, `date`, `type`) VALUES
(7, 1, 1, 'pp_CfUHnJQM', '2019-01-09 15:04:16', 'healthSessionAdd'),
(11, 1, 5, 'pp_CfUHnJQM', '2019-02-02 09:00:00', 'share');

-- --------------------------------------------------------

--
-- Table structure for table `patient`
--

DROP TABLE IF EXISTS `patient`;
CREATE TABLE IF NOT EXISTS `patient` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patientId` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `nom` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `prenom` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dateNaissance` datetime NOT NULL,
  `sexe` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `adresse` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tel` int(11) DEFAULT NULL,
  `socialnumber` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `medecinTraitant` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `proId` int(11) DEFAULT NULL,
  `public` tinyint(1) NOT NULL,
  `data` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:array)',
  `partage` int(11) NOT NULL,
  `archived` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_1ADAD7EB8F803478` (`patientId`),
  UNIQUE KEY `UNIQ_1ADAD7EB5638475A` (`socialnumber`),
  UNIQUE KEY `UNIQ_1ADAD7EBE7927C74` (`email`),
  KEY `IDX_1ADAD7EBD629A2D5` (`proId`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `patient`
--

INSERT INTO `patient` (`id`, `patientId`, `nom`, `prenom`, `email`, `dateNaissance`, `sexe`, `adresse`, `tel`, `socialnumber`, `medecinTraitant`, `proId`, `public`, `data`, `partage`, `archived`) VALUES
(1, 'pp_CfUHnJQM', 'pat1', 'pat1', 'pat@1.com', '1970-01-01 00:00:00', 'Masculin', '1 pat street', 102030405, '010203040506071', 'doc1', 1, 0, 'a:4:{s:8:\"comments\";a:0:{}s:6:\"images\";a:0:{}s:6:\"videos\";a:0:{}s:5:\"files\";a:0:{}}', 0, 0),
(2, 'pp_ZR8JZdhQ', 'pat2', 'pat2', 'pat@2.com', '1971-01-01 00:00:00', 'Masculin', '2 pat street', 102030405, '010203040506072', 'doc2', 1, 0, 'a:4:{s:8:\"comments\";a:0:{}s:6:\"images\";a:0:{}s:6:\"videos\";a:0:{}s:5:\"files\";a:0:{}}', 0, 0),
(3, 'pp_Qm16OsEi', 'pat3', 'pat3', 'pat@3.com', '1972-01-01 00:00:00', 'Masculin', '3 pat street', 102030405, '010203040506073', 'doc1', 1, 0, 'a:4:{s:8:\"comments\";a:0:{}s:6:\"images\";a:0:{}s:6:\"videos\";a:0:{}s:5:\"files\";a:0:{}}', 0, 0),
(4, 'pp_sDNYEhvn', 'pat4', 'pat4', 'pat@4.com', '1973-01-01 00:00:00', 'Masculin', '4 pat street', 102030405, '010203040506074', 'doc2', 2, 0, 'a:4:{s:8:\"comments\";a:0:{}s:6:\"images\";a:0:{}s:6:\"videos\";a:0:{}s:5:\"files\";a:0:{}}', 0, 0),
(5, 'pp_PXWZuoDB', 'pat5', 'pat5', 'pat@5.com', '1974-01-01 00:00:00', 'Masculin', '5 pat street', 102030405, '010203040506075', 'doc1', 1, 0, 'a:4:{s:8:\"comments\";a:0:{}s:6:\"images\";a:0:{}s:6:\"videos\";a:0:{}s:5:\"files\";a:0:{}}', 0, 0),
(6, 'pp_MdNpdIql', 'pat6', 'pat6', 'pat@6.com', '1975-01-01 00:00:00', 'Masculin', '6 pat street', 102030405, '010203040506076', 'doc2', 2, 0, 'a:4:{s:8:\"comments\";a:0:{}s:6:\"images\";a:0:{}s:6:\"videos\";a:0:{}s:5:\"files\";a:0:{}}', 0, 0),
(7, 'sd_pJAxF8pb', 'sec', 'delete', 'sec@delete.com', '1969-09-06 00:00:00', 'Masculin', NULL, NULL, '010203040506077', NULL, 1, 0, 'a:4:{s:8:\"comments\";a:0:{}s:6:\"images\";a:0:{}s:6:\"videos\";a:0:{}s:5:\"files\";a:0:{}}', 0, 0),
(8, 'dd_Np5ZH3jh', 'doc', 'delete', 'doc@delete.com', '1969-09-06 00:00:00', 'Masculin', NULL, NULL, '010203040506078', NULL, 1, 0, 'a:4:{s:8:\"comments\";a:0:{}s:6:\"images\";a:0:{}s:6:\"videos\";a:0:{}s:5:\"files\";a:0:{}}', 0, 0),
(9, 'pp_ZxZ28G6c', 'part1', 'part1', NULL, '1898-01-01 00:00:00', 'Masculin', NULL, NULL, NULL, NULL, 1, 0, 'a:4:{s:8:\"comments\";a:0:{}s:6:\"images\";a:0:{}s:6:\"videos\";a:0:{}s:5:\"files\";a:0:{}}', 0, 0),
(10, 'pp_12uDT6YI', 'part2', 'part2', NULL, '1898-01-01 00:00:00', 'Masculin', NULL, NULL, NULL, NULL, 1, 0, 'a:4:{s:8:\"comments\";a:0:{}s:6:\"images\";a:0:{}s:6:\"videos\";a:0:{}s:5:\"files\";a:0:{}}', 0, 0),
(11, 'pp_hj8fMoUw', 'part3', 'part3', NULL, '1898-01-01 00:00:00', 'Masculin', NULL, NULL, NULL, NULL, 1, 0, 'a:4:{s:8:\"comments\";a:0:{}s:6:\"images\";a:0:{}s:6:\"videos\";a:0:{}s:5:\"files\";a:0:{}}', 0, 0),
(12, 'aa_nCHFXhRl', 'archived', 'a', 'a@archived.com', '1899-01-01 00:00:00', 'Masculin', '1 rue des archives', NULL, NULL, NULL, 1, 0, 'a:4:{s:8:\"comments\";a:0:{}s:6:\"images\";a:0:{}s:6:\"videos\";a:0:{}s:5:\"files\";a:0:{}}', 0, 1),
(13, 'ab_NPfqliGQ', 'archived', 'b', 'b@archived.com', '1899-01-01 00:00:00', 'Masculin', '2 rue des archives', NULL, NULL, NULL, 1, 0, 'a:4:{s:8:\"comments\";a:0:{}s:6:\"images\";a:0:{}s:6:\"videos\";a:0:{}s:5:\"files\";a:0:{}}', 0, 1),
(14, 'ta_ykr9L29F', 'toarchive', 'a', 'a@toarchive.com', '1899-01-01 00:00:00', 'Masculin', NULL, NULL, NULL, NULL, 1, 0, 'a:4:{s:8:\"comments\";a:0:{}s:6:\"images\";a:0:{}s:6:\"videos\";a:0:{}s:5:\"files\";a:0:{}}', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `speciality`
--

DROP TABLE IF EXISTS `speciality`;
CREATE TABLE IF NOT EXISTS `speciality` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `speciality` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `role` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `occupation` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_F3D7A08EF3D7A08E` (`speciality`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `speciality`
--

INSERT INTO `speciality` (`id`, `speciality`, `role`, `occupation`) VALUES
(1, 'Médecin', 'ROLE_MEDICAL', 'medical'),
(2, 'Secrétaire', 'ROLE_SECRETARY', 'medical'),
(3, 'Ergotherapie', 'ROLE_PARAMEDICAL', 'paramedical'),
(4, 'Kinésithérapie', 'ROLE_PARAMEDICAL', 'paramedical'),
(5, 'Psychologue', 'ROLE_PARAMEDICAL', 'paramedical'),
(6, 'Psychomotricité', 'ROLE_PARAMEDICAL', 'paramedical'),
(7, 'Neuropsychologue', 'ROLE_PARAMEDICAL', 'paramedical'),
(8, 'Musicothérapie', 'ROLE_PARAMEDICAL', 'paramedical'),
(9, 'Orthophonie', 'ROLE_PARAMEDICAL', 'paramedical');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `roles` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `nonlocked` tinyint(1) NOT NULL,
  `notifications` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `speciality` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_8D93D649F85E0677` (`username`),
  KEY `IDX_8D93D649F3D7A08E` (`speciality`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `username`, `password`, `roles`, `nonlocked`, `notifications`, `speciality`) VALUES
(1, 'doc1', '$2y$13$caEaP.Tb1dAnvnzqCcZHk.qnPENaUJ4evXqyx2LzmexJvho1QfvNW', 's:12:\"ROLE_MEDICAL\";', 1, 'a:2:{i:0;i:1;i:1;i:1;}', 1),
(2, 'doc2', '$2y$13$mol38v//bhCEqun4kvi/jeeawZL0TUH9WsZPOi8zwWtUrLYH5dhUS', 's:12:\"ROLE_MEDICAL\";', 1, 'a:2:{i:0;i:1;i:1;i:1;}', 1),
(3, 'sec1', '$2y$13$CwU2.2fq4Z3kLCCCmXVVV.LxU0oyvvw8/.C0OZjpu9Y0iIyewOKRG', 's:14:\"ROLE_SECRETARY\";', 1, 'a:2:{i:0;i:1;i:1;i:1;}', 2),
(4, 'sec2', '$2y$13$CiORYwik.RY1/kaJsrih5O6XJvbxgq/ehFOU9p8gJH1WoE34yjOwG', 's:14:\"ROLE_SECRETARY\";', 1, 'a:2:{i:0;i:1;i:1;i:1;}', 2),
(5, 'par1', '$2y$13$XU5o92iOMse6Pf9QTs7x2ebtS23N7t/XNBFLwF3KGZooUBDF.dgQW', 's:16:\"ROLE_PARAMEDICAL\";', 1, 'a:2:{i:0;i:1;i:1;i:1;}', 3),
(6, 'par2', '$2y$13$/ubzEPOrNvvEfW8b2Y.7WO9O4uo1PJRTOjJ2mTmOxI5X1NFFs8iti', 's:16:\"ROLE_PARAMEDICAL\";', 1, 'a:2:{i:0;i:1;i:1;i:1;}', 8);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `a_c_l`
--
ALTER TABLE `a_c_l`
  ADD CONSTRAINT `FK_C2A8AF8518E5153E` FOREIGN KEY (`id_doc`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_C2A8AF85F53E2428` FOREIGN KEY (`id_evaluator`) REFERENCES `user` (`id`);

--
-- Constraints for table `healthCare`
--
ALTER TABLE `healthCare`
  ADD CONSTRAINT `FK_BE8DC5917123BE64` FOREIGN KEY (`idSpeciality`) REFERENCES `speciality` (`id`),
  ADD CONSTRAINT `FK_BE8DC591A63BC19` FOREIGN KEY (`idPatient`) REFERENCES `patient` (`id`);

--
-- Constraints for table `healthSession`
--
ALTER TABLE `healthSession`
  ADD CONSTRAINT `FK_9D20E34ED3167795` FOREIGN KEY (`idhealthcare`) REFERENCES `healthCare` (`id`),
  ADD CONSTRAINT `FK_9D20E34EDB43F593` FOREIGN KEY (`idCaregiver`) REFERENCES `user` (`id`);

--
-- Constraints for table `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `FK_BF5476CA466F2FFC` FOREIGN KEY (`target`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_BF5476CA5F8A7F73` FOREIGN KEY (`source`) REFERENCES `user` (`id`);

--
-- Constraints for table `patient`
--
ALTER TABLE `patient`
  ADD CONSTRAINT `FK_1ADAD7EBD629A2D5` FOREIGN KEY (`proId`) REFERENCES `user` (`id`);

--
-- Constraints for table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `FK_8D93D649F3D7A08E` FOREIGN KEY (`speciality`) REFERENCES `speciality` (`id`);

SET FOREIGN_KEY_CHECKS=1;