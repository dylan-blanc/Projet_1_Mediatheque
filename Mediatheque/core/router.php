<?php
/**
 * Système de Routage MVC
 * Dirige les requêtes vers les bons contrôleurs et actions
 */

// Variables globales
$admin_controller_loaded = false;

// Fonctions de gestion des contrôleurs

/**
 * Charge le contrôleur admin si nécessaire
 */
function ensure_admin_controller_loaded() {
    global $admin_controller_loaded;
    
    // Vérifier si le contrôleur admin est déjà chargé
    if (!$admin_controller_loaded) {
        $controller_file = CONTROLLER_PATH . '/admin_controller.php';
        
        // Vérifier que le fichier existe avant de l'inclure
        if (file_exists($controller_file)) {
            require_once $controller_file;
            $admin_controller_loaded = true;
            return true;
        } else {
            // Fichier contrôleur introuvable
            error_log("Contrôleur admin introuvable : " . $controller_file);
            return false;
        }
    }
    
    // Contrôleur déjà chargé
    return true;
}


function parse_request_url() {
    // Récupérer l'URL depuis les paramètres GET (configuré dans .htaccess)
    $url = $_GET['url'] ?? '';
    
    // Nettoyer l'URL : supprimer les slashes en fin et appliquer des filtres
    $url = rtrim($url, '/');
    $url = filter_var($url, FILTER_SANITIZE_URL);
    
    // Si l'URL est vide, rediriger vers la page d'accueil par défaut
    if (empty($url)) {
        return ['controller' => 'home', 'action' => 'index', 'params' => []];
    }
    
    // Diviser l'URL en parties séparées par des slashes
    $url_parts = explode('/', $url);
    
    // Extraire les composants de l'URL
    $controller = $url_parts[0] ?? 'home';  // Premier segment = contrôleur
    $action = $url_parts[1] ?? 'index';     // Deuxième segment = action
    $params = array_slice($url_parts, 2);   // Segments suivants = paramètres
    
    return [
        'controller' => $controller,
        'action' => $action,
        'params' => $params
    ];
}


function dispatch() {
    // Parser l'URL pour extraire les composants de routage
    $route = parse_request_url();
    
    $controller_name = $route['controller'];
    $action_name = $route['action'];
    $params = $route['params'];
    
    
    // Les routes admin ont un traitement particulier car elles
    // utilisent un seul contrôleur avec plusieurs actions
    if ($controller_name === 'admin') {
        handle_admin_routes($action_name, $params);
        return;
    }
    
   
    // Construire le chemin vers le fichier contrôleur
    // Convention : {controller}_controller.php
    $controller_file = CONTROLLER_PATH . '/' . $controller_name . '_controller.php';
    
    // Vérifier si le contrôleur existe avant de l'inclure
    if (!file_exists($controller_file)) {
        error_log("Contrôleur introuvable : " . $controller_file);
        load_404();
        return;
    }
    
    // Charger le fichier contrôleur
    require_once $controller_file;
    
    
    // Convention de nommage : {controller}_{action}
    // Exemple : media_library, home_index, auth_login
    $action_function = $controller_name . '_' . $action_name;
    
    // Vérifier si la fonction d'action existe
    if (!function_exists($action_function)) {
        error_log("Action introuvable : " . $action_function);
        load_404();
        return;
    }
    
    // Exécuter l'action avec gestion robuste des erreurs
    try {
        // Essayer d'exécuter avec tous les paramètres
        call_user_func_array($action_function, $params);
        
    } catch (ArgumentCountError $e) {
        // Si pas assez de paramètres, essayer sans paramètres
        try {
            call_user_func($action_function);
        } catch (Exception $e) {
            error_log("Erreur lors de l'exécution de l'action : " . $e->getMessage());
            load_404();
        }
        
    } catch (Exception $e) {
        // Toute autre erreur pendant l'exécution
        error_log("Erreur lors de l'exécution de l'action : " . $e->getMessage());
        load_404();
    }
}


function handle_admin_routes($action, $params) {
    // S'assurer que le contrôleur admin est chargé avant d'exécuter les actions
    if (!ensure_admin_controller_loaded()) {
        error_log("Impossible de charger le contrôleur admin");
        load_404();
        return;
    }
   
    switch ($action) {
        // Tableau de bord principal de l'administration
        case 'dashboard':
        case 'index':
            admin_dashboard();
            break;
            
        // Liste et gestion globale des utilisateurs
        case 'users':
            admin_users();
            break;
            
        // Gestion détaillée d'un utilisateur spécifique
        case 'user':
            if (isset($params[0]) && is_numeric($params[0])) {
                if (isset($params[1])) {
                    // Routes sous-utilisateur avec actions spécifiques
                    // Exemples : admin/user/123/loans, admin/user/123/return_all
                    handle_user_sub_routes($params[0], $params[1], array_slice($params, 2));
                } else {
                    // Route simple : admin/user/123 (affichage détail utilisateur)
                    admin_user_detail($params[0]);
                }
            } else {
                // ID utilisateur manquant ou invalide
                error_log("ID utilisateur manquant ou invalide pour admin/user");
                load_404();
            }
            break;
            
        // Action de retour forcé d'un emprunt (admin uniquement)
        case 'force_return':
            if (isset($params[0]) && is_numeric($params[0])) {
                admin_force_single_return($params[0]);
            } else {
                load_404();
            }
            break;
            
        // Gestion des événements
        case 'evenements':
            require_once CONTROLLER_PATH . '/admin_evenement_controller.php';
            admin_evenements_list();
            break;
            
        case 'evenement':
            require_once CONTROLLER_PATH . '/admin_evenement_controller.php';
            if (isset($params[0])) {
                if ($params[0] === 'add') {
                    admin_evenement_edit(null);
                } elseif ($params[0] === 'edit' && isset($params[1])) {
                    admin_evenement_edit($params[1]);
                } elseif ($params[0] === 'delete' && isset($params[1])) {
                    admin_evenement_delete($params[1]);
                } else {
                    load_404();
                }
            } else {
                load_404();
            }
            break;
            
        // Gestion des genres
        case 'genres':
            require_once CONTROLLER_PATH . '/admin_genre_controller.php';
            admin_genres_list();
            break;
            
        case 'genre':
            require_once CONTROLLER_PATH . '/admin_genre_controller.php';
            if (isset($params[0])) {
                if ($params[0] === 'add') {
                    admin_genre_add();
                } elseif ($params[0] === 'edit' && isset($params[1])) {
                    admin_genre_edit($params[1]);
                } elseif ($params[0] === 'delete' && isset($params[1])) {
                    admin_genre_delete($params[1]);
                } else {
                    load_404();
                }
            } else {
                load_404();
            }
            break;
            
        default:
            // Essayer d'appeler la fonction admin_$action
            $function_name = 'admin_' . $action;
            if (function_exists($function_name)) {
                call_user_func_array($function_name, $params);
            } else {
                load_404();
            }
            break;
    }
}

/**
 * Gère les sous-routes pour les utilisateurs
 */
function handle_user_sub_routes($user_id, $sub_action, $params) {
    // Charger le contrôleur admin si pas déjà chargé
    if (!ensure_admin_controller_loaded()) {
        load_404();
        return;
    }
    
    switch ($sub_action) {
        case 'loans':
            // Afficher les emprunts de l'utilisateur
            // Note: admin_user_loans() est définie dans admin_controller.php (chargé dynamiquement)
            if (function_exists('admin_user_loans')) {
                call_user_func('admin_user_loans', $user_id);
            } else {
                load_404();
            }
            break;
            
            
        case 'return_all':
            // Forcer le retour de tous les emprunts en retard
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (function_exists('admin_user_return_all_overdue')) {
                    call_user_func('admin_user_return_all_overdue', $user_id);
                } else {
                    load_404();
                }
            } else {
                load_404();
            }
            break;
            
        case 'delete':
            // Supprimer l'utilisateur
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (function_exists('admin_delete_user')) {
                    call_user_func('admin_delete_user', $user_id);
                } else {
                    load_404();
                }
            } else {
                load_404();
            }
            break;
            
        default:
            load_404();
            break;
    }
}

/**
 * Charge la page 404
 */
function load_404() {
    http_response_code(404);
    require_once VIEW_PATH . '/errors/404.php';
}
