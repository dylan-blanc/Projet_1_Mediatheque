<div class="page-header">
    <div class="container">
        <h1><?php e($title); ?></h1>
        <nav class="breadcrumb">
            <a href="<?php echo url('admin/dashboard'); ?>">Administration</a>
            <span class="breadcrumb-separator">></span>
            <span>Gestion des emprunts</span>
        </nav>
    </div>
</div>

<style>
    <?php require_once __DIR__ . '/../../public/assets/css/dashboard.css'; ?>
</style>

<section class="admin-content">
    <div class="container">

        <!-- Statistiques des emprunts -->
        <div class="stats-grid">
            <div class="stat-card stat-primary">
                <div class="stat-icon">
                    <i class="fas fa-hand-paper"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['total_emprunts'] ?? 0); ?></h3>
                    <p>Total emprunts</p>
                </div>
            </div>

            <div class="stat-card stat-success">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['emprunts_en_cours'] ?? 0); ?></h3>
                    <p>En cours</p>
                </div>
            </div>

            <div class="stat-card stat-warning">
                <div class="stat-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['emprunts_en_retard'] ?? 0); ?></h3>
                    <p>En retard</p>
                </div>
            </div>

            <div class="stat-card stat-info">
                <div class="stat-icon">
                    <i class="fas fa-check"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['emprunts_termines'] ?? 0); ?></h3>
                    <p>Terminés</p>
                </div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="admin-card">
            <div class="filters-section">
                <form method="GET" class="filters-form">
                    <div class="filters-row">
                        <div class="filter-group">
                            <label for="statut">Statut</label>
                            <select id="statut" name="statut">
                                <option value="">Tous les statuts</option>
                                <option value="En cours" <?php echo ($filters['statut'] ?? '') === 'En cours' ? 'selected' : ''; ?>>En cours</option>
                                <option value="Rendu" <?php echo ($filters['statut'] ?? '') === 'Rendu' ? 'selected' : ''; ?>>Rendu</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label for="en_retard">Retard</label>
                            <select id="en_retard" name="en_retard">
                                <option value="">Tous</option>
                                <option value="1" <?php echo ($filters['en_retard'] ?? '') === '1' ? 'selected' : ''; ?>>En retard uniquement</option>
                            </select>
                        </div>

                        <div class="filter-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filtrer
                            </button>
                            <a href="<?php echo url('media/loans'); ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Effacer
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Liste des emprunts -->
        <div class="admin-card">
            <?php if (empty($emprunts)): ?>
                <div class="no-results">
                    <i class="fas fa-inbox"></i>
                    <h3>Aucun emprunt trouvé</h3>
                    <p>Aucun emprunt ne correspond aux critères sélectionnés.</p>
                </div>
            <?php else: ?>
                <div class="media-loans-table-responsive">
                    <table class="media-loans-emprunts-table">
                        <thead>
                            <tr>
                                <th>Utilisateur</th>
                                <th>Média</th>
                                <th>Type</th>
                                <th>Date d'emprunt</th>
                                <th>Date de retour prévue</th>
                                <th>Statut</th>
                                <th>Retard</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($emprunts as $emprunt): ?>
                                <tr class="<?php echo $emprunt['statut'] === 'En cours' && $emprunt['en_retard'] ? 'overdue' : ''; ?>">
                                    <td>
                                        <div class="media-loans-user-info">
                                            <strong><?php e($emprunt['prenom'] . ' ' . $emprunt['nom']); ?></strong>
                                            <small><?php e($emprunt['email']); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="media-loans-media-info">
                                            <strong><?php e($emprunt['media_titre']); ?></strong>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="media-loans-type-badge media-loans-type-<?php echo $emprunt['media_type']; ?>">
                                            <?php echo ucfirst($emprunt['media_type']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($emprunt['date_emprunt'])); ?></td>
                                    <td>
                                        <span class="<?php echo $emprunt['statut'] === 'En cours' && $emprunt['en_retard'] ? 'media-loans-text-danger' : ''; ?>">
                                            <?php echo date('d/m/Y', strtotime($emprunt['date_retour_prevue'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="media-loans-status-badge media-loans-status-<?php echo strtolower(str_replace(' ', '-', $emprunt['statut'])); ?>">
                                            <?php e($emprunt['statut']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($emprunt['statut'] === 'En cours' && $emprunt['en_retard']): ?>
                                            <span class="media-loans-retard-badge">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                <?php echo $emprunt['jours_retard']; ?> jour(s)
                                            </span>
                                        <?php elseif ($emprunt['statut'] === 'Rendu' && $emprunt['date_retour_reelle'] && strtotime($emprunt['date_retour_reelle']) > strtotime($emprunt['date_retour_prevue'])): ?>
                                            <span class="media-loans-retard-badge-past">
                                                Retard passé
                                            </span>
                                        <?php else: ?>
                                            <span class="media-loans-text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="media-loans-actions">
                                            <a href="<?php echo url('admin/user/' . $emprunt['user_id']); ?>"
                                                class="btn btn-sm btn-outline" title="Voir utilisateur">
                                                <i class="fas fa-user"></i>
                                            </a>

                                            <a href="<?php echo url('media/detail/' . $emprunt['media_id']); ?>"
                                                class="btn btn-sm btn-outline" title="Voir média">
                                                <i class="fas fa-book"></i>
                                            </a>

                                            <?php if ($emprunt['statut'] === 'En cours'): ?>
                                                <form method="POST" action="<?php echo url('admin/force_return/' . $emprunt['id']); ?>" style="display: inline;">
                                                    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                                                    <button type="submit" class="btn btn-sm btn-warning"
                                                        onclick="return confirm('Forcer le retour de ce média ?')"
                                                        title="Forcer le retour">
                                                        <i class="fas fa-undo"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if (($total_pages ?? 0) > 1): ?>
                    <div class="pagination">
                        <?php if ($current_page > 1): ?>
                            <a href="<?php echo url('media/loans?page=' . ($current_page - 1) . '&' . http_build_query($filters)); ?>"
                                class="btn-btn-outline">
                                <i class="fas fa-chevron-left"></i> Précédent
                            </a>
                        <?php endif; ?>

                        <span class="page-info">
                            Page <?php echo $current_page; ?> sur <?php echo $total_pages; ?>
                        </span>

                        <?php if ($current_page < $total_pages): ?>
                            <a href="<?php echo url('media/loans?page=' . ($current_page + 1) . '&' . http_build_query($filters)); ?>"
                                class="btn-btn-outline">
                                Suivant <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</section>