<?php
// Modèle pour les utilisateurs

/**
 * Récupère un utilisateur par son email
 */
function get_user_by_email($email) {
    $query = "SELECT * FROM users WHERE email = ? AND (role IS NULL OR role != 'deleted') LIMIT 1";
    return db_select_one($query, [$email]);
}

/**
 * Récupère un utilisateur par son ID
 */
function get_user_by_id($id)
{
    $query = "SELECT * FROM users WHERE id = ? LIMIT 1";
    return db_select_one($query, [$id]);
}

/**
 * Crée un nouvel utilisateur
 */
function create_user($prenom, $nom, $email, $password) {
    $hashed_password = hash_password($password);
    $query = "INSERT INTO users (prenom, nom, email, password, created_at) VALUES (?, ?, ?, ?, NOW())";
    
    if (db_execute($query, [$prenom, $nom, $email, $hashed_password])) {
        return db_last_insert_id();
    }

    return false;
}

/**
 * Met à jour un utilisateur (mise à jour)
 */
function update_user($id, $data)
{
    $fields = [];
    $params = [];

    if (isset($data['prenom'])) {
        $fields[] = 'prenom = ?';
        $params[] = $data['prenom'];
    }

    if (isset($data['nom'])) {
        $fields[] = 'nom = ?';
        $params[] = $data['nom'];
    }

    if (isset($data['email'])) {
        $fields[] = 'email = ?';
        $params[] = $data['email'];
    }

    if (isset($data['password'])) {
        $fields[] = 'password = ?';
        $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
    }

    $fields[] = 'updated_at = NOW()';
    $params[] = $id;

    $query = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
    return db_execute($query, $params);
}

/**
 * Met à jour le mot de passe d'un utilisateur
 */
function update_user_password($id, $password)
{
    $hashed_password = hash_password($password);
    $query = "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?";
    return db_execute($query, [$hashed_password, $id]);
}


/**
 * Supprime un utilisateur 
 * - Si aucun emprunt : suppression complète
 * - Sinon : anonymisation pour conserver l'historique
 */
function delete_user($id) {
    // Vérifier si l'utilisateur a des emprunts
    $query_check = "SELECT COUNT(*) as count FROM emprunts WHERE user_id = ?";
    $result = db_query($query_check, [$id]);
    $has_loans = $result && $result[0]['count'] > 0;
    
    if ($has_loans) {
        // Utilisateur avec historique d'emprunts : ANONYMISATION
        // Anonymiser l'utilisateur (préserve l'historique)
        $query = "UPDATE users SET 
                  email = CONCAT('utilisateur_supprime_', id, '@anonyme.local'),
                  prenom = 'Utilisateur',
                  nom = 'Supprimé',
                  password = '',
                  role = 'deleted',
                  updated_at = NOW()
                  WHERE id = ?";
        return db_execute($query, [$id]);
    } else {
        // Aucun emprunt : SUPPRESSION COMPLÈTE
        $query = "DELETE FROM users WHERE id = ?";
        return db_execute($query, [$id]);
    }
}

/**
 * Récupère tous les utilisateurs (mise à jour : filtres et pagination avec stats emprunts)
 */
function get_all_users($filters = [], $limit = 20, $offset = 0) {
     // Requête avec sous-requête pour les statistiques d'emprunts
     $query = "SELECT u.id, u.prenom, u.nom, u.email, u.role, u.created_at,
               COALESCE(e_stats.total_emprunts, 0) as total_emprunts,
               COALESCE(e_stats.emprunts_en_cours, 0) as emprunts_en_cours,
               COALESCE(e_stats.emprunts_en_retard, 0) as emprunts_en_retard,
               COALESCE(e_stats.emprunts_rendus, 0) as emprunts_rendus
               FROM users u
               LEFT JOIN (
                   SELECT 
                       user_id,
                       COUNT(*) as total_emprunts,
                       SUM(CASE WHEN statut = 'En cours' THEN 1 ELSE 0 END) as emprunts_en_cours,
                       SUM(CASE WHEN statut = 'En retard' OR (statut = 'En cours' AND date_retour_prevue < CURDATE()) THEN 1 ELSE 0 END) as emprunts_en_retard,
                       SUM(CASE WHEN statut = 'Rendu' THEN 1 ELSE 0 END) as emprunts_rendus
                   FROM emprunts 
                   GROUP BY user_id
               ) e_stats ON u.id = e_stats.user_id";
     
     $params = [];
     $conditions = [];
     
     // Filtrer par statut
     if (isset($filters['status']) && !empty($filters['status'])) {
         switch ($filters['status']) {
             case 'all':
                 // Tous les utilisateurs (y compris supprimés)
                 // Pas de condition supplémentaire
                 break;
             case 'active':
                 // Actifs uniquement : avec emprunts en cours ET non supprimés
                 $conditions[] = "(u.role IS NULL OR u.role != 'deleted')";
                 $conditions[] = "e_stats.emprunts_en_cours > 0";
                 break;
             case 'inactive':
                 // Inactifs uniquement : sans emprunts en cours ET non supprimés
                 $conditions[] = "(u.role IS NULL OR u.role != 'deleted')";
                 $conditions[] = "(e_stats.emprunts_en_cours IS NULL OR e_stats.emprunts_en_cours = 0)";
                 break;
             case 'deleted':
                 // Supprimés uniquement
                 $conditions[] = "u.role = 'deleted'";
                 break;
             default:
                 // Par défaut : tous les utilisateurs non supprimés
                 $conditions[] = "(u.role IS NULL OR u.role != 'deleted')";
         }
     } else {
         // Par défaut si aucun filtre : tous les utilisateurs non supprimés
         $conditions[] = "(u.role IS NULL OR u.role != 'deleted')";
     }
    
     if (!empty($filters['search'])) {
         // Recherche dans nom, prénom, email
         $conditions[] = "(u.prenom LIKE ? OR u.nom LIKE ? OR u.email LIKE ? OR CONCAT(u.prenom, ' ', u.nom) LIKE ?)";
         $search = '%' . $filters['search'] . '%';
         $params = array_merge($params, [$search, $search, $search, $search]);
     }
    
     if (!empty($filters['role']) && $filters['role'] !== 'deleted') {
         $conditions[] = "u.role = ?";
         $params[] = $filters['role'];
     }
    
     if (!empty($conditions)) {
        $query .= " WHERE " . implode(' AND ', $conditions);
    }

    // Tri dynamique selon le filtre 'sort'
    $order_by = "u.nom ASC, u.prenom ASC"; // Par défaut : tri par nom
    if (!empty($filters['sort'])) {
        switch ($filters['sort']) {
            case 'nom':
                $order_by = "u.nom ASC, u.prenom ASC";
                break;
            case 'email':
                $order_by = "u.email ASC";
                break;
            case 'inscription':
                $order_by = "u.created_at DESC";
                break;
            case 'activite':
                $order_by = "e_stats.total_emprunts DESC, u.nom ASC";
                break;
            default:
                $order_by = "u.nom ASC, u.prenom ASC";
        }
    }

    $query .= " ORDER BY $order_by LIMIT ? OFFSET ?";
    $params = array_merge($params, [$limit, $offset]);

    return db_select($query, $params);
}

/**
 * Compte le nombre total d'utilisateurs (mise à jour : count_users en count_all_users )
 */
function count_all_users($filters = []) {
    $query = "SELECT COUNT(DISTINCT u.id) as total FROM users u
              LEFT JOIN (
                  SELECT 
                      user_id,
                      SUM(CASE WHEN statut = 'En cours' THEN 1 ELSE 0 END) as emprunts_en_cours
                  FROM emprunts 
                  GROUP BY user_id
              ) e_stats ON u.id = e_stats.user_id";
    $params = [];
    $conditions = [];
    
    // Filtrer par statut
    if (isset($filters['status']) && !empty($filters['status'])) {
        switch ($filters['status']) {
            case 'all':
                // Tous les utilisateurs (y compris supprimés)
                // Pas de condition supplémentaire
                break;
            case 'active':
                // Actifs uniquement : avec emprunts en cours ET non supprimés
                $conditions[] = "(u.role IS NULL OR u.role != 'deleted')";
                $conditions[] = "e_stats.emprunts_en_cours > 0";
                break;
            case 'inactive':
                // Inactifs uniquement : sans emprunts en cours ET non supprimés
                $conditions[] = "(u.role IS NULL OR u.role != 'deleted')";
                $conditions[] = "(e_stats.emprunts_en_cours IS NULL OR e_stats.emprunts_en_cours = 0)";
                break;
            case 'deleted':
                // Supprimés uniquement
                $conditions[] = "u.role = 'deleted'";
                break;
            default:
                // Par défaut : tous les utilisateurs non supprimés
                $conditions[] = "(u.role IS NULL OR u.role != 'deleted')";
        }
    } else {
        // Par défaut si aucun filtre : tous les utilisateurs non supprimés
        $conditions[] = "(u.role IS NULL OR u.role != 'deleted')";
    }
    
    if (!empty($filters['search'])) {
        // Recherche dans nom, prénom, email
        $conditions[] = "(u.prenom LIKE ? OR u.nom LIKE ? OR u.email LIKE ? OR CONCAT(u.prenom, ' ', u.nom) LIKE ?)";
        $search = '%' . $filters['search'] . '%';
        $params = array_merge($params, [$search, $search, $search, $search]);
    }
    
    if (!empty($filters['role']) && $filters['role'] !== 'deleted') {
        $conditions[] = "u.role = ?";
        $params[] = $filters['role'];
    }
    
    if (!empty($conditions)) {
        $query .= " WHERE " . implode(' AND ', $conditions);
    }
    
    $result = db_select_one($query, $params);
    return $result['total'] ?? 0;
}


/**
 * Vérifie si un email existe déjà
 */
function email_exists($email, $exclude_id = null)
{
    $query = "SELECT COUNT(*) as count FROM users WHERE email = ?";
    $params = [$email];

    if ($exclude_id) {
        $query .= " AND id != ?";
        $params[] = $exclude_id;
    }

    $result = db_select_one($query, $params);
    return $result['count'] > 0;
}

// function rajouter (ne pas modifier tout ce qu"il y a au dessus)

//_______________________________________________________________________________________________________________________________________________________________________________
//_______________________________________________________________________________________________________________________________________________________________________________
//_______________________________________________________________________________________________________________________________________________________________________________
//_______________________________________________________________________________________________________________________________________________________________________________
//_______________________________________________________________________________________________________________________________________________________________________________

/**
 * Crée un nouvel utilisateur
 */

// Fonctions de validation et vérification


/**
 * Compte les emprunts en cours d'un utilisateur
 */
function count_user_current_loans($user_id)
{
    $query = "SELECT COUNT(*) as count FROM emprunts WHERE user_id = ? AND statut = 'En cours'";
    $result = db_select_one($query, [$user_id]);
    return $result ? $result['count'] : 0;
}

/**
 * Récupère l'historique des emprunts d'un utilisateur
 */
function get_user_loan_history($user_id, $limit = 20)
{
    // S'assurer que les statuts en retard sont à jour avant de récupérer l'historique
    // (met à jour les emprunts 'En cours' dont la date_retour_prevue est dépassée)
    if (file_exists(MODEL_PATH . '/emprunt_model.php')) {
        require_once MODEL_PATH . '/emprunt_model.php';
        if (function_exists('update_overdue_statuses')) {
            update_overdue_statuses();
        }
    }

    $query = "SELECT e.*, m.titre, m.type, m.image 
              FROM emprunts e 
              JOIN medias m ON e.media_id = m.id 
              WHERE e.user_id = ? 
              ORDER BY e.date_emprunt DESC 
              LIMIT ?";

    return db_select($query, [$user_id, $limit]);
}

/**
 * Compte le nombre total d'emprunts d'un utilisateur
 */
function count_user_total_loans($user_id)
{
    $query = "SELECT COUNT(*) as total FROM emprunts WHERE user_id = ?";
    $result = db_select_one($query, [$user_id]);
    return $result ? $result['total'] : 0;
}

/**
 * Compte le nombre d'emprunts en retard d'un utilisateur
 */
function count_user_overdue_loans($user_id)
{
    // Compte les emprunts en retard : soit déjà marqués 'En retard', soit encore 'En cours' mais date dépassée
    $query = "SELECT COUNT(*) as total FROM emprunts 
              WHERE user_id = ? AND (statut = 'En retard' OR (statut = 'En cours' AND date_retour_prevue < CURDATE()))";
    $result = db_select_one($query, [$user_id]);
    return $result ? $result['total'] : 0;
}

/**
 * Récupère les utilisateurs les plus actifs
 */
function get_most_active_users($limit = 5)
{
    $query = "SELECT u.id, u.prenom, u.nom, u.email, COUNT(e.id) as total_emprunts
              FROM users u 
              LEFT JOIN emprunts e ON u.id = e.user_id 
              WHERE u.role = 'user'
              GROUP BY u.id, u.prenom, u.nom, u.email
              ORDER BY total_emprunts DESC 
              LIMIT ?";
    return db_select($query, [$limit]);
}


/**
 * Compte le nombre total d'utilisateurs (version publique)
 */
function count_all_users_public()
{
    $query = "SELECT COUNT(*) as total FROM users WHERE role = 'user'";
    $result = db_select_one($query);
    return $result ? $result['total'] : 0;
}


function get_logged_user()
{
    // Vérification de l'existence d'une session active
    if (!isset($_SESSION['user_id'])) {
        return false;
    }

    // Récupération des données utilisateur par ID
    return get_user_by_id($_SESSION['user_id']);
}


function update_user_profile($user_id, $prenom, $nom, $email)
{
    // Vérification de l'unicité de l'email (en excluant l'utilisateur actuel)
    if (email_exists($email, $user_id)) {
        return false; // Email déjà utilisé par un autre utilisateur
    }

    // Requête de mise à jour des informations personnelles
    $query = "UPDATE users 
              SET prenom = ?, nom = ?, email = ?, updated_at = NOW() 
              WHERE id = ?";

    return db_execute($query, [$prenom, $nom, $email, $user_id]);
}
