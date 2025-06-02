<?php

namespace Adess\EventManager\Front\Shortcodes;

use Adess\EventManager\Repositories\OrganizerRepository;

class EditProfileForm
{
    public function register()
    {
        add_shortcode('adess_edit_profile', [$this, 'render']);
    }

    public function render(): string
    {
        if (! is_user_logged_in()) {
            return '<p>' . esc_html__('Vous devez être connecté pour modifier votre profil.', 'adess-resa') . '</p>';
        }

        $formMessage = '';
        $user_id     = get_current_user_id();
        $repo        = new OrganizerRepository();
        $org         = $repo->findByUserId($user_id);

        // Traitement POST
        if (
            'POST' === strtoupper($_SERVER['REQUEST_METHOD'])
            && isset($_POST['adess_edit_nonce'])
            && wp_verify_nonce($_POST['adess_edit_nonce'], 'adess_edit_profile')
        ) {
            // sanitize les champs
            $data = [
                'type'             => sanitize_text_field($_POST['entity_type']   ?? ''),
                'name'             => sanitize_text_field($_POST['entity_name']   ?? ''),
                'address'          => sprintf(
                    '%s %s %s',
                    sanitize_text_field($_POST['street']      ?? ''),
                    sanitize_text_field($_POST['postal_code'] ?? ''),
                    sanitize_text_field($_POST['city']        ?? '')
                ),
                'contact_name'     => sanitize_text_field($_POST['contact_name']  ?? ''),
                'contact_email'    => sanitize_email($_POST['contact_email'] ?? ''),
                'phone'            => sanitize_text_field($_POST['phone']         ?? ''),
                'second_street'    => sanitize_text_field($_POST['second_street']     ?? ''),
                'second_postal_code' => sanitize_text_field($_POST['second_postal_code'] ?? ''),
                'second_city'      => sanitize_text_field($_POST['second_city']       ?? ''),
            ];

            // on garde l’id de l’ancien pour forcer un UPDATE
            $data['id']   = $org->getId();
            $data['user_id'] = get_current_user_id();
            // (les autres clés sont déjà dans $data grâce à ton code POST)
            // on (re)construit un nouvel Organizer avec TOUTES les données :
            $newOrganizer = new \Adess\EventManager\Models\Organizer($data);

            // on sauve
            if ((new \Adess\EventManager\Repositories\OrganizerRepository())
                ->save($newOrganizer)
            ) {
                $formMessage = '<p class="adess-profile-success">'
                    . esc_html__('Profil mis à jour !', 'adess-resa')
                    . '</p>';
                // on peut recharger $org pour pré‐remplir à nouveau si besoin
                $org = (new OrganizerRepository())->findByUserId(get_current_user_id());
            } else {
                $formMessage = '<p class="adess-profile-error">'
                    . esc_html__('Erreur lors de la mise à jour.', 'adess-resa')
                    . '</p>';
            }
        }

        // Prépare les valeurs à injecter dans le template
        $currentData = [];

        if ($org) {
            // 1) Adresse complète
            $fullAddress = $org->getAddress() ?? '';
            // 2) Découpe en 3 parties : [rue, code postal, ville]
            $parts = array_pad(explode(' ', $fullAddress, 3), 3, '');

            $currentData = [
                'entity_type'        => $org->getType(),
                'entity_name'        => $org->getName(),
                'address'       => $org->getAddress(),      // adresse complète
                'contact_name'       => $org->getContactName(),
                'contact_email'      => $org->getContactEmail(),
                'phone'              => $org->getPhone(),
                'second_street'      => $org->getSecondStreet(),
                'second_postal_code' => $org->getSecondPostalCode(),
                'second_city'        => $org->getSecondCity(),
            ];
        }


        return $this->renderTemplate($formMessage, $currentData);
    }

    private function renderTemplate(string $formMessage, array $data): string
    {
        $view = __DIR__ . '/../Views/edit-profile-form.php';
        if (! file_exists($view)) {
            return '<p>' . esc_html__('Formulaire indisponible.', 'adess-resa') . '</p>';
        }
        ob_start();
        include $view;
        return ob_get_clean();
    }
}

// initialisation
new EditProfileForm();
