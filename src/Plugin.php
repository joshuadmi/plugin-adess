<?php

namespace Adess\EventManager; // pour éviter les conflits de noms

use Adess\EventManager\Admin\Menu;
use Adess\EventManager\Front\Shortcodes\BookingForm;

// Classe principale du plugin
// Elle initialise le plugin et enregistre les hooks WordPress
class Plugin
{

    // Méthode d’exécution principale du plugin
    // Elle est appelée au chargement du plugin pour lancer les différentes fonctionnalités
    public function run()
    {
        // Enregistrement du menu dans le back-office uniquement
        // Cela évite de charger inutilement du code côté visiteur
        if (is_admin()) {
            $menu = new Menu();
            $menu->register();
        }

        add_action('init', function () {
            $booking = new BookingForm();
            $booking->register();
        });
        add_action('wp_enqueue_scripts', [$this, 'enqueueStyles']);
    }

    public function enqueueStyles(): void
    {
        wp_enqueue_style(
            'adess-resa-style',                                   // handle
            plugin_dir_url(__FILE__) . '../assets/css/style.css',    // URL du fichier
            [],                                                   // dépendances
            '0.1.0'                                               // version
        );
    }
}
