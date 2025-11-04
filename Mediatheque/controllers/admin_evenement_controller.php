<?php
/**
 * Contrôleur Admin - Gestion des événements
 */
require_once MODEL_PATH . '/evenement_model.php';
require_once MODEL_PATH . '/media_model.php';

function admin_evenements_list() {
    require_admin();
    
    // Récupérer les filtres
    $search_titre = isset($_GET['search_titre']) ? trim($_GET['search_titre']) : '';
    $search_date = isset($_GET['search_date']) ? $_GET['search_date'] : '';
    
    // Récupérer tous les événements
    $all_evenements = get_all_evenements();
    
    // Appliquer les filtres
    if (!empty($search_titre) || !empty($search_date)) {
        $all_evenements = array_filter($all_evenements, function($event) use ($search_titre, $search_date) {
            $match = true;
            
            // Filtre par titre
            if (!empty($search_titre)) {
                $match = $match && (stripos($event['titre'], $search_titre) !== false);
            }
            
            // Filtre par date
            if (!empty($search_date)) {
                $match = $match && ($event['date_evenement'] === $search_date);
            }
            
            return $match;
        });
    }
    
    // Pagination
    $per_page = 30;
    $current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $total_events = count($all_evenements);
    $total_pages = max(1, ceil($total_events / $per_page));
    $current_page = min($current_page, $total_pages);
    $offset = ($current_page - 1) * $per_page;
    
    // Extraire la page courante
    $evenements = array_slice($all_evenements, $offset, $per_page);
    
    load_view_with_layout('admin/evenements_list', [
        'evenements' => $evenements,
        'all_evenements' => $all_evenements,
        'total_events' => $total_events,
        'current_page' => $current_page,
        'total_pages' => $total_pages,
        'per_page' => $per_page,
        'title' => 'Gestion des événements'
    ]);
}

function admin_evenement_edit($id = null) {
    require_admin();
    $is_edit = $id !== null;
    $evenement = $is_edit ? get_evenement_by_id($id) : null;
    $medias = get_all_medias();
    $errors = [];

    if (is_post()) {
        $data = [
            'titre' => trim(post('titre')),
            'date_evenement' => post('date_evenement'),
            'heure_evenement' => trim(post('heure_evenement')),
            'description' => trim(post('description')),
            'media_id' => post('media_id') ?: null
        ];
        
        // Validation
        if (empty($data['titre'])) $errors[] = 'Le titre est obligatoire.';
        if (empty($data['date_evenement'])) $errors[] = 'La date est obligatoire.';
        if (empty($data['description'])) $errors[] = 'La description est obligatoire.';
        
        // Gestion de l'upload d'image
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../public/uploads/evenements/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
            $file_type = $_FILES['image']['type'];
            
            if (!in_array($file_type, $allowed_types)) {
                $errors[] = 'Format d\'image non autorisé. Utilisez JPG, PNG ou GIF.';
            } elseif ($_FILES['image']['size'] > 2097152) { // 2 MB
                $errors[] = 'L\'image est trop volumineuse (max 2 Mo).';
            } else {
                $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $filename = 'event_' . uniqid() . '.' . $extension;
                $filepath = $upload_dir . $filename;
                
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $filepath)) {
                    $errors[] = 'Erreur lors de l\'upload de l\'image.';
                }
            }
        }
        
        if (!$errors) {
            if ($is_edit) {
                update_evenement($id, $data);
                
                // Suppression de l'image si demandée
                if (isset($_POST['delete_image']) && $_POST['delete_image'] == '1') {
                    $old_image = get_evenement_image($id);
                    if ($old_image && file_exists(__DIR__ . '/../public/' . $old_image)) {
                        unlink(__DIR__ . '/../public/' . $old_image);
                    }
                    delete_evenement_image($id);
                }
                
                // Mise à jour de l'image si uploadée
                if (isset($filename)) {
                    // Supprimer l'ancienne image
                    $old_image = get_evenement_image($id);
                    if ($old_image && file_exists(__DIR__ . '/../public/' . $old_image)) {
                        unlink(__DIR__ . '/../public/' . $old_image);
                    }
                    update_evenement_image($id, 'uploads/evenements/' . $filename);
                }
                
                set_flash_message('Événement modifié.', 'success');
            } else {
                $event_id = create_evenement($data);
                
                // Ajouter l'image si uploadée
                if (isset($filename) && $event_id) {
                    add_evenement_image($event_id, 'uploads/evenements/' . $filename);
                }
                
                set_flash_message('Événement ajouté.', 'success');
            }
            redirect('admin/evenements');
        }
    }
    load_view_with_layout('admin/evenement_edit', [
        'evenement' => $evenement,
        'medias' => $medias,
        'is_edit' => $is_edit,
        'errors' => $errors,
        'title' => $is_edit ? 'Modifier un événement' : 'Ajouter un événement'
    ]);
}

function admin_evenement_delete($id) {
    require_admin();
    delete_evenement($id);
    set_flash_message('Événement supprimé.', 'success');
    redirect('admin/evenements');
}
