<?php

/** @var string $formMessage */
/** @var array  $data */
?>

<?php if ($formMessage): ?>
    <?php echo $formMessage; ?>
<?php endif; ?>

<form method="post" class="adess-edit-profile">
    <?php wp_nonce_field('adess_edit_profile', 'adess_edit_nonce'); ?>

    <!-- Exemple de champ pré-rempli -->
    <p>
        <label><?php esc_html_e('Nom de l’entité :', 'adess-resa'); ?><br>
            <input
                type="text"
                name="entity_name"
                value="<?php echo esc_attr($data['entity_name'] ?? ''); ?>"
                required>
        </label>
    </p>



    <!-- Adresse  -->
    <p>
        <label for="address">
            <?php esc_html_e('Adresse complète :', 'adess-resa'); ?><br>
            <input
                type="text"
                id="address"
                name="address"
                value="<?php echo esc_attr($data['address'] ?? ''); ?>"
                required>
        </label>
    </p>

    <p>
        <label for="default_location">
            <?php esc_html_e('Adresse de la salle :', 'adess-resa'); ?>
        </label><br>
        <input
            type="text"
            name="default_location"
            id="default_location"
            value="<?php
                    // on assemble à l’affichage
                    echo esc_attr(
                        trim(
                            ($data['second_street'] ?? '') . ' ' .
                                ($data['second_postal_code'] ?? '') . ' ' .
                                ($data['second_city'] ?? '')
                        )
                    );
                    ?>"
            placeholder="<?php esc_attr_e('Rue, Code postal, Ville', 'adess-resa'); ?>"
            required>
    </p>
    <p>
        <label for="contact_name">
            <?php esc_html_e('Contact (nom) :', 'adess-resa'); ?><br>
            <input
                type="text"
                id="contact_name"
                name="contact_name"
                value="<?php echo esc_attr($data['contact_name'] ?? ''); ?>">
        </label>
    </p>

    <p>
        <label for="contact_email">
            <?php esc_html_e('Contact (email) :', 'adess-resa'); ?><br>
            <input
                type="email"
                id="contact_email"
                name="contact_email"
                value="<?php echo esc_attr($data['contact_email'] ?? ''); ?>">
        </label>
    </p>

    <p>
        <label for="phone">
            <?php esc_html_e('Téléphone :', 'adess-resa'); ?><br>
            <input
                type="text"
                id="phone"
                name="phone"
                value="<?php echo esc_attr($data['phone'] ?? ''); ?>">
        </label>
    </p>



    <!-- répète pour chaque champ -->

    <p>
        <button type="submit" class="button button-primary">
            <?php esc_html_e('Mettre à jour mon profil', 'adess-resa'); ?>
        </button>
    </p>
</form>