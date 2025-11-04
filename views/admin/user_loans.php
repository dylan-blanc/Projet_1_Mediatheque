<link rel="stylesheet" href="<?php echo url('assets/css/user_loans.css'); ?>">

<div class="page-header">
    <div class="container">
        <h1><?php e($title); ?></h1>
        <nav class="breadcrumb">
            <a href="<?php echo url('admin/dashboard'); ?>">Administration</a>
            <span class="breadcrumb-separator">></span>
            <a href="<?php echo url('admin/users'); ?>">Utilisateurs</a>
            <span class="breadcrumb-separator">></span>
            <a href="<?php echo url('admin/user/' . $user['id']); ?>"><?php e($user['prenom'] . ' ' . $user['nom']); ?></a>
            <span class="breadcrumb-separator">></span>
            <span>Emprunts</span>
        </nav>
    </div>
</div>

<section class="user-loans">
    <div class="container">

        <!-- Informations utilisateur -->
        <div class="user-info-card">
            <div class="user-header">
                <h2><?php e($user['prenom'] . ' ' . $user['nom']); ?></h2>
                <p><?php e($user['email']); ?></p>
            </div>
            <div class="user-stats">
                <div class="stat">
                    <span class="number"><?php echo count($emprunts_en_cours); ?></span>
                    <span class="label">Emprunts actifs</span>
                </div>
                <div class="stat">
                    <span class="number"><?php echo count($historique_emprunts); ?></span>
                    <span class="label">Total emprunts</span>
                </div>
            </div>
        </div>

        <!-- Emprunts en cours -->
        <?php if (!empty($emprunts_en_cours)): ?>
            <div class="loans-section">
                <h3>Emprunts en cours</h3>
                <div class="loans-grid">
                    <?php foreach ($emprunts_en_cours as $emprunt): ?>
                        <div class="loan-card current">
                            <div class="media-info">
                                <h4><?php e($emprunt['titre']); ?></h4>
                                <p class="media-type"><?php e(ucfirst($emprunt['type'])); ?></p>
                                <p class="genre"><?php e($emprunt['genres'] ?? 'Non défini'); ?></p>
                            </div>
                            <div class="loan-dates">
                                <p><strong>Emprunté le :</strong> <?php echo format_date($emprunt['date_emprunt'], 'd/m/Y'); ?></p>
                                <p><strong>Retour prévu :</strong> <?php echo format_date($emprunt['date_retour_prevue'], 'd/m/Y'); ?></p>
                                <?php if (strtotime($emprunt['date_retour_prevue']) < time()): ?>
                                    <p class="overdue">⚠️ En retard</p>
                                <?php endif; ?>
                            </div>
                            <div class="loan-actions">
                                <form method="POST" action="<?php echo url('admin/force_return/' . $emprunt['id']); ?>"
                                    onsubmit="return confirm('Forcer le retour de ce média ?')">
                                    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                                    <button type="submit" class="btn btn-warning btn-sm">
                                        <i class="fas fa-undo"></i> Forcer retour
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Historique -->
        <?php if (!empty($historique_emprunts)): ?>
            <div class="loans-section">
                <h3>Historique des emprunts</h3>
                <div class="history-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Média</th>
                                <th>Type</th>
                                <th>Date emprunt</th>
                                <th>Date retour prévue</th>
                                <th>Date retour effective</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($historique_emprunts as $emprunt): ?>
                                <tr>
                                    <td>
                                        <strong><?php e($emprunt['titre']); ?></strong>
                                        <br><small><?php e($emprunt['genres'] ?? 'Non défini'); ?></small>
                                    </td>
                                    <td><?php e(ucfirst($emprunt['type'])); ?></td>
                                    <td><?php echo format_date($emprunt['date_emprunt'], 'd/m/Y'); ?></td>
                                    <td><?php echo format_date($emprunt['date_retour_prevue'], 'd/m/Y'); ?></td>
                                    <td>
                                        <?php if ($emprunt['date_retour_reelle']): ?>
                                            <?php echo format_date($emprunt['date_retour_reelle'], 'd/m/Y'); ?>
                                        <?php else: ?>
                                            <span class="current-loan">En cours</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                            // Préférer le flag calculé en base si disponible (get_user_loans_history fournit is_overdue)
                                            $is_overdue = isset($emprunt['is_overdue']) ? (bool)$emprunt['is_overdue'] : ( (!empty($emprunt['date_retour_prevue']) && strtotime($emprunt['date_retour_prevue']) < time() && $emprunt['statut'] === 'En cours') );

                                            if ($is_overdue) {
                                                $status_display = 'En retard';
                                                $status_slug = 'retard';
                                            } else {
                                                $status_display = $emprunt['statut'];
                                                $status_slug = strtolower(str_replace(' ', '-', $status_display));
                                                if ($status_slug === 'en-retard') { $status_slug = 'retard'; }
                                            }
                                        ?>
                                        <span class="status status-<?php echo $status_slug; ?>">
                                            <?php e($status_display); ?>
                                        </span>
                                            <?php if (isset($_GET['debug_loans']) && $_GET['debug_loans'] == '1'): ?>
                                                <div style="margin-top:6px; font-size:0.85rem; color:#666;">
                                                    DEBUG: statut="<?php echo htmlspecialchars($emprunt['statut'] ?? ''); ?>" | date_retour_prevue="<?php echo htmlspecialchars($emprunt['date_retour_prevue'] ?? ''); ?>" | is_overdue_db="<?php echo isset($emprunt['is_overdue']) ? (int)$emprunt['is_overdue'] : 'N/A'; ?>" | computed_overdue="<?php echo $is_overdue ? '1' : '0'; ?>"
                                                </div>
                                            <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <div class="actions">
            <a href="<?php echo url('admin/user/' . $user['id']); ?>" class="btn-btn-outline">
                <i class="fas fa-arrow-left"></i> Retour au profil
            </a>
            <a href="<?php echo url('admin/users'); ?>" class="btn btn-secondary">
                <i class="fas fa-list"></i> Liste des utilisateurs
            </a>
        </div>

    </div>
</section>