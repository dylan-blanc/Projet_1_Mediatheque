<?php
// Contrôleur pour la gestion des médias

// Charger le model des médias
require_once MODEL_PATH . '/media_model.php';

/**
 * Affiche le catalogue des médias avec filtres et pagination
 */
function media_library()
{
    // Gestion de la pagination
    $page = max(1, intval(get('page', 1)));
    $limit = 20;
    $offset = ($page - 1) * $limit;

    // Récupération des filtres
    $filters = [
        'type' => get('type'),
        'genre' => get('genre'),
        'disponible' => get('disponible'),
        'search' => get('search')
    ];

    // Si le type est "allgenres" ou vide, on ne filtre pas sur le type
    if (empty($filters['type']) || $filters['type'] === 'allgenres') {
        unset($filters['type']);
    }

    // Suppression des filtres vides
    $filters = array_filter($filters);

    // Récupération des médias avec filtres et pagination
    $medias = get_all_medias($filters, $limit, $offset);
    $total_medias = count_medias($filters);
    $total_pages = ceil($total_medias / $limit);

    // Récupération des genres pour les filtres (tous les genres maintenant)
    $all_genres = get_all_genres();

    $data = [
        'title' => 'Catalogue des médias',
        'medias' => $medias,
        'current_page' => $page,
        'total_pages' => $total_pages,
        'total_medias' => $total_medias,
        'filters' => $filters,
        'genres_livre' => $all_genres,
        'genres_film' => $all_genres,
        'genres_jeu' => $all_genres
    ];

    load_view_with_layout('media/library', $data);
}

/**
 * Affiche les détails d'un média
 */
function media_detail($id)
{
    // Récupération du média
    $media = get_media_by_id($id);

    if (!$media) {
        set_flash('error', 'Média introuvable.');
        redirect('media/library');
        return;
    }

    // Vérification des droits d'emprunt
    $can_borrow = ['can_borrow' => false, 'reason' => 'Veuillez vous connecter'];
    if (is_logged_in()) {
        $can_borrow = can_borrow_media(current_user_id(), $id);
    }

    $data = [
        'title' => $media['titre'],
        'media' => $media,
        'can_borrow' => $can_borrow
    ];

    load_view_with_layout('media/detail', $data);
}

/**
 * Emprunter un média
 */
function media_borrow($id)
{
    require_login();

    if (!is_post()) {
        redirect('media/detail/' . $id);
    }

    $media = get_media_by_id($id);
    if (!$media) {
        set_flash('error', 'Média introuvable.');
        redirect('media/library');
    }

    // Vérifier si l'emprunt est possible
    $can_borrow = can_borrow_media(current_user_id(), $id);
    if (!$can_borrow['can_borrow']) {
        set_flash('error', $can_borrow['reason']);
        redirect('media/detail/' . $id);
    }

    // Créer l'emprunt
    $emprunt_success = create_emprunt(current_user_id(), $id);

    if ($emprunt_success) {
        $date_retour = date('d/m/Y', strtotime('+14 days'));
        set_flash('success', "Emprunt enregistré avec succès. Date de retour prévue : $date_retour");
        redirect('home/profile');
    } else {
        set_flash('error', 'Erreur lors de l\'emprunt.');
        redirect('media/detail/' . $id);
    }
}

/**
 * Page d'ajout de média (admin)
 * 
 * Cette fonction gère l'affichage du formulaire d'ajout et la validation des données.
 * En cas d'erreur de validation, les données saisies sont conservées pour éviter
 * à l'utilisateur de tout retaper.
 */
function media_add()
{
    // Vérification des droits administrateur
    require_admin();
    
    // ========== NETTOYAGE DES ANCIENNES DONNÉES ==========
    // Si on arrive sur le formulaire en GET ET qu'il n'y a pas de messages flash d'erreur,
    // c'est un nouvel affichage du formulaire (pas une redirection après erreur)
    // → On nettoie les anciennes données sauvegardées
    if (!is_post() && !has_flash_messages('error')) {
        unset($_SESSION['old_input']);
    }

    // Préparation des données pour la vue (formulaire)
    $data = [
        'title' => 'Ajouter un média',
        'genres_livre' => get_all_genres(),  // Liste de tous les genres disponibles
        'genres_film' => get_all_genres(),   // (Même liste pour tous les types de médias)
        'genres_jeu' => get_all_genres()
    ];

    // ========== TRAITEMENT DU FORMULAIRE (si POST) ==========
    if (is_post()) {
        // --- ÉTAPE 1 : Récupération des données du formulaire ---
        // On récupère les données communes à tous les types de médias
        $media_data = [
            'titre' => trim(post('titre')),     // Titre du média (obligatoire)
            'type' => post('type'),              // Type : livre, film ou jeu (obligatoire)
            'stock' => intval(post('stock', 1))  // Stock disponible (obligatoire, min 1)
        ];

        // Récupération des genres sélectionnés (checkboxes)
        // Les genres sont envoyés sous forme de tableau d'IDs
        $genre_ids = isset($_POST['genre_ids']) ? $_POST['genre_ids'] : [];

        // --- ÉTAPE 2 : Initialisation du tableau des erreurs ---
        // Ce tableau va collecter toutes les erreurs de validation
        // Chaque erreur sera affichée à l'utilisateur via un message flash
        $errors = [];

        if (empty($media_data['titre'])) {
            $errors[] = 'Le titre est obligatoire.';
        } elseif (strlen($media_data['titre']) > 200) {
            $errors[] = 'Le titre ne peut pas dépasser 200 caractères.';
        }

        if (!in_array($media_data['type'], ['livre', 'film', 'jeu'])) {
            $errors[] = 'Type de média invalide.';
        }

        // Valider les genres avec la fonction helper
        $genre_validation = validate_media_genre_ids($genre_ids);
        if ($genre_validation !== true) {
            // La fonction retourne une string d'erreur ou true
            $errors[] = $genre_validation;
        } else {
            // Assigner les genre_ids validés (1 à 5)
            for ($i = 1; $i <= 5; $i++) {
                $media_data["genre_id_$i"] = isset($genre_ids[$i - 1]) ? intval($genre_ids[$i - 1]) : null;
            }
        }

        if ($media_data['stock'] < 1) {
            $errors[] = 'Le stock doit être au moins de 1.';
        }

        // --- ÉTAPE 3 : Validation spécifique selon le type de média ---
        // Chaque type de média (livre, film, jeu) a des champs spécifiques
        // avec des règles de validation différentes
        switch ($media_data['type']) {
            // ===== VALIDATION POUR UN LIVRE =====
            case 'livre':
                // Récupération des champs spécifiques aux livres
                $media_data['auteur'] = trim(post('auteur'));
                $media_data['isbn'] = trim(post('isbn'));
                $media_data['nombre_pages'] = intval(post('nombre_pages'));
                $media_data['resume'] = trim(post('resume'));
                $media_data['annee_publication'] = intval(post('annee_publication')) ?: null;

                // Validation de l'auteur (champ obligatoire)
                if (empty($media_data['auteur'])) {
                    $errors[] = 'L\'auteur est obligatoire pour un livre.';
                } elseif (strlen($media_data['auteur']) < 2 || strlen($media_data['auteur']) > 100) {
                    $errors[] = 'L\'auteur doit contenir entre 2 et 100 caractères.';
                }

                // Validation de l'ISBN (optionnel mais doit être valide si fourni)
                if (!empty($media_data['isbn'])) {
                    // Vérification du format (10 ou 13 chiffres)
                    if (!validate_isbn($media_data['isbn'])) {
                        $errors[] = 'Format ISBN invalide (10 ou 13 chiffres).';
                    } 
                    // Vérification d'unicité (pas de doublon dans la base)
                    elseif (isbn_exists($media_data['isbn'])) {
                        $errors[] = 'Cet ISBN existe déjà dans la base de données.';
                    }
                }

                // Validation du nombre de pages
                if ($media_data['nombre_pages'] < 1 || $media_data['nombre_pages'] > 9999) {
                    $errors[] = 'Le nombre de pages doit être entre 1 et 9999.';
                }

                // Validation de l'année de publication (optionnelle)
                if (!empty($media_data['annee_publication']) && !validate_year($media_data['annee_publication'])) {
                    $errors[] = 'L\'année de publication doit être entre 1900 et ' . date('Y') . '.';
                }
                break;

            // ===== VALIDATION POUR UN FILM =====
            case 'film':
                $media_data['realisateur'] = trim(post('realisateur'));
                $media_data['duree_minutes'] = intval(post('duree_minutes'));
                $media_data['synopsis'] = trim(post('synopsis'));
                $media_data['classification'] = post('classification');
                $media_data['annee_film'] = intval(post('annee_film')) ?: null;

                if (empty($media_data['realisateur'])) {
                    $errors[] = 'Le réalisateur est obligatoire pour un film.';
                } elseif (strlen($media_data['realisateur']) < 2 || strlen($media_data['realisateur']) > 100) {
                    $errors[] = 'Le réalisateur doit contenir entre 2 et 100 caractères.';
                }

                if ($media_data['duree_minutes'] < 1 || $media_data['duree_minutes'] > 999) {
                    $errors[] = 'La durée doit être entre 1 et 999 minutes.';
                }

                if (!in_array($media_data['classification'], ['Tous publics', '-12', '-16', '-18'])) {
                    $errors[] = 'Classification invalide.';
                }

                if (!empty($media_data['annee_film']) && !validate_year($media_data['annee_film'])) {
                    $errors[] = 'L\'année du film doit être entre 1900 et ' . date('Y') . '.';
                }
                break;

            // ===== VALIDATION POUR UN JEU VIDÉO =====
            case 'jeu':
                // Récupération des champs spécifiques aux jeux
                $media_data['editeur'] = trim(post('editeur'));
                $media_data['plateforme'] = post('plateforme');
                $media_data['age_minimum'] = post('age_minimum');
                $media_data['description'] = trim(post('description'));

                // Validation de l'éditeur (champ obligatoire)
                if (empty($media_data['editeur'])) {
                    $errors[] = 'L\'éditeur est obligatoire pour un jeu.';
                } elseif (strlen($media_data['editeur']) < 2 || strlen($media_data['editeur']) > 100) {
                    $errors[] = 'L\'éditeur doit contenir entre 2 et 100 caractères.';
                }

                // Validation de la plateforme (liste prédéfinie)
                if (!in_array($media_data['plateforme'], ['PC', 'PlayStation', 'Xbox', 'Nintendo', 'Mobile'])) {
                    $errors[] = 'Veuillez sélectionner une plateforme valide.';
                }

                // Validation de l'âge minimum (liste prédéfinie)
                if (!in_array($media_data['age_minimum'], ['3', '7', '12', '16', '18'])) {
                    $errors[] = 'Veuillez sélectionner un âge minimum valide.';
                }
                break;
        }

        // --- ÉTAPE 4 : Traitement selon le résultat de la validation ---
        
        // ========== CAS 1 : AUCUNE ERREUR - Création du média ==========
        if (empty($errors)) {
            // Succès ! On peut créer le média dans la base de données
            
            // Nettoyer les anciennes données de formulaire sauvegardées (si elles existent)
            unset($_SESSION['old_input']);
            
            // Créer le média en base de données (sans image dans un premier temps)
            // Note : On crée d'abord le média pour obtenir son ID, nécessaire pour nommer l'image
            $temp_image = $media_data['image'] ?? null;
            unset($media_data['image']);

            $media_id = create_media($media_data);

            if ($media_id) {
                // Média créé avec succès ! ID obtenu : $media_id
                
                // Gestion de l'image (optionnelle)
                if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                    // Une image a été uploadée, on la traite
                    $uploadError = null;
                    $image_path = upload_cover_image_for_media(
                        $_FILES['image'],
                        $media_data['type'],
                        $media_id,
                        $media_data['titre'],
                        $uploadError
                    );
                    
                    if ($image_path === false) {
                        // L'upload a échoué : on supprime le média créé pour éviter les incohérences
                        delete_media($media_id);
                        // Message d'erreur spécifique à l'upload
                        set_flash('error', $uploadError ?: 'Erreur lors de l\'upload de l\'image.');
                        redirect('media/add');
                        return;
                    } else {
                        // Image uploadée avec succès : mise à jour du média avec le chemin
                        update_media($media_id, ['image' => $image_path]);
                    }
                } else {
                    // Pas d'image fournie : utilisation de l'image par défaut selon le type
                    $default = get_default_image_for_type($media_data['type']);
                }

                // Tout s'est bien passé ! Message de succès et redirection vers la page de détail
                set_flash('success', 'Média ajouté avec succès !');
                redirect('media/detail/' . $media_id);
                
            } else {
                // Erreur lors de la création du média en base de données
                set_flash('error', 'Erreur lors de l\'ajout du média. Veuillez réessayer.');
            }
            
        // ========== CAS 2 : ERREURS DE VALIDATION ==========
        } else {
            // Des erreurs ont été détectées lors de la validation
            // On va sauvegarder les données et afficher les erreurs à l'utilisateur
            
            // SAUVEGARDE DES DONNÉES DU FORMULAIRE dans la session
            // Cela permet de ré-afficher les valeurs saisies après la redirection
            // L'utilisateur n'a pas besoin de tout retaper !
            $_SESSION['old_input'] = $_POST;
            $_SESSION['old_input']['genre_ids'] = $genre_ids;
            
            // AFFICHAGE DE CHAQUE ERREUR via un message flash
            // Chaque erreur sera affichée en rouge en haut de la page
            foreach ($errors as $error) {
                set_flash('error', $error);
            }
            
            // REDIRECTION vers le formulaire
            // Important : on redirige (GET) au lieu de réafficher directement (POST)
            // Cela évite les problèmes de resoumission du formulaire si l'utilisateur
            // rafraîchit la page (F5)
            redirect('media/add');
            return;
        }
    }
    
    // ========== AFFICHAGE DU FORMULAIRE ==========
    // Chargement de la vue avec le layout
    // La vue utilisera la fonction old() pour réafficher les valeurs en cas d'erreur
    // Note: Les old_input seront automatiquement nettoyées après affichage dans la vue
    load_view_with_layout('media/add', $data);
}

/**
 * Gestion des emprunts (admin)
 */
function media_loans()
{
    require_admin();

    $page = max(1, intval(get('page', 1)));
    $limit = 20;
    $offset = ($page - 1) * $limit;

    $filters = [
        'statut' => get('statut'),
        'en_retard' => get('en_retard')
    ];
    $filters = array_filter($filters);

    // Récupération des emprunts (simplifié)
    $emprunts = get_overdue_loans();
    $total_emprunts = count($emprunts);
    $total_pages = ceil($total_emprunts / $limit);

    // Stats simplifiées
    $stats = [
        'total' => count_total_loans(),
        'en_cours' => count_current_loans(),
        'en_retard' => count_overdue_loans(),
        'termines' => count_returned_loans()
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

    load_view_with_layout('media/loans', $data);
}

/**
 * Retour d'emprunt (admin)
 */
function media_return($emprunt_id)
{
    require_admin();

    if (!is_post()) {
        redirect('media/loans');
        return;
    }

    // Force le retour par l'admin (pas besoin de user_id)
    if (force_return_media_admin($emprunt_id)) {
        set_flash('success', 'Retour enregistré avec succès.');
    } else {
        set_flash('error', 'Erreur lors du retour.');
    }

    redirect('media/loans');
}

/**
 * Page d'édition d'un média (admin)
 */
function media_edit($id)
{
    require_admin();

    $media = get_media_by_id($id);
    if (!$media) {
        set_flash('error', 'Média introuvable.');
        redirect('media/library');
        return;
    }

    $data = [
        'title' => 'Modifier un média',
        'media' => $media,
        'genres_livre' => get_all_genres(),  // Tous les genres maintenant
        'genres_film' => get_all_genres(),
        'genres_jeu' => get_all_genres()
    ];

    if (is_post()) {
        $media_data = [
            'titre' => trim(post('titre')),
            'type' => post('type'),
            'stock' => intval(post('stock', 1))
        ];

        // Récupérer les genre_ids depuis le formulaire (checkboxes)
        $genre_ids = isset($_POST['genre_ids']) ? $_POST['genre_ids'] : [];

        // Validation de base
        $errors = [];

        if (empty($media_data['titre'])) {
            $errors[] = 'Le titre est obligatoire.';
        } elseif (strlen($media_data['titre']) > 200) {
            $errors[] = 'Le titre ne peut pas dépasser 200 caractères.';
        }

        if (!in_array($media_data['type'], ['livre', 'film', 'jeu'])) {
            $errors[] = 'Type de média invalide.';
        }

        // Valider les genres avec la fonction helper
        $genre_validation = validate_media_genre_ids($genre_ids);
        if ($genre_validation !== true) {
            // La fonction retourne une string d'erreur ou true
            $errors[] = $genre_validation;
        } else {
            // Assigner les genre_ids validés (1 à 5)
            for ($i = 1; $i <= 5; $i++) {
                $media_data["genre_id_$i"] = isset($genre_ids[$i - 1]) ? intval($genre_ids[$i - 1]) : null;
            }
        }

        if ($media_data['stock'] < 1) {
            $errors[] = 'Le stock doit être au moins de 1.';
        }

        // Validation spécifique par type
        switch ($media_data['type']) {
            case 'livre':
                $media_data['auteur'] = trim(post('auteur'));
                $media_data['isbn'] = trim(post('isbn'));
                $media_data['nombre_pages'] = intval(post('nombre_pages')) ?: null;
                $media_data['resume'] = trim(post('resume'));
                $media_data['annee_publication'] = intval(post('annee_publication')) ?: null;

                if (empty($media_data['auteur'])) {
                    $errors[] = 'L\'auteur est obligatoire pour un livre.';
                } elseif (strlen($media_data['auteur']) < 2 || strlen($media_data['auteur']) > 100) {
                    $errors[] = 'L\'auteur doit contenir entre 2 et 100 caractères.';
                }

                if (!empty($media_data['isbn']) && !validate_isbn($media_data['isbn'])) {
                    $errors[] = 'Format ISBN invalide.';
                }

                if (!empty($media_data['isbn']) && isbn_exists($media_data['isbn'], $id)) {
                    $errors[] = 'Cet ISBN est déjà utilisé par un autre livre.';
                }
                break;

            case 'film':
                $media_data['realisateur'] = trim(post('realisateur'));
                $media_data['duree_minutes'] = intval(post('duree_minutes')) ?: null;
                $media_data['synopsis'] = trim(post('synopsis'));
                $media_data['classification'] = post('classification');
                $media_data['annee_film'] = intval(post('annee_film')) ?: null;

                if (empty($media_data['realisateur'])) {
                    $errors[] = 'Le réalisateur est obligatoire pour un film.';
                } elseif (strlen($media_data['realisateur']) < 2 || strlen($media_data['realisateur']) > 100) {
                    $errors[] = 'Le réalisateur doit contenir entre 2 et 100 caractères.';
                }
                break;

            case 'jeu':
                $media_data['editeur'] = trim(post('editeur'));
                $media_data['plateforme'] = post('plateforme');
                $media_data['age_minimum'] = post('age_minimum');
                $media_data['description'] = trim(post('description'));

                if (empty($media_data['editeur'])) {
                    $errors[] = 'L\'éditeur est obligatoire pour un jeu.';
                } elseif (strlen($media_data['editeur']) < 2 || strlen($media_data['editeur']) > 100) {
                    $errors[] = 'L\'éditeur doit contenir entre 2 et 100 caractères.';
                }
                break;
        }

        // Ajuster le stock_disponible si le stock total a changé
        if ($media_data['stock'] != $media['stock']) {
            $difference = $media_data['stock'] - $media['stock'];
            $media_data['stock_disponible'] = max(0, $media['stock_disponible'] + $difference);
        }

        // Gestion de la suppression d'image si demandée
        if (post('delete_image') == '1') {
            // Supprimer l'image actuelle et utiliser l'image par défaut
            if (!empty($media['image'])) {
                $filename = basename($media['image']);
                if (defined('UPLOADS_PATH')) {
                    $oldPath = rtrim(UPLOADS_PATH, "\\/") . DIRECTORY_SEPARATOR . $filename;
                } else {
                    $oldPath = ROOT_PATH . 'public/uploads/covers/' . $filename;
                }
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            // Définir l'image par défaut selon le type
            $media_data['image'] = get_default_image_for_type($media_data['type']);
        }
        // Upload de nouvelle image si fournie (uniquement si pas de suppression demandée)
        elseif (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            // Utiliser le titre du POST ou celui existant si vide
            $titre_for_filename = !empty($media_data['titre']) ? $media_data['titre'] : $media['titre'];

            // Utiliser la nouvelle fonction pour nommer l'image avec l'ID
            $uploadError = null;
            $image_path = upload_cover_image_for_media(
                $_FILES['image'],
                $media_data['type'],
                $id,
                $titre_for_filename,
                $uploadError
            );

            if ($image_path) {
                // Supprimer l'ancienne image si elle existe
                if (!empty($media['image'])) {
                    $filename = basename($media['image']);
                    if (defined('UPLOADS_PATH')) {
                        $oldPath = rtrim(UPLOADS_PATH, "\\/") . DIRECTORY_SEPARATOR . $filename;
                    } else {
                        $oldPath = ROOT_PATH . 'public/uploads/covers/' . $filename;
                    }
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }
                $media_data['image'] = $image_path;
            } else {
                $errors[] = $uploadError ?: 'Erreur lors de l\'upload de l\'image.';
            }
        }

        if (empty($errors)) {
            // Nettoyer old_input en cas de succès
            unset($_SESSION['old_input']);
            
            $success = update_media($id, $media_data);

            if ($success) {
                set_flash('success', 'Média modifié avec succès.');
                redirect('media/detail/' . $id);
            } else {
                set_flash('error', 'Erreur lors de la modification.');
            }
        } else {
            // Sauvegarder les données pour les réafficher
            $_SESSION['old_input'] = $_POST;
            $_SESSION['old_input']['genre_ids'] = $genre_ids;
            
            foreach ($errors as $error) {
                set_flash('error', $error);
            }
            
            // Ne pas charger la vue, rediriger pour éviter les problèmes de POST
            redirect('media/edit/' . $id);
            return;
        }
    }
    
    // Nettoyer old_input si on affiche le formulaire vide
    if (!is_post()) {
        unset($_SESSION['old_input']);
    }

    load_view_with_layout('media/edit', $data);
}

/**
 * Suppression d'un média (admin)
 */
function media_delete($id)
{
    require_admin();

    if (!is_post()) {
        redirect('media/library');
    }

    $media = get_media_by_id($id);
    if (!$media) {
        set_flash('error', 'Média introuvable.');
        redirect('media/library');
    }

    // Vérifier qu'il n'y a pas d'emprunts en cours
    $emprunts_en_cours = get_media_current_loans($id);
    if (!empty($emprunts_en_cours)) {
        set_flash('error', 'Impossible de supprimer ce média : il y a des emprunts en cours.');
        redirect('media/detail/' . $id);
    }

    // Supprimer le média
    $success = delete_media($id);

    if ($success) {
        // Supprimer l'image associée
        if (!empty($media['image'])) {
            $filename = basename($media['image']);
            $oldPath = ROOT_PATH . 'public/uploads/covers/' . $filename;
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }

        set_flash('success', 'Média supprimé avec succès.');
        redirect('media/library');
    } else {
        set_flash('error', 'Erreur lors de la suppression.');
        redirect('media/detail/' . $id);
    }
}
