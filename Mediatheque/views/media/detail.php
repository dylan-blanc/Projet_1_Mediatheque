<link rel="stylesheet" href="<?php echo url('assets/css/media_detail.css'); ?>">

<div class="page-header">
    <div class="container">
        <h1><?php e($title); ?></h1>
        <nav class="breadcrumb">
            <a href="<?php echo url('media/library'); ?>">Catalogue</a>
            <span class="breadcrumb-separator">></span>
            <span><?php e($media['titre']); ?></span>
        </nav>
    </div>
</div>

<section class="media-detail">
    <div class="container">
        <div class="media-detail-detail-grid">
            <!-- Image -->
            <div class="media-detail-media-image">
                <img src="<?php echo url(get_media_image($media)); ?>" alt="<?php e($media['titre']); ?>" class="media-detail-detail-img">
            </div>
            <!-- Infos -->
            <div class="media-info">
                <div class="media-detail-media-header">
                    <h2><?php e($media['titre']); ?></h2>
                    <span class="media-detail-media-type media-detail-type-<?php echo $media['type']; ?>">
                        <i class="fas fa-<?php echo $media['type'] === 'livre' ? 'book' : ($media['type'] === 'film' ? 'film' : 'gamepad'); ?>"></i>
                        <?php echo ucfirst($media['type']); ?>
                    </span>
                </div>
                <div class="media-detail-media-meta">
                    <?php if ($media['type'] === 'livre'): ?>
                        <p><strong>Auteur :</strong> <?php e($media['auteur']); ?></p>
                        <p><strong>Genre :</strong> <?php e($media['genres'] ?? 'Non défini'); ?></p>
                        <p><strong>Nombre de pages :</strong> <?php e($media['nombre_pages']); ?></p>
                        <?php if ($media['isbn']): ?><p><strong>ISBN :</strong> <?php e($media['isbn']); ?></p><?php endif; ?>
                        <?php if ($media['annee_publication']): ?><p><strong>Année de publication :</strong> <?php e($media['annee_publication']); ?></p><?php endif; ?>
                    <?php elseif ($media['type'] === 'film'): ?>
                        <p><strong>Réalisateur :</strong> <?php e($media['realisateur']); ?></p>
                        <p><strong>Genre :</strong> <?php e($media['genres'] ?? 'Non défini'); ?></p>
                        <p><strong>Durée :</strong> <?php e($media['duree_minutes']); ?> minutes</p>
                        <p><strong>Année :</strong> <?php e($media['annee_film']); ?></p>
                        <?php if ($media['classification']): ?><p><strong>Classification :</strong> <?php e($media['classification']); ?></p><?php endif; ?>
                    <?php elseif ($media['type'] === 'jeu'): ?>
                        <p><strong>Éditeur :</strong> <?php e($media['editeur']); ?></p>
                        <p><strong>Plateforme :</strong> <?php e($media['plateforme']); ?></p>
                        <p><strong>Genre :</strong> <?php e($media['genres'] ?? 'Non défini'); ?></p>
                        <p><strong>Âge minimum :</strong> <?php e($media['age_minimum']); ?>+</p>
                    <?php endif; ?>
                </div>
                <?php
                $description = '';
                if ($media['type'] === 'livre' && !empty($media['resume'])) {
                    $description = $media['resume'];
                } elseif ($media['type'] === 'film' && !empty($media['synopsis'])) {
                    $description = $media['synopsis'];
                } elseif ($media['type'] === 'jeu' && !empty($media['description'])) {
                    $description = $media['description'];
                }
                ?>
                <?php if ($description): ?>
                    <div class="media-detail-media-description">
                        <h3><?php echo $media['type'] === 'livre' ? 'Résumé' : ($media['type'] === 'film' ? 'Synopsis' : 'Description'); ?></h3>
                        <p><?php e($description); ?></p>
                    </div>
                <?php endif; ?>
                <div class="media-detail-media-status">
                    <h3>Disponibilité</h3>
                    <?php if (isset($media['stock_disponible']) && $media['stock_disponible'] > 0): ?>
                        <span class="media-detail-status-badge media-detail-status-available">
                            <i class="fas fa-check-circle"></i>
                            Disponible (<?php echo $media['stock_disponible']; ?>/<?php echo $media['stock']; ?>)
                        </span>
                    <?php else: ?>
                        <span class="media-detail-status-badge media-detail-status-unavailable">
                            <i class="fas fa-times-circle"></i>
                            Indisponible
                        </span>
                    <?php endif; ?>
                </div>
                <?php if (is_logged_in()): ?>
                    <div class="media-detail-media-actions">
                        <?php if ($can_borrow['can_borrow']): ?>
                            <form method="POST" action="<?php echo url('media/borrow/' . $media['id']); ?>" class="media-detail-borrow-form">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-hand-paper"></i>
                                    Emprunter ce <?php echo $media['type']; ?>
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="media-detail-borrow-disabled">
                                <button class="btn btn-secondary btn-lg" disabled>
                                    <i class="fas fa-ban"></i>
                                    <?php e($can_borrow['reason']); ?>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="media-detail-media-actions">
                        <div class="media-detail-login-prompt">
                            <p>Vous devez être connecté pour emprunter ce média.</p>
                            <a href="<?php echo url('auth/login'); ?>" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt"></i>
                                Se connecter
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="media-detail-back-actions">
            <a href="<?php echo url('media/library'); ?>" class="btn-btn-outline">
                <i class="fas fa-arrow-left"></i>
                Retour au catalogue
            </a>
            <?php if (is_admin()): ?>
                <a href="<?php echo url('media/edit/' . $media['id']); ?>" class="btn btn-warning">
                    <i class="fas fa-edit"></i>
                    Modifier ce média
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>