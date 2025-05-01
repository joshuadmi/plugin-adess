<?php
namespace Adess\EventManager\Front\Shortcodes;

use Adess\EventManager\Models\Organizer;
use Adess\EventManager\Repositories\OrganizerRepository;

// Classe qui gère l’affichage et le traitement du formulaire de profil organisateur
class ProfileForm
{
    // On enregistre le shortcode [adess_profile_form]
    public function register()
    {
        add_shortcode('adess_profile_form', [$this, 'render']);
    }

    // Génère et affiche le formulaire, puis traite la soumission
    public function render(array $atts): string
    {
        $output = '';

        // 1) On vérifie que l’utilisateur est connecté
        if (! is_user_logged_in()) {
            return '<p>Vous devez être connecté pour créer un profil organisateur.</p>';
        }

        // 2) Si le formulaire est soumis, on traite les données
        if (
            $_SERVER['REQUEST_METHOD'] === 'POST'
            && isset($_POST['adess_profile_nonce'])
            && wp_verify_nonce($_POST['adess_profile_nonce'], 'adess_profile')
        ) {
            // On récupère et nettoie chaque champ
            $data = [
                'user_id'          => get_current_user_id(),
                'type'             => sanitize_text_field($_POST['entity_type'] ?? ''),
                'name'             => sanitize_text_field($_POST['entity_name'] ?? ''),
                'address'          => sanitize_textarea_field($_POST['address'] ?? ''),
                'contact_name'     => sanitize_text_field($_POST['contact_name'] ?? ''),
                'contact_email'    => sanitize_email($_POST['contact_email'] ?? ''),
                'phone'            => sanitize_text_field($_POST['phone'] ?? ''),
                'default_location' => sanitize_text_field($_POST['default_location'] ?? ''),
                'status'           => 'pending', // statut par défaut
            ];

            // 3) On sauvegarde via le repository
            $repo      = new OrganizerRepository();
            $organizer = new Organizer($data);
            $id        = $repo->save($organizer);

            // 4) Message de retour
            if ($id) {
                $output .= '<p>Votre profil a bien été créé. Il sera validé par un administrateur.</p>';
            } else {
                $output .= '<p>Une erreur est survenue, veuillez réessayer.</p>';
            }
        }

        // 5) On génère le formulaire HTML
        $output .= '<form method="post">';
        $output .= wp_nonce_field('adess_profile', 'adess_profile_nonce', true, false);

        // Type d’entité (entreprise ou collectivité)
        $output .= '<p><label for="entity_type">Type d’entité :</label><br>';
        $output .= '<select name="entity_type" id="entity_type" required>';
        $output .= '<option value="company">Entreprise</option>';
        $output .= '<option value="collectivity">Collectivité</option>';
        $output .= '</select></p>';

        // Nom de l’entité
        $output .= '<p><label for="entity_name">Nom de l’entité :</label><br>';
        $output .= '<input type="text" name="entity_name" id="entity_name" required></p>';

        // Adresse
        $output .= '<p><label for="address">Adresse :</label><br>';
        $output .= '<textarea name="address" id="address" required></textarea></p>';

        // Nom du contact
        $output .= '<p><label for="contact_name">Nom du contact :</label><br>';
        $output .= '<input type="text" name="contact_name" id="contact_name"></p>';

        // Email du contact
        $output .= '<p><label for="contact_email">Email du contact :</label><br>';
        $output .= '<input type="email" name="contact_email" id="contact_email"></p>';

        // Téléphone du contact
        $output .= '<p><label for="phone">Téléphone :</label><br>';
        $output .= '<input type="text" name="phone" id="phone"></p>';

        // Lieu de prestation par défaut
        $output .= '<p><label for="default_location">Lieu de prestation par défaut :</label><br>';
        $output .= '<input type="text" name="default_location" id="default_location"></p>';

        // Bouton d’envoi
        $output .= '<p><input type="submit" value="Envoyer ma demande"></p>';
        $output .= '</form>';

        // 6) On retourne le HTML complet
        return $output;
    }
}
