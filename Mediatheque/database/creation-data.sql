-- Script complet de recréation avec la NOUVELLE STRUCTURE (5 colonnes genre_id)

-- 1. Utiliser la bonne base de données
CREATE DATABASE IF NOT EXISTS `Mediatheque` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `Mediatheque`;

-- 2. Suppression des tables existantes (dans l'ordre pour respecter les clés étrangères)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS evenement_images;
DROP TABLE IF EXISTS evenements;
DROP TABLE IF EXISTS emprunts;
DROP TABLE IF EXISTS medias;
DROP TABLE IF EXISTS genres;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS contact_messages;
DROP TABLE IF EXISTS settings;
DROP VIEW IF EXISTS stats_mediatheque;
SET FOREIGN_KEY_CHECKS = 1;

-- 3. Création de toutes les tables (NOUVELLE STRUCTURE)

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    prenom VARCHAR(50) NOT NULL COMMENT 'Prénom - minimum 2 caractères, maximum 50',
    nom VARCHAR(50) NOT NULL COMMENT 'Nom - minimum 2 caractères, maximum 50',
    email VARCHAR(100) NOT NULL UNIQUE COMMENT 'Email unique - maximum 100 caractères',
    password VARCHAR(255) NOT NULL COMMENT 'Mot de passe hashé',
    role ENUM('user', 'admin', 'deleted') DEFAULT 'user' COMMENT 'Rôle utilisateur',
    created_at DATETIME DEFAULT NULL,
    updated_at DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- *** NOUVELLE STRUCTURE : Genres sans type_media ***
CREATE TABLE genres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL UNIQUE COMMENT 'Nom du genre (unique, partageable entre types)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- *** NOUVELLE STRUCTURE : Médias avec 5 colonnes genre_id ***
CREATE TABLE medias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(200) NOT NULL COMMENT 'Titre - minimum 1 caractère, maximum 200',
    type ENUM('livre', 'film', 'jeu') NOT NULL COMMENT 'Type de média',
    -- *** 5 COLONNES POUR LES GENRES (maximum 5 genres par média) ***
    genre_id_1 INT DEFAULT NULL COMMENT 'Premier genre (obligatoire)',
    genre_id_2 INT DEFAULT NULL COMMENT 'Deuxième genre (optionnel)',
    genre_id_3 INT DEFAULT NULL COMMENT 'Troisième genre (optionnel)',
    genre_id_4 INT DEFAULT NULL COMMENT 'Quatrième genre (optionnel)',
    genre_id_5 INT DEFAULT NULL COMMENT 'Cinquième genre (optionnel)',
    stock INT NOT NULL DEFAULT 1 COMMENT 'Nombre total d exemplaires - minimum 1',
    stock_disponible INT NOT NULL DEFAULT 1 COMMENT 'Nombre d exemplaires disponibles',
    image VARCHAR(255) DEFAULT NULL COMMENT 'Nom du fichier image de couverture',
    created_at DATETIME DEFAULT NULL,
    updated_at DATETIME DEFAULT NULL,
    -- Champs spécifiques aux livres
    auteur VARCHAR(100) DEFAULT NULL COMMENT 'Auteur du livre - 2-100 caractères',
    isbn VARCHAR(20) DEFAULT NULL COMMENT 'ISBN unique - 10 ou 13 chiffres',
    nombre_pages INT DEFAULT NULL COMMENT 'Nombre de pages - entre 1 et 9999',
    resume TEXT DEFAULT NULL COMMENT 'Résumé du livre',
    annee_publication YEAR DEFAULT NULL COMMENT 'Année de publication - entre 1900 et année courante',
    -- Champs spécifiques aux films
    realisateur VARCHAR(100) DEFAULT NULL COMMENT 'Réalisateur du film - 2-100 caractères',
    duree_minutes INT DEFAULT NULL COMMENT 'Durée en minutes - entre 1 et 999',
    synopsis TEXT DEFAULT NULL COMMENT 'Synopsis du film',
    classification ENUM('Tous publics', '-12', '-16', '-18') DEFAULT NULL COMMENT 'Classification par âge',
    annee_film YEAR DEFAULT NULL COMMENT 'Année de sortie - entre 1900 et année courante',
    -- Champs spécifiques aux jeux vidéo
    editeur VARCHAR(100) DEFAULT NULL COMMENT 'Éditeur du jeu - 2-100 caractères',
    plateforme ENUM('PC', 'PlayStation', 'Xbox', 'Nintendo', 'Mobile') DEFAULT NULL COMMENT 'Plateforme de jeu',
    age_minimum ENUM('3', '7', '12', '16', '18') DEFAULT NULL COMMENT 'Âge minimum requis',
    description TEXT DEFAULT NULL COMMENT 'Description du jeu',
    -- Clés étrangères pour les genres
    FOREIGN KEY (genre_id_1) REFERENCES genres(id) ON DELETE SET NULL,
    FOREIGN KEY (genre_id_2) REFERENCES genres(id) ON DELETE SET NULL,
    FOREIGN KEY (genre_id_3) REFERENCES genres(id) ON DELETE SET NULL,
    FOREIGN KEY (genre_id_4) REFERENCES genres(id) ON DELETE SET NULL,
    FOREIGN KEY (genre_id_5) REFERENCES genres(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE emprunts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    media_id INT NOT NULL,
    date_emprunt DATE NOT NULL,
    date_retour_prevue DATE NOT NULL COMMENT 'Date d emprunt + 14 jours',
    date_retour_reelle DATE DEFAULT NULL,
    statut ENUM('En cours', 'En retard', 'Rendu') DEFAULT 'En cours',
    created_at DATETIME DEFAULT NULL,
    updated_at DATETIME DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (media_id) REFERENCES medias(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    created_at DATETIME DEFAULT NULL,
    read_at DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    key_name VARCHAR(100) NOT NULL UNIQUE,
    value TEXT,
    description TEXT,
    created_at DATETIME DEFAULT NULL,
    updated_at DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. Index pour optimiser les performances
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_emprunts_user_id ON emprunts(user_id);
CREATE INDEX idx_emprunts_media_id ON emprunts(media_id);
CREATE INDEX idx_emprunts_statut ON emprunts(statut);
CREATE INDEX idx_emprunts_date_retour_prevue ON emprunts(date_retour_prevue);
CREATE INDEX idx_medias_type ON medias(type);
CREATE INDEX idx_medias_titre ON medias(titre);
CREATE INDEX idx_medias_isbn ON medias(isbn);
CREATE INDEX idx_medias_stock_disponible ON medias(stock_disponible);
-- Index pour les 5 colonnes de genres
CREATE INDEX idx_medias_genre_id_1 ON medias(genre_id_1);
CREATE INDEX idx_medias_genre_id_2 ON medias(genre_id_2);
CREATE INDEX idx_medias_genre_id_3 ON medias(genre_id_3);
CREATE INDEX idx_medias_genre_id_4 ON medias(genre_id_4);
CREATE INDEX idx_medias_genre_id_5 ON medias(genre_id_5);

-- 5. Vue pour les statistiques de la médiathèque
CREATE VIEW stats_mediatheque AS
SELECT 
    (SELECT COUNT(*) FROM users WHERE role = 'user') as total_users,
    (SELECT COUNT(*) FROM medias) as total_medias,
    (SELECT COUNT(*) FROM medias WHERE type = 'livre') as total_livres,
    (SELECT COUNT(*) FROM medias WHERE type = 'film') as total_films,
    (SELECT COUNT(*) FROM medias WHERE type = 'jeu') as total_jeux,
    (SELECT COUNT(*) FROM emprunts WHERE statut = 'En cours') as emprunts_en_cours,
    (SELECT COUNT(*) FROM emprunts WHERE statut = 'En cours' AND date_retour_prevue < CURDATE()) as emprunts_retard;

-- 6. Paramètres de configuration de l'application
INSERT INTO settings (key_name, value, description) VALUES 
('site_name', 'PHP MVC Starter', 'Nom du site web'),
('maintenance_mode', '0', 'Mode maintenance (0 = désactivé, 1 = activé)'),
('max_login_attempts', '5', 'Nombre maximum de tentatives de connexion'),
('session_timeout', '7200', 'Timeout de session en secondes (2 heures)'),
('max_emprunts_simultanees', '3', 'Nombre maximum d emprunts simultanés par utilisateur'),
('duree_emprunt_jours', '14', 'Durée d emprunt en jours calendaires'),
('taille_max_image', '2097152', 'Taille maximale des images en octets (2 Mo)'),
('formats_images_autorises', 'jpg,jpeg,png,gif', 'Formats d images autorisés'),
('pagination_medias', '20', 'Nombre de médias par page dans le catalogue');

-- 7. Genres prédéfinis (NOUVELLE STRUCTURE : sans type_media, uniques)
INSERT INTO genres (nom) VALUES
-- Genres communs à plusieurs types
('Action'), ('Aventure'), ('Comédie'), ('Drame'), ('Horreur'),
('Science-fiction'), ('Fantasy'), ('Thriller'), ('Romance'), ('Documentaire'),
-- Genres spécifiques livres
('Roman'), ('Policier'), ('Biographie'), ('Histoire'), ('Essai'), 
('Poésie'), ('Bande dessinée'), ('Jeunesse'),
-- Genres spécifiques films
('Animation'), ('Crime'), ('Fantastique'),
-- Genres spécifiques jeux
('RPG'), ('Stratégie'), ('Sport'), ('Course'), ('Simulation'), 
('Puzzle'), ('Plateforme'), ('Tir'), ('Combat'), ('Survie'), ('Multijoueur');

-- 8. Utilisateur administrateur par défaut
INSERT INTO users (prenom, nom, email, password, role, created_at, updated_at) VALUES 
('Admin', 'Système', 'admin@mediatheque.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NOW(), NOW());

-- 9. Utilisateurs de test
INSERT INTO users (prenom, nom, email, password, role, created_at, updated_at) VALUES 
('Jean', 'Martin', 'jean.martin@email.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', NOW(), NOW()),
('Marie', 'Dubois', 'marie.dubois@email.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', NOW(), NOW()),
('Paul', 'Durand', 'paul.durand@email.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', NOW(), NOW());

-- 10. Médias d'exemple - LIVRES (10 livres) - AVEC genre_id_1, genre_id_2, etc.
INSERT INTO medias (titre, type, genre_id_1, genre_id_2, stock, stock_disponible, auteur, isbn, nombre_pages, resume, annee_publication, created_at, updated_at) VALUES
-- Le Seigneur des Anneaux : Fantasy, Aventure
('Le Seigneur des Anneaux', 'livre', (SELECT id FROM genres WHERE nom = 'Fantasy'), (SELECT id FROM genres WHERE nom = 'Aventure'), 3, 3, 'J.R.R. Tolkien', '9782070612888', 1216, 'Une quête épique en Terre du Milieu pour détruire l''Anneau Unique et sauver la Terre du Milieu de l''emprise du Seigneur Ténébreux Sauron.', 1954, NOW(), NOW()),
-- 1984 : Science-fiction, Drame
('1984', 'livre', (SELECT id FROM genres WHERE nom = 'Science-fiction'), (SELECT id FROM genres WHERE nom = 'Drame'), 2, 2, 'George Orwell', '9782070368228', 372, 'Dans un monde totalitaire, Winston Smith travaille au Ministère de la Vérité où il falsifie l''Histoire. Une dystopie troublante sur le totalitarisme.', 1949, NOW(), NOW()),
-- Harry Potter : Fantasy, Jeunesse, Aventure
('Harry Potter à l''école des sorciers', 'livre', (SELECT id FROM genres WHERE nom = 'Fantasy'), (SELECT id FROM genres WHERE nom = 'Jeunesse'), 4, 4, 'J.K. Rowling', '9782070518425', 309, 'Harry Potter découvre qu''il est un sorcier le jour de ses 11 ans et entre à Poudlard, l''école de sorcellerie.', 1997, NOW(), NOW());

-- Mise à jour pour ajouter le 3e genre à Harry Potter
UPDATE medias SET genre_id_3 = (SELECT id FROM genres WHERE nom = 'Aventure') WHERE titre = 'Harry Potter à l''école des sorciers';

INSERT INTO medias (titre, type, genre_id_1, genre_id_2, stock, stock_disponible, auteur, isbn, nombre_pages, resume, annee_publication, created_at, updated_at) VALUES
-- Le Petit Prince : Jeunesse, Fantasy
('Le Petit Prince', 'livre', (SELECT id FROM genres WHERE nom = 'Jeunesse'), (SELECT id FROM genres WHERE nom = 'Fantasy'), 2, 2, 'Antoine de Saint-Exupéry', '9782070612758', 96, 'L''histoire d''un petit prince qui voyage de planète en planète et rencontre un aviateur dans le désert.', 1943, NOW(), NOW()),
-- Dune : Science-fiction, Aventure
('Dune', 'livre', (SELECT id FROM genres WHERE nom = 'Science-fiction'), (SELECT id FROM genres WHERE nom = 'Aventure'), 2, 2, 'Frank Herbert', '9782266063015', 688, 'Sur la planète Arrakis, Paul Atreides doit survivre aux intrigues politiques et maîtriser les pouvoirs mystiques.', 1965, NOW(), NOW()),
-- L'Écume des jours : Roman, Romance
('L''Écume des jours', 'livre', (SELECT id FROM genres WHERE nom = 'Roman'), (SELECT id FROM genres WHERE nom = 'Romance'), 2, 2, 'Boris Vian', '9782070409235', 256, 'L''histoire d''amour tragique entre Colin et Chloé dans un univers poétique et surréaliste.', 1947, NOW(), NOW()),
-- L'Étranger : Roman, Drame
('L''Étranger', 'livre', (SELECT id FROM genres WHERE nom = 'Roman'), (SELECT id FROM genres WHERE nom = 'Drame'), 3, 3, 'Albert Camus', '9782070360024', 186, 'Meursault, un homme indifférent, commet un meurtre sur une plage d''Alger. Un chef-d''œuvre de la littérature existentialiste.', 1942, NOW(), NOW()),
-- Le Mystère de la chambre jaune : Policier, Thriller
('Le Mystère de la chambre jaune', 'livre', (SELECT id FROM genres WHERE nom = 'Policier'), (SELECT id FROM genres WHERE nom = 'Thriller'), 2, 2, 'Gaston Leroux', '9782253005827', 288, 'Première enquête du célèbre journaliste-détective Rouletabille dans un crime impossible en chambre close.', 1907, NOW(), NOW()),
-- Orgueil et Préjugés : Romance, Roman
('Orgueil et Préjugés (réédition moderne)', 'livre', (SELECT id FROM genres WHERE nom = 'Romance'), (SELECT id FROM genres WHERE nom = 'Roman'), 2, 2, 'Jane Austen', '9782070424481', 448, 'L''histoire d''Elizabeth Bennet et de Fitzwilliam Darcy, un classique de la romance anglaise réédité au XXe siècle.', 1950, NOW(), NOW()),
-- Le Parfum : Thriller, Drame
('Le Parfum', 'livre', (SELECT id FROM genres WHERE nom = 'Thriller'), (SELECT id FROM genres WHERE nom = 'Drame'), 3, 3, 'Patrick Süskind', '9782253037033', 320, 'L''histoire de Jean-Baptiste Grenouille, un homme obsédé par les odeurs et le parfait parfum.', 1985, NOW(), NOW());

-- 11. Médias d'exemple - FILMS (10 films) - AVEC genre_id_1, genre_id_2, genre_id_3
INSERT INTO medias (titre, type, genre_id_1, genre_id_2, genre_id_3, stock, stock_disponible, realisateur, duree_minutes, synopsis, classification, annee_film, created_at, updated_at) VALUES
-- Inception : Science-fiction, Action, Thriller
('Inception', 'film', (SELECT id FROM genres WHERE nom = 'Science-fiction'), (SELECT id FROM genres WHERE nom = 'Action'), (SELECT id FROM genres WHERE nom = 'Thriller'), 2, 2, 'Christopher Nolan', 148, 'Dom Cobb est un voleur expérimenté dans l''art périlleux de l''extraction : sa spécialité consiste à s''approprier les secrets les plus précieux d''un individu, enfouis au plus profond de son subconscient.', '-12', 2010, NOW(), NOW()),
-- Le Parrain : Crime, Drame
('Le Parrain', 'film', (SELECT id FROM genres WHERE nom = 'Crime'), (SELECT id FROM genres WHERE nom = 'Drame'), NULL, 1, 1, 'Francis Ford Coppola', 175, 'L''histoire de la famille Corleone, une puissante famille de la mafia italienne de New York.', '-16', 1972, NOW(), NOW()),
-- Pulp Fiction : Crime, Thriller, Comédie
('Pulp Fiction', 'film', (SELECT id FROM genres WHERE nom = 'Crime'), (SELECT id FROM genres WHERE nom = 'Thriller'), (SELECT id FROM genres WHERE nom = 'Comédie'), 2, 2, 'Quentin Tarantino', 154, 'Les histoires entremêlées de petits malfrats, d''un boxeur et d''un couple de braqueurs dans Los Angeles.', '-18', 1994, NOW(), NOW()),
-- Le Roi Lion : Animation, Aventure
('Le Roi Lion', 'film', (SELECT id FROM genres WHERE nom = 'Animation'), (SELECT id FROM genres WHERE nom = 'Aventure'), NULL, 3, 3, 'Roger Allers', 88, 'Un lionceau nommé Simba ne peut pas attendre d''être roi. Mais son oncle complote pour s''emparer du royaume.', 'Tous publics', 1994, NOW(), NOW()),
-- Avatar : Science-fiction, Aventure, Action
('Avatar', 'film', (SELECT id FROM genres WHERE nom = 'Science-fiction'), (SELECT id FROM genres WHERE nom = 'Aventure'), (SELECT id FROM genres WHERE nom = 'Action'), 2, 2, 'James Cameron', 162, 'Sur Pandora, un marine paraplégique participe au programme Avatar et tombe amoureux d''une femme Na''vi.', '-12', 2009, NOW(), NOW()),
-- Forrest Gump : Drame, Romance
('Forrest Gump', 'film', (SELECT id FROM genres WHERE nom = 'Drame'), (SELECT id FROM genres WHERE nom = 'Romance'), NULL, 2, 2, 'Robert Zemeckis', 142, 'L''histoire extraordinaire d''un homme simple qui traverse les grands événements de l''Histoire américaine.', 'Tous publics', 1994, NOW(), NOW()),
-- Titanic : Romance, Drame
('Titanic', 'film', (SELECT id FROM genres WHERE nom = 'Romance'), (SELECT id FROM genres WHERE nom = 'Drame'), NULL, 2, 2, 'James Cameron', 194, 'L''histoire d''amour tragique entre Jack et Rose à bord du célèbre paquebot.', '-12', 1997, NOW(), NOW()),
-- Joker : Thriller, Drame, Crime
('Joker', 'film', (SELECT id FROM genres WHERE nom = 'Thriller'), (SELECT id FROM genres WHERE nom = 'Drame'), (SELECT id FROM genres WHERE nom = 'Crime'), 1, 1, 'Todd Phillips', 122, 'Arthur Fleck, un humoriste raté, bascule dans la folie et devient le Joker.', '-16', 2019, NOW(), NOW()),
-- Spider-Man : Action, Aventure, Fantasy
('Spider-Man: No Way Home', 'film', (SELECT id FROM genres WHERE nom = 'Action'), (SELECT id FROM genres WHERE nom = 'Aventure'), (SELECT id FROM genres WHERE nom = 'Fantasy'), 3, 3, 'Jon Watts', 148, 'Peter Parker fait appel au Docteur Strange pour faire oublier son identité secrète au monde entier.', '-12', 2021, NOW(), NOW()),
-- Amélie : Comédie, Romance
('Amélie', 'film', (SELECT id FROM genres WHERE nom = 'Comédie'), (SELECT id FROM genres WHERE nom = 'Romance'), NULL, 2, 2, 'Jean-Pierre Jeunet', 122, 'Amélie Poulain décide d''aider les autres à trouver le bonheur tout en découvrant l''amour.', 'Tous publics', 2001, NOW(), NOW());

-- 12. Médias d'exemple - JEUX VIDÉO (10 jeux) - AVEC genre_id_1, genre_id_2, genre_id_3
INSERT INTO medias (titre, type, genre_id_1, genre_id_2, genre_id_3, stock, stock_disponible, editeur, plateforme, age_minimum, description, created_at, updated_at) VALUES
-- The Witcher 3 : RPG, Action, Aventure
('The Witcher 3: Wild Hunt', 'jeu', (SELECT id FROM genres WHERE nom = 'RPG'), (SELECT id FROM genres WHERE nom = 'Action'), (SELECT id FROM genres WHERE nom = 'Aventure'), 2, 2, 'CD Projekt RED', 'PC', '18', 'Un RPG en monde ouvert où vous incarnez Geralt de Riv, un chasseur de monstres à la recherche de sa fille adoptive.', NOW(), NOW()),
-- Super Mario Odyssey : Plateforme, Aventure
('Super Mario Odyssey', 'jeu', (SELECT id FROM genres WHERE nom = 'Plateforme'), (SELECT id FROM genres WHERE nom = 'Aventure'), NULL, 3, 3, 'Nintendo', 'Nintendo', '7', 'Mario embarque pour une aventure à travers différents royaumes pour sauver la Princesse Peach des griffes de Bowser.', NOW(), NOW()),
-- GTA V : Action, Aventure, Crime
('Grand Theft Auto V', 'jeu', (SELECT id FROM genres WHERE nom = 'Action'), (SELECT id FROM genres WHERE nom = 'Aventure'), (SELECT id FROM genres WHERE nom = 'Crime'), 1, 1, 'Rockstar Games', 'PC', '18', 'Trois criminels très différents risquent tout dans une série de braquages audacieux et dangereux à Los Santos.', NOW(), NOW()),
-- Minecraft : Simulation, Aventure, Survie
('Minecraft', 'jeu', (SELECT id FROM genres WHERE nom = 'Simulation'), (SELECT id FROM genres WHERE nom = 'Aventure'), (SELECT id FROM genres WHERE nom = 'Survie'), 4, 4, 'Mojang Studios', 'PC', '7', 'Un jeu de construction et de survie dans un monde fait de blocs où tout est possible.', NOW(), NOW()),
-- FIFA 23 : Sport, Multijoueur
('FIFA 23', 'jeu', (SELECT id FROM genres WHERE nom = 'Sport'), (SELECT id FROM genres WHERE nom = 'Multijoueur'), NULL, 2, 2, 'EA Sports', 'PlayStation', '3', 'Le jeu de football le plus réaliste avec tous les clubs et joueurs officiels.', NOW(), NOW()),
-- Zelda BOTW : Aventure, Action, RPG
('The Legend of Zelda: Breath of the Wild', 'jeu', (SELECT id FROM genres WHERE nom = 'Aventure'), (SELECT id FROM genres WHERE nom = 'Action'), (SELECT id FROM genres WHERE nom = 'RPG'), 2, 2, 'Nintendo', 'Nintendo', '12', 'Link se réveille dans un monde en ruines et doit sauver Hyrule du fléau Ganon.', NOW(), NOW()),
-- Call of Duty : Tir, Action, Multijoueur
('Call of Duty: Modern Warfare', 'jeu', (SELECT id FROM genres WHERE nom = 'Tir'), (SELECT id FROM genres WHERE nom = 'Action'), (SELECT id FROM genres WHERE nom = 'Multijoueur'), 2, 2, 'Activision', 'PC', '18', 'Un jeu de tir à la première personne dans un conflit militaire moderne.', NOW(), NOW()),
-- Assassin's Creed : Action, Aventure, RPG
('Assassin''s Creed Valhalla', 'jeu', (SELECT id FROM genres WHERE nom = 'Action'), (SELECT id FROM genres WHERE nom = 'Aventure'), (SELECT id FROM genres WHERE nom = 'RPG'), 2, 2, 'Ubisoft', 'PlayStation', '18', 'Incarnez Eivor, un guerrier viking parti à la conquête de l''Angleterre.', NOW(), NOW()),
-- Animal Crossing : Simulation
('Animal Crossing: New Horizons', 'jeu', (SELECT id FROM genres WHERE nom = 'Simulation'), NULL, NULL, 3, 3, 'Nintendo', 'Nintendo', '3', 'Créez votre propre paradis sur une île déserte et vivez à votre rythme.', NOW(), NOW()),
-- Cyberpunk 2077 : RPG, Action, Science-fiction
('Cyberpunk 2077', 'jeu', (SELECT id FROM genres WHERE nom = 'RPG'), (SELECT id FROM genres WHERE nom = 'Action'), (SELECT id FROM genres WHERE nom = 'Science-fiction'), 1, 1, 'CD Projekt', 'PC', '18', 'Un RPG futuriste dans la mégalopole de Night City où vous incarnez un mercenaire cyberpunk.', NOW(), NOW());

-- 14. UPDATE des images
UPDATE medias SET image = 'uploads/covers/livre-1-le-seigneur-des-anneaux.jpg' WHERE id = 1;
UPDATE medias SET image = 'uploads/covers/livre-2-1984.jpg' WHERE id = 2;
UPDATE medias SET image = 'uploads/covers/livre-3-harry-potter-l039cole-des-sorciers.jpg' WHERE id = 3;
UPDATE medias SET image = 'uploads/covers/livre-4-le-petit-prince.jpg' WHERE id = 4;
UPDATE medias SET image = 'uploads/covers/livre-5-dune.jpg' WHERE id = 5;
UPDATE medias SET image = 'uploads/covers/livre-6-l039cume-des-jours.jpg' WHERE id = 6;
UPDATE medias SET image = 'uploads/covers/livre-7-l039tranger.jpg' WHERE id = 7;
UPDATE medias SET image = 'uploads/covers/livre-8-le-mystre-de-la-chambre-jaune.jpg' WHERE id = 8;
UPDATE medias SET image = 'uploads/covers/livre-9-orgueil-et-prjugs-rdition-moderne.jpg' WHERE id = 9;
UPDATE medias SET image = 'uploads/covers/livre-10-le-parfum.jpg' WHERE id = 10;
UPDATE medias SET image = 'uploads/covers/film-11-inception.jpg' WHERE id = 11;
UPDATE medias SET image = 'uploads/covers/film-12-le-parrain.jpg' WHERE id = 12;
UPDATE medias SET image = 'uploads/covers/film-13-pulp-fiction.jpg' WHERE id = 13;
UPDATE medias SET image = 'uploads/covers/film-14-le-roi-lion.jpg' WHERE id = 14;
UPDATE medias SET image = 'uploads/covers/film-15-avatar.jpg' WHERE id = 15;
UPDATE medias SET image = 'uploads/covers/film-16-forrest-gump.jpg' WHERE id = 16;
UPDATE medias SET image = 'uploads/covers/film-17-titanic.jpg' WHERE id = 17;
UPDATE medias SET image = 'uploads/covers/film-18-joker.jpg' WHERE id = 18;
UPDATE medias SET image = 'uploads/covers/film-19-spider-man-no-way-home.jpg' WHERE id = 19;
UPDATE medias SET image = 'uploads/covers/film-20-amlie.jpg' WHERE id = 20;
UPDATE medias SET image = 'uploads/covers/jeu-21-the-witcher-3-wild-hunt.png' WHERE id = 21;
UPDATE medias SET image = 'uploads/covers/jeu-22-super-mario-odyssey.png' WHERE id = 22;
UPDATE medias SET image = 'uploads/covers/jeu-23-grand-theft-auto-v.png' WHERE id = 23;
UPDATE medias SET image = 'uploads/covers/jeu-24-minecraft.png' WHERE id = 24;
UPDATE medias SET image = 'uploads/covers/jeu-25-fifa-23.jpg' WHERE id = 25;
UPDATE medias SET image = 'uploads/covers/jeu-26-the-legend-of-zelda-breath-of-the-wild.jpg' WHERE id = 26;
UPDATE medias SET image = 'uploads/covers/jeu-27-call-of-duty-modern-warfare.png' WHERE id = 27;
UPDATE medias SET image = 'uploads/covers/jeu-28-assassins-creed-valhalla.png' WHERE id = 28;
UPDATE medias SET image = 'uploads/covers/jeu-29-animal-crossing-new-horizons.jpg' WHERE id = 29;
UPDATE medias SET image = 'uploads/covers/jeu-30-cyberpunk-2077.png' WHERE id = 30;

-- 15. Table des événements pour la médiathèque
CREATE TABLE IF NOT EXISTS evenements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    date_evenement DATE NOT NULL,
    heure_evenement VARCHAR(20) DEFAULT NULL,
    description TEXT NOT NULL,
    media_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (media_id) REFERENCES medias(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DELIMITER $$
CREATE TRIGGER before_evenements_update
BEFORE UPDATE ON evenements
FOR EACH ROW
BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END$$
DELIMITER ;

-- 16. Table des images d'événements
CREATE TABLE IF NOT EXISTS evenement_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evenement_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT NULL,
    FOREIGN KEY (evenement_id) REFERENCES evenements(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;