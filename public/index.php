<?php

/**
 * Point d'entrée principal de l'application
 * Toutes les requêtes passent par ce fichier
 */

// Configuration du reporting d'erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Démarrage de la session
session_start();

try {
    // Configuration
    require_once '../config/database.php';

    // Fichiers core
    require_once CORE_PATH . '/database.php';
    require_once CORE_PATH . '/router.php';
    require_once CORE_PATH . '/view.php';

    // Fonctions utilitaires
    require_once INCLUDE_PATH . '/helpers.php';

    // Vérification timeout session (2h d'inactivité)
    if (isset($_SESSION['user_id'])) {
        if (!isset($_SESSION['last_activity'])) {
            $_SESSION['last_activity'] = time();
        }

        $timeout_duration = 2 * 60 * 60; // 2 heures
        $inactive_time = time() - $_SESSION['last_activity'];

        if ($inactive_time > $timeout_duration) {
            $message = 'Votre session a expiré après 2 heures d\'inactivité.';
            session_destroy();
            session_start();
            set_flash('warning', $message);
        } else {
            $_SESSION['last_activity'] = time();
        }
    }

    // Modèles de données
    require_once MODEL_PATH . '/user_model.php';
    require_once MODEL_PATH . '/livre_model.php';
    require_once MODEL_PATH . '/media_model.php';
    require_once MODEL_PATH . '/emprunt_model.php';
    require_once MODEL_PATH . '/admin_model.php';

    // Lancer le système de routing
    dispatch();
} catch (Exception $e) {
    echo "Erreur fatale: " . $e->getMessage();
    echo "<br>Fichier: " . $e->getFile();
    echo "<br>Ligne: " . $e->getLine();
}
