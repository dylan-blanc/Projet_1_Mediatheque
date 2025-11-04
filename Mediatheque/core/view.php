<?php
/**
 * Système de Vues et Templating
 * Gère l'affichage des vues avec le pattern MVC
 */

// Fonctions de chargement de vues

/**
 * Charge une vue avec des données
 */
function load_view($view, $data = []) {
    // Extraire les données du tableau en variables individuelles
    if (!empty($data)) {
        extract($data, EXTR_SKIP); // EXTR_SKIP évite d'écraser les variables existantes
    }
    
    // Construire le chemin complet vers le fichier de vue
    $view_file = VIEW_PATH . '/' . $view . '.php';
    
    // Vérifier que le fichier de vue existe avant de l'inclure
    if (!file_exists($view_file)) {
        error_log("Vue non trouvée : " . $view_file);
        die("Vue non trouvée : $view");
    }
    
    // Inclure le fichier de vue (les variables sont disponibles dans ce contexte)
    require $view_file;
}


function load_view_with_layout($view, $data = [], $layout = 'layout') {
    // Démarrer la capture de sortie pour récupérer le HTML de la vue
    ob_start();
    
    // Charger et rendre la vue demandée
    load_view($view, $data);
    
    // Récupérer tout le contenu généré par la vue et arrêter la capture
    $content = ob_get_clean();
    
    // Ajouter le contenu rendu aux données pour le layout
    // Le layout pourra accéder au contenu via $content
    $data['content'] = $content;
    
    // Charger le layout avec le contenu intégré
    load_view('layouts/' . $layout, $data);
}


function include_partial($partial, $data = []) {
    // Extraire les données du tableau en variables individuelles
    if (!empty($data)) {
        extract($data);
    }
    
    // Chemin vers le fichier partial
    $partial_file = VIEW_PATH . '/partials/' . $partial . '.php';
    
    // Vérifier si le partial existe
    if (file_exists($partial_file)) {
        require $partial_file;
    }
}

/**
 * Affiche les messages flash
 */
function flash_messages() {
    if (isset($_SESSION['flash_messages'])) {
        foreach ($_SESSION['flash_messages'] as $type => $messages) {
            if (is_array($messages)) {
                foreach ($messages as $message) {
                    echo "<div class='alert alert-$type'>$message</div>";
                }
            } else {
                echo "<div class='alert alert-$type'>$messages</div>";
            }
        }
        unset($_SESSION['flash_messages']);
    }
} 