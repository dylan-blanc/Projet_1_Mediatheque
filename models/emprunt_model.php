<?php
// Modèle pour la gestion des emprunts

/**
 * Crée un nouvel emprunt
 */
function create_emprunt($user_id, $media_id) {
    // Début de la transaction pour assurer la cohérence
    db_execute("START TRANSACTION");
    
    try {
        // Insertion de l'emprunt
        $date_retour_prevue = date('Y-m-d', strtotime('+14 days')); // 14 jours d'emprunt
        $emprunt_query = "INSERT INTO emprunts (user_id, media_id, date_emprunt, date_retour_prevue, statut) 
                          VALUES (?, ?, NOW(), ?, 'En cours')";
        $emprunt_success = db_execute($emprunt_query, [$user_id, $media_id, $date_retour_prevue]);
        
        // Décrémentation du stock du média
        $stock_success = decrement_stock($media_id);
        
        // Si tout s'est bien passé, valider la transaction
        if ($emprunt_success && $stock_success) {
            db_execute("COMMIT");
            return true;
        } else {
            db_execute("ROLLBACK");
            return false;
        }
    } catch (Exception $e) {
        db_execute("ROLLBACK");
        return false;
    }
}

/**
 * Met à jour les statuts des emprunts passés en retard (exécuté avant les requêtes d'admin)
 */
function update_overdue_statuses() {
    // Marquer comme "En retard" tous les emprunts encore "En cours" et dont la date prévue est dépassée
    db_execute("UPDATE emprunts SET statut = 'En retard' WHERE statut = 'En cours' AND date_retour_prevue < CURDATE()");
}

/**
 * Retourne un média emprunté
 * Gère la transaction complète : mise à jour de l'emprunt + incrémentation du stock
 */
function return_emprunt($emprunt_id, $user_id) {
    // Début de la transaction pour assurer la cohérence
    db_execute("START TRANSACTION");
    
    try {
        // Récupération du media_id avant la mise à jour
        $media_query = "SELECT media_id FROM emprunts WHERE id = ? AND user_id = ? AND statut = 'En cours'";
        $media_result = db_select($media_query, [$emprunt_id, $user_id]);
        
        if (empty($media_result)) {
            db_execute("ROLLBACK");
            return false;
        }
        
        $media_id = $media_result[0]['media_id'];
        
        // Mise à jour du statut de l'emprunt
        $update_emprunt = db_execute(
            "UPDATE emprunts SET statut = 'Rendu', date_retour_reelle = NOW() 
             WHERE id = ? AND user_id = ? AND statut = 'En cours'",
            [$emprunt_id, $user_id]
        );
        
        // Restauration du stock du média
        $update_stock = increment_stock($media_id);
        
        // Validation de la transaction
        if ($update_emprunt && $update_stock) {
            db_execute("COMMIT");
            return true;
        } else {
            db_execute("ROLLBACK");
            return false;
        }
    } catch (Exception $e) {
        db_execute("ROLLBACK");
        return false;
    }
}

/**
 * Vérifie si un utilisateur peut emprunter un média
 */
function can_borrow_media($user_id, $media_id) {
    // Vérifier si le média existe et est disponible
    $media = get_media_by_id($media_id);
    if (!$media || $media['stock_disponible'] <= 0) {
        return ['can_borrow' => false, 'reason' => 'Ce média n\'est plus disponible'];
    }
    
    // Vérifier le nombre d'emprunts en cours de l'utilisateur
    $current_loans = count_user_current_loans($user_id);
    if ($current_loans >= 3) {
        return ['can_borrow' => false, 'reason' => 'Vous avez déjà atteint la limite de 3 emprunts simultanés.'];
    }
    
    // Vérifier si l'utilisateur a des emprunts en retard
    $overdue_loans = count_user_overdue_loans($user_id);
    if ($overdue_loans > 0) {
        return ['can_borrow' => false, 'reason' => 'Vous avez des emprunts en retard'];
    }
    
    // Vérifier si l'utilisateur a déjà emprunté ce média et ne l'a pas encore rendu
    $existing_loan = db_select_one(
        "SELECT id FROM emprunts WHERE user_id = ? AND media_id = ? AND statut = 'En cours'",
        [$user_id, $media_id]
    );
    if ($existing_loan) {
        return ['can_borrow' => false, 'reason' => 'Vous avez déjà emprunté ce média'];
    }
    
    return ['can_borrow' => true, 'reason' => ''];
}

/**
 * Récupère les emprunts en cours d'un utilisateur
 */
function get_user_current_loans($user_id) {
    $query = "SELECT e.*, m.titre, m.type, m.image,
              CONCAT_WS(', ',
                  (SELECT g1.nom FROM genres g1 WHERE g1.id = m.genre_id_1),
                  (SELECT g2.nom FROM genres g2 WHERE g2.id = m.genre_id_2),
                  (SELECT g3.nom FROM genres g3 WHERE g3.id = m.genre_id_3),
                  (SELECT g4.nom FROM genres g4 WHERE g4.id = m.genre_id_4),
                  (SELECT g5.nom FROM genres g5 WHERE g5.id = m.genre_id_5)
              ) as genres
              FROM emprunts e 
              JOIN medias m ON e.media_id = m.id 
              WHERE e.user_id = ? AND e.statut = 'En cours' 
              ORDER BY e.date_emprunt DESC";
    return db_select($query, [$user_id]);
}

/**
 * Récupère l'historique simple des emprunts d'un utilisateur
 */
    // La fonction get_user_loans_simple a été supprimée car elle n'est pas utilisée.

/**
 * Compte les emprunts en cours
 */
function count_current_loans() {
    $result = db_select("SELECT COUNT(*) as count FROM emprunts WHERE statut = 'En cours'");
    return $result[0]['count'] ?? 0;
}

/**
 * Compte les emprunts terminés
 */
function count_returned_loans() {
    $result = db_select("SELECT COUNT(*) as count FROM emprunts WHERE statut = 'termine'");
    return $result[0]['count'] ?? 0;
}

/**
 * Compte le total des emprunts
 */
function count_total_loans() {
    $result = db_select("SELECT COUNT(*) as count FROM emprunts");
    return $result[0]['count'] ?? 0;
}

/**
 * Récupère les emprunts en retard
 */
function get_overdue_loans() {
    // S'assurer que les statuts ont été mis à jour avant la lecture
    update_overdue_statuses();

    $query = "SELECT e.*, m.titre, u.prenom, u.nom, u.email
              FROM emprunts e
              JOIN medias m ON e.media_id = m.id
              JOIN users u ON e.user_id = u.id
              WHERE (e.statut = 'En retard' OR (e.statut = 'En cours' AND e.date_retour_prevue < CURDATE()))
              ORDER BY e.date_retour_prevue ASC";
    return db_select($query);
}

/**
 * Compte les emprunts en retard
 */
function count_overdue_loans() {
    // Mettre à jour les statuts au préalable
    update_overdue_statuses();

    $result = db_select("SELECT COUNT(*) as count FROM emprunts 
                        WHERE statut = 'En retard'");
    return $result[0]['count'] ?? 0;
}

/**
 * Force le retour d'un média (fonction admin)
 */
function force_return_media_admin($emprunt_id) {
    db_execute("START TRANSACTION");
    
    try {
        // Récupération du media_id
        $media_query = "SELECT media_id FROM emprunts WHERE id = ? AND statut = 'En cours'";
        $media_result = db_select($media_query, [$emprunt_id]);
        
        if (empty($media_result)) {
            db_execute("ROLLBACK");
            return false;
        }
        
        $media_id = $media_result[0]['media_id'];
        
        // Mise à jour de l'emprunt
        $update_emprunt = db_execute(
            "UPDATE emprunts SET statut = 'Rendu', date_retour_reelle = NOW() WHERE id = ?",
            [$emprunt_id]
        );
        
        // Restauration du stock
        $update_stock = increment_stock($media_id);
        
        if ($update_emprunt && $update_stock) {
            db_execute("COMMIT");
            return true;
        } else {
            db_execute("ROLLBACK");
            return false;
        }
    } catch (Exception $e) {
        db_execute("ROLLBACK");
        return false;
    }
}

/**
 * Récupère tous les emprunts pour l'administration avec filtres
 */
function get_all_loans_admin($filters = [], $limit = 50, $offset = 0) {
    // Mettre à jour les statuts en retard avant de construire la requête
    update_overdue_statuses();

    $where_conditions = [];
    $params = [];
    
    // Filtre par statut
    if (!empty($filters['statut'])) {
        $where_conditions[] = "e.statut = ?";
        $params[] = $filters['statut'];
    }
    
    // Filtre par retard
    if (!empty($filters['retard'])) {
        if ($filters['retard'] === 'oui') {
            $where_conditions[] = "(e.statut = 'En retard' OR (e.statut = 'En cours' AND e.date_retour_prevue < CURDATE()))";
        } elseif ($filters['retard'] === 'rendu_en_retard') {
            $where_conditions[] = "(e.statut = 'Rendu' AND e.date_retour_reelle > e.date_retour_prevue)";
        }
    }
    
    // Recherche utilisateur
    if (!empty($filters['user_search'])) {
        $where_conditions[] = "(u.nom LIKE ? OR u.prenom LIKE ? OR u.email LIKE ?)";
        $search_term = '%' . $filters['user_search'] . '%';
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
    }
    
    $where_clause = "";
    if (!empty($where_conditions)) {
        $where_clause = "WHERE " . implode(" AND ", $where_conditions);
    }
    
    // Construire la requête (compatible avec ou sans colonne commentaire)
    $query = "SELECT e.id, e.user_id, e.media_id, e.date_emprunt, e.date_retour_prevue, 
                     e.date_retour_reelle, e.statut, e.created_at, e.updated_at,
                     u.prenom, u.nom, u.email, m.titre, m.type, m.image,
                     CONCAT_WS(', ',
                         (SELECT g1.nom FROM genres g1 WHERE g1.id = m.genre_id_1),
                         (SELECT g2.nom FROM genres g2 WHERE g2.id = m.genre_id_2),
                         (SELECT g3.nom FROM genres g3 WHERE g3.id = m.genre_id_3),
                         (SELECT g4.nom FROM genres g4 WHERE g4.id = m.genre_id_4),
                         (SELECT g5.nom FROM genres g5 WHERE g5.id = m.genre_id_5)
                     ) as genres,
                     DATEDIFF(CURDATE(), e.date_retour_prevue) as jours_retard
              FROM emprunts e 
              JOIN users u ON e.user_id = u.id 
              JOIN medias m ON e.media_id = m.id 
              $where_clause 
              ORDER BY e.date_emprunt DESC 
              LIMIT ? OFFSET ?";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $result = db_select($query, $params);
    
    // Ajouter le commentaire si la colonne existe
    $columns_query = "SHOW COLUMNS FROM emprunts LIKE 'commentaire'";
    $column_exists = db_select($columns_query, []);
    
    if (!empty($column_exists) && !empty($result)) {
        // Récupérer les commentaires séparément
        $ids = array_column($result, 'id');
        if (!empty($ids)) {
            $placeholders = str_repeat('?,', count($ids) - 1) . '?';
            $comments_query = "SELECT id, commentaire FROM emprunts WHERE id IN ($placeholders)";
            $comments = db_select($comments_query, $ids);
            
            // Associer les commentaires aux résultats
            $comments_by_id = [];
            foreach ($comments as $comment) {
                $comments_by_id[$comment['id']] = $comment['commentaire'];
            }
            
            // Ajouter les commentaires aux résultats
            for ($i = 0; $i < count($result); $i++) {
                $result[$i]['commentaire'] = $comments_by_id[$result[$i]['id']] ?? '';
            }
        }
    }
    
    return $result;
}

/**
 * Compte tous les emprunts pour l'administration
 */
function count_all_loans_admin($filters = []) {
    // Mettre à jour les statuts en retard avant les calculs
    update_overdue_statuses();

    $where_conditions = [];
    $params = [];
    
    if (!empty($filters['statut'])) {
        $where_conditions[] = "e.statut = ?";
        $params[] = $filters['statut'];
    }
    
    if (!empty($filters['retard'])) {
        if ($filters['retard'] === 'oui') {
            $where_conditions[] = "(e.statut = 'En retard' OR (e.statut = 'En cours' AND e.date_retour_prevue < CURDATE()))";
        } elseif ($filters['retard'] === 'rendu_en_retard') {
            $where_conditions[] = "(e.statut = 'Rendu' AND e.date_retour_reelle > e.date_retour_prevue)";
        }
    }
    
    if (!empty($filters['user_search'])) {
        $where_conditions[] = "(u.nom LIKE ? OR u.prenom LIKE ? OR u.email LIKE ?)";
        $search_term = '%' . $filters['user_search'] . '%';
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
    }
    
    $where_clause = "";
    if (!empty($where_conditions)) {
        $where_clause = "WHERE " . implode(" AND ", $where_conditions);
    }
    
    $query = "SELECT COUNT(*) as total 
              FROM emprunts e 
              JOIN users u ON e.user_id = u.id 
              JOIN medias m ON e.media_id = m.id 
              $where_clause";
    
    $result = db_select_one($query, $params);
    return $result['total'] ?? 0;
}

/**
 * Compte les emprunts par statut
 */
function count_loans_by_status($status) {
    $query = "SELECT COUNT(*) as total FROM emprunts WHERE statut = ?";
    $result = db_select_one($query, [$status]);
    return $result['total'] ?? 0;
}

/**
 * Compte les emprunts d'aujourd'hui
 */
function count_loans_today() {
    $query = "SELECT COUNT(*) as total FROM emprunts WHERE DATE(date_emprunt) = CURDATE()";
    $result = db_select_one($query);
    return $result['total'] ?? 0;
}

/**
 * Retour forcé d'un emprunt par l'administrateur
 * Gère automatiquement le restock du média
 */
function admin_force_return_emprunt($emprunt_id) {
    // Début de la transaction pour assurer la cohérence
    db_execute("START TRANSACTION");
    
    try {
        // Récupération des informations de l'emprunt (En cours ou En retard)
        $emprunt_query = "SELECT media_id, user_id FROM emprunts WHERE id = ? AND (statut = 'En cours' OR statut = 'En retard')";
        $emprunt_result = db_select($emprunt_query, [$emprunt_id]);
        
        if (empty($emprunt_result)) {
            db_execute("ROLLBACK");
            return false; // Emprunt introuvable ou déjà rendu
        }
        
        $media_id = $emprunt_result[0]['media_id'];
        $user_id = $emprunt_result[0]['user_id'];
        
        // Vérifier si la colonne commentaire existe
        $columns_query = "SHOW COLUMNS FROM emprunts LIKE 'commentaire'";
        $column_exists = db_select($columns_query, []);
        
        // Mise à jour du statut de l'emprunt avec ou sans commentaire
        if (!empty($column_exists)) {
            // Avec commentaire si la colonne existe
            $update_emprunt = db_execute(
                "UPDATE emprunts SET statut = 'Rendu', date_retour_reelle = NOW(), 
                 commentaire = 'Retour forcé par l\'administrateur' 
                 WHERE id = ? AND (statut = 'En cours' OR statut = 'En retard')",
                [$emprunt_id]
            );
        } else {
            // Sans commentaire si la colonne n'existe pas
            $update_emprunt = db_execute(
                "UPDATE emprunts SET statut = 'Rendu', date_retour_reelle = NOW() 
                 WHERE id = ? AND (statut = 'En cours' OR statut = 'En retard')",
                [$emprunt_id]
            );
        }
        
        // Restauration automatique du stock du média
        $update_stock = increment_stock($media_id);
        
        // Validation de la transaction
        if ($update_emprunt && $update_stock) {
            db_execute("COMMIT");
            
            // Log de l'action pour traçabilité
            error_log("Admin: Retour forcé de l'emprunt #$emprunt_id - Media #$media_id - User #$user_id");
            
            return true;
        } else {
            db_execute("ROLLBACK");
            error_log("Erreur lors du retour forcé de l'emprunt #$emprunt_id");
            return false;
        }
    } catch (Exception $e) {
        db_execute("ROLLBACK");
        error_log("Exception lors du retour forcé: " . $e->getMessage());
        return false;
    }
}
?>
