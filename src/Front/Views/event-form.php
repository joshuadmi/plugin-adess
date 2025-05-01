<?php
namespace Adess\EventManager\Front\Shortcodes;

use Adess\EventManager\Repositories\EventRepository;
use Adess\EventManager\Repositories\OrganizerRepository;
use Adess\EventManager\Models\Event;

// Classe qui gère l’affichage et le traitement du formulaire d’événement
class EventForm
{
    // On enregistre le shortcode [adess_event_form]
    public function register()
    {
        add_shortcode('adess_event_form', [$this, 'render']);
    }

    // Cette méthode génère le HTML du formulaire (création ou édition)
    public function render(array $atts): string
    {
        // 1) On vérifie que l’utilisateur est connecté
        if (! is_user_logged_in()) {
            return '<p>Vous devez être connecté pour créer un événement.</p>';
        }

        // 2) On récupère l’ID de l’événement (0 = création)
        $atts    = shortcode_atts(['event_id' => 0], $atts, 'adess_event_form');
        $eventId = (int) $atts['event_id'];

        // 3) On s’assure que l’utilisateur a un profil organisateur validé
        $orgRepo   = new OrganizerRepository();
        $organizer = $orgRepo->findByUserId(get_current_user_id());
        if (! $organizer) {
            return '<p>Profil organisateur introuvable ou non validé.</p>';
        }

        // 4) Préparer les données existantes si on édite
        $eventRepo = new EventRepository();
        $data      = [];
        if ($eventId > 0) {
            $existing = $eventRepo->find($eventId);
            if ($existing && $existing->getOrganizerId() === $organizer->getId()) {
                $data = [
                    'id'                => $existing->getId(),
                    'type'              => $existing->getType(),
                    'title'             => $existing->getTitle(),
                    'location'          => $existing->getLocation(),
                    'start_date'        => $existing->getStartDate()->format('Y-m-d'),
                    'participant_count' => $existing->getParticipantCount(),
                    'estimated_cost'    => $existing->getEstimatedCost(),
                    'notes'             => $existing->getNotes(),
                    'status'            => $existing->getStatus(),
                ];
            }
        }

        $output = '';

        // 5) Si on a soumis le formulaire, on traite les données
        if (
            $_SERVER['REQUEST_METHOD'] === 'POST'
            && isset($_POST['adess_event_nonce'])
            && wp_verify_nonce($_POST['adess_event_nonce'], 'adess_event')
        ) {
            // On nettoie tous les champs en une passe
            $input = array_map('sanitize_text_field', $_POST);

            // On crée ou met à jour l’événement
            $event = new Event([
                'id'                => $data['id'] ?? null,
                'organizer_id'      => $organizer->getId(),
                'type'              => $input['type'],
                'title'             => $input['title'],
                'location'          => $input['location'],
                'start_date'        => $input['start_date'],
                'participant_count' => (int) $input['participant_count'],
                'estimated_cost'    => (float) $input['estimated_cost'],
                'notes'             => $input['notes'],
                'status'            => isset($data['id']) ? $data['status'] : 'pending',
            ]);

            // Sauvegarde et message de confirmation
            $savedId = $eventRepo->save($event);
            $output .= '<p>Événement enregistré avec succès ! Statut : en attente de validation.</p>';
            $data['id'] = $savedId;
        }

        // 6) Génération du formulaire HTML
        $output .= '<form method="post">';
        $output .= wp_nonce_field('adess_event', 'adess_event_nonce', true, false);

        // Champs de base : type, titre, lieu
        $fields = [
            'type'     => 'Type de prestation',
            'title'    => "Titre de l'événement",
            'location' => 'Lieu de la prestation',
        ];
        foreach ($fields as $key => $label) {
            $value   = esc_attr($data[$key] ?? '');
            $idAttr  = "adess_event_{$key}";
            $output .= "<p><label for=\"$idAttr\">$label :</label><br>";
            $output .= "<input type=\"text\" name=\"$key\" id=\"$idAttr\" value=\"$value\" required></p>";
        }

        // Date de début (un seul champ)
        $dateValue = esc_attr($data['start_date'] ?? '');
        $output   .= '<p><label for="adess_event_start_date">Date de début :</label><br>';
        $output   .= "<input type=\"date\" name=\"start_date\" id=\"adess_event_start_date\" value=\"$dateValue\" required></p>";

        // Nombre de participants
        $partValue = esc_attr($data['participant_count'] ?? 1);
        $output   .= '<p><label for="adess_event_participant_count">Nombre de participants :</label><br>';
        $output   .= "<input type=\"number\" name=\"participant_count\" id=\"adess_event_participant_count\" min=\"1\" value=\"$partValue\" required></p>";

        // Coût estimé
        $costValue = esc_attr($data['estimated_cost'] ?? '');
        $output   .= '<p><label for="adess_event_estimated_cost">Coût estimé :</label><br>';
        $output   .= "<input type=\"number\" step=\"0.01\" name=\"estimated_cost\" id=\"adess_event_estimated_cost\" value=\"$costValue\"></p>";

        // Notes complémentaires
        $notes = esc_textarea($data['notes'] ?? '');
        $output .= '<p><label for="adess_event_notes">Notes complémentaires :</label><br>';
        $output .= "<textarea name=\"notes\" id=\"adess_event_notes\">$notes</textarea></p>";

        // Bouton de soumission
        $output .= '<p><input type="submit" value="Enregistrer l’événement"></p>';
        $output .= '</form>';

        // 7) On retourne tout le HTML assemblé
        return $output;
    }
}
