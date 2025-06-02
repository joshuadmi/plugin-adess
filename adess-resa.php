<?php

/**
 * Plugin Name: ADESS Resa
 * Plugin URI:  https://plugin-adess.local
 * Description: Gestion des événements découplée pour ADESS
 * Version:     0.1.0
 * Author:      Joshua
 * Text Domain: adess-resa
 */


// condition pour empêcher l’accès direct au fichier
if (! defined('ABSPATH')) {
    exit;
}

// Activation / désactivation

// permet à WordPress d’​exécuter automatiquement des méthodes de la classe Installer quand le plugin est activé.

// appelle la méthode activate() de la classe Adess\EventManager\Installer.
register_activation_hook(
    __FILE__,   // __FILE__ : le chemin vers le fichier principal (adess-resa.php)
    [\Adess\EventManager\Installer::class, 'activate']
);
register_deactivation_hook(
    __FILE__,
    [\Adess\EventManager\Installer::class, 'deactivate']
);

// Chargement des classes communes
require_once __DIR__ . '/src/Plugin.php'; // contient la méthode run() qui démarre le plugin
require_once __DIR__ . '/src/Installer.php'; // contient les méthodes activate() et deactivate()


// Chargement des classes de modèles
require_once __DIR__ . '/src/Models/Organizer.php';
require_once __DIR__ . '/src/Models/Event.php';
require_once __DIR__ . '/src/Models/Reservation.php';

// Chargement des classes de gestionnaires de données
//Ces fichiers utilisent PDO pour lire/écrire dans les tables personnalisées.
// Cela correspond au "M" du MVC (Modèle-Vue-Contrôleur).

require_once __DIR__ . '/src/Repositories/OrganizerRepository.php';
require_once __DIR__ . '/src/Repositories/EventRepository.php';
require_once __DIR__ . '/src/Repositories/ReservationRepository.php';


// Chargement des classes de gestionnaires de formulaires
// permettent aux utilisateurs de créer des profils, proposer des événements, ou réserver.
require_once __DIR__ . '/src/Front/Shortcodes/ProfileForm.php';
require_once __DIR__ . '/src/Front/Shortcodes/EventForm.php';
require_once __DIR__ . '/src/Front/Shortcodes/EditProfileForm.php';
require_once __DIR__ . '/src/Front/Shortcodes/BookingForm.php';
require_once __DIR__ . '/src/Front/Shortcodes/MainMenu.php';



// Ces fichiers sont uniquement chargés si on est dans l’admin WordPress : logique Changé  dans plugins loaded
// Les fichiers ici gèrent les tableaux dans le back-office (WP_List_Table), ainsi que les menus d’administration.
require_once __DIR__ . '/src/Admin/Menu.php';
require_once __DIR__ . '/src/Admin/ListTable/OrganizerTable.php';
require_once __DIR__ . '/src/Admin/ListTable/EventTable.php';
require_once __DIR__ . '/src/Admin/ListTable/ReservationTable.php';


// Initialisation du plugin (menu & shortcodes)
//Demande WordPress d’exécuter la fonction anonyme après avoir chargé tous les plugins. C’est le même point d’accroche qu’avant.
add_action('plugins_loaded', function () {

    (new \Adess\EventManager\Plugin())->run();
    // shortcodes front
    (new \Adess\EventManager\Front\Shortcodes\ProfileForm())->register();
    (new \Adess\EventManager\Front\Shortcodes\EventForm())->register();
    (new \Adess\EventManager\Front\Shortcodes\EditProfileForm())->register();
});

use Adess\EventManager\Front\Shortcodes\MainMenu;

add_action('init', function () {
    // Enregistrement du menu principal
    (new MainMenu())->register();
});


// Permet de passer un paramètre d'URL 'event_id' à WordPress
add_filter('query_vars', function ($vars) {
    $vars[] = 'event_id';
    return $vars;
});

// 2) when WordPress is about to render page content, if we're on our "reserver" page,
//    capture the event_id and run the booking‑form shortcode instead of the normal content.

// Quand WordPress est sur le point de rendre le contenu de la page, 
//    si on est sur la page "reserver", capture l'event_id et exécute le shortcode du formulaire de réservation
add_filter('the_content', function ($content) {
    if (is_page('reserver') && ($id = get_query_var('event_id'))) {
        return do_shortcode(sprintf('[adess_booking_form event_id="%d"]', (int)$id));
    }
    return $content;
});

// La même logique pour la page Détails
add_filter('the_content', function ($content) {
    if (is_page('details') && $id = get_query_var('event_id')) {
        // on injecte l’ID puis on passe UNE SEULE chaîne à do_shortcode()
        $shortcode = sprintf(
            '[adess_event_detail event_id="%d"]',
            intval($id)
        );
        return do_shortcode($shortcode);
    }
    return $content;
});
