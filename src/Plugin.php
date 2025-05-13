<?php

namespace Adess\EventManager;

use Adess\EventManager\Admin\Menu;
use Adess\EventManager\Front\Shortcodes\BookingForm;

// Classe principale du plugin
class Plugin
{
    public function run()
    {
        if (is_admin()) {
            // Back-office : menu et assets admin
            $menu = new Menu();
            $menu->register();
            add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
        } else {
            // Front-end : shortcode et assets front
            add_action('init', function () {
                $booking = new BookingForm();
                $booking->register();
            });
            add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
        }
    }

    /**
     * Enqueue CSS & JS assets for both front and admin
     */
    public function enqueueAssets(): void
    {
        // Styles
        wp_enqueue_style(
            'adess-resa-style',
            plugin_dir_url(__FILE__) . '../assets/css/style.css',
            [],
            '0.1.0'
        );

        // Address Autocomplete JS
        wp_enqueue_script(
            'adess-address-autocomplete',
            plugin_dir_url(__FILE__) . '../assets/js/adress-autocomplete.js',
            [],
            '1.0',
            true
        );
    }
}
