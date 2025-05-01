<?php
// Template part pour afficher le formulaire de réservation.
// Variables disponibles :
//  - $event : instance de \Adess\EventManager\Models\Event
?>
<div class="adess-booking-form">
    <?php
    // Titre de l'événement
    echo '<h2>' . esc_html( $event->getTitle() ) . '</h2>';
    // Date formatée (jour/mois/année)
    echo '<p><strong>Date :</strong> ' . esc_html( $event->getStartDate()->format('d/m/Y') ) . '</p>';
    ?>

    <form method="post">
        <?php
        // Champ nonce pour sécuriser le traitement du formulaire
        wp_nonce_field( 'adess_booking', 'adess_booking_nonce' );
        ?>

        <p>
            <?php
            // Champ email de l'invité
            echo '<label for="adess_guest_email">Votre email :</label><br>';
            echo '<input type="email" id="adess_guest_email" name="guest_email" required>';
            ?>
        </p>

        <p>
            <?php
            // Nombre de places demandées
            echo '<label for="adess_places">Nombre de places :</label><br>';
            echo '<input type="number" id="adess_places" name="places" min="1" value="1" required>';
            ?>
        </p>

        <p>
            <?php
            // Bouton de soumission du formulaire
            echo '<input type="submit" value="Réserver">';
            ?>
        </p>
    </form>
</div>
