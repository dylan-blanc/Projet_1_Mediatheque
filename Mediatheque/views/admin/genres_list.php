<link rel="stylesheet" href="<?php echo url('assets/css/genres.css'); ?>">

<div class="page-header">
    <div class="container">
        <h1><?php e($title); ?></h1>
        <div class="header-actions">
            <a href="<?php echo url('admin/genre/add'); ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Ajouter un genre
            </a>
        </div>
    </div>
</div>

<section class="admin-content">
    <div class="container">
        <!-- Barre de recherche -->
        <div class="admin-card search-section">
            <form method="GET" action="<?php echo url('admin/genres'); ?>" class="search-form">
                <div class="search-input-group">
                    <input type="text" 
                           name="search" 
                           class="search-input" 
                           placeholder="Rechercher un genre..." 
                           value="<?php echo escape($search); ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Rechercher
                    </button>
                    <?php if (!empty($search)): ?>
                        <a href="<?php echo url('admin/genres'); ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Réinitialiser
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Statistiques -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-tags"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $pagination['total']; ?></h3>
                    <p>Total genres</p>
                </div>
            </div>
        </div>

        <!-- Tableau des genres -->
        <div class="admin-card">
            <div class="table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom du genre</th>
                            <th>Utilisé par</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($genres)): ?>
                            <tr>
                                <td colspan="4" class="no-data">
                                    <i class="fas fa-inbox"></i>
                                    <p>Aucun genre trouvé</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($genres as $genre): ?>
                                <tr>
                                    <td><?php echo $genre['id']; ?></td>
                                    <td><strong><?php e($genre['nom']); ?></strong></td>
                                    <td>
                                        <span class="usage-badge">
                                            <?php echo $genre['usage_count']; ?> média(s)
                                        </span>
                                        <?php if ($genre['usage_count'] > 0): ?>
                                            <small class="usage-detail">
                                                (<?php echo $genre['usage_livres']; ?> livres, 
                                                <?php echo $genre['usage_films']; ?> films, 
                                                <?php echo $genre['usage_jeux']; ?> jeux)
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="actions-cell">
                                        <div class="action-buttons">
                                            <a href="<?php echo url('admin/genre/edit/' . $genre['id']); ?>" 
                                               class="btn btn-sm btn-info" 
                                               title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($genre['usage_count'] == 0): ?>
                                                <form method="POST" 
                                                      action="<?php echo url('admin/genre/delete/' . $genre['id']); ?>" 
                                                      style="display: inline;"
                                                      onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce genre ?');">
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Supprimer">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-secondary" 
                                                        title="Impossible de supprimer (genre utilisé)" 
                                                        disabled>
                                                    <i class="fas fa-lock"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($pagination['total_pages'] > 1): ?>
                <?php
                    $totalPages = (int)$pagination['total_pages'];
                    $current = (int)$pagination['current_page'];
                    $suffix = $search ? ('&search=' . urlencode($search)) : '';

                    $pages = [];
                    if ($totalPages <= 8) {
                        for ($i = 1; $i <= $totalPages; $i++) $pages[] = $i;
                    } else {
                        if ($current <= 4) {
                            $pages = [1,2,3,4,5,6,'…', $totalPages];
                        } elseif ($current >= $totalPages - 3) {
                            $pages = [1,'…',$totalPages-5,$totalPages-4,$totalPages-3,$totalPages-2,$totalPages-1,$totalPages];
                        } else {
                            $pages = [1,'…',$current-1,$current,$current+1,'…',$totalPages];
                        }
                    }
                ?>
                <div class="genres-pagination">
                    <?php if ($current > 1): ?>
                        <a href="<?php echo url('admin/genres?page=' . ($current - 1) . $suffix); ?>" class="pagination-prev">
                            <i class="fas fa-chevron-left"></i> Précédente
                        </a>
                    <?php endif; ?>
                    <ul class="pagination-list">
                        <?php foreach ($pages as $p): ?>
                            <?php if ($p === '…'): ?>
                                <li class="pagination-ellipsis">…</li>
                            <?php else: $isActive = ($p === $current); ?>
                                <li class="pagination-item">
                                    <a class="pagination-link<?php echo $isActive ? ' is-active' : ''; ?>" href="<?php echo url('admin/genres?page=' . $p . $suffix); ?>" <?php echo $isActive ? 'aria-current="page"' : ''; ?>>
                                        <?php echo $p; ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                    <?php if ($current < $totalPages): ?>
                        <a href="<?php echo url('admin/genres?page=' . ($current + 1) . $suffix); ?>" class="pagination-next">
                            Suivante <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
