<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="<?php echo url('assets/images/logo.jpg'); ?>">
    
    <!-- Titre dynamique de la page -->
    <title><?php echo isset($title) ? esc($title) . ' - ' . APP_NAME : APP_NAME; ?></title>
    
    <!-- Ressources CSS -->
    <link rel="stylesheet" href="<?php echo url('assets/css/style.css'); ?>">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- CSS spécifique pour la gestion des événements admin -->
    <?php if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'admin/evenement') !== false): ?>
        <link rel="stylesheet" href="<?php echo url('assets/css/admin_events.css'); ?>">
    <?php endif; ?>
    
    <!-- CSS spécifique pour la gestion des genres admin -->
    <?php if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'admin/genre') !== false): ?>
        <link rel="stylesheet" href="<?php echo url('assets/css/users.css'); ?>">
        <link rel="stylesheet" href="<?php echo url('assets/css/genres.css'); ?>">
    <?php endif; ?>
</head>
<body>
    <!-- Header et navigation principale -->
    <header class="header">
        <nav class="navbar">
            <!-- Logo/Brand cliquable vers l'accueil -->
            <div class="nav-brand">
                <a href="<?php echo url(); ?>">Médiathèque</a>
            </div>
            
            <!-- Bouton burger pour mobile -->
            <button class="burger-menu" id="burger-menu" aria-label="Menu de navigation">
                <span></span>
                <span></span>
                <span></span>
            </button>
            
            <ul class="nav-menu" id="nav-menu">
                <!-- Menu Administration (visible pour les admins uniquement) -->
                <?php if (is_logged_in() && is_admin()): ?>
                    <li class="nav-dropdown">
                        <a href="#" class="nav-dropdown-toggle">
                            <i class="fas fa-cog"></i> Administration <i class="fas fa-chevron-down"></i>
                        </a>
                        <ul class="nav-dropdown-menu">
                            <li><a href="<?php echo url('admin/dashboard'); ?>">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a></li>
                            <li><a href="<?php echo url('admin/users'); ?>">
                                <i class="fas fa-users"></i> Utilisateurs
                            </a></li>
                            <li><a href="<?php echo url('admin/loans'); ?>">
                                <i class="fas fa-book-reader"></i> Gestion des emprunts
                            </a></li>
                            <li><a href="<?php echo url('admin/evenements'); ?>">
                                <i class="fas fa-calendar-alt"></i> Gestion des événements
                            </a></li>
                            <li><a href="<?php echo url('admin/genres'); ?>">
                                <i class="fas fa-tags"></i> Gestion des genres
                            </a></li>
                            <li><a href="<?php echo url('media/add'); ?>">
                                <i class="fas fa-plus"></i> Ajouter Média
                            </a></li>
                        </ul>
                    </li>
                <?php endif; ?>
                <li><a href="<?php echo url(); ?>">Accueil</a></li>
                <li><a href="<?php echo url('media/library'); ?>">Catalogue</a></li>
                <?php if (is_logged_in()): ?>
                    <?php if (!is_admin()): ?>
                        <li><a href="<?php echo url('home/profile'); ?>">Mon Profil</a></li>
                    <?php endif; ?>
                    <li><a href="<?php echo url('home/about'); ?>">À propos</a></li>
                    <li><a href="<?php echo url('home/contact'); ?>">Contact</a></li>
                    <li><a href="<?php echo url('auth/logout'); ?>">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                    </a></li>
                <?php else: ?>
                    <li><a href="<?php echo url('home/about'); ?>">À propos</a></li>
                    <li><a href="<?php echo url('home/contact'); ?>">Contact</a></li>
                    <li><a href="<?php echo url('auth/login'); ?>">
                        <i class="fas fa-sign-in-alt"></i> Connexion
                    </a></li>
                    <li><a href="<?php echo url('auth/register'); ?>">
                        <i class="fas fa-user-plus"></i> Inscription
                    </a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main class="main-content">
        <?php flash_messages(); ?>
        <?php echo $content ?? ''; ?>
    </main>

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-contact">
                <p><a href="mailto:campusavarappe@mail.com"><i class="fas fa-envelope"></i> campusavarappe@mail.com</a></p>
                <p><a href="tel:0102030405"><i class="fas fa-phone"></i> 01 02 03 04 05</a></p>
                <p><a href="<?php echo url('home/about'); ?>"><i class="fas fa-map-marker-alt"></i> 131 Avenue Franklin Roosevelt, 83100 TOULON</a></p>
            </div>
            <div class="footer-info">
                <p>&copy; <?php echo date('Y'); ?> Mediatheque. Tous droits réservés.</p>
                <p>Version <?php echo APP_VERSION; ?></p>
            </div>
            <div class="footer-social">
                <a href="https://facebook.com" target="_blank" rel="noopener" title="Facebook">
                    <i class="fab fa-facebook"></i>
                </a>
                <a href="https://twitter.com" target="_blank" rel="noopener" title="Twitter">
                    <i class="fab fa-twitter"></i>
                </a>
                <a href="https://instagram.com" target="_blank" rel="noopener" title="Instagram">
                    <i class="fab fa-instagram"></i>
                </a>
                <a href="https://youtube.com" target="_blank" rel="noopener" title="YouTube">
                    <i class="fab fa-youtube"></i>
                </a>
            </div>
        </div>
    </footer>

    <script src="<?php echo url('assets/js/app.js'); ?>"></script>
</body>
</html> 