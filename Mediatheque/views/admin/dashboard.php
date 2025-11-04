<!-- En-tête de la page -->
<div class="page-header">
    <div class="container">
        <h1><?php e($title); ?></h1>
        <p class="subtitle">Vue d'ensemble de la médiathèque</p>
    </div>
</div>

<!-- Styles spécifiques (prioritaires) -->
<style>
    <?php require_once __DIR__ . '/../../public/assets/css/dashboard.css'; ?>
</style>

<!-- Tableau de bord administratif -->
<section class="admin-dashboard">
    <div class="container">

        <!-- Cartes de statistiques principales -->
        <div class="stats-grid">
            <!-- Total des médias -->
            <div class="stat-card stat-primary">
                <div class="stat-icon">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['total_medias']); ?></h3>
                    <p>Médias total</p>
                </div>
            </div>

            <!-- Total des utilisateurs -->
            <div class="stat-card stat-info">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['total_users']); ?></h3>
                    <p>Utilisateurs inscrits</p>
                </div>
            </div>

            <!-- Emprunts en cours -->
            <div class="stat-card stat-success">
                <div class="stat-icon">
                    <i class="fas fa-hand-paper"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['emprunts_en_cours'] ?? 0); ?></h3>
                    <p>Emprunts en cours</p>
                </div>
            </div>

            <!-- Emprunts en retard -->
            <div class="stat-card stat-warning">
                <div class="stat-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['emprunts_en_retard']); ?></h3>
                    <p>Emprunts en retard</p>
                </div>
            </div>
        </div>

        <!-- Répartition par type de média -->
        <div class="dashboard-section">
            <h2>Répartition des médias par type</h2>
            <div class="media-types-grid">
                <div class="media-type-card">
                    <div class="media-type-icon livre">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="media-type-info">
                        <h4>Livres</h4>
                        <span class="count"><?php echo number_format($stats['total_livres']); ?></span>
                    </div>
                </div>

                <div class="media-type-card">
                    <div class="media-type-icon film">
                        <i class="fas fa-film"></i>
                    </div>
                    <div class="media-type-info">
                        <h4>Films</h4>
                        <span class="count"><?php echo number_format($stats['total_films']); ?></span>
                    </div>
                </div>

                <div class="media-type-card">
                    <div class="media-type-icon jeu">
                        <i class="fas fa-gamepad"></i>
                    </div>
                    <div class="media-type-info">
                        <h4>Jeux vidéo</h4>
                        <span class="count"><?php echo number_format($stats['total_jeux']); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Emprunts en retard -->
        <?php if (!empty($stats['emprunts_retard_details'])): ?>
            <div class="dashboard-section alert-section">
                <h2>
                    <i class="fas fa-exclamation-triangle text-warning"></i>
                    Emprunts en retard (<?php echo count($stats['emprunts_retard_details']); ?>)
                </h2>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Utilisateur</th>
                                <th>Média</th>
                                <th>Date retour prévue</th>
                                <th>Jours de retard</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stats['emprunts_retard_details'] as $emprunt): ?>
                                <tr class="overdue-row">
                                    <td>
                                        <strong><?php e($emprunt['prenom'] . ' ' . $emprunt['nom']); ?></strong><br>
                                        <small><?php e($emprunt['email']); ?></small>
                                    </td>
                                    <td>
                                        <span class="media-title"><?php e($emprunt['titre']); ?></span>
                                        <span class="media-type type-<?php echo $emprunt['type']; ?>">
                                            <?php echo ucfirst($emprunt['type']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo format_date($emprunt['date_retour_prevue'], 'd/m/Y'); ?></td>
                                    <td>
                                        <span class="retard-badge">
                                            <?php echo $emprunt['jours_retard']; ?> jour<?php echo $emprunt['jours_retard'] > 1 ? 's' : ''; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" action="<?php echo url('admin/force_return/' . $emprunt['id']); ?>" style="display: inline;">
                                            <button type="submit" class="btn btn-sm btn-warning"
                                                onclick="return confirm('Forcer le retour de ce média ?')"
                                                data-confirm="Forcer le retour de ce média ?">
                                                <i class="fas fa-undo"></i> Forcer retour
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- Deux colonnes : Derniers emprunts + Utilisateurs actifs -->
        <div class="dashboard-grid">

            <!-- Derniers emprunts -->
            <div class="dashboard-card">
                <h2>
                    <i class="fas fa-clock"></i>
                    Derniers emprunts
                </h2>
                <?php if (!empty($stats['derniers_emprunts'])): ?>
                    <div class="recent-loans">
                        <?php foreach ($stats['derniers_emprunts'] as $emprunt): ?>
                            <div class="loan-item">
                                <div class="loan-user">
                                    <strong><?php e($emprunt['prenom'] . ' ' . $emprunt['nom']); ?></strong>
                                </div>
                                <div class="loan-media">
                                    <?php e($emprunt['titre']); ?>
                                    <span class="media-type type-<?php echo $emprunt['type']; ?>">
                                        <?php echo ucfirst($emprunt['type']); ?>
                                    </span>
                                </div>
                                <div class="loan-date">
                                    <?php echo format_date($emprunt['date_emprunt'], 'd/m/Y'); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="card-footer">
                        <a href="<?php echo url('admin/loans'); ?>" class="btn-btn-outline">
                            Voir tous les emprunts
                        </a>
                    </div>
                <?php else: ?>
                    <p class="no-data">Aucun emprunt récent</p>
                <?php endif; ?>
            </div>

            <!-- Utilisateurs les plus actifs -->
            <div class="dashboard-card">
                <h2>
                    <i class="fas fa-trophy"></i>
                    Utilisateurs les plus actifs
                </h2>
                <?php if (!empty($stats['utilisateurs_actifs'])): ?>
                    <div class="active-users">
                        <?php foreach ($stats['utilisateurs_actifs'] as $index => $user): ?>
                            <div class="user-item">
                                <div class="user-rank">
                                    <span class="rank-number"><?php echo $index + 1; ?></span>
                                </div>
                                <div class="user-info">
                                    <strong><?php e($user['prenom'] . ' ' . $user['nom']); ?></strong>
                                    <div class="user-stats">
                                        <span class="total-loans"><?php echo $user['total_emprunts'] ?? 0; ?> emprunts</span>
                                        <?php if (($user['emprunts_en_cours'] ?? 0) > 0): ?>
                                            <span class="current-loans"><?php echo $user['emprunts_en_cours'] ?? 0; ?> en cours</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="user-actions">
                                    <a href="<?php echo url('admin/user/' . $user['id']); ?>" class="btn btn-sm btn-outline">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="card-footer">
                        <a href="<?php echo url('admin/users'); ?>" class="btn-btn-outline">
                            Gérer les utilisateurs
                        </a>
                    </div>
                <?php else: ?>
                    <p class="no-data">Aucun utilisateur actif</p>
                <?php endif; ?>
            </div>

        </div>

        <!-- Actions rapides -->
        <div class="dashboard-section">
            <h2>Actions rapides</h2>
            <div class="quick-actions">
                <a href="<?php echo url('media/add'); ?>" class="action-btn action-primary">
                    <i class="fas fa-plus"></i>
                    <span>Ajouter un média</span>
                </a>
                <a href="<?php echo url('admin/users'); ?>" class="action-btn action-info">
                    <i class="fas fa-users"></i>
                    <span>Gérer les utilisateurs</span>
                </a>
                <a href="<?php echo url('admin/loans'); ?>" class="action-btn action-success">
                    <i class="fas fa-list"></i>
                    <span>Gérer les emprunts</span>
                </a>
                <a href="<?php echo url('admin/genres'); ?>" class="action-btn action-purple">
                    <i class="fas fa-tags"></i>
                    <span>Gérer les genres</span>
                </a>
                <a href="<?php echo url('admin/evenements'); ?>" class="action-btn action-warning">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Gérer les événements</span>
                </a>
                <a href="<?php echo url('media/library'); ?>" class="action-btn action-secondary">
                    <i class="fas fa-book-open"></i>
                    <span>Catalogue public</span>
                </a>
            </div>
        </div>

    </div>
</section>