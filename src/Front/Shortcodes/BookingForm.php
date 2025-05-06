<?php

namespace Adess\EventManager\Front\Shortcodes;

use Adess\EventManager\Repositories\EventRepository;
use Adess\EventManager\Repositories\ReservationRepository;
use Adess\EventManager\Models\Reservation;

// Classe BookingForm : gère le formulaire de réservation pour un événement
class BookingForm
{
    // Enregistre le shortcode [adess_booking_form]
    public function register()
    {
        add_shortcode('adess_booking_form', [$this, 'render']);
    }

    // Affiche et traite le formulaire de réservation
    public function render($atts)
    {
        // Récupère l'ID de l'événement passé en attribut du shortcode
        $atts    = shortcode_atts(['event_id' => 0], $atts, 'adess_booking_form');
        $eventId = intval($atts['event_id']);

        // Charge l'événement depuis la base
        $eventRepo = new EventRepository();
        $event     = $eventRepo->find($eventId);
        if (! $event) {
            // Si l'événement n'existe pas, affiche un message
            return '<p>Événement introuvable.</p>';
        }

        $output = '';
        // Si le formulaire est soumis (méthode POST et nonce valide)
        if (
            'POST' === strtoupper($_SERVER['REQUEST_METHOD'])
            && isset($_POST['adess_booking_nonce'])
            && wp_verify_nonce($_POST['adess_booking_nonce'], 'adess_booking')
        ) {
            // Récupère et sécurise les données postées
            $guestName      = sanitize_text_field($_POST['guest_name'] ?? '');
            $guestFirstname = sanitize_text_field($_POST['guest_firstname'] ?? '');
            $guestPostcode  = sanitize_text_field($_POST['guest_postcode'] ?? '');
            $guestEmail     = sanitize_email($_POST['guest_email'] ?? '');
            $places         = max(1, intval($_POST['places'] ?? 1));

            // Crée et enregistre la réservation
            $reservationRepo = new ReservationRepository();
            $reservation     = new Reservation([
                'event_id'       => $eventId,
                'guest_name'     => $guestName,
                'guest_firstname'=> $guestFirstname,
                'guest_postcode' => $guestPostcode,
                'guest_email'    => $guestEmail,
                'places'         => $places,
                'status'         => 'pending', // statut initial
            ]);
            $reservationRepo->save($reservation);

            // Message de confirmation
            $output .= '<p>Votre réservation a été enregistrée. Merci !</p>';
        }

        // Inclusion du template de vue si disponible
        $viewFile = __DIR__ . '/../Views/booking-form.php';

        if (file_exists($viewFile)) {
            ob_start();
            // Rend la variable $event disponible dans la vue
            include $viewFile;
            $output .= ob_get_clean();
        } else {
            // Si le fichier de vue est manquant, affiche un fallback
            error_log('BookingForm: template introuvable à ' . $viewFile);
            $output .= '<p>Le formulaire de réservation n\'est pas disponible pour le moment.</p>';
        }

        return $output;
    }
}
