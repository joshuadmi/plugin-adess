<?php

namespace Adess\EventManager\Front\Shortcodes;

use Adess\EventManager\Models\Organizer;
use Adess\EventManager\Repositories\OrganizerRepository;

class ProfileForm
{
    public function register()
    {
        add_shortcode('adess_profile_form', [$this, 'render']);
    }

    public function render(array $atts): string
    {
        // Si déjà connecté, on arrête tout
        if (is_user_logged_in()) {
            return '<p>' . esc_html__('Vous êtes déjà connecté — inutile de créer un nouveau profil organisateur.', 'adess-resa') . '</p>';
        }
        //        // On vérifie que l’utilisateur a un profil organisateur validé
        $formMessage = '';
        $isGuest     = true;

        // Traitement POST
        if (
            'POST' === strtoupper($_SERVER['REQUEST_METHOD'])
            && isset($_POST['adess_profile_nonce'])
            && wp_verify_nonce($_POST['adess_profile_nonce'], 'adess_profile')
        ) {
            // Si l'utilisateur n'est pas encore connecté, on crée son compte WP
            if (! is_user_logged_in()) {
                $username = sanitize_user($_POST['user_login'] ?? '');
                $email    = sanitize_email($_POST['user_email'] ?? '');
                $password = sanitize_text_field($_POST['user_pass'] ?? '');

                $user_id = wp_create_user($username, $password, $email);

                if (is_wp_error($user_id)) {
                    $formMessage = '<p class="adess-profile-error">' . esc_html($user_id->get_error_message()) . '</p>';
                    return $this->renderFormTemplate($isGuest, $formMessage);
                }

                // On connecte immédiatement le nouvel utilisateur
                wp_set_current_user($user_id);
                wp_set_auth_cookie($user_id);



                // 2) Préparation des données du profil organisateur
                $data = [
                    'user_id'          => $user_id,
                    'type'             => sanitize_text_field($_POST['entity_type']        ?? ''),
                    'name'             => sanitize_text_field($_POST['entity_name']        ?? ''),
                    // à la place de 'address' => $_POST['address'] inexistant
                    'address' => sprintf(
                        '%s %s %s',
                        sanitize_text_field($_POST['street']      ?? ''),
                        sanitize_text_field($_POST['postal_code'] ?? ''),
                        sanitize_text_field($_POST['city']        ?? '')
                    ),
                    'contact_name'     => sanitize_text_field($_POST['contact_name']      ?? ''),
                    'contact_email'    => sanitize_email($_POST['contact_email']      ?? ''),
                    'phone'            => sanitize_text_field($_POST['phone']             ?? ''),
                    'use_same_address' => isset($_POST['same_address']) && '1' === $_POST['same_address'],

                    // Choix de l’adresse de la salle
                    'second_street'     => (isset($_POST['same_address']) && '1' === $_POST['same_address'])
                        ? sanitize_text_field($_POST['street']      ?? '')
                        : sanitize_text_field($_POST['second_street']     ?? ''),

                    'second_postal_code' => (isset($_POST['same_address']) && '1' === $_POST['same_address'])
                        ? sanitize_text_field($_POST['postal_code'] ?? '')
                        : sanitize_text_field($_POST['second_postal_code'] ?? ''),

                    'second_city'       => (isset($_POST['same_address']) && '1' === $_POST['same_address'])
                        ? sanitize_text_field($_POST['city']        ?? '')
                        : sanitize_text_field($_POST['second_city']       ?? ''),
                    'status'           => 'pending',
                ];

                // Sauvegarde en base
                $repo = new OrganizerRepository();
                $id   = $repo->save(new Organizer($data));

                // 3) On redirige vers le tableau de bord
                if ($id) {
                    $formMessage = '<p class="adess-profile-success">'
                        . esc_html__('Votre profil et compte ont été créés. Vous êtes connecté.', 'adess-resa')
                        . '</p>';
                    // on peut ensuite cacher les champs WP si on le souhaite
                    $isGuest = false;
                } else {
                    $formMessage = '<p class="adess-profile-error">'
                        . esc_html__('Erreur lors de la création du profil.', 'adess-resa')
                        . '</p>';
                }
            }
        }
        return $this->renderFormTemplate($isGuest, $formMessage, $currentData ?? []);
    }

    // Chargement du template
    //       // On inclut le template de formulaire

    private function renderFormTemplate(bool $isGuest, string $formMessage, array $data): string
    {
        $viewFile = __DIR__ . '/../Views/profile-form.php';
        if (! file_exists($viewFile)) {
            return '<p>' . esc_html__('Formulaire indisponible pour le moment.', 'adess-resa') . '</p>';
        }

        // Expose les variables dans le scope du template
        $isGuest     = $isGuest;
        $formMessage = $formMessage;
        $data        = $data;

        ob_start();
        include $viewFile;
        return ob_get_clean();
    }
}
