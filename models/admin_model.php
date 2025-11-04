<?php
// Modèle d'administration - gestion des statistiques et fonctions admin

/**
 * Récupère les emprunts en retard avec détails
 */
function get_overdue_loans_details()
{
    // Récupère tous les emprunts en retard avec calcul des jours de retard
    $query = "
        SELECT 
            e.id,
            e.date_retour_prevue,
            DATEDIFF(CURDATE(), e.date_retour_prevue) as jours_retard,
            u.prenom,
            u.nom,
            u.email,
            m.titre,
            m.type
        FROM emprunts e
        JOIN users u ON e.user_id = u.id
        JOIN medias m ON e.media_id = m.id
    WHERE (e.statut = 'En retard' OR (e.statut = 'En cours' AND e.date_retour_prevue < CURDATE()))
        ORDER BY e.date_retour_prevue ASC
    ";

    return db_select_all($query);
}

/**
 * Récupère toutes les statistiques pour le tableau de bord
 */
function get_dashboard_stats()
{
    return [
        // Statistiques des médias
        'total_medias' => count_all_medias(),
        'total_livres' => count_medias_by_type('livre'),
        'total_films' => count_medias_by_type('film'),
        'total_jeux' => count_medias_by_type('jeu'),

        // Statistiques des utilisateurs
        'total_users' => count_all_users([]),

        // Statistiques des emprunts
        'total_emprunts' => count_total_loans(),
        'emprunts_en_cours' => count_current_loans(),
        'emprunts_en_retard' => count_overdue_loans(),
        'emprunts_termines' => count_returned_loans(),

        // Données détaillées
        'emprunts_retard_details' => get_overdue_loans_details(),
        'derniers_emprunts' => get_recent_loans(5),
        'utilisateurs_actifs' => get_most_active_users(5)
    ];
}

/**
 * Récupère les emprunts récents
 */
function get_recent_loans($limit = 10)
{
    $query = "SELECT e.*, u.prenom, u.nom, m.titre, m.type
              FROM emprunts e
              JOIN users u ON e.user_id = u.id
              JOIN medias m ON e.media_id = m.id
              ORDER BY e.date_emprunt DESC
              LIMIT ?";
    return db_select($query, [$limit]);
}

/**
 * Récupération des utilisateurs avec filtres
 */
function get_users_with_filters($filters = [], $limit = 20, $offset = 0)
{
    $where_conditions = [];
    $params = [];

    // Filtre par recherche
    if (!empty($filters['search'])) {
        $where_conditions[] = "(u.nom LIKE ? OR u.prenom LIKE ? OR u.email LIKE ?)";
        $search_term = '%' . $filters['search'] . '%';
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
    }

    // Sous-requête pour les statistiques d'emprunts
    $stats_query = "
        LEFT JOIN (
            SELECT 
                user_id,
                COUNT(*) as total_emprunts,
                SUM(CASE WHEN statut = 'En cours' THEN 1 ELSE 0 END) as emprunts_en_cours,
                SUM(CASE WHEN statut = 'En retard' OR (statut = 'En cours' AND date_retour_prevue < CURDATE()) THEN 1 ELSE 0 END) as emprunts_en_retard,
                SUM(CASE WHEN statut = 'Rendu' THEN 1 ELSE 0 END) as emprunts_rendus
            FROM emprunts 
            GROUP BY user_id
        ) e_stats ON u.id = e_stats.user_id
    ";

    // Filtre par statut
    if (!empty($filters['status'])) {
        switch ($filters['status']) {
            case 'active':
                $where_conditions[] = "e_stats.emprunts_en_cours > 0";
                break;
            case 'overdue':
                $where_conditions[] = "e_stats.emprunts_en_retard > 0";
                break;
            case 'inactive':
                $where_conditions[] = "(e_stats.emprunts_en_cours IS NULL OR e_stats.emprunts_en_cours = 0)";
                break;
        }
    }

    $where_clause = '';
    if (!empty($where_conditions)) {
        $where_clause = ' WHERE ' . implode(' AND ', $where_conditions);
    }

    // Tri
    $order_by = 'u.nom ASC, u.prenom ASC';
    if (!empty($filters['sort'])) {
        switch ($filters['sort']) {
            case 'email':
                $order_by = 'u.email ASC';
                break;
            case 'inscription':
                $order_by = 'u.created_at DESC';
                break;
            case 'activite':
                $order_by = 'e_stats.total_emprunts DESC, u.nom ASC';
                break;
        }
    }

    // Requête principale pour les utilisateurs
    $query = "
        SELECT 
            u.*,
            COALESCE(e_stats.total_emprunts, 0) as total_emprunts,
            COALESCE(e_stats.emprunts_en_cours, 0) as emprunts_en_cours,
            COALESCE(e_stats.emprunts_en_retard, 0) as emprunts_en_retard,
            COALESCE(e_stats.emprunts_rendus, 0) as emprunts_rendus
        FROM users u
        $stats_query
        $where_clause
        ORDER BY $order_by
        LIMIT ? OFFSET ?
    ";

    $params[] = $limit;
    $params[] = $offset;

    $users = db_select_all($query, $params);

    // Comptage total
    $count_query = "
        SELECT COUNT(DISTINCT u.id)
        FROM users u
        $stats_query
        $where_clause
    ";

    $count_params = array_slice($params, 0, -2); // Retirer LIMIT et OFFSET
    $count_result = db_select_one($count_query, $count_params);
    $total = $count_result ? array_values($count_result)[0] : 0;

    return [
        'users' => $users,
        'total' => $total
    ];
}

/**
 * Statistiques des utilisateurs (exclu les utilisateurs supprimés)
 */
function get_users_stats()
{
    $query = "
        SELECT 
            COUNT(DISTINCT u.id) as total_users,
            COUNT(DISTINCT CASE WHEN e.statut = 'En cours' THEN u.id END) as users_with_loans,
            COUNT(DISTINCT CASE WHEN e.statut = 'En retard' OR (e.statut = 'En cours' AND e.date_retour_prevue < CURDATE()) THEN u.id END) as users_with_overdue
        FROM users u
        LEFT JOIN emprunts e ON u.id = e.user_id
        WHERE u.role != 'deleted'
    ";

    $result = db_select_one($query);
    return $result ?: ['total_users' => 0, 'users_with_loans' => 0, 'users_with_overdue' => 0];
}

/**
 * Récupération d'un utilisateur avec ses statistiques
 */
function get_user_with_stats($user_id)
{
    $query = "
        SELECT 
            u.*,
            COUNT(e.id) as total_emprunts,
            SUM(CASE WHEN e.statut = 'En cours' THEN 1 ELSE 0 END) as emprunts_en_cours,
            SUM(CASE WHEN e.statut = 'En retard' OR (e.statut = 'En cours' AND e.date_retour_prevue < CURDATE()) THEN 1 ELSE 0 END) as emprunts_en_retard,
            SUM(CASE WHEN e.statut = 'Rendu' THEN 1 ELSE 0 END) as emprunts_rendus
        FROM users u
        LEFT JOIN emprunts e ON u.id = e.user_id
        WHERE u.id = ?
        GROUP BY u.id
    ";

    return db_select_one($query, [$user_id]);
}

/**
 * Récupération de l'historique des emprunts d'un utilisateur
 */
function get_user_loans_history($user_id, $limit = 50)
{
    $query = "
        SELECT 
            e.*,
            m.titre,
            m.type,
            CONCAT_WS(', ',
                (SELECT g1.nom FROM genres g1 WHERE g1.id = m.genre_id_1),
                (SELECT g2.nom FROM genres g2 WHERE g2.id = m.genre_id_2),
                (SELECT g3.nom FROM genres g3 WHERE g3.id = m.genre_id_3),
                (SELECT g4.nom FROM genres g4 WHERE g4.id = m.genre_id_4),
                (SELECT g5.nom FROM genres g5 WHERE g5.id = m.genre_id_5)
            ) as genres,
            CASE 
                WHEN e.statut = 'En retard' OR (e.statut = 'En cours' AND e.date_retour_prevue < CURDATE()) THEN 1 
                ELSE 0 
            END as is_overdue,
            CASE 
                WHEN e.statut = 'En retard' OR (e.statut = 'En cours' AND e.date_retour_prevue < CURDATE()) THEN 
                    DATEDIFF(CURDATE(), e.date_retour_prevue)
                ELSE 0 
            END as jours_retard,
            CASE 
                WHEN e.statut = 'Rendu' AND e.date_retour_reelle > e.date_retour_prevue THEN 1
                ELSE 0 
            END as was_overdue
        FROM emprunts e
        JOIN medias m ON e.media_id = m.id
        WHERE e.user_id = ?
        ORDER BY e.date_emprunt DESC
        LIMIT ?
    ";

    return db_select_all($query, [$user_id, $limit]);
}

/**
 * Vérification ISBN pour admin (exclusion d'un ID)
 */
function isbn_exists_admin($isbn, $exclude_id = null)
{
    $query = "SELECT COUNT(*) as count FROM medias WHERE isbn = ? AND type = 'livre'";
    $params = [$isbn];

    if ($exclude_id) {
        $query .= " AND id != ?";
        $params[] = $exclude_id;
    }

    $result = db_select_one($query, $params);
    return $result && $result['count'] > 0;
}



