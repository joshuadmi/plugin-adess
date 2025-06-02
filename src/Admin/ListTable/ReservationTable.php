<?php

namespace Adess\EventManager\Admin\ListTable;

use WP_List_Table;
use Adess\EventManager\Repositories\ReservationRepository;
use Adess\EventManager\Repositories\EventRepository;


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

    public function prepare_items(): void
    {
        // 1) Colonnes
        $columns  = $this->get_columns();
        $hidden   = [];                    // aucune colonne cachée
        $sortable = $this->get_sortable_columns();

        // Ceci permet à WP_List_Table de connaître tes colonnes
        $this->_column_headers = [$columns, $hidden, $sortable];

        // 2) Pagination
        $per_page     = 20;
        $current_page = $this->get_pagenum();
        $total_items  = $this->repository->countAll();

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
        ]);

        // 3) Récupère les items pour cette page
        $this->items = $this->repository->findByPage(
            $per_page,
            ($current_page - 1) * $per_page
        );
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
    protected function column_cb($item): string
    {
        return sprintf(
            '<input type="checkbox" name="reservation[]" value="%d" />',
            $item->getId()
        );
    }

    protected function get_sortable_columns(): array
    {
        return [
            'id'         => ['id',         true],
            'created_at' => ['created_at', false],
            'status'     => ['status',     false],
        ];
    }

    protected function column_event_id($item): string
    {
        $eventRepo = new EventRepository();
        $event     = $eventRepo->find($item->getEventId());

        if (! $event) {
            return esc_html__('Événement introuvable', 'adess-resa');
        }
        return esc_html($event->getTitle());
    }

    // Affiche le contenu d'une colonne générique
    protected function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'id':
                return esc_html($item->getId());
            case 'user_id':
                return esc_html((string) $item->getUserId());
            case 'guest_email':
                return esc_html((string) $item->getGuestEmail());
            case 'places':
                return esc_html((string) $item->getPlaces());
            case 'amount_paid':
                return esc_html((string) $item->getAmountPaid());
            case 'status':
                return esc_html($item->getStatus());
            case 'created_at':
                return esc_html($item->getCreatedAt()->format('Y-m-d H:i:s'));
            default:
                return '';
        }
    }
    // Affiche le contenu de la colonne ID avec des actions

    protected function column_id($item): string
    {
        $id = $item->getId();

        // URL d'édition et de suppression avec action=edit
        $edit_url = admin_url(
            'admin.php?page=adess_reservation_list'
                . '&action=edit'
                . '&reservation=' . $id
        );
        $delete_url = admin_url(
            'admin.php?page=adess_reservation_list'
                . '&action=delete'
                . '&reservation=' . $id
        );

        // Actions à afficher
        $actions = [
            'edit'   => '<a href="' . esc_url($edit_url) . '">' . __('Éditer', 'adess-resa') . '</a>',
            'delete' => '<a href="' . esc_url($delete_url) . '" onclick="return confirm(\'Voulez-vous vraiment supprimer cette réservation ?\')">Supprimer</a>',
        ];

        return sprintf(
            '%1$s %2$s',
            esc_html($id),
            $this->row_actions($actions)
        );
    }
}
