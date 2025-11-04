<?php
/**
 * Contrôleur Home
 * Gère les pages publiques principales (accueil, à propos, contact, profil)
 */

// Charger les models nécessaires
require_once MODEL_PATH . '/media_model.php';
require_once MODEL_PATH . '/user_model.php';

// Page d'accueil principale

/**
 * Action d'accueil de l'application
 */
function home_index() {
    // Charger les modèles nécessaires
    require_once MODEL_PATH . '/media_model.php';
    
    // Récupération des tops 3 par type de média
    $top_livres = get_popular_medias_by_type('livre', 3);
    $top_films = get_popular_medias_by_type('film', 3);
    $top_jeux = get_popular_medias_by_type('jeu', 3);
    
    // Statistiques de la médiathèque
    $stats = get_mediatheque_stats();
    
    // Récupérer les IDs des genres pour les liens de sélection spéciale
    $all_genres = get_all_genres();
    $genre_ids = [];
    foreach ($all_genres as $genre) {
        // Stocker avec normalisation des noms (minuscules + sans accents)
        $nom_lower = mb_strtolower($genre['nom'], 'UTF-8');
        $nom_normalized = str_replace(
            ['é', 'è', 'ê', 'à', 'â', 'ô', 'î', 'ù', 'ç'],
            ['e', 'e', 'e', 'a', 'a', 'o', 'i', 'u', 'c'],
            $nom_lower
        );
        $genre_ids[$nom_lower] = $genre['id'];
        $genre_ids[$nom_normalized] = $genre['id'];
    }
    
    // Horaires d'ouverture
    $horaires = [
        'Lundi' => '9h00 - 18h00',
        'Mardi' => '9h00 - 18h00',
        'Mercredi' => '9h00 - 20h00',
        'Jeudi' => '9h00 - 18h00',
        'Vendredi' => '9h00 - 18h00',
        'Samedi' => '10h00 - 17h00',
        'Dimanche' => 'Fermé'
    ];
    
    // Événements à venir depuis la base de données
    require_once MODEL_PATH . '/evenement_model.php';
    $evenements_db = get_all_evenements();
    
    // Sélections thématiques
    $selections = get_thematic_selections();
    
    // Traitement des formulaires d'ajout/modification d'événements (admin uniquement)
    if (is_admin() && is_post()) {
        if (isset($_POST['event_id'])) {
            // Modification d'événement
            $event_id = (int)$_POST['event_id'];
            
            $data_event = [
                'titre' => trim(post('titre')),
                'date_evenement' => post('date_evenement'),
                'heure_evenement' => trim(post('heure_evenement')),
                'description' => trim(post('description')),
                'media_id' => null
            ];
            
            // Suppression de l'image si demandée
            if (isset($_POST['delete_image']) && $_POST['delete_image'] == '1') {
                $old_image = get_evenement_image($event_id);
                if ($old_image && file_exists(__DIR__ . '/../public/' . $old_image)) {
                    unlink(__DIR__ . '/../public/' . $old_image);
                }
                delete_evenement_image($event_id);
            }
            
            // Gestion de l'upload d'image
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = __DIR__ . '/../public/uploads/evenements/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                
                $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $filename = 'event_' . uniqid() . '.' . $extension;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $filename)) {
                    // Supprimer l'ancienne image
                    $old_image = get_evenement_image($event_id);
                    if ($old_image && file_exists(__DIR__ . '/../public/' . $old_image)) {
                        unlink(__DIR__ . '/../public/' . $old_image);
                    }
                    update_evenement_image($event_id, 'uploads/evenements/' . $filename);
                }
            }
            
            update_evenement($event_id, $data_event);
            set_flash_message('Événement modifié avec succès.', 'success');
            redirect('');
        } else {
            // Ajout d'événement
            $data_event = [
                'titre' => trim(post('titre')),
                'date_evenement' => post('date_evenement'),
                'heure_evenement' => trim(post('heure_evenement')),
                'description' => trim(post('description')),
                'media_id' => null
            ];
            
            $event_id = create_evenement($data_event);
            
            // Gestion de l'upload d'image
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = __DIR__ . '/../public/uploads/evenements/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                
                $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $filename = 'event_' . uniqid() . '.' . $extension;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $filename)) {
                    add_evenement_image($event_id, 'uploads/evenements/' . $filename);
                }
            }
            
            set_flash_message('Événement ajouté avec succès.', 'success');
            redirect('');
        }
    }
    
    // Préparation des données pour la vue d'accueil
    $data = [
        'title' => 'Accueil - Médiathèque de Toulon',
        'top_livres' => $top_livres,
        'top_films' => $top_films,
        'top_jeux' => $top_jeux,
        'stats' => $stats,
        'horaires' => $horaires,
        'evenements' => $evenements_db,
        'selections' => $selections,
        'genre_ids' => $genre_ids
    ];
    
    // Chargement de la vue avec le layout principal
    load_view_with_layout('home/index', $data);
}


function home_about() {
    // Données simples pour la page à propos
    $data = [
        'title' => 'À propos',                          // Titre de la page
        'content' => 'Cette application est un starter kit PHP MVC développé avec une approche procédurale.'
    ];
    
    // Chargement de la vue avec le layout principal
    load_view_with_layout('home/about', $data);
}


function home_contact() {
    // Données de base pour la page de contact
    $data = [
        'title' => 'Contact'
    ];
    
    // Traitement du formulaire si soumission POST
    if (is_post()) {
        // === RÉCUPÉRATION ET NETTOYAGE DES DONNÉES ===
        $name = clean_input(post('name'));       // Nom du contact
        $email = clean_input(post('email'));     // Email du contact
        $message = clean_input(post('message')); // Message du contact
        
        // === VALIDATION DES DONNÉES ===
        
        // Vérification que tous les champs obligatoires sont remplis
        if (empty($name) || empty($email) || empty($message)) {
            set_flash('error', 'Tous les champs sont obligatoires.');
            
        // Validation du format de l'adresse email
        } elseif (!validate_email($email)) {
            set_flash('error', 'Adresse email invalide.');
            
        } else {
            // === TRAITEMENT RÉUSSI ===
            
            // Ici, vous pourriez ajouter l'envoi d'email réel
            // mail($email_admin, "Contact depuis le site", $message, $headers);
            
            // Pour l'instant, on simule juste un succès
            set_flash('success', 'Votre message a été envoyé avec succès !');
            
            // Redirection pour éviter la resoumission du formulaire
            redirect('home/contact');
        }
    }
    
    // Chargement de la vue (GET ou POST avec erreurs)
    load_view_with_layout('home/contact', $data);
}


function home_profile() {
    // === CONTRÔLE D'ACCÈS ===
    
    // Vérifier que l'utilisateur est connecté
    if (!is_logged_in()) {
        set_flash('error', 'Vous devez être connecté pour accéder à votre profil.');
        redirect('auth/login');
        return;
    }
    
    // Récupérer les informations de l'utilisateur connecté
    $user = get_logged_user();
    
    // Récupérer les emprunts en cours
    $emprunts_en_cours = get_user_current_loans($user['id']);
    // Compter les emprunts en retard pour l'affichage des stats
    require_once MODEL_PATH . '/user_model.php';
    $overdue_count = count_user_overdue_loans($user['id']);
    
    // Pagination pour l'historique
    $history_page = isset($_GET['history_page']) ? max(1, intval($_GET['history_page'])) : 1;
    $history_per_page = 10;
    $history_offset = ($history_page - 1) * $history_per_page;
    
    // Récupérer l'historique des emprunts (tous les emprunts passés et en cours)
    $all_history = get_user_loan_history($user['id']); // Tous les emprunts
    $total_history = count($all_history);
    $total_history_pages = ceil($total_history / $history_per_page);
    $historique = array_slice($all_history, $history_offset, $history_per_page);
    
    // Limite d'emprunts simultanés (selon le cahier des charges)
    $nb_emprunts_max = 3;
    
    // Préparation des données de base pour la vue
    $data = [
        'title' => 'Mon Profil',
        'user' => $user,
        'emprunts_en_cours' => $emprunts_en_cours,
        'overdue_count' => $overdue_count,
        'historique' => $historique,
        'nb_emprunts_max' => $nb_emprunts_max,
        'history_page' => $history_page,
        'total_history_pages' => $total_history_pages,
        'total_history' => $total_history
    ];
    
    // === TRAITEMENT DES MODIFICATIONS DE PROFIL ===
    
    if (is_post()) {
        // Récupération des nouvelles données du formulaire
        $prenom = clean_input(post('prenom'));
        $nom = clean_input(post('nom'));
        $email = clean_input(post('email'));
        $current_password = post('current_password');
        $new_password = post('new_password');
        $confirm_password = post('confirm_password');
        
        // === VALIDATION DES DONNÉES OBLIGATOIRES ===
        
        if (empty($prenom) || empty($nom) || empty($email)) {
            set_flash('error', 'Le prénom, nom et email sont obligatoires.');
            
        } elseif (!validate_email($email)) {
            set_flash('error', 'Format d\'email invalide.');
            
        } elseif ($email !== $user['email'] && email_exists($email)) {
            set_flash('error', 'Cette adresse email est déjà utilisée.');
            
        } else {
            // === MISE À JOUR DES INFORMATIONS DE BASE ===
            
            $update_success = update_user_profile($user['id'], $prenom, $nom, $email);
            
            if ($update_success) {
                set_flash('success', 'Profil mis à jour avec succès !');
                
                // === TRAITEMENT DU CHANGEMENT DE MOT DE PASSE (OPTIONNEL) ===
                
                if (!empty($new_password)) {
                    // Vérification du mot de passe actuel
                    if (empty($current_password) || !verify_password($current_password, $user['password'])) {
                        set_flash('error', 'Mot de passe actuel incorrect.');
                        
                    } elseif ($new_password !== $confirm_password) {
                        set_flash('error', 'Les nouveaux mots de passe ne correspondent pas.');
                        
                    } elseif (strlen($new_password) < 6) {
                        set_flash('error', 'Le nouveau mot de passe doit contenir au moins 6 caractères.');
                        
                    } else {
                        // Mise à jour du mot de passe
                        $password_update_success = update_user_password($user['id'], $new_password);
                        
                        if ($password_update_success) {
                            set_flash('success', 'Mot de passe changé avec succès !');
                        } else {
                            set_flash('error', 'Erreur lors du changement de mot de passe.');
                        }
                    }
                }
                
                // Redirection pour actualiser les données
                redirect('home/profile');
                
            } else {
                set_flash('error', 'Erreur lors de la mise à jour du profil.');
            }
        }
    }
    
    // Chargement de la vue avec les données
    // Chargement de la vue avec les données
    load_view_with_layout('home/profile', $data);
}
