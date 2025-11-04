<link rel="stylesheet" href="<?php echo url('assets/css/library.css'); ?>">

<div class="media-library">
    <div class="library-header">
        <h1><?php e($title); ?></h1>
        <p>Découvrez notre collection de <?php echo $total_medias ?? 0; ?> médias</p>
        <?php if (is_admin()): ?>
            <div class="media-library-admin-actions">
                <a href="<?php echo url('media/add'); ?>" class="buton-add">
                    <i class="fas fa-plus"></i> Ajouter un média
                </a>
                <a href="<?php echo url('admin/loans'); ?>" class="buton-gestion-emprunts">
                    <i class="fas fa-list"></i> Gestion des emprunts
                </a>
            </div>
        <?php endif; ?>
    </div>
    <!-- Filtres -->
    <div class="media-library-filters-section">
        <form method="GET" class="filters-form">
            <div class="media-library-filters-row">
                <div class="media-library-filter-group">
                    <label for="search">Recherche</label>
                    <input type="text" id="search" name="search"
                        value="<?php echo escape($filters['search'] ?? ''); ?>"
                        placeholder="Titre du média...">
                </div>
                <div class="media-library-filter-group">
                    <label for="type">Type</label>
                    <select id="type" name="type">
                        <option value="allgenres" <?php echo ($filters['type'] ?? '') === 'allgenres' ? 'selected' : ''; ?>>Tous les types</option>
                        <option value="livre" <?php echo ($filters['type'] ?? '') === 'livre' ? 'selected' : ''; ?>>Livres</option>
                        <option value="film" <?php echo ($filters['type'] ?? '') === 'film' ? 'selected' : ''; ?>>Films</option>
                        <option value="jeu" <?php echo ($filters['type'] ?? '') === 'jeu' ? 'selected' : ''; ?>>Jeux vidéo</option>
                    </select>
                </div>
                <div class="media-library-filter-group">
                    <label for="genre">Genre</label>
                    <select id="genre" name="genre">
                        <option value="">Tous les genres</option>
                        <?php
                        // Afficher tous les genres disponibles
                        $current_genre = isset($filters['genre']) ? $filters['genre'] : '';
                        $all_genres = isset($genres_livre) ? $genres_livre : [];
                        
                        foreach ($all_genres as $genre):
                            $selected = ($current_genre == $genre['id']) ? 'selected' : '';
                        ?>
                            <option value="<?php echo $genre['id']; ?>" <?php echo $selected; ?>><?php e($genre['nom']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="media-library-filter-group">
                    <label for="disponible">Disponibilité</label>
                    <select id="disponible" name="disponible">
                        <option value="">Tous</option>
                        <option value="oui" <?php echo ($filters['disponible'] ?? '') === 'oui' ? 'selected' : ''; ?>>Disponible</option>
                        <option value="non" <?php echo ($filters['disponible'] ?? '') === 'non' ? 'selected' : ''; ?>>Emprunté</option>
                    </select>
                </div>     
                <div class="filter-actions">
                    <button type="submit" class="buton-filter">
                        <i class="fas fa-search"></i> Filtrer
                    </button>
                    <a href="<?php echo url('media/library'); ?>" class="buton-reset">
                        <i class="fas fa-times"></i> Réinitialiser
                    </a>
                </div>
            </div>
        </form>
    </div>
    <!-- Grille -->
    <div class="media-library-media-grid">
        <?php if (empty($medias)): ?>
            <div class="media-library-no-results">
                <i class="fas fa-search"></i>
                <h3>Aucun média trouvé</h3>
                <p>Essayez de modifier vos critères de recherche.</p>
            </div>
        <?php else: ?>
            <?php foreach ($medias as $media): ?>
                <div class="media-library-media-card">
                    <div class="media-library-media-image">
                        <img src="<?php echo url(get_media_image($media)); ?>" alt="<?php e($media['titre']); ?>" loading="lazy">
                        <div class="media-library-media-overlay">
                            <div class="media-type">
                                <span class="media-library-type-badge media-library-type-<?php echo $media['type']; ?>">
                                    <?php echo ucfirst($media['type']); ?>
                                </span>
                            </div>
                            <?php if ($media['stock_disponible'] > 0): ?>
                                <div class="media-library-availability media-library-available">
                                    <i class="fas fa-check-circle"></i>
                                    <span>Disponible</span>
                                </div>
                            <?php else: ?>
                                <div class="media-library-availability media-library-unavailable">
                                    <i class="fas fa-times-circle"></i>
                                    <span>Emprunté</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="media-library-media-info">
                        <h3 class="media-library-media-title">
                            <a href="<?php echo url('media/detail/' . $media['id']); ?>">
                                <?php e($media['titre']); ?>
                            </a>
                        </h3>
                        <div class="media-library-media-meta">
                            <?php if ($media['type'] === 'livre' && $media['auteur']): ?>
                                <p class="media-library-author">Par <?php e($media['auteur']); ?></p>
                            <?php elseif ($media['type'] === 'film' && $media['realisateur']): ?>
                                <p class="media-library-director">Réalisé par <?php e($media['realisateur']); ?></p>
                            <?php elseif ($media['type'] === 'jeu' && $media['editeur']): ?>
                                <p class="media-library-publisher">Édité par <?php e($media['editeur']); ?></p>
                            <?php endif; ?>

                            <?php
                            // Support both array of genres or comma-separated string
                            $genres_list = [];
                            if (is_array($media['genres'])) {
                                $genres_list = $media['genres'];
                            } elseif (is_string($media['genres'])) {
                                // split by comma and trim
                                $parts = explode(',', $media['genres']);
                                foreach ($parts as $p) {
                                    $trim = trim($p);
                                    if ($trim !== '') $genres_list[] = $trim;
                                }
                            }
                            ?>
                            <div class="media-library-genre-list">
                                <?php foreach ($genres_list as $g): ?>
                                    <span class="media-library-genre-item"><?php e($g); ?></span>
                                <?php endforeach; ?>
                            </div>
                            <p> 
                            <a class="buton-look-detail" href="<?php echo url('media/detail/' . $media['id']); ?>"><i class="fas fa-eye"></i> 
                                Voir détails
                            </a>
                        </p>
                        </div>
                        <div class="media-library-media-actions">
                            <?php if (is_admin()): ?>
                                <button type="submit" class="buton-mod">
                                <a href="<?php echo url('media/edit/' . $media['id']); ?>">Modifier
                                </a>
                                </button>
                                <form method="POST" action="<?php echo url('media/delete/' . $media['id']); ?>" style="display: inline;">
                                    <button type="submit" class="buton-del"
                                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce média ? Cette action est irréversible.')">Supprimer
                                    </button>
                                </form>
                            <?php elseif (is_logged_in() && $media['stock_disponible'] > 0): ?>
                                <form method="POST" action="<?php echo url('media/borrow/' . $media['id']); ?>" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                                    <button type="submit" class="buton-borrow"
                                        onclick="return confirm('Voulez-vous emprunter ce média ?')"> Emprunter
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <!-- Pagination -->
    <?php if (($total_pages ?? 0) > 1): ?>
        <?php
            $totalPages = (int)($total_pages ?? 0);
            $current = (int)($current_page ?? 1);
            $qs = http_build_query($filters ?? []);
            $suffix = $qs ? ('&' . $qs) : '';

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
        <div class="media-library-pagination">
            <?php if ($current > 1): ?>
                <a href="<?php echo url('media/library?page=' . ($current - 1) . $suffix); ?>" class="pagination-prev">
                    <i class="fas fa-chevron-left"></i> Précédente
                </a>
            <?php endif; ?>
            <ul class="pagination-list">
                <?php foreach ($pages as $p): ?>
                    <?php if ($p === '…'): ?>
                        <li class="pagination-ellipsis">…</li>
                    <?php else: $isActive = ($p === $current); ?>
                        <li class="pagination-item">
                            <a class="pagination-link<?php echo $isActive ? ' is-active' : ''; ?>" href="<?php echo url('media/library?page=' . $p . $suffix); ?>" <?php echo $isActive ? 'aria-current="page"' : ''; ?>>
                                <?php echo $p; ?>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
            <?php if ($current < $totalPages): ?>
                <a href="<?php echo url('media/library?page=' . ($current + 1) . $suffix); ?>" class="pagination-next">
                    Suivante <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>