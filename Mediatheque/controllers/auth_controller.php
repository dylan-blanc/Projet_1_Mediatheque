<?php
/**
 * Contrôleur d'Authentification
 * Gère la connexion, inscription et déconnexion
 */

// Charger le model des utilisateurs
require_once MODEL_PATH . '/user_model.php';

// Connexion utilisateur

/**
 * Action de connexion utilisateur
 */
function auth_login() {
    // Si l'utilisateur est déjà connecté, rediriger vers l'accueil
    if (is_logged_in()) {
        redirect('home');
        return;
    }
    
    // Préparation des données pour la vue
    $data = [
        'title' => 'Connexion'
    ];
    
    // === TRAITEMENT DU FORMULAIRE DE CONNEXION ===
    
    if (is_post()) {
        // Récupération et nettoyage des données du formulaire
        $email = clean_input(post('email'));    // Email de connexion (nettoyé)
        $password = post('password');            // Mot de passe (non nettoyé pour préserver les caractères)
        
        // === VALIDATION DES CHAMPS OBLIGATOIRES ===
        
        if (empty($email) || empty($password)) {
            set_flash('error', 'Email et mot de passe obligatoires.');
            
        } else {
            // === PROCESSUS D'AUTHENTIFICATION ===
            
            // Rechercher l'utilisateur par email dans la base de données
            $user = get_user_by_email($email);
            
            // Vérification de l'existence de l'utilisateur et du mot de passe
            if ($user && verify_password($password, $user['password'])) {
                
                // === CONNEXION RÉUSSIE - CRÉATION DE LA SESSION ===
                
                // Stocker les informations utilisateur en session
                $_SESSION['user_id'] = $user['id'];           // ID unique de l'utilisateur
                $_SESSION['user_prenom'] = $user['prenom'];   // Prénom pour personnalisation
                $_SESSION['user_nom'] = $user['nom'];         // Nom pour affichage complet
                $_SESSION['user_email'] = $user['email'];     // Email pour vérifications
                $_SESSION['user_role'] = $user['role'];       // Rôle (user/admin) pour permissions
                $_SESSION['last_activity'] = time();          // Timestamp pour gestion d'inactivité (2h)
                
                // Message de confirmation et redirection
                set_flash('success', 'Connexion réussie !');
                redirect('home');
                
            } else {
                // === ÉCHEC DE L'AUTHENTIFICATION ===
                
                // Message d'erreur générique pour ne pas révéler si l'email existe
                set_flash('error', 'Email ou mot de passe incorrect.');
            }
        }
    }
    
    // Chargement de la vue de connexion
    load_view_with_layout('auth/login', $data);
}


function auth_register() {
    // === CONTRÔLE DE REDIRECTION ===
    
    // Si l'utilisateur est déjà connecté, rediriger vers l'accueil
    if (is_logged_in()) {
        redirect('home');
        return;
    }
    
    // Préparation des données pour la vue
    $data = [
        'title' => 'Inscription'
    ];
    
    // === TRAITEMENT DU FORMULAIRE D'INSCRIPTION ===
    
    if (is_post()) {
        // Récupération et nettoyage des données du formulaire
        $prenom = clean_input(post('prenom'));           // Prénom (nettoyé)
        $nom = clean_input(post('nom'));                 // Nom (nettoyé)
        $email = clean_input(post('email'));             // Email (nettoyé)
        $password = post('password');                    // Mot de passe (non nettoyé)
        $confirm_password = post('confirm_password');    // Confirmation mot de passe
        
        // === VALIDATION COMPLÈTE SELON LE CAHIER DES CHARGES ===
        
        // Vérification des champs obligatoires
        if (empty($prenom) || empty($nom) || empty($email) || empty($password)) {
            set_flash('error', 'Tous les champs sont obligatoires.');
            
        // Validation du format du prénom (2-50 caractères, lettres uniquement)
        } elseif (!validate_name($prenom)) {
            set_flash('error', 'Le prénom doit contenir entre 2 et 50 caractères, lettres, espaces et tirets uniquement.');
            

        // Validation du format du nom (2-50 caractères, lettres uniquement)
        } elseif (!validate_name($nom)) {
            set_flash('error', 'Le nom doit contenir entre 2 et 50 caractères, lettres, espaces et tirets uniquement.');
            
        // Validation du format de l'email (format valide + max 100 caractères)
        } elseif (!validate_email($email)) {
            set_flash('error', 'Adresse email invalide ou trop longue (max 100 caractères).');
            
        // Validation de la complexité du mot de passe
        } elseif (!validate_password($password)) {
            set_flash('error', 'Le mot de passe doit contenir au moins 8 caractères avec majuscules, minuscules et chiffres.');
            
        // Vérification de la correspondance des mots de passe
        } elseif ($password !== $confirm_password) {
            set_flash('error', 'Les mots de passe ne correspondent pas.');
            
        // Vérification de l'unicité de l'email
        } elseif (get_user_by_email($email)) {
            set_flash('error', 'L\'email est déjà utilisé par un autre compte.');
            
        } else {
            // === TRAITEMENT RÉUSSI - CRÉATION DU COMPTE ===
            
            // Formatage des noms (première lettre majuscule, reste minuscule)
            $prenom = format_name($prenom);
            $nom = format_name($nom);
            
            // === CRÉATION DE L'UTILISATEUR EN BASE ===
            $user_id = create_user($prenom, $nom, $email, $password);
            
            if ($user_id) {
                set_flash('success', 'Inscription réussie ! Vous pouvez maintenant vous connecter.');
                redirect('auth/login');
            } else {
                set_flash('error', 'Erreur lors de l\'inscription.');
            }
        }
    }
    
    load_view_with_layout('auth/register', $data);
}

/**
 * Déconnexion
 */
function auth_logout() {
    logout();
} 