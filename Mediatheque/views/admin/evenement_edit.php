<?php
/**
 * Vue - Formulaire d'édition/ajout d'événement admin
 */
?>
<link rel="stylesheet" href="<?php echo url('assets/css/admin_forms.css'); ?>">

<div class="page-header">
    <div class="container">
        <h1><?php e($title); ?></h1>
        <nav class="breadcrumb">
            <a href="<?php echo url('admin/evenements'); ?>">Événements</a>
            <span class="breadcrumb-separator">></span>
            <span><?php echo $is_edit ? 'Modifier' : 'Ajouter'; ?></span>
        </nav>
    </div>
</div>

<section class="admin-content">
    <div class="admin-form-container">
        <div class="admin-form-card">
            <form method="post" enctype="multipart/form-data">
                <div class="admin-form-section">
                    <h2>Informations de l'événement</h2>
                    <div class="admin-form-row">
                        <div class="admin-form-group">
                            <label for="titre">Titre *</label>
                            <input type="text" name="titre" id="titre" value="<?php e($evenement['titre'] ?? ''); ?>" required>
                        </div>
                    </div>
                    <div class="admin-form-row">
                        <div class="admin-form-group">
                            <label for="date_evenement">Date *</label>
                            <input type="date" name="date_evenement" id="date_evenement" min="<?php echo date('Y-m-d'); ?>" value="<?php e($evenement['date_evenement'] ?? ''); ?>" required>
                        </div>
                        <div class="admin-form-group">
                            <label for="heure_evenement">Heure</label>
                            <input type="time" name="heure_evenement" id="heure_evenement" min="09:00" max="20:00" value="<?php e($evenement['heure_evenement'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="admin-form-row">
                        <div class="admin-form-group full-width">
                            <label for="description">Description *</label>
                            <textarea name="description" id="description" required><?php e($evenement['description'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    <div class="admin-form-row">
                        <div class="admin-form-group full-width">
                            <label for="image">Image de l'événement</label>
                            <?php 
                            $event_image = $is_edit ? get_evenement_image($evenement['id']) : null;
                            if ($event_image): 
                            ?>
                                <div class="current-image-preview" style="margin-bottom: 1rem;">
                                    <img src="<?php echo url($event_image); ?>" alt="Image actuelle" style="max-width: 200px; height: auto; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                    <label style="display: block; margin-top: 0.75rem;">
                                        <input type="checkbox" name="delete_image" value="1" style="margin-right: 0.5rem;"> 
                                        <span style="color: #e74c3c; font-weight: 500;">Supprimer cette image</span>
                                    </label>
                                </div>
                            <?php endif; ?>
                            <input type="file" name="image" id="image" accept="image/jpeg,image/png,image/jpg,image/gif" onchange="previewEventImage(event)">
                            <small>Formats acceptés : JPG, PNG, GIF (max 2 Mo)</small>
                            <div id="event-image-preview" style="margin-top: 1rem; display: none;">
                                <img id="event-preview-img" src="" alt="Aperçu" style="max-width: 200px; max-height: 200px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="admin-form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                    <a href="<?php echo url('admin/evenements'); ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
function previewEventImage(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('event-image-preview');
    const img = document.getElementById('event-preview-img');
    
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
</script>
</div>
