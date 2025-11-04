<?php

# Configuration de la BDD et paramètres de l'application
 
//! Configuration de la base de données

// Adresse de l'hôte MySQL (généralement 'localhost')
define('DB_HOST', 'localhost');

// Nom de la BDD (doit correspondre au nom donné lors de sa génération)
define('DB_NAME', 'Mediatheque');

// Nom d'utilisateur pour la connexion MySQL (permissions SELECT, INSERT, UPDATE, DELETE requises)
define('DB_USER', 'root');

// Mot de passe de l'utilisateur MySQL (si pas de mot de passe => laisser vide '')
define('DB_PASS', '');

// Jeu de caractères utilisé pour la BDD ('utf8mb4' recommandé)
define('DB_CHARSET', 'utf8mb4');


//! Configuration générale de l'application

// URL de base de l'application (ajuster selon le nom du dossier)
define('BASE_URL', 'http://localhost/mediatheque-tln-grp2/public');

// Nom de l'application (affiché dans le titre des pages)
define('APP_NAME', 'Mediatheque');

// Version actuelle de l'application (pour le suivi des mises à jour)
define('APP_VERSION', '1.0.0');

//! Configuration des chemins

// Chemin de la racine du projet
define('ROOT_PATH', dirname(__DIR__));

// Dossier contenant les fichiers de configuration (database.php et autres configs)
define('CONFIG_PATH', ROOT_PATH . '/config');

// Dossier contrôleurs MVC (gestion logique métier et interactions utilisateur)
define('CONTROLLER_PATH', ROOT_PATH . '/controllers');

// Dossier des modèles MVC (gestion accès aux données et logique de la BDD)
define('MODEL_PATH', ROOT_PATH . '/models');

// Dossier contenant les vues MVC (Templates HTML/PHP pour l'affichage)
define('VIEW_PATH', ROOT_PATH . '/views');

// Dossier des fichiers d'aide (helpers: Fonctions utilitaires partagées dans toute l'application)
define('INCLUDE_PATH', ROOT_PATH . '/includes');

// Dossier contenant le cœur du framework MVC (Classes principales : Router, Database, View)
define('CORE_PATH', ROOT_PATH . '/core');

// Dossier public accessible via l'URL (CSS, JS, images publiques, index.php)
define('PUBLIC_PATH', ROOT_PATH . '/public');

// Dossier pour uploader les images de couverture des médias
// Les fichiers doivent être placés dans le dossier public pour être accessibles via l'URL
define('UPLOADS_PATH', PUBLIC_PATH . '/uploads/covers');