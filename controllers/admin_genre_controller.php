<?php
// Contrôleur pour la gestion des genres (Admin)

/**
 * Liste des genres
 */
function admin_genres_list() {
    require_admin();
    
    // Filtres et recherche
    $search = trim(get('search', ''));
    $page = max(1, intval(get('page', 1)));
    $limit = 20;
    $offset = ($page - 1) * $limit;
    
    // Récupérer les genres
    if (!empty($search)) {
        $genres_all = search_genres($search);
    } else {
        $genres_all = get_all_genres();
    }
    
    // Ajouter les statistiques d'utilisation pour chaque genre
    foreach ($genres_all as &$genre) {
        $stats = get_genre_usage_stats($genre['id']);
        $genre['usage_count'] = $stats['total_medias'];
        $genre['usage_livres'] = $stats['total_livres'];
        $genre['usage_films'] = $stats['total_films'];
        $genre['usage_jeux'] = $stats['total_jeux'];
    }
    
    // Pagination
    $total_genres = count($genres_all);
    $genres = array_slice($genres_all, $offset, $limit);
    $total_pages = ceil($total_genres / $limit);
    
    $data = [
        'title' => 'Gestion des genres',
        'genres' => $genres,
        'search' => $search,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total' => $total_genres
        ]
    ];
    
    load_view_with_layout('admin/genres_list', $data);
}

/**
 * Ajouter un genre
 */
function admin_genre_add() {
    require_admin();
    
    if (is_post()) {
        $nom = trim(post('nom'));
        
        // Validation
        $errors = [];
        
        if (empty($nom)) {
            $errors[] = 'Le nom du genre est obligatoire.';
        } elseif (strlen($nom) < 2) {
            $errors[] = 'Le nom du genre doit contenir au moins 2 caractères.';
        } elseif (strlen($nom) > 100) {
            $errors[] = 'Le nom du genre ne peut pas dépasser 100 caractères.';
        }
        
        // Vérifier si le genre existe déjà
        if (empty($errors)) {
            $existing = db_query("SELECT id FROM genres WHERE nom = ?", [$nom]);
            if ($existing) {
                $errors[] = 'Ce genre existe déjà.';
            }
        }
        
        if (empty($errors)) {
            $genre_id = create_genre($nom);
            
            if ($genre_id) {
                set_flash('success', 'Genre ajouté avec succès.');
                redirect('admin/genres');
                return;
            } else {
                set_flash('error', 'Erreur lors de l\'ajout du genre.');
            }
        } else {
            set_flash('error', implode('<br>', $errors));
        }
    }
    
    $data = [
        'title' => 'Ajouter un genre'
    ];
    
    load_view_with_layout('admin/genre_form', $data);
}

/**
 * Modifier un genre
 */
function admin_genre_edit($genre_id) {
    require_admin();
    
    $genre_id = intval($genre_id);
    $genre = get_genre_by_id($genre_id);
    
    if (!$genre) {
        set_flash('error', 'Genre introuvable.');
        redirect('admin/genres');
        return;
    }
    
    if (is_post()) {
        $nom = trim(post('nom'));
        
        // Validation
        $errors = [];
        
        if (empty($nom)) {
            $errors[] = 'Le nom du genre est obligatoire.';
        } elseif (strlen($nom) < 2) {
            $errors[] = 'Le nom du genre doit contenir au moins 2 caractères.';
        } elseif (strlen($nom) > 100) {
            $errors[] = 'Le nom du genre ne peut pas dépasser 100 caractères.';
        }
        
        // Vérifier si le genre existe déjà (sauf le genre actuel)
        if (empty($errors)) {
            $existing = db_query("SELECT id FROM genres WHERE nom = ? AND id != ?", [$nom, $genre_id]);
            if ($existing) {
                $errors[] = 'Ce nom de genre est déjà utilisé.';
            }
        }
        
        if (empty($errors)) {
            if (update_genre($genre_id, $nom)) {
                set_flash('success', 'Genre modifié avec succès.');
                redirect('admin/genres');
                return;
            } else {
                set_flash('error', 'Erreur lors de la modification du genre.');
            }
        } else {
            set_flash('error', implode('<br>', $errors));
        }
    }
    
    $stats = get_genre_usage_stats($genre_id);
    
    $data = [
        'title' => 'Modifier un genre',
        'genre' => $genre,
        'stats' => $stats
    ];
    
    load_view_with_layout('admin/genre_form', $data);
}

/**
 * Supprimer un genre
 */
function admin_genre_delete($genre_id) {
    require_admin();
    
    if (!is_post()) {
        redirect('admin/genres');
        return;
    }
    
    $genre_id = intval($genre_id);
    $result = delete_genre($genre_id);
    
    if ($result === true) {
        set_flash('success', 'Genre supprimé avec succès.');
    } else {
        set_flash('error', $result); // Message d'erreur retourné par delete_genre()
    }
    
    redirect('admin/genres');
}
