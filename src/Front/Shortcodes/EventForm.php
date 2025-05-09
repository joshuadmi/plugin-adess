<?php

namespace Adess\EventManager\Front\Shortcodes;

use Adess\EventManager\Models\Event;
use Adess\EventManager\Repositories\EventRepository;
use Adess\EventManager\Repositories\OrganizerRepository;

class EventForm
{
    public function register(): void
    {
        add_shortcode('adess_event_form', [$this, 'render']);
    }

    public function render(array $atts): string
    {


        // 1) Vérif. profil organisateur (sauf admin)
        $organizer = null;
        if (! current_user_can('manage_options')) {
            if (! is_user_logged_in()) {
                return '<p>' . esc_html__('Vous devez être connecté pour proposer un événement.', 'adess-resa') . '</p>';
            }
            $organizer = (new OrganizerRepository())->findByUserId(get_current_user_id());
            if (! $organizer) {
                return '<p>' . esc_html__('Profil organisateur introuvable ou non validé.', 'adess-resa') . '</p>';
            }
        }

        // 2) Préparation des données pour GET (édition) ou valeurs par défaut
        $atts    = shortcode_atts(
            [
                'event_id' => 0,
                'context' => 'front',
            ],
            $atts,
            'adess_event_form'
        );
        $eventId = (int) $atts['event_id'];
        $context = $atts['context'];
        $repo    = new EventRepository();
        $data    = [
            'title'             => '',
            'location'          => '',
            'start_date'        => '',
            'participant_count' => 1,
            'estimated_cost'    => '',
            'subsidy_amount'    => '',
            'notes'             => '',
            'status'            => 'pending',
        ];

        if ($eventId > 0) {
            $existing = $repo->find($eventId);
            if ($existing && (current_user_can('manage_options') || $existing->getOrganizerId() === $organizer->getId())) {
                $data = [
                    'id'                => $existing->getId(),
                    'title'             => $existing->getTitle(),
                    'location'          => $existing->getLocation(),
                    'start_date'        => $existing->getStartDate()->format('Y-m-d'),
                    'participant_count' => $existing->getParticipantCount(),
                    'estimated_cost'    => $existing->getEstimatedCost(),
                    'subsidy_amount'    => $existing->getSubsidyAmount(),
                    'notes'             => $existing->getNotes(),
                    'status'            => $existing->getStatus(),
                ];
            }
        }

        $formMessage = '';

        // 3) Traitement du POST
        if (
            'POST' === strtoupper($_SERVER['REQUEST_METHOD'])
            && isset($_POST['adess_event_nonce'])
            && wp_verify_nonce($_POST['adess_event_nonce'], 'adess_event')
        ) {
            $input = array_map('sanitize_text_field', $_POST);

            $status = current_user_can('manage_options')
                ? ($input['status'] ?? 'pending')
                : 'pending';
            // si on est en admin, on prend la subvention envoyée
            if ($context === 'admin') {
                $data['subsidy_amount'] = floatval($input['subsidy_amount'] ?? 0);
            }

            $event = new Event([
                'id'                => $data['id'] ?? null,
                'organizer_id'      => $organizer ? $organizer->getId() : null,
                'title'             => $input['title'] ?? '',
                'location'          => $input['location'] ?? '',
                'start_date'        => $input['start_date'] ?? '',
                'participant_count' => intval($input['participant_count'] ?? 1),
                'estimated_cost'    => floatval($input['estimated_cost'] ?? 0),
                'subsidy_amount'    => floatval($input['subsidy_amount'] ?? 0),
                'notes'             => $input['notes'] ?? '',
                'status'            => $status,
            ]);

            $savedId = $repo->save($event);
            if ($savedId) {
                $formMessage = '<p class="notice notice-success">' .
                    esc_html__('Événement enregistré avec succès.', 'adess-resa') .
                    '</p>';
                $data['id']     = $savedId;
                $data['status'] = $status;
                $data['subsidy_amount'] = $event->getSubsidyAmount();

                
            } else {
                $formMessage = '<p class="notice notice-error">' .
                    esc_html__('Erreur lors de l’enregistrement, réessayez.', 'adess-resa') .
                    '</p>';
            }
        }

        // 4) On délègue le rendu au template
        return $this->renderFormTemplate($formMessage, $data, $eventId, $context);
    }

    private function renderFormTemplate(string $formMessage, array $data, int $eventId, string $context): string
    {

        $viewFile = __DIR__ . '/../Views/event-form.php';
        if (file_exists($viewFile)) {
            ob_start();
            // variables disponibles : $formMessage, $data, $eventId et $context
            include $viewFile;
            return ob_get_clean();
        }

        return '<p>' . esc_html__('Formulaire indisponible.', 'adess-resa') . '</p>';
    }
}
