<?php
/**
 * Modèle Media - Version corrigée
 * Gestion complète des médias (livres, films, jeux vidéo)
 */

// Include required models
require_once MODEL_PATH . '/user_model.php';

/**
 * Récupère les médias populaires (les plus empruntés)
 */
function get_popular_medias($limit = 6) {
    $query = "SELECT m.*, COUNT(e.id) as total_emprunts,
              CONCAT_WS(', ',
                  (SELECT g1.nom FROM genres g1 WHERE g1.id = m.genre_id_1),
                  (SELECT g2.nom FROM genres g2 WHERE g2.id = m.genre_id_2),
                  (SELECT g3.nom FROM genres g3 WHERE g3.id = m.genre_id_3),
                  (SELECT g4.nom FROM genres g4 WHERE g4.id = m.genre_id_4),
                  (SELECT g5.nom FROM genres g5 WHERE g5.id = m.genre_id_5)
              ) as genres
              FROM medias m
              LEFT JOIN emprunts e ON m.id = e.media_id
              GROUP BY m.id
              ORDER BY total_emprunts DESC, m.titre ASC
              LIMIT ?";
    return db_select($query, [$limit]);
}

/**
 * Récupère les médias disponibles pour la page d'accueil
 */
function get_available_medias_for_home($limit = 12) {
    $query = "SELECT m.*,
              CONCAT_WS(', ',
                  (SELECT g1.nom FROM genres g1 WHERE g1.id = m.genre_id_1),
                  (SELECT g2.nom FROM genres g2 WHERE g2.id = m.genre_id_2),
                  (SELECT g3.nom FROM genres g3 WHERE g3.id = m.genre_id_3),
                  (SELECT g4.nom FROM genres g4 WHERE g4.id = m.genre_id_4),
                  (SELECT g5.nom FROM genres g5 WHERE g5.id = m.genre_id_5)
              ) as genres
              FROM medias m
              WHERE m.stock_disponible > 0 
              ORDER BY m.created_at DESC 
              LIMIT ?";
    return db_select($query, [$limit]);
}

/**
 * Compte le nombre de médias disponibles
 */
function count_available_medias() {
    $query = "SELECT COUNT(*) as total FROM medias WHERE stock_disponible > 0";
    $result = db_select_one($query);
    return $result ? $result['total'] : 0;
}

/**
 * Récupère tous les médias avec filtres et pagination
 */
function get_all_medias($filters = [], $limit = 50, $offset = 0) {
    $where_conditions = [];
    $params = [];

    // Ne pas afficher LES médias dans le catalogue si le stock est égal à 0
    $where_conditions[] = "m.stock > 0";


    // Filtre par type de média (livre, film, jeu)
    if (!empty($filters['type'])) {
        $where_conditions[] = "m.type = ?";
        $params[] = $filters['type'];
    }
    
    // Filtre par genre spécifique (cherche dans les 5 colonnes genre_id)
    if (!empty($filters['genre'])) {
        $genre_id = intval($filters['genre']);
        $where_conditions[] = "(m.genre_id_1 = ? OR m.genre_id_2 = ? OR m.genre_id_3 = ? OR m.genre_id_4 = ? OR m.genre_id_5 = ?)";
        $params[] = $genre_id;
        $params[] = $genre_id;
        $params[] = $genre_id;
        $params[] = $genre_id;
        $params[] = $genre_id;
    }
    
    // Filtre par disponibilité (stock disponible)
    if (!empty($filters['disponible'])) {
        if ($filters['disponible'] === 'oui') {
            $where_conditions[] = "m.stock_disponible > 0";
        } else {
            $where_conditions[] = "m.stock_disponible = 0";
        }
    }
    
    // Recherche textuelle dans les titres
    if (!empty($filters['search'])) {
        $where_conditions[] = "m.titre LIKE ?";
        $params[] = '%' . $filters['search'] . '%';
    }
    
    // Construction de la clause WHERE
    $where_clause = "";
    if (!empty($where_conditions)) {
        $where_clause = "WHERE " . implode(" AND ", $where_conditions);
    }
    
    // Requête complète avec tri et pagination - Avec genres concaténés
    $query = "SELECT m.*,
              CONCAT_WS(', ',
                  (SELECT g1.nom FROM genres g1 WHERE g1.id = m.genre_id_1),
                  (SELECT g2.nom FROM genres g2 WHERE g2.id = m.genre_id_2),
                  (SELECT g3.nom FROM genres g3 WHERE g3.id = m.genre_id_3),
                  (SELECT g4.nom FROM genres g4 WHERE g4.id = m.genre_id_4),
                  (SELECT g5.nom FROM genres g5 WHERE g5.id = m.genre_id_5)
              ) as genres
              FROM medias m
              $where_clause 
              ORDER BY m.titre ASC 
              LIMIT ? OFFSET ?";
    
    // Ajout des paramètres de pagination
    $params[] = $limit;
    $params[] = $offset;
    
    return db_select($query, $params);
}

/**
 * Compte le nombre total de médias avec filtres
 */
function count_medias($filters = []) {
    $where_conditions = [];
    $params = [];
    
    if (!empty($filters['type'])) {
        $where_conditions[] = "type = ?";
        $params[] = $filters['type'];
    }
    
    // Filtre par genre - chercher dans les 5 colonnes genre_id
    if (!empty($filters['genre'])) {
        $genre_id = intval($filters['genre']);
        $where_conditions[] = "(genre_id_1 = ? OR genre_id_2 = ? OR genre_id_3 = ? OR genre_id_4 = ? OR genre_id_5 = ?)";
        $params[] = $genre_id;
        $params[] = $genre_id;
        $params[] = $genre_id;
        $params[] = $genre_id;
        $params[] = $genre_id;
    }
    
    if (!empty($filters['disponible'])) {
        if ($filters['disponible'] === 'oui') {
            $where_conditions[] = "stock_disponible > 0";
        } else {
            $where_conditions[] = "stock_disponible = 0";
        }
    }
    
    if (!empty($filters['search'])) {
        $where_conditions[] = "titre LIKE ?";
        $params[] = '%' . $filters['search'] . '%';
    }
    
    $where_clause = "";
    if (!empty($where_conditions)) {
        $where_clause = "WHERE " . implode(" AND ", $where_conditions);
    }
    
    $query = "SELECT COUNT(*) as total FROM medias $where_clause";
    $result = db_select_one($query, $params);
    return $result ? $result['total'] : 0;
}

/**
 * Récupère un média par son ID
 */
function get_media_by_id($id)
{
    $query = "SELECT m.*,
              CONCAT_WS(', ',
                  (SELECT g1.nom FROM genres g1 WHERE g1.id = m.genre_id_1),
                  (SELECT g2.nom FROM genres g2 WHERE g2.id = m.genre_id_2),
                  (SELECT g3.nom FROM genres g3 WHERE g3.id = m.genre_id_3),
                  (SELECT g4.nom FROM genres g4 WHERE g4.id = m.genre_id_4),
                  (SELECT g5.nom FROM genres g5 WHERE g5.id = m.genre_id_5)
              ) as genres
              FROM medias m
              WHERE m.id = ? AND m.stock > 0";
    // Si le stock est égal a 0 impossible d'afficher le media via l'id dans "voir detail" : /media/detail/{id}
    return db_select_one($query, [$id]);
}

/**
 * Crée un nouveau média
 */
function create_media($data) {
    $fields = ['titre', 'type', 'stock', 'stock_disponible'];
    $values = ['?', '?', '?', '?'];
    $params = [$data['titre'], $data['type'], $data['stock'], $data['stock']];
    
    // Ajouter les 5 colonnes de genres
    for ($i = 1; $i <= 5; $i++) {
        $fields[] = "genre_id_$i";
        $values[] = '?';
        $params[] = $data["genre_id_$i"] ?? null;
    }
    
    // Ajouter l'image si présente
    if (!empty($data['image'])) {
        $fields[] = 'image';
        $values[] = '?';
        $params[] = $data['image'];
    }
    
    // Champs spécifiques selon le type
    switch ($data['type']) {
        case 'livre':
            if (!empty($data['auteur'])) {
                $fields[] = 'auteur';
                $values[] = '?';
                $params[] = $data['auteur'];
            }
            if (!empty($data['isbn'])) {
                $fields[] = 'isbn';
                $values[] = '?';
                $params[] = $data['isbn'];
            }
            if (!empty($data['nombre_pages'])) {
                $fields[] = 'nombre_pages';
                $values[] = '?';
                $params[] = $data['nombre_pages'];
            }
            if (!empty($data['resume'])) {
                $fields[] = 'resume';
                $values[] = '?';
                $params[] = $data['resume'];
            }
            if (!empty($data['annee_publication'])) {
                $fields[] = 'annee_publication';
                $values[] = '?';
                $params[] = $data['annee_publication'];
            }
            break;
            
        case 'film':
            if (!empty($data['realisateur'])) {
                $fields[] = 'realisateur';
                $values[] = '?';
                $params[] = $data['realisateur'];
            }
            if (!empty($data['duree_minutes'])) {
                $fields[] = 'duree_minutes';
                $values[] = '?';
                $params[] = $data['duree_minutes'];
            }
            if (!empty($data['synopsis'])) {
                $fields[] = 'synopsis';
                $values[] = '?';
                $params[] = $data['synopsis'];
            }
            if (!empty($data['classification'])) {
                $fields[] = 'classification';
                $values[] = '?';
                $params[] = $data['classification'];
            }
            if (!empty($data['annee_film'])) {
                $fields[] = 'annee_film';
                $values[] = '?';
                $params[] = $data['annee_film'];
            }
            break;
            
        case 'jeu':
            if (!empty($data['editeur'])) {
                $fields[] = 'editeur';
                $values[] = '?';
                $params[] = $data['editeur'];
            }
            if (!empty($data['plateforme'])) {
                $fields[] = 'plateforme';
                $values[] = '?';
                $params[] = $data['plateforme'];
            }
            if (!empty($data['age_minimum'])) {
                $fields[] = 'age_minimum';
                $values[] = '?';
                $params[] = $data['age_minimum'];
            }
            if (!empty($data['description'])) {
                $fields[] = 'description';
                $values[] = '?';
                $params[] = $data['description'];
            }
            break;
    }
    
    $query = "INSERT INTO medias (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $values) . ")";
    
    try {
        if (db_execute($query, $params)) {
            return db_last_insert_id();
        }
        error_log("create_media failed: " . json_encode($data));
        return false;
    } catch (PDOException $e) {
        error_log("create_media exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Met à jour un média
 */
function update_media($id, $data) {
    // Récupérer le média existant pour utiliser ses valeurs par défaut si $data est partiel
    $existing = get_media_by_id($id);
    if (!$existing) return false;

    $titre = $data['titre'] ?? $existing['titre'];
    $type = $data['type'] ?? $existing['type'];
    $stock = $data['stock'] ?? $existing['stock'];

    $set_clauses = ['titre = ?', 'type = ?', 'stock = ?'];
    $params = [$titre, $type, $stock];
    
    // Ajouter les 5 colonnes de genres
    // Important: Si genre_id_X existe dans $data (même si NULL), on l'utilise
    // Sinon on garde l'ancienne valeur (pour les updates partiels)
    for ($i = 1; $i <= 5; $i++) {
        $set_clauses[] = "genre_id_$i = ?";
        $key = "genre_id_$i";
        if (array_key_exists($key, $data)) {
            // La clé existe dans $data (peut être NULL ou un ID)
            $params[] = $data[$key];
        } else {
            // La clé n'existe pas dans $data, garder l'ancienne valeur
            $params[] = $existing[$key] ?? null;
        }
    }
    
    // Mettre à jour stock_disponible si nécessaire
    if (isset($data['stock_disponible'])) {
        $set_clauses[] = 'stock_disponible = ?';
        $params[] = $data['stock_disponible'];
    }
    
    // Ajouter l'image si présente
    if (isset($data['image'])) {
        $set_clauses[] = 'image = ?';
        $params[] = $data['image'];
    }
    
    // Champs spécifiques selon le type (utiliser la valeur calculée $type)
    switch ($type) {
        case 'livre':
            $set_clauses[] = 'auteur = ?';
            $params[] = $data['auteur'] ?? $existing['auteur'];
            $set_clauses[] = 'isbn = ?';
            $params[] = $data['isbn'] ?? $existing['isbn'];
            $set_clauses[] = 'nombre_pages = ?';
            $params[] = $data['nombre_pages'] ?? $existing['nombre_pages'];
            $set_clauses[] = 'resume = ?';
            $params[] = $data['resume'] ?? $existing['resume'];
            $set_clauses[] = 'annee_publication = ?';
            $params[] = $data['annee_publication'] ?? $existing['annee_publication'];
            // Vider les champs des autres types
            $set_clauses[] = 'realisateur = NULL, duree_minutes = NULL, synopsis = NULL, classification = NULL, annee_film = NULL';
            $set_clauses[] = 'editeur = NULL, plateforme = NULL, age_minimum = NULL, description = NULL';
            break;
            
        case 'film':
            $set_clauses[] = 'realisateur = ?';
            $params[] = $data['realisateur'] ?? $existing['realisateur'];
            $set_clauses[] = 'duree_minutes = ?';
            $params[] = $data['duree_minutes'] ?? $existing['duree_minutes'];
            $set_clauses[] = 'synopsis = ?';
            $params[] = $data['synopsis'] ?? $existing['synopsis'];
            $set_clauses[] = 'classification = ?';
            $params[] = $data['classification'] ?? $existing['classification'];
            $set_clauses[] = 'annee_film = ?';
            $params[] = $data['annee_film'] ?? $existing['annee_film'];
            // Vider les champs des autres types
            $set_clauses[] = 'auteur = NULL, isbn = NULL, nombre_pages = NULL, resume = NULL, annee_publication = NULL';
            $set_clauses[] = 'editeur = NULL, plateforme = NULL, age_minimum = NULL, description = NULL';
            break;
            
        case 'jeu':
            $set_clauses[] = 'editeur = ?';
            $params[] = $data['editeur'] ?? $existing['editeur'];
            $set_clauses[] = 'plateforme = ?';
            $params[] = $data['plateforme'] ?? $existing['plateforme'];
            $set_clauses[] = 'age_minimum = ?';
            $params[] = $data['age_minimum'] ?? $existing['age_minimum'];
            $set_clauses[] = 'description = ?';
            $params[] = $data['description'] ?? $existing['description'];
            // Vider les champs des autres types
            $set_clauses[] = 'auteur = NULL, isbn = NULL, nombre_pages = NULL, resume = NULL, annee_publication = NULL';
            $set_clauses[] = 'realisateur = NULL, duree_minutes = NULL, synopsis = NULL, classification = NULL, annee_film = NULL';
            break;
    }
    
    $params[] = $id;
    $query = "UPDATE medias SET " . implode(', ', $set_clauses) . ", updated_at = NOW() WHERE id = ?";
    
    return db_execute($query, $params);
}

/**
 * Supprime un média
 */
function delete_media($id) {
    // Vérifier s'il y a des emprunts en cours
    $emprunts_en_cours = db_select_one("SELECT COUNT(*) as count FROM emprunts WHERE media_id = ? AND statut = 'En cours'", [$id]);
    
    if ($emprunts_en_cours && $emprunts_en_cours['count'] > 0) {
        return false; // Impossible de supprimer, il y a des emprunts en cours
    }
    
    // Récupérer l'image pour la supprimer
    $media = get_media_by_id($id);
    if ($media && !empty($media['image'])) {
        // Extraire le nom de fichier pour éviter les problèmes de chemin
        $filename = basename($media['image']);
        // Construire le chemin physique en utilisant UPLOADS_PATH (doit pointer vers public/uploads/covers)
        if (defined('UPLOADS_PATH')) {
            $image_path = rtrim(UPLOADS_PATH, "\\/") . DIRECTORY_SEPARATOR . $filename;
        } else {
            $image_path = rtrim(ROOT_PATH, "\\/") . 'public/uploads/covers/' . $filename;
        }

        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }

    $query = "UPDATE medias SET stock = 0, stock_disponible = 0 WHERE id = ?";    
    // rend le media invisible dans le catalogue et dans le voir detail
    return db_execute($query, [$id]);
}

/**
 * Vérifie si un ISBN existe déjà
 */
function isbn_exists($isbn, $exclude_id = null) {
    if ($exclude_id) {
        $query = "SELECT COUNT(*) as count FROM medias WHERE isbn = ? AND id != ?";
        $result = db_select_one($query, [$isbn, $exclude_id]);
    } else {
        $query = "SELECT COUNT(*) as count FROM medias WHERE isbn = ?";
        $result = db_select_one($query, [$isbn]);
    }
    
    return $result['count'] > 0;
}

/**
 * Décrémente le stock d'un média
 */
function decrement_stock($media_id) {
    $query = "UPDATE medias 
              SET stock_disponible = stock_disponible - 1 
              WHERE id = ? AND stock_disponible > 0";
    
    return db_execute($query, [$media_id]);
}

/**
 * Incrémente le stock d'un média
 */
function increment_stock($media_id) {
    $query = "UPDATE medias 
              SET stock_disponible = stock_disponible + 1 
              WHERE id = ?";
    
    return db_execute($query, [$media_id]);
}

/**
 * Compte le nombre total de médias
 */
function count_all_medias() {
    $query = "SELECT COUNT(*) as total FROM medias";
    $result = db_select_one($query);
    return $result ? $result['total'] : 0;
}

/**
 * Récupère les emprunts en cours pour un média
 */
function get_media_current_loans($media_id) {
    $query = "SELECT e.*, u.prenom, u.nom, u.email 
              FROM emprunts e 
              JOIN users u ON e.user_id = u.id 
              WHERE e.media_id = ? AND e.statut = 'En cours'
              ORDER BY e.date_emprunt DESC";
    
    return db_select($query, [$media_id]);
}

/**
 * Compte le nombre de médias par type
 */
function count_medias_by_type($type) {
    $query = "SELECT COUNT(*) as total FROM medias WHERE type = ?";
    $result = db_select_one($query, [$type]);
    return $result ? $result['total'] : 0;
}

/**
 * Récupère les genres disponibles pour un type de média
 * Note: Dans la nouvelle structure, les genres ne sont plus liés à un type
 * Cette fonction retourne tous les genres
 */
function get_genres_by_type($type) {
    // Appeler la fonction helper qui retourne tous les genres
    return get_all_genres();
}