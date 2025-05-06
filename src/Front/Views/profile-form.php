<?php
// Variables disponibles :
//   $formMessage  (string)  – message de succès ou d’erreur
//   $isGuest      (bool)    – true si l’utilisateur venait de se créer un compte WP
?>

<?php echo $formMessage; ?>

<form method="post" class="adess-profile-form">
    <?php wp_nonce_field('adess_profile', 'adess_profile_nonce'); ?>

    <?php if (! is_user_logged_in()): ?>
        <p>
            <label for="user_login"><?php esc_html_e('Identifiant :', 'adess-resa'); ?></label><br>
            <input type="text" name="user_login" id="user_login" required>
        </p>
        <p>
            <label for="user_email"><?php esc_html_e('Email :', 'adess-resa'); ?></label><br>
            <input type="email" name="user_email" id="user_email" required>
        </p>
        <p>
            <label for="user_pass"><?php esc_html_e('Mot de passe :', 'adess-resa'); ?></label><br>
            <input type="password" name="user_pass" id="user_pass" required>


        <?php endif; ?>


        <p>
            <label for="entity_type"><?php esc_html_e("Type d’entité :", 'adess-resa'); ?></label><br>
            <select name="entity_type" id="entity_type" required>
                <option value="company"><?php esc_html_e('Entreprise', 'adess-resa'); ?></option>
                <option value="collectivity"><?php esc_html_e('Collectivité', 'adess-resa'); ?></option>
            </select>
        </p>

        <p>
            <label for="entity_name"><?php esc_html_e("Nom de l’entité :", 'adess-resa'); ?></label><br>
            <input type="text" name="entity_name" id="entity_name" required>
        </p>

        <p>
            <label for="address"><?php esc_html_e("Adresse :", 'adess-resa'); ?></label><br>
            <textarea name="address" id="address" required></textarea>
        </p>

        <p>
            <label for="contact_name"><?php esc_html_e("Nom du contact :", 'adess-resa'); ?></label><br>
            <input type="text" name="contact_name" id="contact_name">
        </p>

        <p>
            <label for="contact_email"><?php esc_html_e("Email du contact :", 'adess-resa'); ?></label><br>
            <input type="email" name="contact_email" id="contact_email">
        </p>

        <p>
            <label for="phone"><?php esc_html_e("Téléphone :", 'adess-resa'); ?></label><br>
            <input type="text" name="phone" id="phone">
        </p>

        <p>
            <label for="default_location"><?php esc_html_e("Lieu de prestation par défaut :", 'adess-resa'); ?></label><br>
            <input type="text" name="default_location" id="default_location">
        </p>

        <p>
            <input type="submit" value="<?php esc_attr_e('Envoyer ma demande', 'adess-resa'); ?>">
        </p>
</form>