<?php

namespace Adess\EventManager\Front\Shortcodes;

use Adess\EventManager\Repositories\EventRepository;
use Adess\EventManager\Repositories\OrganizerRepository;
use Adess\EventManager\Models\Event;

class EventForm
{
    // Enregistre le shortcode
    public function register()
    {
        add_shortcode('adess_event_form', [$this, 'render']);
    }

    // Affiche ou traite le formulaire
    public function render(array $atts): string
    {
        // 1) Vérification profil organisateur (sauf si admin)
        $organizer = null;          // défaut
        if (! current_user_can('manage_options')) {
            if (! is_user_logged_in()) {
                return '<p>Vous devez être connecté pour créer un événement.</p>';
            }
            $organizer = (new OrganizerRepository())->findByUserId(get_current_user_id());
            if (! $organizer) {
                return '<p>Profil organisateur introuvable ou non validé.</p>';
            }
        }

        // 2) Préparation des données (édition éventuelle)
        $atts     = shortcode_atts(['event_id' => 0], $atts);
        $eventId  = (int) $atts['event_id'];
        $repo     = new EventRepository();
        $data     = [];             // champs pré-remplis

        if ($eventId) {
            $existing = $repo->find($eventId);
            if ($existing && (current_user_can('manage_options') || $existing->getOrganizerId() === $organizer->getId())) {
                $data = [
                    'id'                => $existing->getId(),
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

        // 3) Soumission
        if (
            $_SERVER['REQUEST_METHOD'] === 'POST' &&
            isset($_POST['adess_event_nonce']) &&
            wp_verify_nonce($_POST['adess_event_nonce'], 'adess_event')
        ) {
            $input = array_map('sanitize_text_field', $_POST);

            // Détermine le statut : admin → valeur du select, sinon pending
            $status = current_user_can('manage_options')
                ? ($input['status'] ?? 'pending')
                : 'pending';

            $event = new Event([
                'id'                => $data['id']        ?? null,
                'organizer_id'      => $organizer?->getId(),
                'title'             => $input['title'],
                'location'          => $input['location'],
                'start_date'        => $input['start_date'],
                'participant_count' => (int) $input['participant_count'],
                'estimated_cost'    => (float) $input['estimated_cost'],
                'notes'             => $input['notes'],
                'status'            => $status,
            ]);

            $savedId = $repo->save($event);
            $data['id'] = $savedId;                      // pour un éventuel 2ᵉ reload
            $data['status'] = $status;

            $output .= '<p class="notice notice-success">Événement enregistré.</p>';
        }

        // 4) Options d’événement + calcul coût
        $options = [
            'Sensibilisation aux premiers secours'    => 50,
            'Sensibilisation cyber-sécurité'          => 60,
            'Atelier pratique Self Defense'           => 75,
            'Conférence risques de la route'          => 65,
            'Sécurité sur chantier'                   => 80,
            'Sécurité incendie'                       => 70,
            'Sécurité sur la route'                   => 55,
        ];

        // 5) Formulaire HTML
        $output .= '<form method="post" id="adess-event-form">';
        $output .= wp_nonce_field('adess_event', 'adess_event_nonce', true, false);

        // Sélect évènement
        $output .= '<p><label>Événement :</label><br><select name="title" id="adess_event_title" required>';
        foreach ($options as $label => $unit) {
            $sel = selected($label, $data['title'] ?? '', false);
            $output .= "<option value=\"" . esc_attr($label) . "\" data-unit-cost=\"$unit\" $sel>" . esc_html($label) . '</option>';
        }
        $output .= '</select></p>';

        // Autres champs
        $output .= field('Lieu de la prestation', 'location', $data['location'] ?? '');
        $output .= field('Date de début',         'start_date', $data['start_date'] ?? '', 'date');
        $output .= field('Nombre de participants', 'participant_count', $data['participant_count'] ?? 1, 'number');
        $output .= field('Coût estimé',           'estimated_cost', $data['estimated_cost'] ?? '', 'number', 'step="0.01" readonly');

        // Select Statut uniquement admin
        if (current_user_can('manage_options')) {
            $output .= '<p><label>Statut :</label><br><select name="status">';
            foreach (['pending' => 'En attente', 'validated' => 'Validé', 'cancelled' => 'Annulé'] as $val => $lab) {
                $output .= '<option value="' . $val . '"' . selected($val, $data['status'] ?? 'pending', false) . ">$lab</option>";
            }
            $output .= '</select></p>';
        }

        // Notes
        $output .= '<p><label>Notes :</label><br><textarea name="notes">' . esc_textarea($data['notes'] ?? '') . '</textarea></p>';
        $output .= '<p><input type="submit" value="Enregistrer"></p></form>';

        // Script JS de calcul de coût
        $json = wp_json_encode($options);
        $output .= "<script>
            (function(){
                const prices = $json;
                const form = document.getElementById('adess-event-form');
                if (!form) return;
                const sel  = form.querySelector('#adess_event_title');
                const qty  = form.querySelector('#participant_count');
                const cost = form.querySelector('#estimated_cost');
                function calc(){
                    const unit = parseFloat(sel.selectedOptions[0].dataset.unitCost||0);
                    const n    = parseInt(qty.value||1,10);
                    cost.value = (unit*n).toFixed(2);
                }
                sel.addEventListener('change', calc);
                qty.addEventListener('input', calc);
                calc();
            })();
        </script>";

        return $output;
    }
}

// Petit helper pour générer un <p><label>… input</p>
function field(string $label, string $name, $value, string $type = 'text', string $extra = ''): string
{
    $id = 'adess_event_' . $name;
    $value = esc_attr($value);
    return "<p><label for=\"$id\">$label :</label><br><input type=\"$type\" name=\"$name\" id=\"$id\" value=\"$value\" $extra></p>";
}
