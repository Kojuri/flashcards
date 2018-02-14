-- phpMyAdmin SQL Dump
-- version 4.6.4
-- https://www.phpmyadmin.net/
--
-- Client :  127.0.0.1
-- Généré le :  Mer 14 Février 2018 à 15:15
-- Version du serveur :  5.7.14
-- Version de PHP :  7.0.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `flashcards`
--

-- --------------------------------------------------------

--
-- Structure de la table `carte`
--

CREATE TABLE `carte` (
  `id` int(11) NOT NULL,
  `description` text NOT NULL,
  `url_image` text NOT NULL,
  `collection_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Contenu de la table `carte`
--

INSERT INTO `carte` (`id`, `description`, `url_image`, `collection_id`) VALUES
(11, 'Ane', 'upload/988dc563-ce19-4d0c-9b93-04486d38577b.jpg', 1),
(10, 'Canard', 'upload/f8cc2714-ee91-45b3-a2db-2de1d371df07.jpg', 1),
(9, 'Coq', 'upload/2037d513-e1d0-4e85-a965-19fbbc5b5c4a.jpg', 1),
(8, 'Lapin', 'upload/a9b6fd38-8f2c-4ca3-96a2-037cf4c147eb.jpg', 1),
(12, 'Chat', 'upload/86d80375-54a6-4e90-9cf3-67d7355bff4c.jpg', 1),
(13, 'Cheval', 'upload/eb94f7ae-29ec-4c45-bd2e-8a2fb1f88235.jpg', 1),
(14, 'Chien', 'upload/f466b3f1-00c0-4c1a-93b6-e6cc2cb60994.jpg', 1),
(15, 'Cochon', 'upload/41ed6dae-5c97-405c-9855-4ae3d90ce331.jpg', 1),
(16, 'Grenouille', 'upload/ad650a03-2e9c-447d-8f4f-ba191253a613.jpg', 1),
(17, 'Poisson Rouge', 'upload/7593d359-9759-4dea-a0fc-76547d7ac545.jpg', 1),
(18, 'Vache', 'upload/fd2e6626-6e1b-48d3-acc1-fd96aae897d2.jpg', 1),
(19, 'Oie', 'upload/d7bf912d-5902-4869-a215-65456c84705c.jpg', 1);

-- --------------------------------------------------------

--
-- Structure de la table `collection`
--

CREATE TABLE `collection` (
  `id` int(11) NOT NULL,
  `libelle` varchar(250) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Contenu de la table `collection`
--

INSERT INTO `collection` (`id`, `libelle`) VALUES
(1, 'Animaux');

-- --------------------------------------------------------

--
-- Structure de la table `partie`
--

CREATE TABLE `partie` (
  `id` int(11) NOT NULL,
  `token` text NOT NULL,
  `statut` int(11) NOT NULL,
  `joueur` varchar(250) NOT NULL,
  `score` int(11) DEFAULT '0',
  `collection_id` int(11) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `professeur`
--

CREATE TABLE `professeur` (
  `id` int(11) NOT NULL,
  `mail` varchar(250) NOT NULL,
  `nom` varchar(250) NOT NULL,
  `prenom` varchar(250) NOT NULL,
  `mdp` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Contenu de la table `professeur`
--

INSERT INTO `professeur` (`id`, `mail`, `nom`, `prenom`, `mdp`) VALUES
(1, 'thomas@thomas.fr', 'Pascuzzo', 'Thomas', '$2y$10$bL2LcU2cUmhla1vMIOvjAedwXYqjsQPlrCpXNnekEoKI80QyH1Qga');

--
-- Index pour les tables exportées
--

--
-- Index pour la table `carte`
--
ALTER TABLE `carte`
  ADD PRIMARY KEY (`id`),
  ADD KEY `serie_id` (`collection_id`);

--
-- Index pour la table `collection`
--
ALTER TABLE `collection`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `partie`
--
ALTER TABLE `partie`
  ADD PRIMARY KEY (`id`),
  ADD KEY `serie_id` (`collection_id`);

--
-- Index pour la table `professeur`
--
ALTER TABLE `professeur`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pour les tables exportées
--

--
-- AUTO_INCREMENT pour la table `carte`
--
ALTER TABLE `carte`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;
--
-- AUTO_INCREMENT pour la table `collection`
--
ALTER TABLE `collection`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT pour la table `partie`
--
ALTER TABLE `partie`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `professeur`
--
ALTER TABLE `professeur`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
