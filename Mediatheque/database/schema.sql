-- Schéma de base de données pour la médiathèque TLN
-- Conforme au cahier des charges détaillé
-- Exécutez ce script dans votre base de données MySQL

CREATE DATABASE IF NOT EXISTS `Mediatheque` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `Mediatheque`;

-- Table des utilisateurs - Conforme au cahier des charges
-- Champs : nom, prénom, email (unique), mot de passe
-- Rôles : user (standard) et admin
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    prenom VARCHAR(50) NOT NULL COMMENT 'Prénom - minimum 2 caractères, maximum 50',
    nom VARCHAR(50) NOT NULL COMMENT 'Nom - minimum 2 caractères, maximum 50',
    email VARCHAR(100) NOT NULL UNIQUE COMMENT 'Email unique - maximum 100 caractères',
    password VARCHAR(255) NOT NULL COMMENT 'Mot de passe hashé',
    role ENUM('user', 'admin') DEFAULT 'user' COMMENT 'Rôle utilisateur',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Index pour optimiser les recherches
CREATE INDEX idx_users_email ON users(email);

-- Table des genres prédéfinis par type de média
CREATE TABLE genres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    type_media ENUM('livre', 'film', 'jeu') NOT NULL,
    UNIQUE KEY unique_genre_type (nom, type_media)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table des médias - Conforme au cahier des charges
-- Support pour livres, films et jeux vidéo avec tous les champs requis
CREATE TABLE medias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(200) NOT NULL COMMENT 'Titre - minimum 1 caractère, maximum 200',
    type ENUM('livre', 'film', 'jeu') NOT NULL COMMENT 'Type de média',
    genre VARCHAR(100) NOT NULL COMMENT 'Genre sélectionné dans une liste prédéfinie',
    stock INT NOT NULL DEFAULT 1 COMMENT 'Nombre total d exemplaires - minimum 1',
    stock_disponible INT NOT NULL DEFAULT 1 COMMENT 'Nombre d exemplaires disponibles',
    image VARCHAR(255) DEFAULT NULL COMMENT 'Nom du fichier image de couverture',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
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
    
    -- Contraintes de validation
    CONSTRAINT chk_stock_positive CHECK (stock > 0),
    CONSTRAINT chk_stock_disponible_positive CHECK (stock_disponible >= 0),
    CONSTRAINT chk_stock_coherent CHECK (stock_disponible <= stock),
    CONSTRAINT chk_pages_valid CHECK (nombre_pages IS NULL OR (nombre_pages >= 1 AND nombre_pages <= 9999)),
    CONSTRAINT chk_duree_valid CHECK (duree_minutes IS NULL OR (duree_minutes >= 1 AND duree_minutes <= 999)),
    CONSTRAINT chk_annee_publication_valid CHECK (annee_publication IS NULL OR (annee_publication >= 1900 AND annee_publication <= YEAR(CURDATE()))),
    CONSTRAINT chk_annee_film_valid CHECK (annee_film IS NULL OR (annee_film >= 1900 AND annee_film <= YEAR(CURDATE())))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table des emprunts - Gestion complète du système d'emprunt
-- Limite de 3 emprunts simultanés par utilisateur
-- Durée d'emprunt : 14 jours calendaires
CREATE TABLE emprunts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    media_id INT NOT NULL,
    date_emprunt DATE NOT NULL DEFAULT (CURDATE()),
    date_retour_prevue DATE NOT NULL COMMENT 'Date d emprunt + 14 jours',
    date_retour_reelle DATE DEFAULT NULL,
    statut ENUM('En cours', 'En retard', 'Rendu') DEFAULT 'En cours',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (media_id) REFERENCES medias(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table de messages de contact
CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table de paramètres de configuration
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    key_name VARCHAR(100) NOT NULL UNIQUE,
    value TEXT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Vue pour les statistiques de la médiathèque
CREATE VIEW stats_mediatheque AS
SELECT 
    (SELECT COUNT(*) FROM users WHERE role = 'user') as total_users,
    (SELECT COUNT(*) FROM medias) as total_medias,
    (SELECT COUNT(*) FROM medias WHERE type = 'livre') as total_livres,
    (SELECT COUNT(*) FROM medias WHERE type = 'film') as total_films,
    (SELECT COUNT(*) FROM medias WHERE type = 'jeu') as total_jeux,
    (SELECT COUNT(*) FROM emprunts WHERE statut = 'En cours') as emprunts_en_cours,
    (SELECT COUNT(*) FROM emprunts WHERE statut = 'En cours' AND date_retour_prevue < CURDATE()) as emprunts_retard;

-- Index pour optimiser les performances
CREATE INDEX idx_emprunts_user_id ON emprunts(user_id);
CREATE INDEX idx_emprunts_media_id ON emprunts(media_id);
CREATE INDEX idx_emprunts_statut ON emprunts(statut);
CREATE INDEX idx_emprunts_date_retour_prevue ON emprunts(date_retour_prevue);
CREATE INDEX idx_medias_type ON medias(type);
CREATE INDEX idx_medias_genre ON medias(genre);
CREATE INDEX idx_medias_titre ON medias(titre);
CREATE INDEX idx_medias_isbn ON medias(isbn);
CREATE INDEX idx_medias_stock_disponible ON medias(stock_disponible); 

-- Données initiales pour les paramètres de l'application
INSERT INTO settings (key_name, value, description) VALUES 
('site_name', 'Médiathèque TLN', 'Nom du site web'),
('maintenance_mode', '0', 'Mode maintenance (0 = désactivé, 1 = activé)'),
('max_login_attempts', '5', 'Nombre maximum de tentatives de connexion'),
('session_timeout', '7200', 'Timeout de session en secondes (2 heures)'),
('max_emprunts_simultanees', '3', 'Nombre maximum d emprunts simultanés par utilisateur'),
('duree_emprunt_jours', '14', 'Durée d emprunt en jours calendaires'),
('taille_max_image', '2097152', 'Taille maximale des images en octets (2 Mo)'),
('formats_images_autorises', 'jpg,jpeg,png,gif', 'Formats d images autorisés'),
('pagination_medias', '20', 'Nombre de médias par page dans le catalogue');

-- Genres prédéfinis pour les livres
INSERT INTO genres (nom, type_media) VALUES
('Roman', 'livre'),
('Science-fiction', 'livre'),
('Fantasy', 'livre'),
('Policier', 'livre'),
('Biographie', 'livre'),
('Histoire', 'livre'),
('Essai', 'livre'),
('Poésie', 'livre'),
('Bande dessinée', 'livre'),
('Thriller', 'livre'),
('Romance', 'livre'),
('Jeunesse', 'livre');

-- Genres prédéfinis pour les films
INSERT INTO genres (nom, type_media) VALUES
('Action', 'film'),
('Comédie', 'film'),
('Drame', 'film'),
('Horreur', 'film'),
('Science-fiction', 'film'),
('Romance', 'film'),
('Thriller', 'film'),
('Documentaire', 'film'),
('Animation', 'film'),
('Aventure', 'film'),
('Crime', 'film'),
('Fantastique', 'film');

-- Genres prédéfinis pour les jeux vidéo
INSERT INTO genres (nom, type_media) VALUES
('Action', 'jeu'),
('Aventure', 'jeu'),
('RPG', 'jeu'),
('Stratégie', 'jeu'),
('Sport', 'jeu'),
('Course', 'jeu'),
('Simulation', 'jeu'),
('Puzzle', 'jeu'),
('Plateforme', 'jeu'),
('Tir', 'jeu'),
('Combat', 'jeu'),
('Survie', 'jeu'),
('Multijoueur', 'jeu');

-- Utilisateur administrateur par défaut
-- Mot de passe : "Admin123!" (à changer en production)
INSERT INTO users (prenom, nom, email, password, role, created_at, updated_at) VALUES 
('Admin', 'Médiathèque', 'admin@mediatheque.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NOW(), NOW());

-- Utilisateurs de test
-- Mot de passe : "User123!" pour tous
INSERT INTO users (prenom, nom, email, password, role, created_at, updated_at) VALUES 
('Jean', 'Martin', 'jean.martin@email.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', NOW(), NOW()),
('Marie', 'Dubois', 'marie.dubois@email.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', NOW(), NOW()),
('Paul', 'Durand', 'paul.durand@email.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', NOW(), NOW());

-- Médias d'exemple - LIVRES
INSERT INTO medias (titre, type, genre, stock, stock_disponible, auteur, isbn, nombre_pages, resume, annee_publication, created_at, updated_at) VALUES
('Le Seigneur des Anneaux', 'livre', 'Fantasy', 3, 3, 'J.R.R. Tolkien', '9782070612888', 1216, 'Une quête épique en Terre du Milieu pour détruire l''Anneau Unique et sauver la Terre du Milieu de l''emprise du Seigneur Ténébreux Sauron.', 1954, NOW(), NOW()),
('1984', 'livre', 'Science-fiction', 2, 2, 'George Orwell', '9782070368228', 372, 'Dans un monde totalitaire, Winston Smith travaille au Ministère de la Vérité où il falsifie l''Histoire. Une dystopie troublante sur le totalitarisme.', 1949, NOW(), NOW()),
('Harry Potter à l''école des sorciers', 'livre', 'Fantasy', 4, 4, 'J.K. Rowling', '9782070518425', 309, 'Harry Potter découvre qu''il est un sorcier le jour de ses 11 ans et entre à Poudlard, l''école de sorcellerie.', 1997, NOW(), NOW()),
('Le Petit Prince', 'livre', 'Jeunesse', 2, 2, 'Antoine de Saint-Exupéry', '9782070612758', 96, 'L''histoire d''un petit prince qui voyage de planète en planète et rencontre un aviateur dans le désert.', 1943, NOW(), NOW()),
('Dune', 'livre', 'Science-fiction', 2, 2, 'Frank Herbert', '9782266063015', 688, 'Sur la planète Arrakis, Paul Atreides doit survivre aux intrigues politiques et maîtriser les pouvoirs mystiques.', 1965, NOW(), NOW());

-- Médias d'exemple - FILMS
INSERT INTO medias (titre, type, genre, stock, stock_disponible, realisateur, duree_minutes, synopsis, classification, annee_film, created_at, updated_at) VALUES
('Inception', 'film', 'Science-fiction', 2, 2, 'Christopher Nolan', 148, 'Dom Cobb est un voleur expérimenté dans l''art périlleux de l''extraction : sa spécialité consiste à s''approprier les secrets les plus précieux d''un individu, enfouis au plus profond de son subconscient.', '-12', 2010, NOW(), NOW()),
('Le Parrain', 'film', 'Crime', 1, 1, 'Francis Ford Coppola', 175, 'L''histoire de la famille Corleone, une puissante famille de la mafia italienne de New York.', '-16', 1972, NOW(), NOW()),
('Pulp Fiction', 'film', 'Crime', 2, 2, 'Quentin Tarantino', 154, 'Les histoires entremêlées de petits malfrats, d''un boxeur et d''un couple de braqueurs dans Los Angeles.', '-18', 1994, NOW(), NOW()),
('Le Roi Lion', 'film', 'Animation', 3, 3, 'Roger Allers', 88, 'Un lionceau nommé Simba ne peut pas attendre d''être roi. Mais son oncle complote pour s''emparer du royaume.', 'Tous publics', 1994, NOW(), NOW()),
('Avatar', 'film', 'Science-fiction', 2, 2, 'James Cameron', 162, 'Sur Pandora, un marine paraplégique participe au programme Avatar et tombe amoureux d''une femme Na''vi.', '-12', 2009, NOW(), NOW());

-- Médias d'exemple - JEUX VIDÉO
INSERT INTO medias (titre, type, genre, stock, stock_disponible, editeur, plateforme, age_minimum, description, created_at, updated_at) VALUES
('The Witcher 3: Wild Hunt', 'jeu', 'RPG', 2, 2, 'CD Projekt RED', 'PC', '18', 'Un RPG en monde ouvert où vous incarnez Geralt de Riv, un chasseur de monstres à la recherche de sa fille adoptive.', NOW(), NOW()),
('Super Mario Odyssey', 'jeu', 'Plateforme', 3, 3, 'Nintendo', 'Nintendo', '7', 'Mario embarque pour une aventure à travers différents royaumes pour sauver la Princesse Peach des griffes de Bowser.', NOW(), NOW()),
('Grand Theft Auto V', 'jeu', 'Action', 1, 1, 'Rockstar Games', 'PC', '18', 'Trois criminels très différents risquent tout dans une série de braquages audacieux et dangereux à Los Santos.', NOW(), NOW()),
('Minecraft', 'jeu', 'Simulation', 4, 4, 'Mojang Studios', 'PC', '7', 'Un jeu de construction et de survie dans un monde fait de blocs où tout est possible.', NOW(), NOW()),
('FIFA 23', 'jeu', 'Sport', 2, 2, 'EA Sports', 'PlayStation', '3', 'Le jeu de football le plus réaliste avec tous les clubs et joueurs officiels.', NOW(), NOW());

COMMIT;