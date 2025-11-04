<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1><?php e($title); ?></h1>
            <p>Créez votre compte</p>
        </div>

        <form method="POST" class="auth-form">
            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">

            <div class="form-group">
                <label for="prenom">Prénom</label>
                <input type="text" id="prenom" name="prenom" required
                    value="<?php echo escape(post('prenom', '')); ?>"
                    placeholder="Votre prénom"
                    minlength="2" maxlength="50"
                    oninput="capitalizeFirstLetter(this)">
            </div>

            <div class="form-group">
                <label for="nom">Nom</label>
                <input type="text" id="nom" name="nom" required
                    value="<?php echo escape(post('nom', '')); ?>"
                    placeholder="Votre nom"
                    minlength="2" maxlength="50"
                    oninput="capitalizeFirstLetter(this)">
            </div>

            <div class="form-group">
                <label for="email">Adresse email</label>
                <input type="email" id="email" name="email" required
                    value="<?php echo escape(post('email', '')); ?>"
                    placeholder="votre@email.com"
                    maxlength="100">
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required
                    placeholder="Au moins 8 caractères avec majuscules, minuscules et chiffres"
                    minlength="8">
                <small class="form-text">Au moins 8 caractères avec 1 majuscule, 1 minuscule et 1 chiffre</small>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirmer le mot de passe</label>
                <input type="password" id="confirm_password" name="confirm_password" required
                    placeholder="Confirmez votre mot de passe"
                    minlength="8">
            </div>

            <button type="submit" class="btn btn-primary btn-full">
                <i class="fas fa-user-plus"></i>
                S'inscrire
            </button>
        </form>

        <div class="auth-footer">
            <p>Déjà un compte ?
                <a href="<?php echo url('auth/login'); ?>">Se connecter</a>
            </p>
        </div>
    </div>
</div>

