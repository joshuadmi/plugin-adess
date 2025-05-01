<?php

namespace Adess\EventManager\Front\Shortcodes;

use Adess\EventManager\Models\Organizer;
use Adess\EventManager\Repositories\OrganizerRepository;

// Class ProfileForm: formulaire de création de profil organisateur + création utilisateur WP

class ProfileForm
{

    // Enregistre le shortcode.
    public function register()
    {
        add_shortcode('adess_profile_form', array($this, 'render'));
    }

    // Class ProfileForm: formulaire de création de profil organisateur + création utilisateur WP
    public function render($atts)
    {
        if (is_user_logged_in()) {
            return '<p>Vous êtes déjà connecté — inutile de créer un nouveau profil organisateur.</p>';
        }
        $output = '';

        // Traitement de la soumission
        if (
            $_SERVER['REQUEST_METHOD'] === 'POST'
            && isset($_POST['adess_profile_nonce'])
            && wp_verify_nonce($_POST['adess_profile_nonce'], 'adess_profile')
        ) {

        
            // Si non connecté, créer un utilisateur WP
            if (!is_user_logged_in()) {
                $email    = sanitize_email($_POST['user_email'] ?? '');
                $username = sanitize_user($_POST['user_login'] ?? '');
                $password = sanitize_text_field($_POST['user_pass'] ?? '');
                $user_id  = wp_create_user($username, $password, $email);
                if (is_wp_error($user_id)) {
                    $output .= '<p>Erreur création compte: ' . esc_html($user_id->get_error_message()) . '</p>';
                    return $output;
                }
                // auto-login
                wp_set_current_user($user_id);
                wp_set_auth_cookie($user_id);


                $data['user_id'] = $user_id;
            } else {
                $data['user_id'] = get_current_user_id();
            }

            // Récupération et sanitisation des autres champs
            $data += [
                'type'             => sanitize_text_field($_POST['entity_type'] ?? ''),
                'name'             => sanitize_text_field($_POST['entity_name'] ?? ''),
                'address'          => sanitize_textarea_field($_POST['address'] ?? ''),
                'contact_name'     => sanitize_text_field($_POST['contact_name'] ?? ''),
                'contact_email'    => sanitize_email($_POST['contact_email'] ?? ''),
                'phone'            => sanitize_text_field($_POST['phone'] ?? ''),
                'default_location' => sanitize_text_field($_POST['default_location'] ?? ''),
                'status'           => 'pending',
            ];

            // Sauvegarde
            $repo = new OrganizerRepository();
            $organizer = new Organizer($data);
            $id = $repo->save($organizer);

            if ($id) {
                $output .= '<p>Votre profil a bien été créé. Il sera validé par un administrateur.</p>';
            } else {
                $output .= '<p>Une erreur est survenue, veuillez réessayer.</p>';
            }
        }

        // Affichage du formulaire
        $output .= '<form method="post">';
        $output .= wp_nonce_field('adess_profile', 'adess_profile_nonce', true, false);


        // Si non connecté, afficher champs création compte WP
        if (!is_user_logged_in()) {
            $output .= '<p><label for="user_login">Identifiant :</label><br>';
            $output .= '<input type="text" name="user_login" id="user_login" required></p>';
            $output .= '<p><label for="user_email">Email :</label><br>';
            $output .= '<input type="email" name="user_email" id="user_email" required></p>';
            $output .= '<p><label for="user_pass">Mot de passe :</label><br>';
            $output .= '<input type="password" name="user_pass" id="user_pass" required></p>';
        }
        $output .= '<p><label for="entity_type">Type d’entité :</label><br>';
        $output .= '<select name="entity_type" id="entity_type" required>';
        $output .= '<option value="company">Entreprise</option>';
        $output .= '<option value="collectivity">Collectivité</option>';
        $output .= '</select></p>';

        $output .= '<p><label for="entity_name">Nom de l’entité :</label><br>';
        $output .= '<input type="text" name="entity_name" id="entity_name" required></p>';

        $output .= '<p><label for="address">Adresse :</label><br>';
        $output .= '<textarea name="address" id="address" required></textarea></p>';

        $output .= '<p><label for="contact_name">Nom du contact :</label><br>';
        $output .= '<input type="text" name="contact_name" id="contact_name"></p>';

        $output .= '<p><label for="contact_email">Email du contact :</label><br>';
        $output .= '<input type="email" name="contact_email" id="contact_email"></p>';

        $output .= '<p><label for="phone">Téléphone :</label><br>';
        $output .= '<input type="text" name="phone" id="phone"></p>';

        $output .= '<p><label for="default_location">Lieu par défaut :</label><br>';
        $output .= '<input type="text" name="default_location" id="default_location"></p>';

        $output .= '<p><input type="submit" value="Envoyer ma demande"></p>';
        $output .= '</form>';

        return $output;
    }
}
