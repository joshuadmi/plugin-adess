<?php

namespace Adess\EventManager\Front\Shortcodes;

use Adess\EventManager\Repositories\EventRepository;

// génère dynamiquement la liste d'événements validés
class MainMenu
{
    // Enregistre le shortcode [adess_main_menu]  pour pouvoir l'utiliser dans une page
    public function register()
    {
        add_shortcode('adess_main_menu', [$this, 'render']);
    }

    // Rendu dynamique de la liste d'événements: génère le HTML du menu et de la liste d'événements
    public function render()
    {

        // Récupère uniquement les événements validés
        // Récupération de tous les événements depuis la base de données
        //    (via le repository qui utilise PDO en interne)
        $repo     = new EventRepository(); // instancie le repository
        $all      = $repo->findAll(); // récupère tous les événements
        $articles = ''; // initialise la variable pour le HTML
        $index    = 0; // initialise l'index pour alterner les classes CSS

        // Parcours de chaque événement et filtrage des validés
        foreach ($all as $event) {

            // Si le statut n'est pas "validated", on ne l'affiche pas
            if ($event->getStatus() !== 'validated') {
                continue;
            }
            $index++;

                // Alternance de classes CSS pour différencier pairs/impairs

            $oddEven = ($index % 2 === 0) ? 'adess-event--even' : 'adess-event--odd';
            $date    = $event->getStartDate()->format('Y-m-d');
            $place   = esc_html($event->getLocation());
            $title   = esc_html($event->getTitle());
            $remaining = max(0, (int) $event->getParticipantCount());

            // Construction du bloc HTML pour l'événement courant: qu'est ce qu'on affiche ?
            $articles .= '<article class="adess-event ' . $oddEven . '">';
            $articles .= '<div class="adess-event__info">'
                . '<h2>' . $title . '</h2>'
                . '<p class="adess-event__meta"><strong>Date :</strong> ' . $date . '</p>'
                . '<p class="adess-event__meta"><strong>Lieu :</strong> ' . $place . '</p>'
                . '<p class="adess-event__meta"><strong>Places disponibles :</strong> ' . $remaining . '</p>'
                . '<div class="adess-event__actions">'
                . '<a href="' . site_url('/?shortcode=adess_event_detail&event_id=' . $event->getId()) . '">Détails</a>'
                . '<a href="' . site_url('/?shortcode=adess_booking_form&event_id=' . $event->getId()) . '">S\'inscrire</a>'
                . '</div>'
                . '</div>';
            $articles .= '</article>';
        }

        // Encapsule le tout dans une section
        $html  = '<nav class="adess-menu">'
            . '<p>' . $loginLink . '</p>'
            . $profileLink
            . $eventFormLink
            . '</nav>';

        $html .= '<section class="adess-events-list">' . $articles . '</section>';
        return $html;
    }
}
