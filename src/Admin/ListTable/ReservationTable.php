<?php

namespace Adess\EventManager\Admin\ListTable;

use WP_List_Table;
use Adess\EventManager\Repositories\ReservationRepository;
use Adess\EventManager\Models\Reservation;

// Classe ReservationTable : affiche la liste des réservations en back-office
class ReservationTable extends WP_List_Table
{
    // Repository pour accéder aux données des réservations
    private $repository;

    // Constructeur : initialise le list-table et le repository
    public function __construct()
    {
        parent::__construct([
            'singular' => 'reservation',   // nom singulier
            'plural'   => 'reservations',  // nom pluriel
            'ajax'     => false,           // désactive AJAX
        ]);
        $this->repository = new ReservationRepository();
    }

    // Définit les colonnes affichées dans la table
    public function get_columns(): array
    {
        return [
            'cb'          => '<input type="checkbox" />', // case à cocher
            'id'          => 'ID',                         // ID de la réservation
            'event_id'    => 'Événement',                  // ID de l'événement
            'user_id'     => 'ID Utilisateur',             // ID du user WordPress
            'guest_email' => 'Email invité',               // Email de l'invité
            'places'      => 'Places',                     // Nombre de places réservées
            'amount_paid' => 'Montant payé',               // Montant réglé
            'status'      => 'Statut',                     // Statut (pending, confirmed...)
            'created_at'  => 'Réservé le',                 // Date de création
        ];
    }

    // Colonnes pouvant être triées
    protected function get_sortable_columns(): array
    {
        return [
            'id'         => ['id', true],          // triable par ID
            'created_at' => ['created_at', false],  // triable par date
            'status'     => ['status', false],      // triable par statut
        ];
    }

    // Prépare les données : pagination, tri, récupération des items
    public function prepare_items(): void
    {
        $columns  = $this->get_columns();
        $hidden   = [];
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = [$columns, $hidden, $sortable];

        // Paramètres de pagination
        $per_page     = 20;
        $current_page = $this->get_pagenum();
        $total_items  = $this->repository->countAll();

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
        ]);

        // Récupération des réservations pour la page actuelle
        $this->items = $this->repository->findByPage(
            $per_page,
            ($current_page - 1) * $per_page,
            $this->get_sortable_column_order()
        );
    }

    // Affiche le contenu d'une colonne générique
    protected function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'id':
            case 'event_id':
            case 'user_id':
            case 'guest_email':
            case 'places':
            case 'amount_paid':
            case 'status':
            case 'created_at':
                // affiche la propriété correspondante
                return esc_html((string) $item->{$column_name});
            default:
                // debug si colonne inconnue
                return print_r($item, true);
        }
    }

    // Checkbox pour les actions groupées
    protected function column_cb($item): string
    {
        return sprintf(
            '<input type="checkbox" name="reservation[]" value="%d" />',
            $item->getId()
        );
    }

    // Colonne ID avec liens Éditer / Supprimer
    protected function column_id($item): string
    {
        $actions = [
            'edit'   => sprintf('<a href="?page=adess_reservation_edit&reservation=%d">Éditer</a>', $item->getId()),
            'delete' => sprintf('<a href="?page=adess_reservation_list&action=delete&reservation=%d">Supprimer</a>', $item->getId()),
        ];
        return sprintf('%1$s %2$s', esc_html($item->getId()), $this->row_actions($actions));
    }

    // Récupère la colonne et l'ordre de tri depuis la requête HTTP
    private function get_sortable_column_order(): array
    {
        $orderby = !empty($_REQUEST['orderby']) ? sanitize_text_field($_REQUEST['orderby']) : 'id';
        $order   = !empty($_REQUEST['order'])   ? sanitize_text_field($_REQUEST['order'])   : 'ASC';
        return [$orderby, $order];
    }

    // Affiche la table dans un formulaire pour les bulk actions
    public function display(): void
    {
        echo '<form method="post">';
        parent::display();
        echo '</form>';
    }
}
