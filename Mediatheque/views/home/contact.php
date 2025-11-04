<!-- Styles spécifiques -->
<link rel="stylesheet" href="<?php echo url('assets/css/dashboard.css'); ?>">
<link rel="stylesheet" href="<?php echo url('assets/css/admin_forms.css'); ?>">

<style>
    .contact-center {
        max-width: 900px;
        margin: 0 auto;
    }
</style>

<div class="page-header">
    <div class="container">
        <h1><?php e($title); ?></h1>
        <p class="subtitle">Nous sommes là pour vous aider</p>
    </div>
</div>

<section class="admin-content">
    <div class="container">
        
        <div class="contact-center">
            <!-- Formulaire de contact -->
            <div class="dashboard-section" style="margin-top: 30px;">
                <h2><i class="fas fa-paper-plane"></i> Nous contacter</h2>
                <p style="font-size: 1.05em; line-height: 1.8; color: #555; margin-bottom: 30px; text-align: center;">
                    N'hésitez pas à nous envoyer un message. Nous vous répondrons dans les plus brefs délais.
                </p>
                
                <form method="POST" class="admin-form">
                    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                    
                    <div class="admin-form-row">
                        <div class="admin-form-group">
                            <label for="name">Nom complet</label>
                            <input type="text" id="name" name="name" class="form-control" required 
                                   value="<?php echo escape(post('name', '')); ?>">
                        </div>
                        
                        <div class="admin-form-group">
                            <label for="email">Adresse email</label>
                            <input type="email" id="email" name="email" class="form-control" required 
                                   value="<?php echo escape(post('email', '')); ?>">
                        </div>
                    </div>
                    
                    <div class="admin-form-group">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" class="form-control" rows="8" required><?php echo escape(post('message', '')); ?></textarea>
                    </div>
                    
                    <div class="admin-form-actions" style="justify-content: center;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i>
                            Envoyer le message
                        </button>
                    </div>
                </form>
            </div>

            <!-- Informations de contact -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 30px; margin-top: 40px;">
                
                <!-- Contact -->
                <div class="info-box" style="background: linear-gradient(135deg, #fff, #f8f9fa); padding: 25px; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); border-left: 4px solid #3498db;">
                    <h3 style="color: #2c3e50; margin-top: 0;"><i class="fas fa-phone"></i> Informations de contact</h3>
                    <p style="margin: 10px 0;"><i class="fas fa-envelope" style="color: #3498db; width: 25px;"></i> <a href="mailto:campusavarappe@mail.com" style="color: #555;">campusavarappe@mail.com</a></p>
                    <p style="margin: 10px 0;"><i class="fas fa-phone" style="color: #3498db; width: 25px;"></i> 01 02 03 04 05</p>
                    <p style="margin: 10px 0;"><i class="fas fa-map-marker-alt" style="color: #3498db; width: 25px;"></i> 131 Avenue Franklin Roosevelt<br><span style="margin-left: 35px;">83100 TOULON</span></p>
                </div>

                <!-- Horaires -->
                <div class="info-box" style="background: linear-gradient(135deg, #fff, #f8f9fa); padding: 25px; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); border-left: 4px solid #28a745;">
                    <h3 style="color: #2c3e50; margin-top: 0;"><i class="fas fa-clock"></i> Horaires d'ouverture</h3>
                    <p style="margin: 10px 0;"><strong>Lundi :</strong> 9-20h</p>
                    <p style="margin: 10px 0;"><strong>Mardi :</strong> 9-20h</p>
                    <p style="margin: 10px 0;"><strong>Mercredi :</strong> 9-20h</p>
                    <p style="margin: 10px 0;"><strong>Jeudi :</strong> 9-20h</p>
                    <p style="margin: 10px 0;"><strong>Vendredi :</strong> 9-20h</p>
                    <p style="margin: 10px 0;"><strong>Samedi :</strong> 9-20h</p>
                    <p style="margin: 10px 0;"><strong>Dimanche :</strong> Fermé</p>
                </div>

            </div>
        </div>

    </div>
    
    <!-- Espace avant le footer -->
    <div style="margin-bottom: 4rem;"></div>
</section> 