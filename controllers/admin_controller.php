<?php
// Contrôleur d'administration

// Charger les models nécessaires
require_once MODEL_PATH . '/admin_model.php';
require_once MODEL_PATH . '/user_model.php';
require_once MODEL_PATH . '/media_model.php';
require_once MODEL_PATH . '/emprunt_model.php';

/**
 * Tableau de bord administrateur
 */
function admin_dashboard() {
    require_admin();
    
    // Récupération des statistiques
    $stats = get_dashboard_stats();
    
    $data = [
        'title' => 'Tableau de bord - Administration',
        'stats' => $stats
    ];
    
    load_view_with_layout('admin/dashboard', $data);
}

/**
 * Gestion des utilisateurs
 */
function admin_users() {
    require_admin();
    
    // Pagination
    $page = max(1, intval(get('page', 1)));
    $limit = 20;
    $offset = ($page - 1) * $limit;
    
    // Filtres
    $filters = [
        'search' => trim(get('search', '')),
        'status' => get('status', 'all'), // all, active, inactive, deleted
        'sort' => get('sort', 'nom')
    ];
    
    // Récupération des utilisateurs avec filtres
    $users = get_all_users($filters, $limit, $offset);
    $total_users = count_all_users($filters);
    
    // Statistiques des utilisateurs
    $users_stats = get_users_stats();
    $stats = [
        'total_users' => count_all_users(['status' => 'active']),
        'deleted_users' => count_all_users(['status' => 'deleted']),
        'users_with_loans' => $users_stats['users_with_loans'] ?? 0, // calciulé dans le model si nécessaire
        'users_with_overdue' => $users_stats['users_with_overdue'] ?? 0 // calciulé dans le model si nécessaire
    ];
    
    // Pagination
    $total_pages = ceil($total_users / $limit);
    $pagination = [
        'current_page' => $page,
        'total_pages' => $total_pages,
        'total' => $total_users,
        'start' => $offset + 1,
        'end' => min($offset + $limit, $total_users)
    ];
    
    $data = [
        'title' => 'Gestion des utilisateurs',
        'users' => $users,
        'stats' => $stats,
        'filters' => $filters,
        'pagination' => $pagination
    ];
    
    load_view_with_layout('admin/users', $data);
}

/**
 * Détail d'un utilisateur
 */
function admin_user_detail($user_id) {
    require_admin();
    
    $user_id = intval($user_id);
    $user = get_user_with_stats($user_id);
    
    if (!$user) {
        set_flash('error', 'Utilisateur introuvable.');
        redirect('admin/users');
        return;
    }
    
    // Mettre à jour les statuts des emprunts en retard avant lecture
    // (s'assure que les compteurs et affichages sont cohérents)
    update_overdue_statuses();

    // Emprunts en cours
    $emprunts_en_cours = get_user_current_loans($user_id);

    // Historique des emprunts
    $historique_emprunts = get_user_loans_history($user_id);
    
    $data = [
        'title' => 'Détail utilisateur - ' . $user['prenom'] . ' ' . $user['nom'],
        'user' => $user,
        'emprunts_en_cours' => $emprunts_en_cours,
        'historique_emprunts' => $historique_emprunts
    ];
    
    load_view_with_layout('admin/user_detail', $data);
}

/**
 * Force le retour d'un emprunt spécifique (Version simplifiée)
 * Route: POST /admin/force_return/{emprunt_id}
 * 
 * @param int $emprunt_id ID de l'emprunt à retourner
 */
function admin_force_single_return($emprunt_id) {
    // SÉCURITÉ : Vérification obligatoire des droits administrateur
    require_admin();
    
    // SÉCURITÉ : Accepter uniquement les requêtes POST
    // Protection contre les attaques CSRF et les liens malveillants
    if (!is_post()) {
        redirect('admin/dashboard');
        return;
    }
    
    // Validation et conversion sécurisée de l'ID d'emprunt
    $emprunt_id = intval($emprunt_id);
    
    if ($emprunt_id <= 0) {
        set_flash('error', 'ID d\'emprunt invalide.');
        redirect('admin/loans');
        return;
    }
    
    // Exécution du retour forcé avec restock automatique
    $result = admin_force_return_emprunt($emprunt_id);
    
    // Traitement du résultat et notification à l'administrateur
    if ($result) {
        // Succès : notification positive avec mention du restock
        set_flash('success', 'Le retour du média a été forcé avec succès et le stock a été restauré.');
        redirect('admin/loans');
    } else {
        // Échec : notification d'erreur et redirection
        set_flash('error', 'Erreur lors du retour forcé du média.');
        redirect('admin/loans');
    }
}



/**
 * Afficher les emprunts d'un utilisateur
 */
function admin_user_loans($user_id) {
    require_admin();
    
    $user_id = intval($user_id);
    $user = get_user_by_id($user_id);
    
    if (!$user) {
        set_flash('error', 'Utilisateur introuvable.');
        redirect('admin/users');
        return;
    }
    
    // Mettre à jour les statuts des emprunts en retard avant lecture
    update_overdue_statuses();

    // Emprunts en cours
    $emprunts_en_cours = get_user_current_loans($user_id);

    // Historique complet
    $historique_emprunts = get_user_loans_history($user_id, 100);
    
    $data = [
        'title' => 'Emprunts de ' . $user['prenom'] . ' ' . $user['nom'],
        'user' => $user,
        'emprunts_en_cours' => $emprunts_en_cours,
        'historique_emprunts' => $historique_emprunts
    ];
    
    load_view_with_layout('admin/user_loans', $data);
}

/**
 * Gestion générale des emprunts (tous les emprunts)
 */
function admin_loans() {
    require_admin();
    
    // Pagination
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = 50;
    $offset = ($page - 1) * $limit;
    
    // Filtres
    $filters = [
        'statut' => $_GET['statut'] ?? '',
        'retard' => $_GET['retard'] ?? '',
        'user_search' => $_GET['user_search'] ?? ''
    ];
    
    // Récupération des emprunts avec filtres
    $emprunts = get_all_loans_admin($filters, $limit, $offset);
    $total_emprunts = count_all_loans_admin($filters);
    $total_pages = ceil($total_emprunts / $limit);
    
    // Statistiques rapides
    $stats = [
        'en_cours' => count_loans_by_status('En cours'),
        'en_retard' => count_overdue_loans(),
        'total_today' => count_loans_today()
    ];
    
    $data = [
        'title' => 'Gestion des emprunts',
        'emprunts' => $emprunts,
        'current_page' => $page,
        'total_pages' => $total_pages,
        'total_emprunts' => $total_emprunts,
        'filters' => $filters,
        'stats' => $stats
    ];
    
    load_view_with_layout('admin/loans', $data);
}


/**
 * Update media with stock recalculation
 */
function admin_update_media($media_id) {
    require_admin();
    
    if (!is_post()) {
        redirect('admin/medias');
        return;
    }
    
    $media_id = intval($media_id);
    $titre = clean_input(post('titre'));
    $auteur = clean_input(post('auteur'));
    $type = clean_input(post('type'));
    $annee = intval(post('annee'));
    $description = clean_input(post('description'));
    $quantite_totale = intval(post('quantite_totale'));
    
    // Update media (with automatic availability recalculation)
    $result = update_media($media_id, $titre, $auteur, $type, $annee, $description, $quantite_totale);
    
    if ($result) {
        set_flash('success', 'Media updated successfully.');
    } else {
        set_flash('error', 'Error updating media.');
    }
    
    redirect('admin/medias');
}

/**
 * Force le retour de tous les emprunts en retard d'un utilisateur
 */
function admin_user_return_all_overdue($user_id) {
    require_admin();
    
    if (!is_post()) {
        redirect('admin/user_detail/' . $user_id);
        return;
    }
    
    $user_id = intval($user_id);
    
    // Vérifier que l'utilisateur existe
    $user = get_user_by_id($user_id);
    if (!$user) {
        set_flash('error', 'Utilisateur introuvable.');
        redirect('admin/users');
        return;
    }
    
    // Forcer le retour de tous les emprunts en retard
    $returned_count = admin_force_return_all_overdue_for_user($user_id);
    
    if ($returned_count > 0) {
        set_flash('success', $returned_count . ' emprunt(s) en retard ont été retournés avec succès.');
    } else {
        set_flash('info', 'Aucun emprunt en retard à retourner.');
    }
    
    redirect('admin/user_detail/' . $user_id);
}

/**
 * Supprimer un utilisateur (archive pour conserver l'historique)
 */
function admin_delete_user($user_id) {
    require_admin();
    
    if (!is_post()) {
        redirect('admin/users');
        return;
    }
    
    $user_id = intval($user_id);
    
    // Vérifier que l'utilisateur existe
    $user = get_user_by_id($user_id);
    if (!$user) {
        set_flash('error', 'Utilisateur introuvable.');
        redirect('admin/users');
        return;
    }
    
    // VALIDATION : Vérifier qu'il n'a pas d'emprunts en cours
    $emprunts_en_cours = get_user_current_loans($user_id);
    if (!empty($emprunts_en_cours)) {
        set_flash('error', 'Impossible de supprimer cet utilisateur : il a encore ' . count($emprunts_en_cours) . ' emprunt(s) en cours. Veuillez d\'abord forcer le retour de ses médias.');
        redirect('admin/user/' . $user_id);
        return;
    }
    
    // Supprimer (archiver) l'utilisateur
    $result = delete_user($user_id);
    
    if ($result) {
        set_flash('success', 'Utilisateur supprimé avec succès. L\'historique des emprunts a été conservé.');
    } else {
        set_flash('error', 'Erreur lors de la suppression de l\'utilisateur.');
    }
    
    redirect('admin/users');
}
?>
