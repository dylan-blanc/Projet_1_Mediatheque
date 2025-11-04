<link rel="stylesheet" href="<?php echo url('assets/css/admin_forms.css'); ?>">

<div class="page-header">
    <div class="container">
        <h1><?php e($title); ?></h1>
        <nav class="breadcrumb">
            <a href="<?php echo url('media/library'); ?>">Catalogue</a>
            <span class="breadcrumb-separator">></span>
            <span>Ajouter un média</span>
        </nav>
    </div>
</div>

<section class="admin-content">
    <div class="admin-form-container">
        <div class="admin-form-card">
            <form method="POST" action="<?php echo url('media/add'); ?>" enctype="multipart/form-data" class="media-form">
                <!-- Informations de base -->
                <div class="admin-form-section">
                    <h2>Informations générales</h2>
                    <div class="admin-form-row">
                        <div class="admin-form-group">
                            <label for="titre">Titre *</label>
                            <input type="text" id="titre" name="titre" value="<?php echo old('titre'); ?>" required maxlength="200">
                        </div>
                        <div class="admin-form-group">
                            <label for="type">Type *</label>
                            <select id="type" name="type" required onchange="showTypeFields(this.value)">
                                <option value="">Sélectionner un type</option>
                                <option value="livre" <?php echo old('type') === 'livre' ? 'selected' : ''; ?>>Livre</option>
                                <option value="film" <?php echo old('type') === 'film' ? 'selected' : ''; ?>>Film</option>
                                <option value="jeu" <?php echo old('type') === 'jeu' ? 'selected' : ''; ?>>Jeu vidéo</option>
                            </select>
                        </div>
                    </div>
                    <div class="admin-form-row">
                        <div class="admin-form-group full-width">
                            <label>Genres * <small>(Sélectionnez un ou plusieurs genres)</small></label>
                            <div id="genres-checkboxes" class="genres-checkbox-grid">
                                <p class="text-muted">Veuillez d'abord sélectionner un type de média</p>
                            </div>
                            <input type="hidden" id="genres-validation" required>
                        </div>
                    </div>
                    <div class="admin-form-row">
                        <div class="admin-form-group">
                            <label for="stock">Stock *</label>
                            <input type="number" id="stock" name="stock" value="<?php echo old('stock', 1); ?>" min="1" required>
                        </div>
                        <div class="admin-form-group">
                            <label for="image">Image de couverture</label>
                            <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.gif" onchange="previewImage(event)">
                            <small>Formats acceptés: JPG, PNG, GIF. Taille max: 2Mo. Dimensions optimales: 300x400px</small>
                            <div id="image-preview" style="margin-top: 1rem; display: none;">
                                <img id="preview-img" src="" alt="Aperçu" style="max-width: 200px; max-height: 200px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Livres -->
                <div id="livre-fields" class="admin-form-section type-fields" style="display: none;">
                    <h2>Informations spécifiques - Livre</h2>
                    <div class="admin-form-row">
                        <div class="admin-form-group">
                            <label for="auteur">Auteur *</label>
                            <input type="text" id="auteur" name="auteur" value="<?php echo old('auteur'); ?>" maxlength="100">
                        </div>
                        <div class="admin-form-group">
                            <label for="isbn">ISBN</label>
                            <input type="text" id="isbn" name="isbn" value="<?php echo old('isbn'); ?>" maxlength="20" placeholder="Ex: 978-2-123456-78-9">
                        </div>
                    </div>
                    <div class="admin-form-row">
                        <div class="admin-form-group">
                            <label for="nombre_pages">Nombre de pages</label>
                            <input type="number" id="nombre_pages" name="nombre_pages" value="<?php echo old('nombre_pages'); ?>" min="1" max="9999">
                        </div>
                        <div class="admin-form-group">
                            <label for="annee_publication">Année de publication</label>
                            <input type="number" id="annee_publication" name="annee_publication" value="<?php echo old('annee_publication'); ?>" min="1900" max="<?php echo date('Y'); ?>">
                        </div>
                    </div>
                    <div class="admin-form-group">
                        <label for="resume">Résumé</label>
                        <textarea id="resume" name="resume" rows="4"><?php echo old('resume'); ?></textarea>
                    </div>
                </div>
                <!-- Films -->
                <div id="film-fields" class="admin-form-section type-fields" style="display: none;">
                    <h2>Informations spécifiques - Film</h2>
                    <div class="admin-form-row">
                        <div class="admin-form-group">
                            <label for="realisateur">Réalisateur *</label>
                            <input type="text" id="realisateur" name="realisateur" value="<?php echo old('realisateur'); ?>" maxlength="100">
                        </div>
                        <div class="admin-form-group">
                            <label for="duree_minutes">Durée (minutes)</label>
                            <input type="number" id="duree_minutes" name="duree_minutes" value="<?php echo old('duree_minutes'); ?>" min="1" max="999">
                        </div>
                    </div>
                    <div class="admin-form-row">
                        <div class="admin-form-group">
                            <label for="annee_film">Année</label>
                            <input type="number" id="annee_film" name="annee_film" value="<?php echo old('annee_film'); ?>" min="1900" max="<?php echo date('Y'); ?>">
                        </div>
                        <div class="admin-form-group">
                            <label for="classification">Classification *</label>
                            <select id="classification" name="classification">
                                <option value="">Sélectionner</option>
                                <option value="Tous publics" <?php echo old('classification') === 'Tous publics' ? 'selected' : ''; ?>>Tous publics</option>
                                <option value="-12" <?php echo old('classification') === '-12' ? 'selected' : ''; ?>>-12</option>
                                <option value="-16" <?php echo old('classification') === '-16' ? 'selected' : ''; ?>>-16</option>
                                <option value="-18" <?php echo old('classification') === '-18' ? 'selected' : ''; ?>>-18</option>
                            </select>
                        </div>
                    </div>
                    <div class="admin-form-group">
                        <label for="synopsis">Synopsis</label>
                        <textarea id="synopsis" name="synopsis" rows="4"><?php echo old('synopsis'); ?></textarea>
                    </div>
                </div>
                <!-- Jeux -->
                <div id="jeu-fields" class="admin-form-section type-fields" style="display: none;">
                    <h2>Informations spécifiques - Jeu vidéo</h2>
                    <div class="admin-form-row">
                        <div class="admin-form-group">
                            <label for="editeur">Éditeur *</label>
                            <input type="text" id="editeur" name="editeur" value="<?php echo old('editeur'); ?>" maxlength="100">
                        </div>
                        <div class="admin-form-group">
                            <label for="plateforme">Plateforme *</label>
                            <select id="plateforme" name="plateforme">
                                <option value="">Sélectionner</option>
                                <option value="PC" <?php echo old('plateforme') === 'PC' ? 'selected' : ''; ?>>PC</option>
                                <option value="PlayStation" <?php echo old('plateforme') === 'PlayStation' ? 'selected' : ''; ?>>PlayStation</option>
                                <option value="Xbox" <?php echo old('plateforme') === 'Xbox' ? 'selected' : ''; ?>>Xbox</option>
                                <option value="Nintendo" <?php echo old('plateforme') === 'Nintendo' ? 'selected' : ''; ?>>Nintendo</option>
                                <option value="Mobile" <?php echo old('plateforme') === 'Mobile' ? 'selected' : ''; ?>>Mobile</option>
                            </select>
                        </div>
                    </div>
                    <div class="admin-form-row">
                        <div class="admin-form-group">
                            <label for="age_minimum">Âge minimum *</label>
                            <select id="age_minimum" name="age_minimum">
                                <option value="">Sélectionner</option>
                                <option value="3" <?php echo old('age_minimum') === '3' ? 'selected' : ''; ?>>3+</option>
                                <option value="7" <?php echo old('age_minimum') === '7' ? 'selected' : ''; ?>>7+</option>
                                <option value="12" <?php echo old('age_minimum') === '12' ? 'selected' : ''; ?>>12+</option>
                                <option value="16" <?php echo old('age_minimum') === '16' ? 'selected' : ''; ?>>16+</option>
                                <option value="18" <?php echo old('age_minimum') === '18' ? 'selected' : ''; ?>>18+</option>
                            </select>
                        </div>
                    </div>
                    <div class="admin-form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4"><?php echo old('description'); ?></textarea>
                    </div>
                </div>
                <!-- Actions -->
                <div class="admin-form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Ajouter le média
                    </button>
                    <a href="<?php echo url('media/library'); ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Données pour le JavaScript (pattern MVC propre) -->
<div id="genre-data-add" style="display:none" 
     data-genres='<?php echo json_encode(array_column($genres_livre ?? [], 'nom')); ?>'
     data-genre-ids='<?php echo json_encode(array_column($genres_livre ?? [], 'id')); ?>'
     data-old-type='<?php echo old('type'); ?>'
     data-old-genre-ids='<?php echo json_encode(old('genre_ids', [])); ?>'></div>

<script>
// Fonction de prévisualisation de l'image
function previewImage(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('image-preview');
    const img = document.getElementById('preview-img');
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            img.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        preview.style.display = 'none';
    }
}

// Fonction pour afficher les champs spécifiques au type de média
function showTypeFields(type) {
    // Masquer tous les champs spécifiques
    document.querySelectorAll('.type-fields').forEach(function(el) {
        el.style.display = 'none';
    });
    
    // Afficher les champs du type sélectionné
    if (type) {
        const fieldsDiv = document.getElementById(type + '-fields');
        if (fieldsDiv) {
            fieldsDiv.style.display = 'block';
        }
    }
    
    // Charger les genres pour ce type
    loadGenresForType(type);
}

// Fonction pour charger les genres dans les checkboxes
function loadGenresForType(type) {
    const container = document.getElementById('genres-checkboxes');
    const dataDiv = document.getElementById('genre-data-add');
    
    if (!container || !dataDiv || !type) {
        container.innerHTML = '<p class="text-muted">Veuillez d\'abord sélectionner un type de média</p>';
        return;
    }
    
    // Récupérer les données des genres
    const genreNames = JSON.parse(dataDiv.getAttribute('data-genres') || '[]');
    const genreIds = JSON.parse(dataDiv.getAttribute('data-genre-ids') || '[]');
    const oldGenreIds = JSON.parse(dataDiv.getAttribute('data-old-genre-ids') || '[]');
    
    // Générer les checkboxes
    let html = '';
    for (let i = 0; i < genreNames.length && i < genreIds.length; i++) {
        const genreId = genreIds[i];
        const genreName = genreNames[i];
        // Vérifier si ce genre était précédemment sélectionné
        const isChecked = oldGenreIds.includes(genreId) || oldGenreIds.includes(String(genreId));
        
        html += `
            <div class="genre-checkbox">
                <input type="checkbox" 
                       id="genre_${genreId}" 
                       name="genre_ids[]" 
                       value="${genreId}"
                       ${isChecked ? 'checked' : ''}>
                <label for="genre_${genreId}">${genreName}</label>
            </div>
        `;
    }
    
    container.innerHTML = html || '<p class="text-muted">Aucun genre disponible</p>';
}

// RESTAURATION DE L'ÉTAT DU FORMULAIRE au chargement de la page
// (Important pour conserver les données en cas d'erreur de validation)
document.addEventListener('DOMContentLoaded', function() {
    const dataDiv = document.getElementById('genre-data-add');
    
    if (dataDiv) {
        // Récupérer le type précédemment sélectionné (s'il existe)
        const oldType = dataDiv.getAttribute('data-old-type');
        
        // Si un type était sélectionné, restaurer l'affichage
        if (oldType) {
            // Afficher les champs correspondants
            showTypeFields(oldType);
            
            // S'assurer que le select est bien à jour (en cas de problème)
            const typeSelect = document.getElementById('type');
            if (typeSelect && typeSelect.value !== oldType) {
                typeSelect.value = oldType;
            }
        }
    }
});
</script>
