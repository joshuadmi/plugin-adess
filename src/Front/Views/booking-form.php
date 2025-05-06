<?php
// Template du formulaire de réservation
// Variables disponibles :
//  - $event : instance de \Adess\EventManager\Models\Event
?>
<div class="adess-booking-form">
    <h2><?php echo esc_html( $event->getTitle() ); ?></h2>
    <p><strong>Date :</strong> <?php echo esc_html( $event->getStartDate()->format('d/m/Y') ); ?></p>

    <form method="post">
        <?php wp_nonce_field( 'adess_booking', 'adess_booking_nonce' ); ?>

        <p>
            <label for="adess_guest_name">Nom :</label><br>
            <input type="text" id="adess_guest_name" name="guest_name" required>
        </p>

        <p>
            <label for="adess_guest_firstname">Prénom :</label><br>
            <input type="text" id="adess_guest_firstname" name="guest_firstname" required>
        </p>

        <p>
            <label for="adess_guest_postcode">Code postal :</label><br>
            <input type="text" id="adess_guest_postcode" name="guest_postcode" pattern="[0-9]{5}" title="5 chiffres" required>
        </p>

        <p>
            <label for="adess_guest_email">Votre email :</label><br>
            <input type="email" id="adess_guest_email" name="guest_email" required>
        </p>

        <p>
            <label for="adess_places">Nombre de places :</label><br>
            <input type="number" id="adess_places" name="places" min="1" value="1" required>
        </p>

        <p>
            <input type="submit" value="Réserver">
        </p>
    </form>
</div>
