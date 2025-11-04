<div class="page-header">
    <div class="container">
        <div class="header-content">
            <div>
                <h1><?php e($title); ?></h1>
                <p class="subtitle">Profil et historique de l'utilisateur</p>
            </div>
            <div class="header-actions">
                <a href="<?php echo url('admin/users'); ?>" class="btn-btn-outline">
                    <i class="fas fa-arrow-left"></i> Retour à la liste
                </a>
                <?php if (!isset($user['role']) || $user['role'] !== 'deleted'): ?>
                    <form method="POST" action="<?php echo url('admin/user/' . $user['id'] . '/delete'); ?>"
                        style="display: inline-block; margin-left: 10px;">
                        <button type="submit" class="btn btn-danger"
                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ? L\'historique des emprunts sera conservé.')"
                            title="Supprimer l'utilisateur">
                            <i class="fas fa-trash"></i> Supprimer
                        </button>
                    </form>
                <?php else: ?>
                    <span class="btn btn-disabled" title="Utilisateur déjà supprimé" style="margin-left: 10px;">
                        <i class="fas fa-ban"></i> Supprimé
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
    <?php require_once __DIR__ . '/../../public/assets/css/user_detail.css'; ?>
</style>

<section class="user-detail">
    <div class="container">

        <!-- Informations utilisateur -->
        <div class="user-profile-card">
            <div class="profile-header">
                <div class="user-avatar-large">
                    <?php echo strtoupper(substr($user['prenom'], 0, 1) . substr($user['nom'], 0, 1)); ?>
                </div>
                <div class="profile-info">
                    <?php if (isset($user['role']) && $user['role'] === 'deleted'): ?>
                        <h2>Utilisateur Supprimé</h2>
                        <p class="user-email text-muted">Compte anonymisé</p>
                        <p class="user-since">
                            <i class="fas fa-calendar"></i>
                            Inscrit le <?php echo format_date($user['created_at'] ?? $user['date_inscription'] ?? '2024-01-01', 'd/m/Y'); ?>
                        </p>
                        <p class="text-muted">Ce compte a été supprimé. Les informations personnelles ne sont plus accessibles.</p>
                    <?php else: ?>
                        <h2><?php e($user['prenom'] . ' ' . $user['nom']); ?></h2>
                        <p class="user-email"><?php e($user['email']); ?></p>
                        <?php if (!empty($user['telephone'])): ?>
                            <p class="user-phone">
                                <i class="fas fa-phone"></i> <?php e($user['telephone']); ?>
                            </p>
                        <?php endif; ?>
                        <p class="user-since">
                            <i class="fas fa-calendar"></i>
                            Inscrit le <?php echo format_date($user['created_at'] ?? $user['date_inscription'] ?? '2024-01-01', 'd/m/Y'); ?>
                        </p>
                    <?php endif; ?>
                </div>
                <div class="profile-status">
                    <?php if ($user['emprunts_en_retard'] > 0): ?>
                        <span class="status-badge status-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            <?php echo $user['emprunts_en_retard']; ?> emprunt<?php echo $user['emprunts_en_retard'] > 1 ? 's' : ''; ?> en retard
                        </span>
                    <?php elseif ($user['emprunts_en_cours'] > 0): ?>
                        <span class="status-badge status-success">
                            <i class="fas fa-check-circle"></i>
                            <?php echo $user['emprunts_en_cours']; ?> emprunt<?php echo $user['emprunts_en_cours'] > 1 ? 's' : ''; ?> actif<?php echo $user['emprunts_en_cours'] > 1 ? 's' : ''; ?>
                        </span>
                    <?php else: ?>
                        <span class="status-badge status-secondary">
                            <i class="fas fa-pause-circle"></i>
                            Aucun emprunt en cours
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Statistiques de l'utilisateur -->
        <div class="user-stats-grid">
            <div class="stat-card stat-primary">
                <div class="stat-icon">
                    <i class="fas fa-list"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($user['total_emprunts']); ?></h3>
                    <p>Total emprunts</p>
                </div>
            </div>

            <div class="stat-card stat-success">
                <div class="stat-icon">
                    <i class="fas fa-hand-paper"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($user['emprunts_en_cours']); ?></h3>
                    <p>En cours</p>
                </div>
            </div>

            <div class="stat-card stat-info">
                <div class="stat-icon">
                    <i class="fas fa-check"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($user['emprunts_rendus']); ?></h3>
                    <p>Rendus</p>
                </div>
            </div>

            <div class="stat-card <?php echo $user['emprunts_en_retard'] > 0 ? 'stat-danger' : 'stat-secondary'; ?>">
                <div class="stat-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($user['emprunts_en_retard']); ?></h3>
                    <p>En retard</p>
                </div>
            </div>
        </div>

        <!-- Emprunts en cours -->
        <?php if (!empty($emprunts_en_cours)): ?>
            <div class="detail-section">
                <div class="section-header">
                    <h2>
                        <i class="fas fa-hand-paper"></i>
                        Emprunts en cours (<?php echo count($emprunts_en_cours); ?>)
                    </h2>
                    <?php if ($user['emprunts_en_retard'] > 0): ?>
                        <form method="POST" action="<?php echo url('admin/user/' . $user['id'] . '/return_all'); ?>" style="display: inline;">
                            <button type="submit" class="btn btn-warning"
                                onclick="return confirm('Forcer le retour de tous les médias en retard ?')"
                                data-confirm="Forcer le retour de tous les médias en retard ?">
                                <i class="fas fa-undo"></i> Forcer retour des retards
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

                <div class="loans-grid">
                    <?php foreach ($emprunts_en_cours as $emprunt): ?>
                        <?php
                        $is_overdue = strtotime($emprunt['date_retour_prevue']) < time() && $emprunt['statut'] === 'En cours';
                        ?>
                        <div class="loan-card <?php echo $is_overdue ? 'loan-overdue' : ''; ?>">
                            <div class="loan-header">
                                <h4><?php e($emprunt['titre']); ?></h4>
                                <span class="media-type type-<?php echo $emprunt['type']; ?>">
                                    <?php echo ucfirst($emprunt['type']); ?>
                                </span>
                            </div>

                            <div class="loan-details">
                                <div class="loan-dates">
                                    <div class="date-item">
                                        <span class="date-label">Emprunté le :</span>
                                        <span class="date-value"><?php echo format_date($emprunt['date_emprunt'], 'd/m/Y'); ?></span>
                                    </div>
                                    <div class="date-item">
                                        <span class="date-label">Retour prévu :</span>
                                        <span class="date-value <?php echo $is_overdue ? 'overdue' : ''; ?>">
                                            <?php echo format_date($emprunt['date_retour_prevue'], 'd/m/Y'); ?>
                                        </span>
                                    </div>
                                    <?php if ($is_overdue): ?>
                                        <div class="date-item overdue-info">
                                            <span class="retard-badge">
                                                <?php
                                                $jours_retard = ceil((time() - strtotime($emprunt['date_retour_prevue'])) / (60 * 60 * 24));
                                                echo $jours_retard; ?> jour<?php echo $jours_retard > 1 ? 's' : ''; ?> de retard
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="loan-actions">
                                    <form method="POST" action="<?php echo url('admin/force_return/' . $emprunt['id']); ?>" style="display: inline;">
                                        <button type="submit" class="btn btn-sm btn-warning"
                                            onclick="return confirm('Forcer le retour de ce média ?')"
                                            data-confirm="Forcer le retour de ce média ?">
                                            <i class="fas fa-undo"></i> Forcer retour
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Historique des emprunts -->
        <div class="detail-section">
            <div class="section-header">
                <h2>
                    <i class="fas fa-history"></i>
                    Historique des emprunts
                </h2>
                <div class="history-filters">
                    <select onchange="filterHistory(this.value)" class="form-select">
                        <option value="all">Tous les emprunts</option>
                        <option value="returned">Emprunts rendus</option>
                        <option value="overdue">Anciens retards</option>
                    </select>
                </div>
            </div>

            <?php if (!empty($historique_emprunts)): ?>
                <div class="history-table-container">
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th>Média</th>
                                <th>Date emprunt</th>
                                <th>Retour prévu</th>
                                <th>Date retour</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($historique_emprunts as $emprunt): ?>
                                <?php
                                // Calcul robuste pour savoir si c'est en retard ou était en retard
                                // Utiliser comparaison sur date (YYYY-MM-DD) pour éviter problèmes d'heure/format
                                $retour_prevue_raw = $emprunt['date_retour_prevue'] ?? null;
                                $retour_reelle_raw = $emprunt['date_retour_reelle'] ?? null;
                                $today_date = date('Y-m-d');

                                $retour_prevue_date = $retour_prevue_raw ? substr($retour_prevue_raw, 0, 10) : null;
                                $retour_reelle_date = $retour_reelle_raw ? substr($retour_reelle_raw, 0, 10) : null;

                                // Déterminer si l'emprunt est en retard actuellement.
                                // Prendre en compte les cas où le statut a déjà été basculé en 'En retard'.
                                $is_currently_overdue = false;
                                if ($retour_prevue_date) {
                                    if ($emprunt['statut'] === 'En retard') {
                                        $is_currently_overdue = true;
                                    } elseif ($emprunt['statut'] === 'En cours') {
                                        $is_currently_overdue = ($retour_prevue_date < $today_date);
                                    }
                                }

                                $was_returned_late = false;
                                if ($retour_reelle_date && $emprunt['statut'] === 'Rendu') {
                                    $was_returned_late = ($retour_reelle_date > $retour_prevue_date);
                                }
                                ?>
                                <tr class="history-row"
                                    data-status="<?php echo strtolower($emprunt['statut']); ?>"
                                    data-overdue="<?php echo ($is_currently_overdue || $was_returned_late) ? '1' : '0'; ?>">
                                    <td>
                                        <div class="media-info">
                                            <strong><?php e($emprunt['titre']); ?></strong>
                                            <span class="media-type type-<?php echo $emprunt['type']; ?>">
                                                <?php echo ucfirst($emprunt['type']); ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td><?php echo format_date($emprunt['date_emprunt'], 'd/m/Y'); ?></td>
                                    <td><?php echo format_date($emprunt['date_retour_prevue'], 'd/m/Y'); ?></td>
                                    <td>
                                        <?php if ($emprunt['date_retour_reelle']): ?>
                                            <?php echo format_date($emprunt['date_retour_reelle'], 'd/m/Y'); ?>
                                        <?php else: ?>
                                            <span class="text-muted">En cours</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($emprunt['statut'] === 'En cours' || $emprunt['statut'] === 'En retard'): ?>
                                            <?php if ($is_currently_overdue): ?>
                                                <span class="status-badge status-danger">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                    <?php
                                                    // Calculer jours de retard sur base de dates (arrondi)
                                                    $jours_retard_emprunt = 0;
                                                    if ($retour_prevue_date) {
                                                        $jours_retard_emprunt = (int) ((strtotime($today_date) - strtotime($retour_prevue_date)) / (60 * 60 * 24));
                                                        if ($jours_retard_emprunt < 1) { $jours_retard_emprunt = 1; }
                                                    }
                                                    ?>
                                                    En retard (<?php echo $jours_retard_emprunt; ?>j)
                                                </span>
                                            <?php else: ?>
                                                <span class="status-badge status-success">
                                                    <i class="fas fa-hand-paper"></i> En cours
                                                </span>
                                            <?php endif; ?>
                                        <?php elseif ($emprunt['statut'] === 'Rendu'): ?>
                                            <?php if ($was_returned_late): ?>
                                                <span class="status-badge status-warning">
                                                    <i class="fas fa-clock"></i> Rendu en retard
                                                </span>
                                            <?php else: ?>
                                                <span class="status-badge status-info">
                                                    <i class="fas fa-check"></i> Rendu à temps
                                                </span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        <?php if (isset($_GET['debug_loans']) && $_GET['debug_loans'] == '1'): ?>
                                            <div style="margin-top:6px; font-size:0.85rem; color:#666;">
                                                DEBUG: statut="<?php echo htmlspecialchars($emprunt['statut'] ?? ''); ?>" | date_retour_prevue="<?php echo htmlspecialchars($retour_prevue_raw ?? ''); ?>" | date_retour_prevue_date="<?php echo htmlspecialchars($retour_prevue_date ?? ''); ?>" | today="<?php echo $today_date; ?>" | is_currently_overdue="<?php echo $is_currently_overdue ? '1' : '0'; ?>" | was_returned_late="<?php echo $was_returned_late ? '1' : '0'; ?>"
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="no-history">
                    <div class="no-data-icon">
                        <i class="fas fa-history"></i>
                    </div>
                    <p>Cet utilisateur n'a encore effectué aucun emprunt.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Actions administrateur -->
        <div class="detail-section">
            <h2>
                <i class="fas fa-cogs"></i>
                Actions administrateur
            </h2>
            <div class="admin-actions">
                <button type="button" class="btn btn-primary"
                    onclick="sendEmail('<?php e($user['email']); ?>', '<?php e($user['prenom'] . ' ' . $user['nom']); ?>')">
                    <i class="fas fa-envelope"></i>
                    Envoyer un email
                </button>

                <?php if ($user['emprunts_en_retard'] > 0): ?>
                    <button type="button" class="btn btn-warning"
                        onclick="sendReminderEmail('<?php e($user['email']); ?>', '<?php e($user['prenom'] . ' ' . $user['nom']); ?>', <?php echo $user['emprunts_en_retard']; ?>)">
                        <i class="fas fa-bell"></i>
                        Rappel retards
                    </button>
                <?php endif; ?>

                <button type="button" class="btn btn-info" onclick="printUserReport(<?php echo $user['id']; ?>)">
                    <i class="fas fa-print"></i>
                    Imprimer le rapport
                </button>

                <a href="<?php echo url('admin/users'); ?>" class="btn-btn-outline">
                    <i class="fas fa-arrow-left"></i>
                    Retour à la liste
                </a>
            </div>
        </div>

    </div>
</section>