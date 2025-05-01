<?php

namespace Adess\EventManager\Models;

// Représente une réservation pour un événement
class Reservation
{
    // Identifiant unique (null si nouveau)
    private $id;

    // Identifiant de l'événement associé
    private $event_id;

    // Identifiant de l'utilisateur WordPress (null si invité)
    private $user_id;

    // Email de l'invité si pas d'utilisateur connecté
    private $guest_email;

    // Nombre de places réservées
    private $places;

    // Montant payé (null si pas encore réglé)
    private $amount_paid;

    // Statut de la réservation: 'pending', 'confirmed' ou 'cancelled'
    private $status;

    // Date de création de la réservation
    private $created_at;

    // Hydrate l'objet à partir d'un tableau de données
    public function __construct(array $data = [])
    {
        // Si on fournit un 'id', on le cast en int, sinon on reste null
        $this->id = isset($data['id']) ? (int) $data['id'] : null;

        // event_id est obligatoire, casté en int, par défaut 0
        $this->event_id = isset($data['event_id']) ? (int) $data['event_id'] : 0;

        // user_id peut être null ou un int
        $this->user_id = isset($data['user_id']) ? (int) $data['user_id'] : null;

        // guest_email peut être null ou une chaîne
        $this->guest_email = $data['guest_email'] ?? null;

        // places, nombre de participants, int par défaut 1
        $this->places = isset($data['places']) ? (int) $data['places'] : 1;

        // amount_paid peut être null ou float
        $this->amount_paid = isset($data['amount_paid']) ? (float) $data['amount_paid'] : null;

        // status, valeur par défaut 'pending'
        $this->status = $data['status'] ?? 'pending';

        // created_at: transforme la chaîne en DateTime ou prend maintenant
        if (isset($data['created_at'])) {
            $this->created_at = new \DateTime($data['created_at']);
        } else {
            $this->created_at = new \DateTime();
        }
    }

    // Retourne l'ID (null si pas encore en base)
    public function getId(): ?int
    {
        return $this->id;
    }

    // Retourne l'ID de l'événement lié
    public function getEventId(): int
    {
        return $this->event_id;
    }

    // Permet de définir plus tard l'event_id
    public function setEventId(int $eventId): self
    {
        $this->event_id = $eventId;
        return $this;
    }

    // Retourne l'ID de l'utilisateur ou null
    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    // Définit l'ID utilisateur (ou null pour invité)
    public function setUserId(?int $userId): self
    {
        $this->user_id = $userId;
        return $this;
    }

    // Retourne l'email de l'invité
    public function getGuestEmail(): ?string
    {
        return $this->guest_email;
    }

    // Définit l'email de l'invité
    public function setGuestEmail(?string $email): self
    {
        $this->guest_email = $email;
        return $this;
    }

    // Retourne le nombre de places réservées
    public function getPlaces(): int
    {
        return $this->places;
    }

    // Modifie le nombre de places
    public function setPlaces(int $places): self
    {
        $this->places = $places;
        return $this;
    }

    // Retourne le montant payé
    public function getAmountPaid(): ?float
    {
        return $this->amount_paid;
    }

    // Définit le montant payé
    public function setAmountPaid(?float $amount): self
    {
        $this->amount_paid = $amount;
        return $this;
    }

    // Retourne le statut actuel
    public function getStatus(): string
    {
        return $this->status;
    }

    // Modifie le statut ('pending', 'confirmed', 'cancelled')
    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    // Retourne la date de création
    public function getCreatedAt(): \DateTime
    {
        return $this->created_at;
    }
}
