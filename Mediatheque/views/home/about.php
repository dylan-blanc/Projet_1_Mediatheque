<!-- Styles spécifiques -->
<link rel="stylesheet" href="<?php echo url('assets/css/dashboard.css'); ?>">

<style>
    .about-layout {
        display: grid;
        grid-template-columns: 1fr 400px;
        gap: 30px;
        margin-top: 30px;
    }
    
    .about-main {
        display: flex;
        flex-direction: column;
        gap: 30px;
    }
    
    .about-sidebar {
        display: flex;
        flex-direction: column;
        gap: 30px;
    }
    
    @media (max-width: 992px) {
        .about-layout {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="page-header">
    <div class="container">
        <h1><?php e($title); ?></h1>
        <p class="subtitle">Découvrez notre médiathèque et nos services</p>
    </div>
</div>

<section class="admin-content">
    <div class="container">
        
        <div class="about-layout">
            <!-- Contenu principal à gauche -->
            <div class="about-main">
                
                <!-- Informations principales -->
                <div class="dashboard-section">
                    <h2>Bienvenue à la Médiathèque de Toulon</h2>
                    <p style="font-size: 1.1em; line-height: 1.8; color: #555;">
                        Depuis plus de 30 ans, la Médiathèque de Toulon est bien plus qu'un simple lieu de prêt : c'est un véritable espace de vie culturelle, 
                        un carrefour de rencontres et d'échanges au cœur de notre ville. Ouverte à tous, elle incarne notre volonté de rendre la culture accessible, 
                        sous toutes ses formes et pour tous les publics.
                    </p>
                    <p style="font-size: 1.05em; line-height: 1.8; color: #555;">
                        Notre mission ? Vous accompagner dans vos découvertes, nourrir votre curiosité et favoriser l'ouverture d'esprit. 
                        Que vous soyez passionné de littérature, amateur de cinéma ou joueur invétéré, notre équipe dévouée est là pour 
                        vous guider et vous conseiller dans un cadre chaleureux et convivial.
                    </p>
                </div>

                <!-- Condition d'emprunt -->
                <div class="dashboard-section">
                    <h2><i class="fas fa-book-reader"></i> Conditions d'emprunt</h2>
                    <p style="font-size: 1.05em; line-height: 1.8; color: #555; margin-bottom: 1.5rem;">
                        Emprunter à la Médiathèque de Toulon, c'est simple et pratique ! Voici ce qu'il faut savoir :
                    </p>
                    <ul style="font-size: 1.05em; line-height: 1.8; color: #555; margin-left: 2rem;">
                        <li><strong>Durée d'emprunt :</strong> Vous disposez de <strong>14 jours</strong> pour profiter de chaque média emprunté.</li>
                        <li><strong>Réservation :</strong> Vous pouvez réserver vos médias préférés en ligne et les récupérer à l'accueil.</li>
                        <li><strong>Disponibilité :</strong> Les emprunts sont disponibles du lundi au samedi, durant nos horaires d'ouverture.</li>
                        <li><strong>Restitution :</strong> Pensez à rapporter vos médias à temps pour éviter les pénalités et permettre à d'autres d'en profiter !</li>
                    </ul>
                    <p style="font-size: 1.05em; line-height: 1.8; color: #555; margin-top: 1.5rem;">
                        <i class="fas fa-info-circle" style="color: #3498db;"></i> <strong>Bon à savoir :</strong> 
                        Vous pouvez emprunter jusqu'à <strong>3 médias simultanément</strong>. Profitez-en pour découvrir notre catalogue diversifié !
                    </p>
                </div>

                <!-- Service et événementiel -->
                <div class="dashboard-section">
                    <h2><i class="fas fa-calendar-alt"></i> Service et événementiel</h2>
                    <p style="font-size: 1.05em; line-height: 1.8; color: #555;">
                        La Médiathèque accueil des événements hebdomadaire comme des groupes de lecture, apprentissage du français et aide à la 
                        recherche d'emploi. La Médiathèque accueil des auteurs présentant leur œuvres, voir la section événements et notre calendrier.
                    </p>
                </div>

                <!-- Google Map -->
                <div class="dashboard-section">
                    <h2><i class="fas fa-map-marker-alt"></i> Nous trouver</h2>
                    <p style="font-size: 1.05em; line-height: 1.6; color: #555; margin-bottom: 20px;">
                        <strong>131 Avenue Franklin Roosevelt, 83100 TOULON</strong>
                    </p>
                    <div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.1);">
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2897.5!2d5.928!3d43.124!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x12c91b5a5a5a5a5a%3A0x5a5a5a5a5a5a5a5a!2s131%20Avenue%20Franklin%20Roosevelt%2C%2083100%20Toulon!5e0!3m2!1sfr!2sfr!4v1234567890" 
                            style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                    <p style="margin-top: 20px; text-align: center; color: #555;">
                        <i class="fas fa-car"></i> Parking disponible &nbsp; | &nbsp; <i class="fas fa-bicycle"></i> Accès vélo
                    </p>
                </div>
                
            </div>

            <!-- Sidebar à droite -->
            <div class="about-sidebar">
                
                <!-- Horaires -->
                <div class="info-box" style="background: linear-gradient(135deg, #fff, #f8f9fa); padding: 25px; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); border-left: 4px solid #28a745;">
                    <h3 style="color: #2c3e50; margin-top: 0;"><i class="fas fa-clock"></i> Horaires</h3>
                    <p style="margin: 10px 0;"><strong>Lundi :</strong> 9-20h</p>
                    <p style="margin: 10px 0;"><strong>Mardi :</strong> 9-20h</p>
                    <p style="margin: 10px 0;"><strong>Mercredi :</strong> 9-20h</p>
                    <p style="margin: 10px 0;"><strong>Jeudi :</strong> 9-20h</p>
                    <p style="margin: 10px 0;"><strong>Vendredi :</strong> 9-20h</p>
                    <p style="margin: 10px 0;"><strong>Samedi :</strong> 9-20h</p>
                    <p style="margin: 10px 0;"><strong>Dimanche :</strong> Fermé</p>
                </div>

                <!-- Accessibilité -->
                <div class="info-box" style="background: linear-gradient(135deg, #fff, #f8f9fa); padding: 25px; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); border-left: 4px solid #17a2b8;">
                    <h3 style="color: #2c3e50; margin-top: 0;"><i class="fas fa-wheelchair"></i> Accessibilité</h3>
                    <p style="margin: 10px 0;"><i class="fas fa-eye-slash" style="color: #17a2b8; width: 25px;"></i> Liseuse</p>
                    <p style="margin: 10px 0;"><i class="fas fa-wheelchair" style="color: #17a2b8; width: 25px;"></i> Toilette handicapé</p>
                    <p style="margin: 10px 0;"><i class="fas fa-star" style="color: #17a2b8; width: 25px;"></i> Accès handicapés</p>
                </div>

                <!-- Services -->
                <div class="info-box" style="background: linear-gradient(135deg, #fff, #f8f9fa); padding: 25px; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); border-left: 4px solid #f39c12;">
                    <h3 style="color: #2c3e50; margin-top: 0;"><i class="fas fa-concierge-bell"></i> Service</h3>
                    <p style="margin: 10px 0;"><i class="fas fa-share-alt" style="color: #f39c12; width: 25px;"></i> Distributeur</p>
                    <p style="margin: 10px 0;"><i class="fas fa-utensils" style="color: #f39c12; width: 25px;"></i> Salle à manger</p>
                    <p style="margin: 10px 0;"><i class="fas fa-toilet" style="color: #f39c12; width: 25px;"></i> Toilette</p>
                    <p style="margin: 10px 0;"><i class="fas fa-snowflake" style="color: #f39c12; width: 25px;"></i> Climatisation</p>
                    <p style="margin: 10px 0;"><i class="fas fa-wifi" style="color: #f39c12; width: 25px;"></i> WiFi</p>
                    <p style="margin: 10px 0;"><i class="fas fa-arrow-up" style="color: #f39c12; width: 25px;"></i> Ascenseur</p>
                    <p style="margin: 10px 0;"><i class="fas fa-plug" style="color: #f39c12; width: 25px;"></i> Chargeur</p>
                </div>

            </div>
        </div>

    </div>
</section> 