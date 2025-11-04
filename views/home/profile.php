<link rel="stylesheet" href="<?php echo url('assets/css/profile.css'); ?>">

<div class="page-header">
    <div class="container">
        <h1>
            <i class="fas fa-user-circle"></i> <?php e($title); ?>
        </h1>
        <p class="subtitle">Gérez vos emprunts et consultez votre historique</p>
    </div>
</div>

<section class="profile-content">
    <div class="container">
        <!-- Stats utilisateur -->
        <div class="stats-grid">
            <div class="stat-card stat-primary">
                <div class="stat-icon">
                    <i class="fas fa-book-reader"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo count($emprunts_en_cours); ?></h3>
                    <p>Emprunts en cours</p>
                </div>
            </div>
            <div class="stat-card stat-info">
                <div class="stat-icon">
                    <i class="fas fa-history"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo count($historique); ?></h3>
                    <p>Emprunts totaux</p>
                </div>
            </div>
            <div class="stat-card stat-danger">
                <div class="stat-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo isset($overdue_count) ? $overdue_count : 0; ?></h3>
                    <p>Emprunts en retard</p>
                </div>
            </div>
            <div class="stat-card stat-success">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $nb_emprunts_max - count($emprunts_en_cours); ?></h3>
                    <p>Emprunts disponibles</p>
                </div>
            </div>
        </div>

        <div class="profile-grid">

            <!-- Informations utilisateur -->
            <div class="profile-info">
                <h2>Mes informations</h2>
                <div class="user-card">
                    <?php if (isset($user['role']) && $user['role'] === 'deleted'): ?>
                        <p><strong>Prénom :</strong> Utilisateur</p>
                        <p><strong>Nom :</strong> Supprimé</p>
                        <p><strong>Email :</strong> <span class="text-muted">Compte anonymisé</span></p>
                        <p><strong>Membre depuis :</strong> <?php echo format_date($user['created_at'], 'd/m/Y'); ?></p>
                        <p class="text-muted">Ce compte a été supprimé. Les informations personnelles ne sont plus accessibles.</p>
                    <?php else: ?>
                        <p><strong>Prénom :</strong> <?php e($user['prenom']); ?></p>
                        <p><strong>Nom :</strong> <?php e($user['nom']); ?></p>
                        <p><strong>Email :</strong> <?php e($user['email']); ?></p>
                        <p><strong>Membre depuis :</strong> <?php echo format_date($user['created_at'], 'd/m/Y'); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Emprunts en cours -->
            <div class="current-loans">
                <h2>Mes emprunts en cours
                    <span class="loan-counter">(<?php echo count($emprunts_en_cours); ?>/<?php echo $nb_emprunts_max; ?>)</span>
                </h2>

                <?php if (empty($emprunts_en_cours)): ?>
                    <div class="no-loans">
                        <p>Aucun emprunt en cours.</p>
                        <a href="<?php echo url('media/library'); ?>" class="btn btn-primary">
                            <i class="fas fa-search"></i> Découvrir le catalogue
                        </a>
                    </div>
                <?php else: ?>
                    <div class="loans-list">
                        <?php foreach ($emprunts_en_cours as $emprunt): ?>
                            <div class="loan-item">
                                <div class="loan-image">
                                    <?php if ($emprunt['image']): ?>
                                        <img src="<?php echo url($emprunt['image']); ?>" alt="<?php e($emprunt['titre']); ?>">
                                    <?php else: ?>
                                        <div class="no-image">
                                            <i class="fas fa-<?php echo $emprunt['type'] === 'livre' ? 'book' : ($emprunt['type'] === 'film' ? 'film' : 'gamepad'); ?>"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="loan-info">
                                    <h3><?php e($emprunt['titre']); ?></h3>
                                    <p class="media-type"><?php echo ucfirst($emprunt['type']); ?></p>
                                    <p class="loan-dates">
                                        <strong>Emprunté le :</strong> <?php echo format_date($emprunt['date_emprunt'], 'd/m/Y'); ?><br>
                                        <strong>À rendre le :</strong>
                                        <span class="<?php echo (strtotime($emprunt['date_retour_prevue']) < time()) ? 'overdue' : 'due-date'; ?>">
                                            <?php echo format_date($emprunt['date_retour_prevue'], 'd/m/Y'); ?>
                                        </span>
                                        <?php if (strtotime($emprunt['date_retour_prevue']) < time()): ?>
                                            <span class="overdue-warning">(En retard !)</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Historique des emprunts -->
            <div class="loan-history">
                <h2>Historique de mes emprunts</h2>

                <?php if (empty($historique)): ?>
                    <p>Aucun emprunt dans l'historique.</p>
                <?php else: ?>
                    <div class="history-list">
                        <?php foreach ($historique as $emprunt): ?>
                            <div class="history-item">
                                <div class="history-info">
                                    <h4><?php e($emprunt['titre']); ?></h4>
                                    <p class="history-dates">
                                        Emprunté le <?php echo format_date($emprunt['date_emprunt'], 'd/m/Y'); ?>
                                        <?php if ($emprunt['date_retour_reelle']): ?>
                                            - Rendu le <?php echo format_date($emprunt['date_retour_reelle'], 'd/m/Y'); ?>
                                        <?php else: ?>
                                            - <strong>En cours</strong>
                                        <?php endif; ?>
                                    </p>
                                    <?php
                                        // Préférer le flag calculé en base si disponible (get_user_loans_history fournit is_overdue)
                                        $is_overdue = isset($emprunt['is_overdue']) ? (bool)$emprunt['is_overdue'] : ( (!empty($emprunt['date_retour_prevue']) && strtotime($emprunt['date_retour_prevue']) < time() && $emprunt['statut'] === 'En cours') );

                                        if ($is_overdue) {
                                            $status_display = 'En retard';
                                            $status_slug = 'retard';
                                        } else {
                                            // Normaliser la classe de statut (remplacer espaces par tirets)
                                            $status_display = $emprunt['statut'];
                                            $status_slug = strtolower(str_replace(' ', '-', $status_display));
                                            if ($status_slug === 'en-retard') { $status_slug = 'retard'; }
                                        }
                                    ?>
                                    <span class="status-badge status-<?php echo $status_slug; ?>">
                                        <?php e($status_display); ?>
                                    </span>
                                    <?php if (isset($_GET['debug_loans']) && $_GET['debug_loans'] == '1'): ?>
                                        <div class="debug-info" style="margin-top:6px; font-size:0.85rem; color:#666;">
                                            DEBUG: statut="<?php echo htmlspecialchars($emprunt['statut'] ?? ''); ?>" | date_retour_prevue="<?php echo htmlspecialchars($emprunt['date_retour_prevue'] ?? ''); ?>" | is_overdue_db="<?php echo isset($emprunt['is_overdue']) ? (int)$emprunt['is_overdue'] : 'N/A'; ?>" | computed_overdue="<?php echo $is_overdue ? '1' : '0'; ?>"
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="show-more">
                        <p><em>Affichage des <?php echo count($historique); ?> derniers emprunts sur <?php echo $total_history; ?> au total</em></p>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_history_pages > 1): ?>
                        <div class="pagination" style="display: flex; justify-content: space-between; align-items: center; padding: 2rem 0; margin-top: 2rem; border-top: 2px solid #e9ecef;">
                            <?php if ($history_page > 1): ?>
                                <a href="<?php echo url('profile?history_page=' . ($history_page - 1)); ?>" 
                                   class="btn btn-sm btn-secondary">
                                    <i class="fas fa-chevron-left"></i> Précédent
                                </a>
                            <?php else: ?>
                                <span></span>
                            <?php endif; ?>
                            
                            <span class="page-info" style="color: #555; font-weight: 500; font-size: 0.95rem;">
                                Page <?php echo $history_page; ?> sur <?php echo $total_history_pages; ?>
                            </span>
                            
                            <?php if ($history_page < $total_history_pages): ?>
                                <a href="<?php echo url('profile?history_page=' . ($history_page + 1)); ?>" 
                                   class="btn btn-sm btn-secondary">
                                    Suivant <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php else: ?>
                                <span></span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

        </div>
    </div>
</section>

<style>
    <?php require_once __DIR__ . '/../../public/assets/css/profile.css'; ?>
</style>