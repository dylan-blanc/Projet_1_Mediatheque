<?php
// Fonctions utilitaires

/**
 * Log personnalisé pour le débogage
 */
function debug_log($message)
{
    // Utiliser un chemin absolu pour éviter les problèmes de constantes
    $log_file = dirname(__DIR__) . '/public/debug.log';
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[{$timestamp}] {$message}\n";
    @file_put_contents($log_file, $log_message, FILE_APPEND);
}

/**
 * Sécurise l'affichage d'une chaîne de caractères (protection XSS)
 */
function escape($string)
{
    // Accepter null et autres types en les castant en chaîne pour éviter les warnings
    if ($string === null) {
        $string = '';
    }
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}

/**
 * Affiche une chaîne sécurisée (échappée)
 */
function e($string)
{
    echo escape($string);
}

/**
 * Retourne une chaîne sécurisée sans l'afficher
 */
function esc($string)
{
    return escape($string);
}

/**
 * Génère une URL absolue
 */
function url($path = '')
{
    $base_url = rtrim(BASE_URL, '/');
    $path = ltrim($path, '/');
    return $base_url . '/' . $path;
}

/**
 * Redirection HTTP
 */
function redirect($path = '')
{
    $url = url($path);
    header("Location: $url");
    exit;
}

/**
 * Génère un token CSRF
 */
function csrf_token()
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Vérifie un token CSRF
 */
function verify_csrf_token($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Définit un message flash
 */
function set_flash($type, $message)
{
    $_SESSION['flash_messages'][$type][] = $message;
}

/**
 * Récupère et supprime les messages flash
 */
function get_flash_messages($type = null)
{
    if (!isset($_SESSION['flash_messages'])) {
        return [];
    }

    if ($type) {
        $messages = $_SESSION['flash_messages'][$type] ?? [];
        unset($_SESSION['flash_messages'][$type]);
        return $messages;
    }

    $messages = $_SESSION['flash_messages'];
    unset($_SESSION['flash_messages']);
    return $messages;
}

/**
 * Vérifie s'il y a des messages flash
 */
function has_flash_messages($type = null)
{
    if (!isset($_SESSION['flash_messages'])) {
        return false;
    }

    if ($type) {
        return !empty($_SESSION['flash_messages'][$type]);
    }

    return !empty($_SESSION['flash_messages']);
}

/**
 * Nettoie une chaîne de caractères
 */
function clean_input($data)
{
    // Accepter null et autres types
    $data = (string)$data;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Valide une adresse email
 */
function validate_email($email)
{
    if (strlen($email) > 100) return false;
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Génère un mot de passe sécurisé
 */
function generate_password($length = 12)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $password;
}

/**
 * Hache un mot de passe
 */
function hash_password($password)
{
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Vérifie un mot de passe
 */
function verify_password($password, $hash)
{
    return password_verify($password, $hash);
}

/**
 * Formate une date
 */
function format_date($date, $format = 'd/m/Y H:i')
{
    return date($format, strtotime($date));
}

/**
 * Vérifie si une requête est en POST
 */
function is_post()
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Vérifie si une requête est en GET
 */
function is_get()
{
    return $_SERVER['REQUEST_METHOD'] === 'GET';
}

/**
 * Retourne la valeur d'un paramètre POST
 */
function post($key, $default = null)
{
    return $_POST[$key] ?? $default;
}

/**
 * Retourne la valeur d'un paramètre GET
 */
function get($key, $default = null)
{
    return $_GET[$key] ?? $default;
}

/**
 * Vérifie si un utilisateur est connecté (modifier)
 */
function is_logged_in()
{
    if (!isset($_SESSION['user_id'])) {
        return false;
    }

    // Vérifier l'inactivité (2 heures selon le cahier des charges)
    // regarder ligne 559 au 585
    if (check_session_timeout()) {
        logout_due_to_timeout();
        return false;
    }

    // Mettre à jour le timestamp de dernière activité
    $_SESSION['last_activity'] = time();

    return true;
}

/**
 * Retourne l'ID de l'utilisateur connecté
 */
function current_user_id()
{
    return $_SESSION['user_id'] ?? null;
}

/**
 * Déconnecte l'utilisateur
 */
function logout()
{
    session_destroy();
    redirect('auth/login');
}

/**
 * Formate un nombre
 */
function format_number($number, $decimals = 2)
{
    return number_format($number, $decimals, ',', ' ');
}

/**
 * Génère un slug à partir d'une chaîne
 */
function generate_slug($string)
{
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
    $string = preg_replace('/[\s-]+/', '-', $string);
    return trim($string, '-');
}

// function rajouter (ne pas modifier tout ce qu"il y a au dessus)

//_______________________________________________________________________________________________________________________________________________________________________________
//_______________________________________________________________________________________________________________________________________________________________________________
//_______________________________________________________________________________________________________________________________________________________________________________
//_______________________________________________________________________________________________________________________________________________________________________________
//_______________________________________________________________________________________________________________________________________________________________________________




/**
 * Valide un mot de passe selon le cahier des charges
 */
function validate_password($password)
{
    // Minimum 8 caractères
    if (strlen($password) < 8) return false;

    // Au moins 1 minuscule
    if (!preg_match('/[a-z]/', $password)) return false;

    // Au moins 1 majuscule  
    if (!preg_match('/[A-Z]/', $password)) return false;

    // Au moins 1 chiffre
    if (!preg_match('/\d/', $password)) return false;

    return true;
}

/**
 * Valide un nom ou prénom
 */
function validate_name($name)
{
    // Entre 2 et 50 caractères
    if (strlen($name) < 2 || strlen($name) > 50) return false;

    // Lettres, espaces et tirets uniquement
    if (!preg_match('/^[a-zA-ZÀ-ÿ\s\-]+$/', $name)) return false;

    return true;
}

/**
 * Valide un ISBN (10 ou 13 chiffres)
 */
function validate_isbn($isbn)
{
    $isbn = preg_replace('/[^0-9]/', '', $isbn);
    return strlen($isbn) === 10 || strlen($isbn) === 13;
}

/**
 * Valide une année de publication
 */
function validate_year($year)
{
    $current_year = date('Y');
    return $year >= 1900 && $year <= $current_year;
}

/**
 * Formate un nom (première lettre en majuscule)
 */
function format_name($name)
{
    $name = trim($name);
    // Gérer les espaces (pour utilisateurs avec plusieurs Noms)
    $name = ucwords(strtolower($name));
    // Gérer les tirets (pour les utilisateurs avec des prénoms composés)
    $name = preg_replace_callback('/-([a-z])/', function ($matches) {
        return '-' . strtoupper($matches[1]);
    }, $name);
    return $name;
}

/**
 * Vérifie si l'utilisateur est administrateur
 */
function is_admin()
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Redirige si pas admin
 */
function require_admin()
{
    if (!is_admin()) {
        // Déconnecter l'utilisateur non autorisé
        session_destroy();
        session_start(); // Redémarrer pour afficher le message
        set_flash('error', 'Accès non autorisé. Vous avez été déconnecté.');
        redirect('auth/login');
    }
}

/**
 * Redirige si pas connecté
 */
function require_login()
{
    if (!is_logged_in()) {
        set_flash('error', 'Vous devez être connecté pour accéder à cette page.');
        redirect('auth/login');
    }
}


/**
 * Génère un nom de fichier pour une image de média au format: type-id-titre.ext
 */
function generate_media_filename($type, $id, $titre, $extension)
{
    // Slugifier le titre
    $slug = generate_slug($titre);
    $base = sprintf('%s-%s-%s', $type, $id, $slug);
    $filename = $base . '.' . $extension;

    // Construire le dossier d'upload
    $upload_dir = rtrim(UPLOADS_PATH, '/\\') . DIRECTORY_SEPARATOR;

    // Si le fichier existe déjà, ajouter un suffixe incremente
    $counter = 1;
    while (file_exists($upload_dir . $filename)) {
        $filename = $base . '-' . $counter . '.' . $extension;
        $counter++;
        // Pour éviter une boucle infinie, casser après quelques essais
        if ($counter > 100) break;
    }

    return $filename;
}

/**
 * Upload d'une image pour un média en utilisant le nom formaté "type-id-titre.ext"
 * Retourne le chemin relatif (ex: 'uploads/covers/nom.ext') ou false en cas d'erreur
 * Renseigne $error (string) avec un message utilisateur en cas d'échec.
 */
function upload_cover_image_for_media($file, $type, $id, $titre, &$error = null)
{
    $error = null;

    // Vérifier si un fichier a été uploadé
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        $error = 'Aucun fichier n\'a été envoyé.';
        error_log('upload_cover_image_for_media: no file uploaded');
        return false;
    }

    // Vérifier les erreurs d'upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        // Mapping basique des erreurs PHP
        switch ($file['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $error = 'Image trop volumineuse. Taille maximum : 2 Mo.';
                break;
            case UPLOAD_ERR_PARTIAL:
            case UPLOAD_ERR_NO_FILE:
                $error = 'Le fichier n\'a pas été entièrement uploadé.';
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
            case UPLOAD_ERR_CANT_WRITE:
            case UPLOAD_ERR_EXTENSION:
                $error = 'Erreur serveur lors de l\'upload du fichier.';
                break;
            default:
                $error = 'Erreur lors de l\'upload du fichier (code: ' . $file['error'] . ').';
        }
        error_log('upload_cover_image_for_media: upload error ' . $file['error']);
        return false;
    }

    // Vérifier la taille (2Mo max - 2 097 152 octets)
    if ($file['size'] > 2097152) {
        $error = 'Image trop volumineuse. Taille maximum : 2 Mo.';
        error_log('upload_cover_image_for_media: file too large ' . $file['size'] . ' bytes (max 2097152)');
        return false;
    }

    // Vérifier le type MIME (sécurité)
    $allowed_types = ['image/jpeg', 'image/pjpeg', 'image/png', 'image/x-png', 'image/gif'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime_type, $allowed_types)) {
        $error = 'Format d\'image non supporté. Utilisez JPG, PNG ou GIF.';
        error_log('upload_cover_image_for_media: invalid mime type ' . $mime_type);
        return false;
    }

    // Vérifier l'extension (double vérification avec MIME)
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($extension, $allowed_extensions)) {
        $error = 'Extension de fichier invalide. Utilisez JPG, PNG ou GIF.';
        error_log('upload_cover_image_for_media: invalid extension ' . $extension);
        return false;
    }

    // Vérifier les dimensions minimales (100x100 px minimum)
    $image_info = @getimagesize($file['tmp_name']);
    if (!$image_info) {
        $error = 'Fichier image invalide.';
        error_log('upload_cover_image_for_media: cannot read image dimensions');
        return false;
    }

    $width = $image_info[0];
    $height = $image_info[1];

    if ($width < 100 || $height < 100) {
        $error = 'Image trop petite. Dimensions minimales : 100x100 pixels.';
        error_log('upload_cover_image_for_media: image too small ' . $width . 'x' . $height . ' (min 100x100)');
        return false;
    }

    // Préparer le dossier d'upload à partir de la constante UPLOADS_PATH
    // CORRECTION: S'assurer que le chemin contient '/covers'
    $uploads_base = defined('UPLOADS_PATH') ? UPLOADS_PATH : (PUBLIC_PATH . '/uploads/covers');

    // Si UPLOADS_PATH ne contient pas '/covers', le corriger
    if (strpos($uploads_base, '/covers') === false && strpos($uploads_base, '\\covers') === false) {
        $uploads_base = rtrim($uploads_base, '/\\') . '/covers';
    }

    $upload_dir = rtrim($uploads_base, '/\\') . DIRECTORY_SEPARATOR;

    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            $error = 'Impossible de créer le dossier d\'upload.';
            error_log('upload_cover_image_for_media: cannot create upload directory');
            return false;
        }
    }

    // Vérifier l'espace disque disponible (au moins 10 Mo libres)
    $free_space = @disk_free_space($upload_dir);
    if ($free_space !== false && $free_space < 10485760) { // 10 Mo
        $error = 'Espace disque insuffisant sur le serveur.';
        error_log('upload_cover_image_for_media: insufficient disk space');
        return false;
    }

    // Générer le nom avec identifiant unique pour éviter les conflits
    $filename = generate_media_filename($type, $id, $titre, $extension);
    $filepath = $upload_dir . $filename;

    // Déplacer le fichier
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Redimensionner l'image (max 300x400)
        $resize_result = resize_image($filepath, 300, 400);
        if ($resize_result === false) {
            // Non bloquant: on conserve le fichier et on log seulement
            error_log('upload_cover_image_for_media: resize failed, but file uploaded');
        }

        // Retourner le chemin relatif utilisé par l'application (pour url())
        return 'uploads/covers/' . $filename;
    } else {
        $error = 'Impossible d\'enregistrer le fichier sur le serveur.';
        error_log('upload_cover_image_for_media: move_uploaded_file failed for ' . ($file['name'] ?? 'unknown'));
        return false;
    }
}

/**
 * Redimensionne une image
 */
function resize_image($filepath, $max_width, $max_height)
{
    $image_info = getimagesize($filepath);
    if (!$image_info) return false;

    $width = $image_info[0];
    $height = $image_info[1];
    $type = $image_info[2];

    // Calculer les nouvelles dimensions
    $ratio = min($max_width / $width, $max_height / $height);
    if ($ratio >= 1) return true; // Pas besoin de redimensionner

    $new_width = intval($width * $ratio);
    $new_height = intval($height * $ratio);

    // Créer l'image source
    switch ($type) {
        case IMAGETYPE_JPEG:
            $source = imagecreatefromjpeg($filepath);
            break;
        case IMAGETYPE_PNG:
            $source = imagecreatefrompng($filepath);
            break;
        case IMAGETYPE_GIF:
            $source = imagecreatefromgif($filepath);
            break;
        default:
            return false;
    }

    // Créer la nouvelle image
    $destination = imagecreatetruecolor($new_width, $new_height);

    // Préserver la transparence pour PNG et GIF
    if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
        imagealphablending($destination, false);
        imagesavealpha($destination, true);
        $transparent = imagecolorallocatealpha($destination, 255, 255, 255, 127);
        imagefilledrectangle($destination, 0, 0, $new_width, $new_height, $transparent);
    }

    // Redimensionner
    imagecopyresampled($destination, $source, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

    // Sauvegarder
    switch ($type) {
        case IMAGETYPE_JPEG:
            imagejpeg($destination, $filepath, 90);
            break;
        case IMAGETYPE_PNG:
            imagepng($destination, $filepath);
            break;
        case IMAGETYPE_GIF:
            imagegif($destination, $filepath);
            break;
    }

    // Libérer la mémoire
    imagedestroy($source);
    imagedestroy($destination);

    return true;
}

/**
 * Fonction set_flash_message (alias pour set_flash)
 */
function set_flash_message($message, $type = 'info')
{
    set_flash($type, $message);
}

/**
 * Récupère une valeur du serveur
 */
function get_server($key, $default = null)
{
    return $_SERVER[$key] ?? $default;
}

/**
 * Retourne l'URL de base
 */
function base_url()
{
    return rtrim(BASE_URL, '/');
}

/**
 * Formate un temps relatif (ex: "il y a 2 heures")
 */
function time_ago($datetime)
{
    $time = time() - strtotime($datetime);

    if ($time < 60) return 'à l\'instant';
    if ($time < 3600) return floor($time / 60) . ' min';
    if ($time < 86400) return floor($time / 3600) . 'h';
    if ($time < 2592000) return floor($time / 86400) . 'j';
    if ($time < 31536000) return floor($time / 2592000) . ' mois';

    return floor($time / 31536000) . ' ans';
}

/**
 * Récupère une ancienne valeur de formulaire (pour réaffichage après erreur)
 */
function old($key, $default = '')
{
    return $_SESSION['old_input'][$key] ?? $default;
}

/**
 * Compte le nombre d'emprunts en cours pour un média donné
 */
function compter_emprunts_en_cours_media($media_id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM emprunts WHERE media_id = ? AND statut = 'En cours'");
    $stmt->execute([$media_id]);
    return $stmt->fetchColumn();
}

/**
 * Met à jour le stock d'un média avec un recalcul automatique de la disponibilité
 */
function mettre_a_jour_stock_media($media_id, $quantite_totale, $quantite_disponible)
{
    global $pdo;
    $stmt = $pdo->prepare("UPDATE medias SET quantite_totale = ?, quantite_disponible = ? WHERE id = ?");
    return $stmt->execute([$quantite_totale, $quantite_disponible, $media_id]);
}

/**
 * Retourne l'image par défaut pour un type de média donné
 */
function get_default_image_for_type($type)
{
    $default_images = [
        'livre' => 'assets/images/default-book.jpg',
        'film' => 'assets/images/default-movie.jpg',
        'jeu' => 'assets/images/default-game.jpg'
    ];

    return $default_images[$type] ?? 'assets/images/default-media.jpg';
}

/**
 * Recalcule et met à jour la quantité disponible pour un média selon les emprunts en cours
 */
function recalculer_disponibilite_media($media_id)
{
    global $pdo;

    // Récupérer le nombre d'emprunts en cours pour ce média
    $emprunts_en_cours = compter_emprunts_en_cours_media($media_id);

    // Récupérer la quantité totale du média
    $stmt = $pdo->prepare("SELECT quantite_totale FROM medias WHERE id = ?");
    $stmt->execute([$media_id]);
    $quantite_totale = $stmt->fetchColumn();

    // Calculer la quantité disponible (jamais négative)
    $quantite_disponible = max(0, $quantite_totale - $emprunts_en_cours);

    // Mettre à jour la quantité disponible en base
    $stmt = $pdo->prepare("UPDATE medias SET quantite_disponible = ? WHERE id = ?");
    return $stmt->execute([$quantite_disponible, $media_id]);
}

/**
 * Vérifie si la session a expiré (2 heures d'inactivité)
 */
function check_session_timeout()
{
    // Durée d'inactivité autorisée : 2 heures (7200 secondes)
    $timeout_duration = 2 * 60 * 60; // 2 heures en secondes

    // Si pas de timestamp de dernière activité, en créer un
    if (!isset($_SESSION['last_activity'])) {
        $_SESSION['last_activity'] = time();
        return false;
    }

    // Vérifier si l'inactivité dépasse 2 heures
    $inactive_time = time() - $_SESSION['last_activity'];

    return $inactive_time > $timeout_duration;
}

/**
 * Déconnecte l'utilisateur pour cause d'inactivité
 */
function logout_due_to_timeout()
{
    // Détruire la session
    session_destroy();
    session_start(); // Redémarrer pour pouvoir afficher le message

    // Message informatif
    set_flash('warning', 'Votre session a expiré après 2 heures d\'inactivité. Veuillez vous reconnecter.');
}

// =====================================================================================================
// Fonctions ajoutées pour gestion des médias et images par défaut
// =====================================================================================================

/**
 * Retourne le chemin de l'image d'un média ou l'image par défaut selon le type
 * @param array $media Tableau contenant les données du média (doit avoir 'image' et 'type')
 * @return string Chemin de l'image à afficher
 */
function get_media_image($media)
{
    // Si l'image existe et n'est pas vide, la retourner
    if (!empty($media['image']) && $media['image'] !== '' && $media['image'] !== null) {
        return $media['image'];
    }

    // Sinon, retourner l'image par défaut selon le type
    return get_default_image_for_type($media['type'] ?? 'livre');
}

/**
 * Met à jour tous les médias sans image pour leur attribuer l'image par défaut
 * Cette fonction doit être appelée pour s'assurer que tous les médias ont une image
 */
function ensure_all_medias_have_default_images()
{
    // Récupérer tous les médias sans image ou avec image vide/null
    $query = "SELECT id, type FROM medias WHERE image IS NULL OR image = ''";
    $medias = db_select_all($query);

    $updated_count = 0;

    foreach ($medias as $media) {
        $default_image = get_default_image_for_type($media['type']);
        $update_query = "UPDATE medias SET image = ? WHERE id = ?";

        if (db_execute($update_query, [$default_image, $media['id']])) {
            $updated_count++;
        }
    }

    return $updated_count;
}

/**
 * Récupère les médias les plus populaires par type
 * @param string $type Type de média (livre, film, jeu)
 * @param int $limit Nombre maximum de résultats
 * @return array Liste des médias populaires
 */
function get_popular_medias_by_type($type, $limit = 3)
{
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
              WHERE m.type = ?
              GROUP BY m.id
              ORDER BY total_emprunts DESC, m.titre ASC
              LIMIT ?";
    return db_select($query, [$type, $limit]);
}

/**
 * Récupère les statistiques globales de la médiathèque
 * @return array Tableau contenant les statistiques (livres, films, jeux)
 */
function get_mediatheque_stats()
{
    $stats = [
        'livres' => 0,
        'films' => 0,
        'jeux' => 0,
        'total' => 0
    ];

    $query = "SELECT type, COUNT(*) as count FROM medias GROUP BY type";
    $results = db_select($query);

    foreach ($results as $row) {
        $type = $row['type'];
        $count = (int)$row['count'];

        if ($type === 'livre') {
            $stats['livres'] = $count;
        } elseif ($type === 'film') {
            $stats['films'] = $count;
        } elseif ($type === 'jeu') {
            $stats['jeux'] = $count;
        }

        $stats['total'] += $count;
    }

    return $stats;
}

/**
 * Récupère les horaires d'ouverture de la médiathèque
 * @return array Tableau des horaires par jour
 */

/**
 * Récupère les événements à venir de la médiathèque
 * @return array Liste des événements
 */
function get_upcoming_events()
{
    return [
        [
            'title' => 'Atelier d\'écriture créative',
            'date' => '25 Octobre 2025',
            'time' => '14h00 - 16h00',
            'description' => 'Découvrez les techniques d\'écriture avec un auteur local'
        ],
        [
            'title' => 'Club de lecture jeunesse',
            'date' => '1er Novembre 2025',
            'time' => '15h00 - 16h30',
            'description' => 'Rencontre mensuelle pour les 8-12 ans'
        ],
        [
            'title' => 'Projection ciné-débat',
            'date' => '10 Novembre 2025',
            'time' => '19h00 - 22h00',
            'description' => 'Film classique suivi d\'une discussion'
        ]
    ];
}

/**
 * Récupère les sélections thématiques de la médiathèque
 * @return array Liste des sélections avec leurs médias
 */
function get_thematic_selections()
{
    return [
        [
            'title' => 'Sélection jeunesse',
            'description' => 'Les meilleurs livres pour les enfants',
            'icon' => 'fa-child'
        ],
        [
            'title' => 'Découverte cinéma',
            'description' => 'Films d\'auteur et cinéma indépendant',
            'icon' => 'fa-film'
        ],
        [
            'title' => 'Jeux en famille',
            'description' => 'Des jeux pour tous les âges',
            'icon' => 'fa-gamepad'
        ]
    ];
}
// ========================================================================
// HELPERS POUR LA GESTION DES GENRES (5 colonnes genre_id)
// ========================================================================

/**
 * Get all genres from database
 * @return array List of all genres
 */
function get_all_genres()
{
    $query = "SELECT * FROM genres ORDER BY nom ASC";
    return db_query($query);
}

/**
 * Get genre by ID
 * @param int $id Genre ID
 * @return array|null Genre data or null if not found
 */
function get_genre_by_id($id)
{
    $query = "SELECT * FROM genres WHERE id = ?";
    $result = db_query($query, [$id]);
    return $result ? $result[0] : null;
}

/**
 * Get genres for a specific media (from 5 columns)
 * @param int $media_id Media ID
 * @return array List of genres assigned to this media
 */
function get_media_genres($media_id)
{
    $query = "SELECT genre_id_1, genre_id_2, genre_id_3, genre_id_4, genre_id_5 
              FROM medias WHERE id = ?";
    $result = db_query($query, [$media_id]);

    if (!$result) return [];

    $genre_ids = array_filter([
        $result[0]['genre_id_1'],
        $result[0]['genre_id_2'],
        $result[0]['genre_id_3'],
        $result[0]['genre_id_4'],
        $result[0]['genre_id_5']
    ]);

    if (empty($genre_ids)) return [];

    $placeholders = implode(',', array_fill(0, count($genre_ids), '?'));
    $genre_query = "SELECT * FROM genres WHERE id IN ($placeholders) ORDER BY nom ASC";
    return db_query($genre_query, $genre_ids);
}

/**
 * Get genre IDs for a specific media (array of max 5 IDs)
 * @param int $media_id Media ID
 * @return array List of genre IDs
 */
function get_media_genre_ids($media_id)
{
    $query = "SELECT genre_id_1, genre_id_2, genre_id_3, genre_id_4, genre_id_5 
              FROM medias WHERE id = ?";
    $result = db_query($query, [$media_id]);

    if (!$result) return [];

    return array_values(array_filter([
        $result[0]['genre_id_1'],
        $result[0]['genre_id_2'],
        $result[0]['genre_id_3'],
        $result[0]['genre_id_4'],
        $result[0]['genre_id_5']
    ]));
}

/**
 * Validate genre IDs array (1-5 genres, no duplicates)
 * @param array $genre_ids Array of genre IDs
 * @return array|string True if valid, error message otherwise
 */
function validate_media_genre_ids($genre_ids)
{
    // Remove empty values
    $genre_ids = array_filter($genre_ids, function ($id) {
        return !empty($id) && $id > 0;
    });

    // Check minimum
    if (count($genre_ids) < 1) {
        return "Au moins un genre doit être sélectionné.";
    }

    // Check maximum
    if (count($genre_ids) > 5) {
        return "Maximum 5 genres peuvent être sélectionnés.";
    }

    // Check duplicates
    if (count($genre_ids) !== count(array_unique($genre_ids))) {
        return "Un genre ne peut pas être sélectionné plusieurs fois.";
    }

    return true;
}

/**
 * Save genres for a media (max 5 genres in columns)
 * @param int $media_id Media ID
 * @param array $genre_ids Array of genre IDs (max 5)
 * @return bool|string True on success, error message on failure
 */
function save_media_genres($media_id, $genre_ids)
{
    // Validate
    $validation = validate_media_genre_ids($genre_ids);
    if ($validation !== true) {
        return $validation;
    }

    // Clean and prepare IDs
    $clean_ids = array_values(array_filter($genre_ids, function ($id) {
        return !empty($id) && $id > 0;
    }));

    // Pad with NULLs up to 5 slots
    while (count($clean_ids) < 5) {
        $clean_ids[] = null;
    }

    // Update media
    $query = "UPDATE medias 
              SET genre_id_1 = ?, genre_id_2 = ?, genre_id_3 = ?, genre_id_4 = ?, genre_id_5 = ?
              WHERE id = ?";

    return db_execute($query, [
        $clean_ids[0],
        $clean_ids[1],
        $clean_ids[2],
        $clean_ids[3],
        $clean_ids[4],
        $media_id
    ]) ? true : "Erreur lors de la sauvegarde des genres.";
}

/**
 * Create a new genre
 * @param string $nom Genre name
 * @return int|string New genre ID or error message on failure
 */
function create_genre($nom)
{
    $nom = trim($nom);

    // Validate name length
    if (strlen($nom) < 2 || strlen($nom) > 100) {
        return "Le nom du genre doit contenir entre 2 et 100 caractères.";
    }

    // Check if genre already exists
    $check_query = "SELECT id FROM genres WHERE nom = ?";
    $existing = db_query($check_query, [$nom]);
    if ($existing) {
        return "Ce genre existe déjà.";
    }

    // Create genre
    $query = "INSERT INTO genres (nom) VALUES (?)";
    if (db_execute($query, [$nom])) {
        return db_last_insert_id();
    }

    return "Erreur lors de la création du genre.";
}

/**
 * Update genre name
 * @param int $id Genre ID
 * @param string $nom New genre name
 * @return bool|string True on success, error message on failure
 */
function update_genre($id, $nom)
{
    $nom = trim($nom);

    // Validate name length
    if (strlen($nom) < 2 || strlen($nom) > 100) {
        return "Le nom du genre doit contenir entre 2 et 100 caractères.";
    }

    // Check if another genre with same name exists
    $check_query = "SELECT id FROM genres WHERE nom = ? AND id != ?";
    $existing = db_query($check_query, [$nom, $id]);
    if ($existing) {
        return "Un autre genre avec ce nom existe déjà.";
    }

    // Update genre
    $query = "UPDATE genres SET nom = ? WHERE id = ?";
    return db_execute($query, [$nom, $id]) ? true : "Erreur lors de la mise à jour du genre.";
}

/**
 * Get media IDs that use a specific genre
 * @param int $genre_id Genre ID
 * @return array Array of media IDs
 */
function get_medias_using_genre($genre_id)
{
    $query = "SELECT id FROM medias 
              WHERE genre_id_1 = ? OR genre_id_2 = ? OR genre_id_3 = ? OR genre_id_4 = ? OR genre_id_5 = ?";
    $result = db_query($query, [$genre_id, $genre_id, $genre_id, $genre_id, $genre_id]);

    return $result ? array_column($result, 'id') : [];
}

/**
 * Delete genre if not used by any media
 * @param int $id Genre ID
 * @return bool|string True on success, error message with media IDs on failure
 */
function delete_genre($id)
{
    // Get medias using this genre
    $media_ids = get_medias_using_genre($id);

    if (!empty($media_ids)) {
        $media_list = implode(', ', $media_ids);
        return "Ce genre est utilisé par " . count($media_ids) . " média(s) (IDs: " . $media_list . ") et ne peut pas être supprimé.";
    }

    // Delete genre
    $query = "DELETE FROM genres WHERE id = ?";
    return db_execute($query, [$id]) ? true : "Erreur lors de la suppression du genre.";
}

/**
 * Search genres by name
 * @param string $search Search term
 * @return array Matching genres with usage count
 */
function search_genres($search)
{
    $genres = get_all_genres();
    $results = [];

    foreach ($genres as $genre) {
        if (stripos($genre['nom'], $search) !== false) {
            $media_ids = get_medias_using_genre($genre['id']);
            $genre['usage_count'] = count($media_ids);
            $results[] = $genre;
        }
    }

    return $results;
}

/**
 * Count total genres
 * @return int Total number of genres
 */
function count_genres()
{
    $query = "SELECT COUNT(*) as count FROM genres";
    $result = db_query($query);
    return $result ? $result[0]['count'] : 0;
}

/**
 * Get genre usage statistics
 * @param int $id Genre ID
 * @return array Usage statistics (total medias, by type)
 */
function get_genre_usage_stats($id)
{
    $query = "SELECT 
              COUNT(*) as total_medias,
              SUM(CASE WHEN type = 'livre' THEN 1 ELSE 0 END) as total_livres,
              SUM(CASE WHEN type = 'film' THEN 1 ELSE 0 END) as total_films,
              SUM(CASE WHEN type = 'jeu' THEN 1 ELSE 0 END) as total_jeux
              FROM medias
              WHERE genre_id_1 = ? OR genre_id_2 = ? OR genre_id_3 = ? OR genre_id_4 = ? OR genre_id_5 = ?";

    $result = db_query($query, [$id, $id, $id, $id, $id]);

    return $result ? $result[0] : [
        'total_medias' => 0,
        'total_livres' => 0,
        'total_films' => 0,
        'total_jeux' => 0
    ];
}

/**
 * Force return of all overdue loans for a user
 * Retourne de force tous les emprunts en retard d'un utilisateur
 * @param int $user_id User ID
 * @return int Number of loans returned
 */
function admin_force_return_all_overdue_for_user($user_id) {
    // Récupérer tous les emprunts en retard de cet utilisateur
    $query = "SELECT id FROM emprunts 
              WHERE user_id = ? 
              AND (statut = 'En retard' OR (statut = 'En cours' AND date_retour_prevue < CURDATE()))";
    $overdue_loans = db_select($query, [$user_id]);
    
    if (empty($overdue_loans)) {
        return 0;
    }
    
    $returned_count = 0;
    
    // Forcer le retour de chaque emprunt en retard
    foreach ($overdue_loans as $loan) {
        if (admin_force_return_emprunt($loan['id'])) {
            $returned_count++;
        }
    }
    
    return $returned_count;
}
