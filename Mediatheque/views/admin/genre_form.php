<link rel="stylesheet" href="<?php echo url('assets/css/genres.css'); ?>">
<link rel="stylesheet" href="<?php echo url('assets/css/admin_forms.css'); ?>">

<div class="page-header">
    <div class="container">
        <h1><?php e($title); ?></h1>
        <div class="header-actions">
            <a href="<?php echo url('admin/genres'); ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour à la liste
            </a>
        </div>
    </div>
</div>

<section class="admin-content">
        <div class="container">
        <div class="admin-card">
            <form method="POST" class="form-horizontal">
                <div class="form-group">
                    <label for="nom">Nom du genre *</label>
                    <input type="text" 
                           id="nom" 
                           name="nom" 
                           class="form-control" 
                           value="<?php echo escape($genre['nom'] ?? ''); ?>" 
                           required 
                           minlength="2" 
                           maxlength="100"
                           placeholder="Ex: Science-fiction, Thriller, RPG...">
                    <small class="form-text">Entre 2 et 100 caractères</small>
                </div>

                <?php if (isset($genre) && isset($stats)): ?>
                    <div class="genre-stats-box">
                        <h3><i class="fas fa-chart-bar"></i> Utilisation de ce genre</h3>
                        <div class="stats-grid-genres">
                            <div class="stat-item-genre">
                                <span class="stat-label">Livres</span>
                                <span class="stat-value"><?php echo $stats['total_livres']; ?></span>
                            </div>
                            <div class="stat-item-genre">
                                <span class="stat-label">Films</span>
                                <span class="stat-value"><?php echo $stats['total_films']; ?></span>
                            </div>
                            <div class="stat-item-genre">
                                <span class="stat-label">Jeux</span>
                                <span class="stat-value"><?php echo $stats['total_jeux']; ?></span>
                            </div>
                            <div class="stat-item-genre stat-total">
                                <span class="stat-label">Total</span>
                                <span class="stat-value"><?php echo $stats['total_medias']; ?></span>
                            </div>
                        </div>
                        <?php if ($stats['total_medias'] > 0): ?>
                            <p class="warning-text-genre">
                                <i class="fas fa-info-circle"></i>
                                Ce genre est utilisé par <?php echo $stats['total_medias']; ?> média(s). 
                                La modification du nom affectera tous les médias associés.
                            </p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="admin-form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> 
                        <?php echo isset($genre) ? 'Modifier' : 'Ajouter'; ?>
                    </button>
                    <a href="<?php echo url('admin/genres'); ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</section>
