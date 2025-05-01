<?php

namespace Adess\EventManager\Models;

// Représente une subvention attribuée à un organisateur
class Subsidy
{
    // Identifiant unique (NULL si nouvelle subvention)
    private $id;

    // ID de l'organisateur lié
    private $organizer_id;

    // Montant total alloué
    private $total_amount;

    // Montant restant disponible
    private $remaining_amount;

    // Date de création de la subvention
    private $created_at;

    // Constructeur : initialise l'objet à partir du tableau de données
    public function __construct(array $data = [])
    {
        // Si un ID existe, on le prend, sinon on reste à null
        $this->id = isset($data['id']) ? (int) $data['id'] : null;

        // On stocke l'ID de l'organisateur (0 par défaut)
        $this->organizer_id = isset($data['organizer_id']) ? (int) $data['organizer_id'] : 0;

        // Montant total : conversion en float
        $this->total_amount = isset($data['total_amount']) ? (float) $data['total_amount'] : 0.0;

        // Montant restant : si fourni, on l'utilise, sinon on le calcule à partir du total
        $this->remaining_amount = isset($data['remaining_amount'])
            ? (float) $data['remaining_amount']
            : $this->total_amount;

        // Date de création : si fournie, on la convertit en DateTime, sinon on prend maintenant
        $this->created_at = isset($data['created_at'])
            ? new \DateTime($data['created_at'])
            : new \DateTime();
    }

    // Retourne l'ID de la subvention (ou null si nouvelle)
    public function getId(): ?int
    {
        return $this->id;
    }

    // Retourne l'ID de l'organisateur lié
    public function getOrganizerId(): int
    {
        return $this->organizer_id;
    }

    // Définit l'ID de l'organisateur
    public function setOrganizerId(int $organizerId): self
    {
        $this->organizer_id = $organizerId;
        return $this;
    }

    // Retourne le montant total alloué
    public function getTotalAmount(): float
    {
        return $this->total_amount;
    }

    // Définit un nouveau montant total et ajuste le restant si besoin
    public function setTotalAmount(float $amount): self
    {
        $this->total_amount = $amount;
        // Si le restant était plus élevé, on le ramène au total
        if ($this->remaining_amount > $amount) {
            $this->remaining_amount = $amount;
        }
        return $this;
    }

    // Retourne le montant restant disponible
    public function getRemainingAmount(): float
    {
        return $this->remaining_amount;
    }

    // Définit un montant restant en veillant à rester entre 0 et le total
    public function setRemainingAmount(float $amount): self
    {
        $this->remaining_amount = max(0.0, min($amount, $this->total_amount));
        return $this;
    }

    // Déduit un montant du restant (pour une utilisation)
    public function deductAmount(float $amount): self
    {
        $this->remaining_amount = max(0.0, $this->remaining_amount - $amount);
        return $this;
    }

    // Retourne la date de création
    public function getCreatedAt(): \DateTime
    {
        return $this->created_at;
    }
}
