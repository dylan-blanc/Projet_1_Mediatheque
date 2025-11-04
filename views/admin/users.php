<link rel="stylesheet" href="<?php echo url('assets/css/dashboard.css'); ?>">
<link rel="stylesheet" href="<?php echo url('assets/css/users.css'); ?>">

<div class="page-header">
    <div class="container">
        <h1><?php e($title); ?></h1>
        <p class="subtitle">Gestion des utilisateurs de la médiathèque</p>
    </div>
</div>

<section class="admin-users">
    <div class="container">

        <!-- Statistiques des utilisateurs -->
        <div class="stats-grid">
            <div class="stat-card stat-info">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['total_users']); ?></h3>
                    <p>Utilisateurs actifs</p>
                </div>
            </div>
            <div class="stat-card stat-danger">
                <div class="stat-icon">
                    <i class="fas fa-trash"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['deleted_users']); ?></h3>
                    <p>Utilisateurs supprimés</p>
                </div>
            </div>
            <div class="stat-card stat-success">
                <div class="stat-icon">
                    <i class="fas fa-book-reader"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['users_with_loans']); ?></h3>
                    <p>Avec emprunts actifs</p>
                </div>
            </div>
            <div class="stat-card stat-warning">
                <div class="stat-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['users_with_overdue']); ?></h3>
                    <p>Avec retards</p>
                </div>
            </div>
        </div>

        <!-- Filtres et recherche -->
        <div class="users-controls">
            <form method="GET" action="<?php echo url('admin/users'); ?>" class="search-form">
                <div class="search-group">
                    <input type="text" name="search" value="<?php e($filters['search'] ?? ''); ?>"
                        placeholder="Rechercher par nom, prénom ou email..." class="search-input">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Rechercher
                    </button>
                </div>

                <div class="filters-row">
                    <select name="status" class="form-select">
                        <option value="all" <?php echo ($filters['status'] ?? 'all') === 'all' ? 'selected' : ''; ?>>
                            Tous les utilisateurs
                        </option>
                        <option value="active" <?php echo ($filters['status'] ?? '') === 'active' ? 'selected' : ''; ?>>
                            Actifs uniquement (avec emprunts)
                        </option>
                        <option value="inactive" <?php echo ($filters['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>
                            Inactifs uniquement (sans emprunts)
                        </option>
                        <option value="deleted" <?php echo ($filters['status'] ?? '') === 'deleted' ? 'selected' : ''; ?>>
                            Supprimés uniquement
                        </option>
                    </select>

                    <select name="sort" class="form-select">
                        <option value="nom" <?php echo ($filters['sort'] ?? 'nom') === 'nom' ? 'selected' : ''; ?>>
                            Trier par nom
                        </option>
                        <option value="email" <?php echo ($filters['sort'] ?? '') === 'email' ? 'selected' : ''; ?>>
                            Trier par email
                        </option>
                        <option value="inscription" <?php echo ($filters['sort'] ?? '') === 'inscription' ? 'selected' : ''; ?>>
                            Date d'inscription
                        </option>
                        <option value="activite" <?php echo ($filters['sort'] ?? '') === 'activite' ? 'selected' : ''; ?>>
                            Activité récente
                        </option>
                    </select>

                    <?php if (!empty($filters['search']) || !empty($filters['status']) || (!empty($filters['sort']) && $filters['sort'] !== 'nom')): ?>
                        <a href="<?php echo url('admin/users'); ?>" class="btn-btn-outline">
                            <i class="fas fa-times"></i> Reset
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Liste des utilisateurs -->
        <div class="users-table-container">
            <?php if (!empty($users)): ?>
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>Utilisateur</th>
                            <th>Contact</th>
                            <th>Emprunts</th>
                            <th>Statut</th>
                            <th>Inscription</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr class="user-row <?php echo isset($user['role']) && $user['role'] === 'deleted' ? 'user-deleted' : ''; ?> <?php echo ($user['emprunts_en_retard'] ?? 0) > 0 ? 'user-overdue' : ''; ?>">
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar <?php echo isset($user['role']) && $user['role'] === 'deleted' ? 'avatar-deleted' : ''; ?>">
                                            <?php echo strtoupper(substr($user['prenom'], 0, 1) . substr($user['nom'], 0, 1)); ?>
                                        </div>
                                        <div class="user-details">
                                            <strong><?php e($user['prenom'] . ' ' . $user['nom']); ?></strong>
                                            <small>ID: <?php echo $user['id']; ?>
                                                <?php if (isset($user['role']) && $user['role'] === 'deleted'): ?>
                                                    <span class="deleted-badge">SUPPRIMÉ</span>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="contact-info">
                                        <?php if (isset($user['role']) && $user['role'] === 'deleted'): ?>
                                            <div class="deleted-info">Compte anonymisé</div>
                                            <small>Supprimé le <?php echo isset($user['deleted_at']) ? format_date($user['deleted_at'], 'd/m/Y') : 'Date inconnue'; ?></small>
                                        <?php else: ?>
                                            <div><?php e($user['email']); ?></div>
                                            <?php if (!empty($user['telephone'])): ?>
                                                <small><?php e($user['telephone']); ?></small>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="loans-summary">
                                        <?php if (isset($user['role']) && $user['role'] === 'deleted'): ?>
                                            <span class="loan-badge deleted">Compte supprimé</span>
                                            <small>Historique conservé</small>
                                        <?php else: ?>
                                            <?php if (($user['emprunts_en_cours'] ?? 0) > 0): ?>
                                                <span class="loan-badge active">
                                                    <?php echo $user['emprunts_en_cours']; ?> en cours
                                                </span>
                                            <?php endif; ?>

                                            <?php if (($user['emprunts_en_retard'] ?? 0) > 0): ?>
                                                <span class="loan-badge overdue">
                                                    <?php echo $user['emprunts_en_retard']; ?> en retard
                                                </span>
                                            <?php endif; ?>

                                            <?php if (($user['emprunts_en_cours'] ?? 0) == 0 && ($user['emprunts_en_retard'] ?? 0) == 0): ?>
                                                <span class="loan-badge inactive">Aucun emprunt</span>
                                            <?php endif; ?>

                                            <small>Total: <?php echo $user['total_emprunts'] ?? 0; ?></small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if (isset($user['role']) && $user['role'] === 'deleted'): ?>
                                        <span class="status-badge status-deleted">
                                            <i class="fas fa-trash"></i> Supprimé
                                        </span>
                                    <?php elseif (($user['emprunts_en_retard'] ?? 0) > 0): ?>
                                        <span class="status-badge status-warning">
                                            <i class="fas fa-exclamation-triangle"></i> En retard
                                        </span>
                                    <?php elseif (($user['emprunts_en_cours'] ?? 0) > 0): ?>
                                        <span class="status-badge status-success">
                                            <i class="fas fa-check-circle"></i> Actif
                                        </span>
                                    <?php else: ?>
                                        <span class="status-badge status-secondary">
                                            <i class="fas fa-pause-circle"></i> Inactif
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="date-inscription">
                                        <?php echo format_date($user['created_at'] ?? $user['date_inscription'] ?? date('Y-m-d'), 'd/m/Y'); ?>
                                    </span>
                                    <small>
                                        <?php echo time_ago($user['created_at'] ?? $user['date_inscription'] ?? date('Y-m-d')); ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="user-actions">
                                        <?php if (isset($user['role']) && $user['role'] === 'deleted'): ?>
                                            <a href="<?php echo url('admin/user/' . $user['id']); ?>"
                                                class="btn btn-sm btn-primary" title="Voir le profil (historique)">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo url('admin/user/' . $user['id'] . '/loans'); ?>"
                                                class="btn btn-sm btn-success" title="Voir les emprunts">
                                                <i class="fas fa-book"></i>
                                            </a>
                                            <span class="btn btn-sm btn-disabled" title="Utilisateur supprimé">
                                                <i class="fas fa-ban"></i>
                                            </span>
                                        <?php else: ?>
                                            <a href="<?php echo url('admin/user/' . $user['id']); ?>"
                                                class="btn btn-sm btn-primary" title="Voir détail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="mailto:<?php e($user['email']); ?>"
                                                class="btn btn-sm btn-info" title="Envoyer un mail">
                                                <i class="fas fa-envelope"></i>
                                            </a>
                                            <a href="<?php echo url('admin/user/' . $user['id'] . '/loans'); ?>"
                                                class="btn btn-sm btn-success" title="Gérer les emprunts">
                                                <i class="fas fa-book"></i>
                                            </a>

                                            <form method="POST" action="<?php echo url('admin/user/' . $user['id'] . '/delete'); ?>"
                                                style="display: inline;">
                                                <button type="submit" class="btn btn-sm btn-danger"
                                                    title="Supprimer l'utilisateur"
                                                    onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ? L\'historique des emprunts sera conservé.')"
                                                    data-confirm="Supprimer l'utilisateur ?">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
                    <div class="pagination-container">
                        <div class="pagination-info">
                            Utilisateurs <?php echo $pagination['start']; ?> à <?php echo $pagination['end']; ?>
                            sur <?php echo number_format($pagination['total']); ?>
                        </div>
                        <div class="pagination">
                            <?php if ($pagination['current_page'] > 1): ?>
                                <a href="<?php echo url('admin/users?' . http_build_query(array_merge($filters, ['page' => $pagination['current_page'] - 1]))); ?>"
                                    class="page-link">
                                    <i class="fas fa-chevron-left"></i> Précédent
                                </a>
                            <?php endif; ?>

                            <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++): ?>
                                <?php if ($i == $pagination['current_page']): ?>
                                    <span class="page-link active"><?php echo $i; ?></span>
                                <?php else: ?>
                                    <a href="<?php echo url('admin/users?' . http_build_query(array_merge($filters, ['page' => $i]))); ?>"
                                        class="page-link"><?php echo $i; ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                <a href="<?php echo url('admin/users?' . http_build_query(array_merge($filters, ['page' => $pagination['current_page'] + 1]))); ?>"
                                    class="page-link">
                                    Suivant <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="no-users">
                    <div class="no-data-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Aucun utilisateur trouvé</h3>
                    <p>
                        <?php if (!empty($filters['search'])): ?>
                            Aucun utilisateur ne correspond à votre recherche.
                        <?php else: ?>
                            Il n'y a aucun utilisateur inscrit dans la médiathèque.
                        <?php endif; ?>
                    </p>
                    <?php if (!empty($filters['search'])): ?>
                        <a href="<?php echo url('admin/users'); ?>" class="btn-btn-outline">
                            Voir tous les utilisateurs
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>
</section>