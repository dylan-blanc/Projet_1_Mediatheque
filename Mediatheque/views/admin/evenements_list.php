<?php
/**
 * Vue - Liste des événements admin
 */
?>
<link rel="stylesheet" href="<?php echo url('assets/css/dashboard.css'); ?>">
<link rel="stylesheet" href="<?php echo url('assets/css/admin_events.css'); ?>">

<div class="page-header">
    <div class="container">
        <h1>Gestion des événements</h1>
        <p class="subtitle">Administrez les événements de la médiathèque</p>
    </div>
</div>

<section class="admin-content">
    <div class="container">
        
        <!-- Filtres de recherche -->
        <div class="admin-card filters-card" style="margin-bottom: 2rem;">
            <h2 style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.5rem;">
                <i class="fas fa-filter"></i> Filtres de recherche
            </h2>
            <form method="GET" action="<?php echo url('admin/evenements'); ?>" class="filters-form">
                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 1rem; align-items: end;">
                    <div class="form-group">
                        <label for="search_titre">Recherche par titre</label>
                        <input type="text" 
                               id="search_titre" 
                               name="search_titre" 
                               class="form-control" 
                               placeholder="Titre de l'événement..."
                               value="<?php echo isset($_GET['search_titre']) ? escape($_GET['search_titre']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="search_date">Filtrer par date</label>
                        <input type="date" 
                               id="search_date" 
                               name="search_date" 
                               class="form-control"
                               value="<?php echo isset($_GET['search_date']) ? escape($_GET['search_date']) : ''; ?>">
                    </div>
                    
                    <div class="form-group" style="display: flex; gap: 0.5rem;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Rechercher
                        </button>
                        <?php if (!empty($_GET['search_titre']) || !empty($_GET['search_date'])): ?>
                            <a href="<?php echo url('admin/evenements'); ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Réinitialiser
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>

        <!-- Bouton d'ajout -->
        <div style="margin-bottom: 1.5rem;">
            <a href="<?php echo url('admin/evenement/add'); ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Ajouter un événement
            </a>
        </div>

        <!-- Tableau des événements -->
        <div class="admin-card">
            <div class="table-wrapper">
                <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Titre</th>
                        <th>Date</th>
                        <th>Heure</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($evenements)): ?>
                        <tr>
                            <td colspan="6" class="no-data" style="text-align: center; padding: 3rem; color: #6c757d;">
                                <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 1rem; display: block;"></i>
                                <p>Aucun événement trouvé</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($evenements as $event): ?>
                            <tr>
                                <td><?php e($event['id']); ?></td>
                                <td><strong><?php e($event['titre']); ?></strong></td>
                                <td><?php echo format_date($event['date_evenement'], 'd/m/Y'); ?></td>
                                <td><?php e($event['heure_evenement']); ?></td>
                                <td class="description-cell"><?php e(substr($event['description'], 0, 100)); ?><?php echo strlen($event['description']) > 100 ? '...' : ''; ?></td>
                                <td>
                                    <div class="action-buttons" style="display: flex; gap: 0.5rem;">
                                        <a href="<?php echo url('admin/evenement/edit/' . $event['id']); ?>" 
                                           class="btn btn-sm btn-warning" 
                                           title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?php echo url('admin/evenement/delete/' . $event['id']); ?>" 
                                           class="btn btn-sm btn-danger" 
                                           title="Supprimer"
                                           onclick="return confirm('Supprimer cet événement ?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination" style="display: flex; justify-content: space-between; align-items: center; padding: 2rem 0; margin-top: 2rem; border-top: 2px solid #e9ecef;">
                    <?php if ($current_page > 1): ?>
                        <a href="<?php echo url('admin/evenements?page=' . ($current_page - 1) . (isset($_GET['search_titre']) ? '&search_titre=' . urlencode($_GET['search_titre']) : '') . (isset($_GET['search_date']) ? '&search_date=' . $_GET['search_date'] : '')); ?>" 
                           class="btn btn-sm btn-secondary">
                            <i class="fas fa-chevron-left"></i> Précédent
                        </a>
                    <?php else: ?>
                        <span></span>
                    <?php endif; ?>
                    
                    <span class="page-info" style="color: #555; font-weight: 500; font-size: 0.95rem;">
                        Page <?php echo $current_page; ?> sur <?php echo $total_pages; ?> (<?php echo $total_events; ?> événement<?php echo $total_events > 1 ? 's' : ''; ?>)
                    </span>
                    
                    <?php if ($current_page < $total_pages): ?>
                        <a href="<?php echo url('admin/evenements?page=' . ($current_page + 1) . (isset($_GET['search_titre']) ? '&search_titre=' . urlencode($_GET['search_titre']) : '') . (isset($_GET['search_date']) ? '&search_date=' . $_GET['search_date'] : '')); ?>" 
                           class="btn btn-sm btn-secondary">
                            Suivant <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php else: ?>
                        <span></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Espace avant le footer -->
        <div style="margin-bottom: 4rem;"></div>
    </div>
</section>div>
    </div>
</section>
