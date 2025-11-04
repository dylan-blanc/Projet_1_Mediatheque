<style>
    <?php require_once __DIR__ . '/../../public/assets/css/media.css'; ?>
</style>

<div class="container">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-trash"></i> Supprimer le média</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Attention !</strong> Cette action est irréversible.
                    </div>

                    <div class="media-delete-media-preview">
                        <div class="row">
                            <div class="col-md-3">
                                <?php if (!empty($media['image'])): ?>
                                    <img src="<?php echo url($media['image']); ?>"
                                        alt="<?php echo esc($media['titre']); ?>"
                                        class="img-fluid rounded">
                                <?php else: ?>
                                    <div class="media-delete-no-image">
                                        <i class="fas fa-image fa-3x"></i>
                                        <p>Aucune image</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-9">
                                <h4><?php echo esc($media['titre']); ?></h4>
                                <p class="text-muted">
                                    <strong>Type :</strong>
                                    <span class="badge badge-<?php echo $media['type'] == 'livre' ? 'primary' : ($media['type'] == 'film' ? 'warning' : 'info'); ?>">
                                        <?php echo ucfirst($media['type']); ?>
                                    </span>
                                </p>
                                <p><strong>Genre :</strong> <?php echo esc($media['genres'] ?? 'Non défini'); ?></p>
                                <?php if ($media['type'] === 'livre' && !empty($media['auteur'])): ?>
                                    <p><strong>Auteur :</strong> <?php echo esc($media['auteur']); ?></p>
                                <?php elseif ($media['type'] === 'film' && !empty($media['realisateur'])): ?>
                                    <p><strong>Réalisateur :</strong> <?php echo esc($media['realisateur']); ?></p>
                                <?php elseif ($media['type'] === 'jeu' && !empty($media['developpeur'])): ?>
                                    <p><strong>Développeur :</strong> <?php echo esc($media['developpeur']); ?></p>
                                <?php endif; ?>
                                <p><strong>Stock :</strong> <?php echo $media['stock_disponible']; ?> / <?php echo $media['stock']; ?></p>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <p class="text-danger">
                        <strong>Êtes-vous sûr de vouloir supprimer ce média ?</strong>
                    </p>

                    <p class="text-muted">Cette action supprimera définitivement :</p>
                    <ul class="text-muted">
                        <li>Le média et toutes ses informations</li>
                        <li>L'image associée (si elle existe)</li>
                        <li>L'historique des emprunts pour ce média</li>
                    </ul>

                    <div class="media-delete-form-actions">
                        <form method="POST" action="<?php echo url('media/delete/' . $media['id']); ?>" style="display: inline;">
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash"></i> Oui, supprimer définitivement
                            </button>
                        </form>
                        <a href="<?php echo url('media/detail/' . $media['id']); ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Annuler
                        </a>
                        <a href="<?php echo url('media/library'); ?>" class="btn-btn-outline-secondary">
                            <i class="fas fa-list"></i> Retour au catalogue
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>