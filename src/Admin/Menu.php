<?php

namespace Adess\EventManager\Admin;

use Adess\EventManager\Admin\ListTable\OrganizerTable;
use Adess\EventManager\Admin\ListTable\EventTable;
use Adess\EventManager\Admin\ListTable\ReservationTable;
use Adess\EventManager\Repositories\OrganizerRepository;
use Adess\EventManager\Models\Organizer;
use Adess\EventManager\Admin\ListTable\SubsidyTable;


error_log('LOADED Admin/Menu.php');


// Classe Menu: gère les pages du back-office ADESS Resa
class Menu
{
    // Initialise les hooks pour le menu et l'édition des organisateurs
    public function register()
    {
        add_action('admin_menu', [$this, 'addAdminPages']);
        add_action('admin_init', [$this, 'handleOrganizerEdit']);
    }

    // Déclare les pages et sous-pages du menu
    public function addAdminPages()
    {
        // Page principale
        add_menu_page(
            'ADESS Resa',              // Titre de la page
            'ADESS Resa',              // Titre du menu
            'manage_options',          // Capability
            'adess_dashboard',         // Slug
            [$this, 'renderDashboard'], // Callback
            'dashicons-calendar-alt',  // Icône
            6                          // Position
        );

        // Sous-page Organisateurs (liste)
        add_submenu_page(
            'adess_dashboard',         // Parent slug
            'Organisateurs',           // Page title
            'Organisateurs',           // Menu title
            'manage_options',          // Capability
            'adess_organizer_list',    // Slug
            [$this, 'renderOrganizers']
        );

        // Sous-page Édition organisateur (cachée)
        add_submenu_page(
            'adess_dashboard',              // Parent slug
            'Éditer un organisateur',       // Page title
            '',                              // Menu title vide
            'manage_options',               // Capability
            'adess_organizer_edit',         // Slug
            [$this, 'renderOrganizerEdit']  // Callback
        );

        // Sous-page Événements (liste)
        add_submenu_page(
            'adess_dashboard',
            'Événements',
            'Événements',
            'manage_options',
            'adess_event_list',
            [$this, 'renderEvents']
        );

        // Sous-page Édition d'un événement (cachée)
        add_submenu_page(
            'adess_dashboard',
            'Éditer un événement',
            '',
            'manage_options',
            'adess_event_edit',
            [$this, 'renderEventEdit']
        );

        // Sous-page Réservations (liste)
        add_submenu_page(
            'adess_dashboard',
            'Réservations',
            'Réservations',
            'manage_options',
            'adess_reservation_list',
            [$this, 'renderReservations']
        );

        // Sous-page Édition d’une réservation (cachée)
        add_submenu_page(
            'adess_dashboard',               // parent slug
            'Éditer une réservation',        // page title
            '',                              // menu title (vide pour masquer du menu)
            'manage_options',                // capability
            'adess_reservation_edit',        // slug
            [$this, 'renderReservationEdit'] // callback
        );


        // Sous-page subventions (cachée)
        add_submenu_page(
            'adess_dashboard',      // parent slug = ADESS Resa
            'Subventions',          // Page title
            'Subventions',          // Menu label
            'manage_options',       // Capability
            'adess_subsidy_list',   // Slug
            [$this, 'renderSubsidies'] // Méthode qui appellera SubsidyTable
        );
    }

    // Gère la soumission du formulaire d'édition d'un organisateur
    public function handleOrganizerEdit()
    {
        if (
            isset($_POST['adess_organizer_nonce']) &&
            wp_verify_nonce($_POST['adess_organizer_nonce'], 'adess_organizer_edit') &&
            current_user_can('manage_options')
        ) {
            $data = array_map('sanitize_text_field', $_POST);
            $repo = new OrganizerRepository();
            $organizer = new Organizer([
                'id'               => (int) $data['id'],
                'user_id'          => (int) $data['user_id'],
                'type'             => $data['type'],
                'name'             => $data['name'],
                'address'          => $data['address'],
                'contact_name'     => $data['contact_name'],
                'contact_email'    => $data['contact_email'],
                'phone'            => $data['phone'],
                'default_location' => $data['default_location'],
                'status'           => $data['status'],
            ]);
            $repo->save($organizer);
            wp_redirect(admin_url('admin.php?page=adess_organizer_list'));
            exit;
        }
    }

    // Affiche le tableau des organisateurs
    public function renderOrganizers()
    {
        $table = new OrganizerTable();
        $table->prepare_items();
        echo '<div class="wrap"><h1>Organisateurs</h1>';
        $table->display();
        echo '</div>';
    }

    // Affiche le formulaire d'édition d'un organisateur
    public function renderOrganizerEdit()
    {
        $id = isset($_GET['organizer']) ? intval($_GET['organizer']) : 0;
        $repo = new OrganizerRepository();
        $organizer = $repo->find($id);

        echo '<div class="wrap"><h1>Éditer un organisateur</h1>';
        if (! $organizer) {
            echo '<p>Organisateur introuvable.</p></div>';
            return;
        }

        echo '<form method="post">';
        echo wp_nonce_field('adess_organizer_edit', 'adess_organizer_nonce', true, false);
        echo '<input type="hidden" name="id" value="' . esc_attr($organizer->getId()) . '">';
        echo '<input type="hidden" name="user_id" value="' . esc_attr($organizer->getUserId()) . '">';
        echo '<p><label for="organizer_type">Type</label><br>';
        echo '<select name="type" id="organizer_type">';
        echo '<option value="company" ' . selected('company', $organizer->getType(), false) . '>Entreprise</option>';
        echo '<option value="collectivity" ' . selected('collectivity', $organizer->getType(), false) . '>Collectivité</option>';
        echo '</select></p>';
        echo '<p><label for="organizer_name">Nom</label><br>';
        echo '<input type="text" name="name" id="organizer_name" value="' . esc_attr($organizer->getName()) . '"></p>';
        echo '<p><label for="organizer_address">Adresse</label><br>';
        echo '<textarea name="address" id="organizer_address">' . esc_textarea($organizer->getAddress()) . '</textarea></p>';
        echo '<p><label for="organizer_contact_name">Contact</label><br>';
        echo '<input type="text" name="contact_name" id="organizer_contact_name" value="' . esc_attr($organizer->getContactName()) . '"></p>';
        echo '<p><label for="organizer_contact_email">Email</label><br>';
        echo '<input type="email" name="contact_email" id="organizer_contact_email" value="' . esc_attr($organizer->getContactEmail()) . '"></p>';
        echo '<p><label for="organizer_phone">Téléphone</label><br>';
        echo '<input type="text" name="phone" id="organizer_phone" value="' . esc_attr($organizer->getPhone()) . '"></p>';
        echo '<p><label for="organizer_default_location">Lieu par défaut</label><br>';
        echo '<input type="text" name="default_location" id="organizer_default_location" value="' . esc_attr($organizer->getDefaultLocation()) . '"></p>';
        echo '<p><label for="organizer_status">Statut</label><br>';
        echo '<select name="status" id="organizer_status">';
        echo '<option value="pending" ' . selected('pending', $organizer->getStatus(), false) . '>En attente</option>';
        echo '<option value="validated" ' . selected('validated', $organizer->getStatus(), false) . '>Validé</option>';
        echo '<option value="rejected" ' . selected('rejected', $organizer->getStatus(), false) . '>Rejeté</option>';
        echo '</select></p>';
        echo '<p><input type="submit" value="Mettre à jour"></p>';
        echo '</form></div>';
    }

    // Formulaire d'édition d'une réservation
public function renderReservationEdit()
{
    $id   = isset($_GET['reservation']) ? intval($_GET['reservation']) : 0;
    $repo = new \Adess\EventManager\Repositories\ReservationRepository();
    $res  = $repo->find($id);

    echo '<div class="wrap"><h1>Éditer la réservation</h1>';

    if (! $res) {
        echo '<p>Réservation introuvable.</p></div>';
        return;
    }

    // Si soumis, on traite la mise à jour
    if (
        $_SERVER['REQUEST_METHOD'] === 'POST'
        && isset($_POST['adess_reservation_nonce'])
        && wp_verify_nonce($_POST['adess_reservation_nonce'], 'adess_reservation_edit')
    ) {
        $data = [
            'id'          => $res->getId(),
            'event_id'    => $res->getEventId(),
            'user_id'     => $res->getUserId(),
            'guest_email' => sanitize_email($_POST['guest_email'] ?? $res->getGuestEmail()),
            'places'      => max(1, intval($_POST['places'] ?? $res->getPlaces())),
            'amount_paid' => floatval($_POST['amount_paid'] ?? $res->getAmountPaid()),
            'status'      => sanitize_text_field($_POST['status'] ?? $res->getStatus()),
            'created_at'  => $res->getCreatedAt()->format('Y-m-d H:i:s'),
        ];
        $repo->save(new \Adess\EventManager\Models\Reservation($data));
        echo '<div id="message" class="updated"><p>Réservation mise à jour.</p></div>';
        // recharger l'objet
        $res = $repo->find($id);
    }

    // Affichage du formulaire
    echo '<form method="post">';
    wp_nonce_field('adess_reservation_edit', 'adess_reservation_nonce');

    // Exemple de champ : statut
    echo '<p><label for="adess_status">Statut :</label> ';
    echo '<select name="status" id="adess_status">';
    foreach (['pending','confirmed','cancelled'] as $s) {
        printf(
            '<option value="%1$s"%2$s>%1$s</option>',
            esc_attr($s),
            selected($res->getStatus(), $s, false)
        );
    }
    echo '</select></p>';

    // Exemple de champ : places
    echo '<p><label for="adess_places">Places :</label> ';
    echo '<input type="number" name="places" id="adess_places" min="1" value="' . esc_attr($res->getPlaces()) . '"></p>';

    // Exemple de champ : email invité
    echo '<p><label for="adess_guest_email">Email invité :</label> ';
    echo '<input type="email" name="guest_email" id="adess_guest_email" value="' . esc_attr($res->getGuestEmail()) . '"></p>';

    // Exemple de champ : montant payé
    echo '<p><label for="adess_amount_paid">Montant payé :</label> ';
    echo '<input type="text" name="amount_paid" id="adess_amount_paid" value="' . esc_attr($res->getAmountPaid()) . '"></p>';

    echo '<p><input type="submit" value="Mettre à jour"></p>';
    echo '</form></div>';
}


    // Affiche le tableau des événements
    public function renderEvents()
    {
        $table = new EventTable();
        $table->prepare_items();
        echo '<div class="wrap"><h1>Événements</h1>';
        $table->display();
        echo '</div>';
    }

    // Affiche le formulaire d'édition d'un événement
    public function renderEventEdit()
    {
        $eventId = isset($_GET['event']) ? intval($_GET['event']) : 0;
        $form = new \Adess\EventManager\Front\Shortcodes\EventForm();

        echo '<div class="wrap"><h1>Éditer l’événement</h1>';
        echo $form->render(['event_id' => $eventId]);
        echo '</div>';
    }

    // Affiche le tableau des réservations
    public function renderReservations()
    {
        $table = new ReservationTable();
        $table->prepare_items();
        echo '<div class="wrap"><h1>Réservations</h1>';
        $table->display();
        echo '</div>';
    }
    // Affiche le tableau des subventions
    public function renderSubsidies(): void
    {
        $table = new SubsidyTable();
        $table->prepare_items();
        echo '<div class="wrap"><h1>Subventions</h1>';
        $table->display();
        echo '</div>';
    }


    // Affiche la page Dashboard (accueil)
    public function renderDashboard()
    {
        echo '<div class="wrap"><h1>Bienvenue sur le dashboard ADESS Resa</h1>';
        echo '<p>Utilisez le menu à gauche pour gérer les organisateurs, événements et réservations.</p>';
        echo '</div>';
    }
}
