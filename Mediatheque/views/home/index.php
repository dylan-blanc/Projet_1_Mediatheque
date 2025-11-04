<!-- Styles spécifiques à la page d'accueil -->
<link rel="stylesheet" href="<?php echo url('assets/css/home.css'); ?>">
<link rel="stylesheet" href="<?php echo url('assets/css/admin_forms.css'); ?>">

<!-- Bannière d'accueil avec l'image de la médiathèque -->
<section class="home-banner" style="background-image: url('<?php echo url('assets/images/mediatheque_tln.png'); ?>');">
    <div class="home-banner-content">
        <h1>Bienvenue à la Médiathèque de Toulon</h1>
        <p>Votre espace de culture et de découverte</p>
    </div>
</section>

<!-- Présentation + Horaires côte à côte -->
<section class="home-presentation-hours">
    <div class="container home-presentation-hours-flex">
        <div class="home-presentation-col">
            <h2><i class="fas fa-book-open"></i> Notre Médiathèque</h2>
            <p>
                Depuis plus de 30 ans, la Médiathèque de Toulon est un lieu de culture, de rencontre et de partage.<br>
                Notre mission est de rendre accessible à tous la culture sous toutes ses formes : livres, films, jeux vidéo, et bien plus encore.<br>
                Nous valorisons la diversité culturelle, l'éducation et l'ouverture d'esprit.
            </p>
            <p>
                Dans une ambiance chaleureuse et conviviale, notre équipe passionnée vous accueille pour vous guider dans vos découvertes culturelles et vous accompagner dans vos projets personnels et collectifs.
            </p>
            
            <div class="hours-compact">
                <p><i class="far fa-clock"></i> <strong>Horaires :</strong> Du lundi au vendredi 9h-20h • Samedi 10h-18h • Dimanche fermé</p>
            </div>
            
            <div class="presentation-actions">
                <p class="help-text">
                    <i class="fas fa-question-circle"></i> Une question ? Besoin d'informations ?
                </p>
                <div class="action-buttons">
                    <a href="<?php echo url('home/contact'); ?>" class="btn-action btn-contact">
                        <i class="fas fa-envelope"></i> Contactez-nous
                    </a>
                    <a href="<?php echo url('home/about'); ?>" class="btn-action btn-about">
                        <i class="fas fa-info-circle"></i> En savoir plus sur notre système d'emprunt
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section Sélection spéciale et Notre Collection - Layout horizontal -->
<section class="home-selections">
    <div class="selections-container">
        <!-- Sélection spéciale à gauche -->
        <div class="selection-side">
            <h2><i class="fas fa-star"></i> Sélection spéciale</h2>
            <div class="selection-cards-wrapper">
                <a href="<?php echo url('media/library?type=livre&genre=' . ($genre_ids['jeunesse'] ?? $genre_ids['enfant'] ?? '')); ?>" class="selection-card selection-link">
                    <div class="selection-icon">
                        <i class="fas fa-child"></i>
                    </div>
                    <h3 class="selection-title">Livres Jeunesse</h3>
                    <div class="selection-count" style="visibility: hidden;">0</div>
                    <p class="selection-description">Découvrez nos livres pour enfants</p>
                </a>
                <a href="<?php echo url('media/library?type=film&genre=' . ($genre_ids['comedie'] ?? $genre_ids['comédie'] ?? '')); ?>" class="selection-card selection-link">
                    <div class="selection-icon">
                        <i class="fas fa-laugh"></i>
                    </div>
                    <h3 class="selection-title">Films Comédie</h3>
                    <div class="selection-count" style="visibility: hidden;">0</div>
                    <p class="selection-description">Les meilleures comédies</p>
                </a>
                <a href="<?php echo url('media/library?type=jeu&genre=' . ($genre_ids['tir'] ?? $genre_ids['fps'] ?? $genre_ids['action'] ?? '')); ?>" class="selection-card selection-link">
                    <div class="selection-icon">
                        <i class="fas fa-crosshairs"></i>
                    </div>
                    <h3 class="selection-title">Jeux de Tir</h3>
                    <div class="selection-count" style="visibility: hidden;">0</div>
                    <p class="selection-description">Action et adrénaline</p>
                </a>
            </div>
        </div>
        
        <!-- Notre Collection à droite -->
        <div class="selection-side">
            <h2><i class="fas fa-layer-group"></i> Notre Collection</h2>
            <div class="selection-cards-wrapper">
                <a href="<?php echo url('media/library?type=livre'); ?>" class="selection-card selection-link">
                    <div class="selection-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <h3 class="selection-title">Total de Livres</h3>
                    <div class="selection-count"><?php echo number_format($stats['livres']); ?></div>
                    <p class="selection-description">Découvrez notre collection</p>
                </a>
                <a href="<?php echo url('media/library?type=film'); ?>" class="selection-card selection-link">
                    <div class="selection-icon">
                        <i class="fas fa-film"></i>
                    </div>
                    <h3 class="selection-title">Total de Films</h3>
                    <div class="selection-count"><?php echo number_format($stats['films']); ?></div>
                    <p class="selection-description">Explorez notre catalogue</p>
                </a>
                <a href="<?php echo url('media/library?type=jeu'); ?>" class="selection-card selection-link">
                    <div class="selection-icon">
                        <i class="fas fa-gamepad"></i>
                    </div>
                    <h3 class="selection-title">Total de Jeux Vidéo</h3>
                    <div class="selection-count"><?php echo number_format($stats['jeux']); ?></div>
                    <p class="selection-description">Parcourez nos jeux</p>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Carousel Top Médias -->
<section class="top-carousel">
    <div class="carousel-wrapper">
        <!-- Slide 1: Livres -->
        <div class="carousel-slide active" data-type="livre" data-next="film" data-prev="jeu">
            <a href="<?php echo url('media/library?type=livre'); ?>" class="carousel-banner" style="background-image: url('<?php echo url('assets/images/fresk_livre.png'); ?>');">
                <h2><i class="fas fa-book"></i> Nos Livres les plus populaires</h2>
            </a>
            <div class="carousel-content">
                <div class="top-grid">
                    <?php if (!empty($top_livres)): ?>
                        <?php foreach ($top_livres as $livre): ?>
                            <a href="<?php echo url('media/detail/' . $livre['id']); ?>" class="media-card">
                                <img src="<?php echo url(get_media_image($livre)); ?>" alt="<?php e($livre['titre']); ?>">
                                <div class="card-info">
                                    <h3><?php e($livre['titre']); ?></h3>
                                    <p><strong>Auteur:</strong> <?php e($livre['auteur'] ?? 'Inconnu'); ?></p>
                                    <p><strong>Genre:</strong> <?php e($livre['genres'] ?? 'Non défini'); ?></p>
                                    <span class="badge"><?php echo $livre['total_emprunts']; ?> emprunts</span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Aucun livre disponible.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Slide 2: Films -->
        <div class="carousel-slide" data-type="film" data-next="jeu" data-prev="livre">
            <a href="<?php echo url('media/library?type=film'); ?>" class="carousel-banner" style="background-image: url('<?php echo url('assets/images/fresk_film.png'); ?>');">
                <h2><i class="fas fa-film"></i> Nos Films les plus populaires</h2>
            </a>
            <div class="carousel-content">
                <div class="top-grid">
                    <?php if (!empty($top_films)): ?>
                        <?php foreach ($top_films as $film): ?>
                            <a href="<?php echo url('media/detail/' . $film['id']); ?>" class="media-card">
                                <img src="<?php echo url(get_media_image($film)); ?>" alt="<?php e($film['titre']); ?>">
                                <div class="card-info">
                                    <h3><?php e($film['titre']); ?></h3>
                                    <p><strong>Réalisateur:</strong> <?php e($film['realisateur'] ?? 'Inconnu'); ?></p>
                                    <p><strong>Genre:</strong> <?php e($film['genres'] ?? 'Non défini'); ?></p>
                                    <span class="badge"><?php echo $film['total_emprunts']; ?> emprunts</span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Aucun film disponible.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Slide 3: Jeux -->
        <div class="carousel-slide" data-type="jeu" data-next="livre" data-prev="film">
            <a href="<?php echo url('media/library?type=jeu'); ?>" class="carousel-banner" style="background-image: url('<?php echo url('assets/images/fresk_jeux-video.png'); ?>');">
                <h2><i class="fas fa-gamepad"></i> Nos Jeux vidéo les plus populaires</h2>
            </a>
            <div class="carousel-content">
                <div class="top-grid">
                    <?php if (!empty($top_jeux)): ?>
                        <?php foreach ($top_jeux as $jeu): ?>
                            <a href="<?php echo url('media/detail/' . $jeu['id']); ?>" class="media-card">
                                <img src="<?php echo url(get_media_image($jeu)); ?>" alt="<?php e($jeu['titre']); ?>">
                                <div class="card-info">
                                    <h3><?php e($jeu['titre']); ?></h3>
                                    <p><strong>Éditeur:</strong> <?php e($jeu['editeur'] ?? 'Inconnu'); ?></p>
                                    <p><strong>Genre:</strong> <?php e($jeu['genres'] ?? 'Non défini'); ?></p>
                                    <span class="badge"><?php echo $jeu['total_emprunts']; ?> emprunts</span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Aucun jeu disponible.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <button class="nav-btn prev"><i class="fas fa-gamepad"></i></button>
        <button class="nav-btn next"><i class="fas fa-film"></i></button>
    </div>
</section>

<!-- Section Événements à venir -->
<section class="home-events">
    <div class="container">
        <h2><i class="far fa-calendar-alt"></i> Événements à venir</h2>
        <div class="events-grid">
            <?php foreach ($evenements as $event): ?>
                <div class="event-card">
                    <?php if (is_admin() && isset($_GET['edit_event']) && $_GET['edit_event'] == $event['id']): ?>
                        <form method="post" class="event-edit-form" enctype="multipart/form-data">
                            <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                            
                            <div class="admin-form-group">
                                <label for="titre_<?php echo $event['id']; ?>">Titre</label>
                                <input type="text" name="titre" id="titre_<?php echo $event['id']; ?>" value="<?php e($event['titre']); ?>" required class="form-control">
                            </div>
                            
                            <div class="admin-form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div class="admin-form-group">
                                    <label for="date_<?php echo $event['id']; ?>">Date</label>
                                    <input type="date" name="date_evenement" id="date_<?php echo $event['id']; ?>" value="<?php e($event['date_evenement']); ?>" min="<?php echo date('Y-m-d'); ?>" required class="form-control">
                                </div>
                                <div class="admin-form-group">
                                    <label for="time_<?php echo $event['id']; ?>">Heure</label>
                                    <input type="time" name="heure_evenement" id="time_<?php echo $event['id']; ?>" value="<?php e($event['heure_evenement']); ?>" min="09:00" max="20:00" required class="form-control">
                                </div>
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="description_<?php echo $event['id']; ?>">Description</label>
                                <textarea name="description" id="description_<?php echo $event['id']; ?>" required class="form-control" rows="4"><?php e($event['description']); ?></textarea>
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="image_<?php echo $event['id']; ?>">Image de l'événement</label>
                                <?php if (!empty($event['event_image'])): ?>
                                    <div style="margin-bottom: 1rem;">
                                        <img src="<?php echo url($event['event_image']); ?>" alt="Image actuelle" style="max-width: 200px; display: block; margin-bottom: 0.5rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                        <label style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.5rem; cursor: pointer;">
                                            <input type="checkbox" name="delete_image" value="1" style="cursor: pointer;"> 
                                            <span style="color: #dc3545; font-weight: 500;">Supprimer l'image actuelle</span>
                                        </label>
                                    </div>
                                <?php endif; ?>
                                <input type="file" name="image" id="image_<?php echo $event['id']; ?>" accept="image/*" class="form-control">
                            </div>
                            
                            <div class="admin-form-actions" style="display: flex; gap: 0.75rem; justify-content: flex-start; margin-top: 1.5rem;">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Enregistrer
                                </button>
                                <a href="<?php echo url(); ?>" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Annuler
                                </a>
                            </div>
                        </form>
                    <?php else: ?>
                        <?php if (!empty($event['event_image'])): ?>
                            <div class="event-image">
                                <img src="<?php echo url($event['event_image']); ?>" alt="<?php e($event['titre']); ?>">
                            </div>
                        <?php endif; ?>
                        <h3 class="event-title"><?php e($event['titre']); ?></h3>
                        <p class="event-datetime">
                            <i class="far fa-calendar"></i> <?php echo format_date($event['date_evenement'], 'd/m/Y'); ?> - 
                            <i class="far fa-clock"></i> <?php e($event['heure_evenement']); ?>
                        </p>
                        <p class="event-description"><?php e($event['description']); ?></p>
                        <?php if (is_admin()): ?>
                            <a href="?edit_event=<?php echo $event['id']; ?>" class="btn-edit-event">
                                <i class="fas fa-edit"></i> Modifier
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <?php if (is_admin()): ?>
                <div class="event-card event-add-card">
                    <form method="post" class="event-add-form" enctype="multipart/form-data">
                        <h3>Ajouter un événement</h3>
                        <div class="form-group">
                            <label for="add_titre">Titre</label>
                            <input type="text" name="titre" id="add_titre" required>
                        </div>
                        <div class="form-group">
                            <label for="add_date_evenement">Date</label>
                            <input type="date" name="date_evenement" id="add_date_evenement" min="<?php echo date('d-m-Y'); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="add_heure_evenement">Heure</label>
                            <input type="time" name="heure_evenement" id="add_heure_evenement" min="09:00" max="20:00" required>
                        </div>
                        <div class="form-group">
                            <label for="add_description">Description</label>
                            <textarea name="description" id="add_description" class="event-description" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="add_image">Image</label>
                            <input type="file" name="image" id="add_image" accept="image/*">
                        </div>
                        <button type="submit" class="btn btn-success">Ajouter</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>