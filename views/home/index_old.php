<div class="hero">
    <div class="hero-content">
        <h1>Bienvenue à la Médiathèque TLN</h1>
        <p class="hero-subtitle">Découvrez notre collection de livres, films et jeux vidéo</p>
        <?php if (!is_logged_in()): ?>
            <div class="hero-buttons">
                <a href="<?php echo url('auth/register'); ?>" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> S'inscrire
                </a>
                <a href="<?php echo url('auth/login'); ?>" class="btn btn-secondary">
                    <i class="fas fa-sign-in-alt"></i> Se connecter
                </a>
            </div>
        <?php else: ?>
            <p class="welcome-message">
                <i class="fas fa-user"></i>
                Bienvenue, <?php e($_SESSION['user_prenom'] ?? $_SESSION['user_name']); ?> !
            </p>
            <div class="hero-buttons">
                <a href="<?php echo url('media/library'); ?>" class="btn btn-primary">
                    <i class="fas fa-book"></i> Parcourir le catalogue
                </a>
                <a href="<?php echo url('home/profile'); ?>" class="btn btn-secondary">
                    <i class="fas fa-user"></i> Mon profil
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Statistiques de la médiathèque -->
<section class="stats-section">
    <div class="container">
        <h2>Notre collection</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-number"><?php echo $stats['total_livres'] ?? 0; ?></span>
                    <span class="stat-label">Livres</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-film"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-number"><?php echo $stats['total_films'] ?? 0; ?></span>
                    <span class="stat-label">Films</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-gamepad"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-number"><?php echo $stats['total_jeux'] ?? 0; ?></span>
                    <span class="stat-label">Jeux vidéo</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-number"><?php echo $stats['total_users'] ?? 0; ?></span>
                    <span class="stat-label">Membres</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Médias populaires -->
<?php if (isset($popular_medias) && !empty($popular_medias)): ?>
    <section class="popular-section">
        <div class="container">
            <h2>Médias populaires</h2>
            <div class="popular-grid">
                <?php foreach ($popular_medias as $media): ?>
                    <div class="popular-card">
                        <div class="popular-image">
                            <?php if ($media['image']): ?>
                                <img src="<?php echo url($media['image']); ?>" alt="<?php e($media['titre']); ?>" loading="lazy">
                            <?php else: ?>
                                <div class="no-image">
                                    <i class="fas fa-<?php echo $media['type'] === 'livre' ? 'book-open' : ($media['type'] === 'film' ? 'video' : 'gamepad'); ?>"></i>
                                </div>
                            <?php endif; ?>
                            <div class="popular-type">
                                <span class="type-badge type-<?php echo $media['type']; ?>">
                                    <?php echo ucfirst($media['type']); ?>
                                </span>
                            </div>
                        </div>
                        <div class="popular-info">
                            <h3><?php e($media['titre']); ?></h3>
                            <p class="popular-genre"><?php e($media['genre']); ?></p>
                            <a href="<?php echo url('media/detail/' . $media['id']); ?>" class="btn-btn-outline btn-sm">
                                Voir détails
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="section-actions">
                <a href="<?php echo url('media/library'); ?>" class="btn btn-primary">
                    Voir tout le catalogue <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- Services -->
<section class="services-section">
    <div class="container">
        <h2>Nos services</h2>
        <div class="services-grid">
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-hand-holding"></i>
                </div>
                <h3>Emprunts gratuits</h3>
                <p>Empruntez jusqu'à 3 médias simultanément pour une durée de 14 jours.</p>
            </div>

            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h3>Recherche avancée</h3>
                <p>Trouvez facilement vos médias par titre, auteur, genre ou type.</p>
            </div>

            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-bell"></i>
                </div>
                <h3>Notifications</h3>
                <p>Recevez des rappels pour les dates de retour de vos emprunts.</p>
            </div>

            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <h3>Favoris</h3>
                <p>Gardez une liste de vos médias préférés pour les retrouver facilement.</p>
            </div>
        </div>
    </div>
</section>
</section>

<section class="getting-started">
    <div class="container">
        <h2>Commencer rapidement</h2>
        <div class="steps">
            <div class="step">
                <div class="step-number">1</div>
                <h3>Configuration</h3>
                <p>Configurez votre base de données dans <code>config/database.php</code></p>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <h3>Développement</h3>
                <p>Créez vos contrôleurs, modèles et vues dans leurs dossiers respectifs</p>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <h3>Déploiement</h3>
                <p>Uploadez votre application sur votre serveur web</p>
            </div>
        </div>
    </div>
</section>