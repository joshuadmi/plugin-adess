<?php
// Variables disponibles :
//   $formMessage  (string) – message de succès ou d’erreur
//   $isGuest      (bool)   – true si l’utilisateur venait de se créer un compte WP
?>

<?php if (! empty($formMessage)) echo $formMessage; ?>

<form method="post" class="adess-profile-form">
    <?php wp_nonce_field('adess_profile', 'adess_profile_nonce'); ?>

    <?php if ($isGuest): ?>
        <p>
            <label for="user_login"><?php esc_html_e('Identifiant de connexion :', 'adess-resa'); ?></label><br>
            <input type="text" name="user_login" id="user_login" required>
        </p>
        <p>
            <label for="user_email"><?php esc_html_e('Email :', 'adess-resa'); ?></label><br>
            <input type="email" name="user_email" id="user_email" required>
        </p>
        <p>
            <label for="user_pass"><?php esc_html_e('Mot de passe :', 'adess-resa'); ?></label><br>
            <input type="password" name="user_pass" id="user_pass" required>
        </p>
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

    <!-- Subdivision du champ Adresse -->
    <p>
        <label for="profile_street"><?php esc_html_e('Adresse (n° et nom de rue)', 'adess-resa'); ?></label><br>
        <input type="text" name="street" id="profile_street" class="regular-text autocomplete-address" autocomplete="off" required>
    <ul id="autocomplete-list" class="autocomplete-list"></ul>
    </p>
    <p>
        <label for="profile_postal_code"><?php esc_html_e('Code Postal', 'adess-resa'); ?></label><br>
        <input type="text" name="postal_code" id="profile_postal_code" class="regular-text" maxlength="5" required>
    </p>
    <p>
        <label for="profile_city"><?php esc_html_e('Ville', 'adess-resa'); ?></label><br>
        <input type="text" name="city" id="profile_city" class="regular-text" readonly>
    </p>
    <!-- Fin subdivision Adresse -->

    <p>
        <label for="contact_name"><?php esc_html_e("Nom du contact :", 'adess-resa'); ?></label><br>
        <input type="text" name="contact_name" id="contact_name" class="regular-text">
    </p>

    <p>
        <label for="contact_email"><?php esc_html_e("Email du contact :", 'adess-resa'); ?></label><br>
        <input type="email" name="contact_email" id="contact_email" class="regular-text">
    </p>

    <p>
        <label for="phone"><?php esc_html_e("Téléphone :", 'adess-resa'); ?></label><br>
        <input type="text" name="phone" id="phone" class="regular-text">
    </p>

    <p>
        <label for="default_location"><?php esc_html_e("Adresse de la salle si différente de l'adresse principale :", 'adess-resa'); ?></label><br>

    </p>

    <label>
        <input type="checkbox" id="sameAddress" name="same_address" value="1" checked>
        <?php esc_html_e('Même adresse que l’adresse principale', 'adess-resa'); ?>
    </label>

    <div id="second-address-container" style="display: none;">
        <p>
            <label for="profile_second_street"><?php esc_html_e('Adresse de la salle :', 'adess-resa'); ?></label><br>
            <input
                type="text"
                name="second_street"
                id="profile_second_street"
                class="regular-text autocomplete-address"
                autocomplete="off"
                placeholder="<?php esc_attr_e('N° et nom de rue', 'adess-resa'); ?>"
                value="<?php echo esc_attr($data['second_street'] ?? ''); ?>">
        <ul id="autocomplete-list-2" class="autocomplete-list"></ul>
        </p>
        <p>
            <label for="profile_second_postal_code"><?php esc_html_e('Code Postal :', 'adess-resa'); ?></label><br>
            <input
                type="text"
                name="second_postal_code"
                id="profile_second_postal_code"
                class="regular-text"
                maxlength="5"
                value="<?php echo esc_attr($data['second_postal_code'] ?? ''); ?>">
        </p>
        <p>
            <label for="profile_second_city"><?php esc_html_e('Ville :', 'adess-resa'); ?></label><br>
            <input
                type="text"
                name="second_city"
                id="profile_second_city"
                class="regular-text"
                value="<?php echo esc_attr($data['second_city'] ?? ''); ?>">
        </p>
    </div>



    <p>
        <input type="submit" class="button button-primary" value="<?php esc_attr_e('Envoyer ma demande', 'adess-resa'); ?>">
    </p>
</form>