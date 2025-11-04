<!-- En-tête de la page -->
<div class="page-header">
    <div class="container">
        <h1><?php e($title); ?></h1>
        <p class="subtitle">Gestion de tous les emprunts de la médiathèque</p>
    </div>
</div>

<!-- Styles spécifiques -->
<link rel="stylesheet" href="<?php echo url('assets/css/dashboard.css'); ?>">
<link rel="stylesheet" href="<?php echo url('assets/css/loans.css'); ?>">

<!-- Gestion des emprunts -->
<section class="admin-content">
    <div class="container">
        
        <!-- Statistiques rapides -->
        <div class="stats-grid">
            <div class="stat-card stat-success">
                <div class="stat-icon">
                    <i class="fas fa-hand-paper"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['en_cours']); ?></h3>
                    <p>Emprunts en cours</p>
                </div>
            </div>
            
            <div class="stat-card stat-warning">
                <div class="stat-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['en_retard']); ?></h3>
                    <p>Emprunts en retard</p>
                </div>
            </div>
            
            <div class="stat-card stat-info">
                <div class="stat-icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['total_today']); ?></h3>
                    <p>Emprunts aujourd'hui</p>
                </div>
            </div>
        </div>
        
        <!-- Filtres de recherche -->
        <div class="admin-card">
            <h2>Filtres de recherche</h2>
            
            <form method="GET" class="filters-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="statut">Statut</label>
                        <select id="statut" name="statut">
                            <option value="">Tous les statuts</option>
                            <option value="En cours" <?php echo ($filters['statut'] ?? '') === 'En cours' ? 'selected' : ''; ?>>En cours</option>
                            <option value="En retard" <?php echo ($filters['statut'] ?? '') === 'En retard' ? 'selected' : ''; ?>>En retard</option>
                            <option value="Rendu" <?php echo ($filters['statut'] ?? '') === 'Rendu' ? 'selected' : ''; ?>>Rendu</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="retard">Retard</label>
                        <select id="retard" name="retard">
                            <option value="">Tous</option>
                            <option value="oui" <?php echo ($filters['retard'] ?? '') === 'oui' ? 'selected' : ''; ?>>En retard</option>
                            <option value="rendu_en_retard" <?php echo ($filters['retard'] ?? '') === 'rendu_en_retard' ? 'selected' : ''; ?>>Rendu en retard</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="user_search">Recherche utilisateur</label>
                        <input type="text" id="user_search" name="user_search" 
                               value="<?php echo escape($filters['user_search'] ?? ''); ?>"
                               placeholder="Nom, prénom ou email">
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filtrer
                        </button>
                        <a href="<?php echo url('admin/loans'); ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Réinitialiser
                        </a>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Liste des emprunts -->
        <div class="admin-card">
            <div class="card-header">
                <h2>Liste des emprunts (<?php echo number_format($total_emprunts); ?> au total)</h2>
            </div>
            
            <?php if (empty($emprunts)): ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-inbox fa-3x"></i>
                    </div>
                    <h3>Aucun emprunt trouvé</h3>
                    <p>Aucun emprunt ne correspond aux critères sélectionnés.</p>
                    <?php if (!empty(array_filter($filters))): ?>
                        <a href="<?php echo url('admin/loans'); ?>" class="btn btn-primary">
                            <i class="fas fa-times"></i> Réinitialiser les filtres
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Utilisateur</th>
                                <th>Média</th>
                                <th>Date d'emprunt</th>
                                <th>Date de retour prévue</th>
                                <th>Statut</th>
                                <th>Retard</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($emprunts as $emprunt): ?>
                                <tr>
                                    <td>
                                        <div class="user-info">
                                            <strong><?php e($emprunt['prenom'] . ' ' . $emprunt['nom']); ?></strong>
                                            <small><?php e($emprunt['email']); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="media-info">
                                            <?php if ($emprunt['image']): ?>
                                                <img src="<?php echo url($emprunt['image']); ?>" alt="Couverture" class="media-thumb">
                                            <?php endif; ?>
                                            <div>
                                                <strong><?php e($emprunt['titre']); ?></strong>
                                                <span class="media-type"><?php e(ucfirst($emprunt['type'])); ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php echo date('d/m/Y', strtotime($emprunt['date_emprunt'])); ?>
                                    </td>
                                    <td>
                                        <?php echo date('d/m/Y', strtotime($emprunt['date_retour_prevue'])); ?>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $emprunt['statut'])); ?>">
                                            <?php e($emprunt['statut']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (($emprunt['statut'] === 'En retard') || ($emprunt['statut'] === 'En cours' && !empty($emprunt['jours_retard']) && $emprunt['jours_retard'] > 0)): ?>
                                            <span class="retard-badge">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                <?php echo (int) $emprunt['jours_retard']; ?> jour(s)
                                            </span>
                                        <?php elseif ($emprunt['statut'] === 'Rendu'): ?>
                                            <?php if (!empty($emprunt['date_retour_reelle']) && strtotime($emprunt['date_retour_reelle']) > strtotime($emprunt['date_retour_prevue'])): ?>
                                                <span class="status-warning"><i class="fas fa-clock"></i> Rendu en retard</span>
                                            <?php else: ?>
                                                <span class="text-success"><i class="fas fa-check"></i> Rendu</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-success"><i class="fas fa-check"></i> À temps</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="<?php echo url('admin/user_detail/' . $emprunt['user_id']); ?>" 
                                               class="btn btn-sm btn-info" title="Voir utilisateur">
                                                <i class="fas fa-user"></i>
                                            </a>
                                            
                                            <a href="<?php echo url('media/detail/' . $emprunt['media_id']); ?>" 
                                               class="btn btn-sm btn-secondary" title="Voir média">
                                                <i class="fas fa-book"></i>
                                            </a>
                                            
                                            <?php if ($emprunt['statut'] === 'En cours' || $emprunt['statut'] === 'En retard'): ?>
                                                <form method="POST" action="<?php echo url('admin/force_return/' . $emprunt['id']); ?>" class="inline-form"
                                                      onsubmit="return confirm('Êtes-vous sûr de vouloir forcer le retour de cet emprunt ?');">
                                                    <button type="submit" class="btn btn-sm btn-warning" title="Forcer le retour">
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
                <?php if ($total_pages > 1): ?>
                    <div class="pagination-container">
                        <nav class="pagination">
                            <?php
                            $query_params = $_GET;
                            unset($query_params['page']);
                            $base_url = url('admin/loans') . '?' . http_build_query($query_params);
                            ?>
                            
                            <?php if ($current_page > 1): ?>
                                <a href="<?php echo $base_url; ?>&page=<?php echo $current_page - 1; ?>" class="pagination-link">
                                    <i class="fas fa-chevron-left"></i> Précédent
                                </a>
                            <?php endif; ?>
                            
                            <?php
                            $start = max(1, $current_page - 2);
                            $end = min($total_pages, $current_page + 2);
                            
                            for ($i = $start; $i <= $end; $i++):
                            ?>
                                <a href="<?php echo $base_url; ?>&page=<?php echo $i; ?>" 
                                   class="pagination-link <?php echo $i === $current_page ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($current_page < $total_pages): ?>
                                <a href="<?php echo $base_url; ?>&page=<?php echo $current_page + 1; ?>" class="pagination-link">
                                    Suivant <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </nav>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</section>
