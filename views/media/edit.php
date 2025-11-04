<link rel="stylesheet" href="<?php echo url('assets/css/admin_forms.css'); ?>">

<div class="page-header">
    <div class="container">
        <h1><?php e($title); ?></h1>
        <nav class="breadcrumb">
            <a href="<?php echo url('media/library'); ?>">Catalogue</a>
            <span class="breadcrumb-separator">></span>
            <a href="<?php echo url('media/detail/' . $media['id']); ?>"><?php e($media['titre']); ?></a>
            <span class="breadcrumb-separator">></span>
            <span>Modifier</span>
        </nav>
    </div>
</div>

<section class="admin-content">
    <div class="admin-form-container">
        <div class="admin-form-card">
            <form method="POST" action="<?php echo url('media/edit/' . $media['id']); ?>" enctype="multipart/form-data" class="media-form">
                <!-- Informations de base -->
                <div class="admin-form-section">
                    <h2>Informations générales</h2>
                    <div class="admin-form-row">
                        <div class="admin-form-group">
                            <label for="titre">Titre *</label>
                            <input type="text" id="titre" name="titre" value="<?php echo escape($media['titre']); ?>" required maxlength="200">
                        </div>
                        <div class="admin-form-group">
                            <label for="type">Type *</label>
                            <select id="type" name="type" required onchange="showTypeFields(this.value)">
                                <option value="">Sélectionner un type</option>
                                <option value="livre" <?php echo $media['type'] === 'livre' ? 'selected' : ''; ?>>Livre</option>
                                <option value="film" <?php echo $media['type'] === 'film' ? 'selected' : ''; ?>>Film</option>
                                <option value="jeu" <?php echo $media['type'] === 'jeu' ? 'selected' : ''; ?>>Jeu vidéo</option>
                            </select>
                        </div>
                    </div>
                    <div class="admin-form-row">
                        <div class="admin-form-group full-width">
                            <label>Genres * <small>(Sélectionnez 1 à 5 genres)</small></label>
                            <div id="genres-checkboxes-edit" class="genres-checkbox-grid">
                                <p class="text-muted">Chargement des genres...</p>
                            </div>
                            <input type="hidden" id="genres-validation-edit" required>
                        </div>
                    </div>
                    <div class="admin-form-row">
                        <div class="admin-form-group">
                            <label for="stock">Stock *</label>
                            <input type="number" id="stock" name="stock" value="<?php echo $media['stock']; ?>" min="1" required>
                        </div>
                    </div>
                    <div class="admin-form-group">
                        <label for="image">Image de couverture</label>
                        <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.gif">
                        <small>Formats acceptés: JPG, PNG, GIF. Taille max: 2Mo. Laisser vide pour conserver l'image actuelle.</small>

                        <?php if ($media['image']): ?>
                            <div class="current-image-preview">
                                <p>Image actuelle :</p>
                                <img src="<?php echo url($media['image']); ?>" alt="Image actuelle" style="max-width: 200px; max-height: 200px; border-radius: 8px; margin: 10px 0;">
                                <div class="image-actions">
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="delete_image" value="1" id="delete_image">
                                        <span class="checkbox-text">Supprimer l'image actuelle et utiliser l'image par défaut</span>
                                    </label>
                                </div>
                                <p><small class="text-info">
                                        <i class="fas fa-info-circle"></i>
                                        Si vous cochez cette case, l'image actuelle sera supprimée et remplacée par l'image par défaut du type de média.
                                    </small></p>
                            </div>
                        <?php else: ?>
                            <div class="no-image-notice">
                                <p><small class="text-muted">Aucune image définie. L'image par défaut sera utilisée.</small></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- Livres -->
                <div id="livre-fields" class="admin-form-section type-fields" style="display: <?php echo $media['type'] === 'livre' ? 'block' : 'none'; ?>;">
                    <h2>Informations spécifiques - Livre</h2>
                    <div class="admin-form-row">
                        <div class="admin-form-group">
                            <label for="auteur">Auteur *</label>
                            <input type="text" id="auteur" name="auteur" value="<?php echo escape($media['auteur'] ?? ''); ?>" maxlength="100">
                        </div>
                        <div class="admin-form-group">
                            <label for="isbn">ISBN</label>
                            <input type="text" id="isbn" name="isbn" value="<?php echo escape($media['isbn'] ?? ''); ?>" maxlength="20" placeholder="Ex: 978-2-123456-78-9">
                        </div>
                    </div>
                    <div class="admin-form-row">
                        <div class="admin-form-group">
                            <label for="nombre_pages">Nombre de pages</label>
                            <input type="number" id="nombre_pages" name="nombre_pages" value="<?php echo $media['nombre_pages'] ?? ''; ?>" min="1" max="9999">
                        </div>
                        <div class="admin-form-group">
                            <label for="annee_publication">Année de publication</label>
                            <input type="number" id="annee_publication" name="annee_publication" value="<?php echo $media['annee_publication'] ?? ''; ?>" min="1900" max="<?php echo date('Y'); ?>">
                        </div>
                    </div>
                    <div class="admin-form-group">
                        <label for="resume">Résumé</label>
                        <textarea id="resume" name="resume" rows="4"><?php echo escape($media['resume'] ?? ''); ?></textarea>
                    </div>
                </div>
                <!-- Films -->
                <div id="film-fields" class="admin-form-section type-fields" style="display: <?php echo $media['type'] === 'film' ? 'block' : 'none'; ?>;">
                    <h2>Informations spécifiques - Film</h2>
                    <div class="admin-form-row">
                        <div class="admin-form-group">
                            <label for="realisateur">Réalisateur *</label>
                            <input type="text" id="realisateur" name="realisateur" value="<?php echo escape($media['realisateur'] ?? ''); ?>" maxlength="100">
                        </div>
                        <div class="admin-form-group">
                            <label for="duree_minutes">Durée (minutes)</label>
                            <input type="number" id="duree_minutes" name="duree_minutes" value="<?php echo $media['duree_minutes'] ?? ''; ?>" min="1" max="999">
                        </div>
                    </div>
                    <div class="admin-form-row">
                        <div class="admin-form-group">
                            <label for="annee_film">Année</label>
                            <input type="number" id="annee_film" name="annee_film" value="<?php echo $media['annee_film'] ?? ''; ?>" min="1900" max="<?php echo date('Y'); ?>">
                        </div>
                        <div class="admin-form-group">
                            <label for="classification">Classification *</label>
                            <select id="classification" name="classification">
                                <option value="">Sélectionner</option>
                                <option value="Tous publics" <?php echo ($media['classification'] ?? '') === 'Tous publics' ? 'selected' : ''; ?>>Tous publics</option>
                                <option value="-12" <?php echo ($media['classification'] ?? '') === '-12' ? 'selected' : ''; ?>>-12</option>
                                <option value="-16" <?php echo ($media['classification'] ?? '') === '-16' ? 'selected' : ''; ?>>-16</option>
                                <option value="-18" <?php echo ($media['classification'] ?? '') === '-18' ? 'selected' : ''; ?>>-18</option>
                            </select>
                        </div>
                    </div>
                    <div class="admin-form-group">
                        <label for="synopsis">Synopsis</label>
                        <textarea id="synopsis" name="synopsis" rows="4"><?php echo escape($media['synopsis'] ?? ''); ?></textarea>
                    </div>
                </div>
                <!-- Jeux -->
                <div id="jeu-fields" class="admin-form-section type-fields" style="display: <?php echo $media['type'] === 'jeu' ? 'block' : 'none'; ?>;">
                    <h2>Informations spécifiques - Jeu vidéo</h2>
                    <div class="admin-form-row">
                        <div class="admin-form-group">
                            <label for="editeur">Éditeur *</label>
                            <input type="text" id="editeur" name="editeur" value="<?php echo escape($media['editeur'] ?? ''); ?>" maxlength="100">
                        </div>
                        <div class="admin-form-group">
                            <label for="plateforme">Plateforme *</label>
                            <select id="plateforme" name="plateforme">
                                <option value="">Sélectionner</option>
                                <option value="PC" <?php echo ($media['plateforme'] ?? '') === 'PC' ? 'selected' : ''; ?>>PC</option>
                                <option value="PlayStation" <?php echo ($media['plateforme'] ?? '') === 'PlayStation' ? 'selected' : ''; ?>>PlayStation</option>
                                <option value="Xbox" <?php echo ($media['plateforme'] ?? '') === 'Xbox' ? 'selected' : ''; ?>>Xbox</option>
                                <option value="Nintendo" <?php echo ($media['plateforme'] ?? '') === 'Nintendo' ? 'selected' : ''; ?>>Nintendo</option>
                                <option value="Mobile" <?php echo ($media['plateforme'] ?? '') === 'Mobile' ? 'selected' : ''; ?>>Mobile</option>
                            </select>
                        </div>
                    </div>
                    <div class="admin-form-row">
                        <div class="admin-form-group">
                            <label for="age_minimum">Âge minimum *</label>
                            <select id="age_minimum" name="age_minimum">
                                <option value="">Sélectionner</option>
                                <option value="3" <?php echo ($media['age_minimum'] ?? '') === '3' ? 'selected' : ''; ?>>3+</option>
                                <option value="7" <?php echo ($media['age_minimum'] ?? '') === '7' ? 'selected' : ''; ?>>7+</option>
                                <option value="12" <?php echo ($media['age_minimum'] ?? '') === '12' ? 'selected' : ''; ?>>12+</option>
                                <option value="16" <?php echo ($media['age_minimum'] ?? '') === '16' ? 'selected' : ''; ?>>16+</option>
                                <option value="18" <?php echo ($media['age_minimum'] ?? '') === '18' ? 'selected' : ''; ?>>18+</option>
                            </select>
                        </div>
                    </div>
                    <div class="admin-form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4"><?php echo escape($media['description'] ?? ''); ?></textarea>
                    </div>
                </div>
                <!-- Actions -->
                <div class="admin-form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Enregistrer les modifications
                    </button>
                    <a href="<?php echo url('media/detail/' . $media['id']); ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Données pour le JavaScript (pattern MVC propre) -->
<div id="genre-data-edit" style="display:none" 
     data-genres='<?php echo json_encode(array_column($genres_livre ?? [], 'nom')); ?>'
     data-genre-ids='<?php echo json_encode(array_column($genres_livre ?? [], 'id')); ?>'
     data-current-type='<?php echo $media['type'] ?? ''; ?>'
     data-current-genre-ids='<?php 
        // Récupérer les genre_ids actuels du média
        $current_genre_ids = [];
        for ($i = 1; $i <= 5; $i++) {
            if (!empty($media["genre_id_$i"])) {
                $current_genre_ids[] = (int)$media["genre_id_$i"];
            }
        }
        echo json_encode($current_genre_ids);
     ?>'></div>


