<?php
// Variables disponibles :
//   $formMessage (string), $data (array), $eventId (int)
?>

<?php echo $formMessage; ?>

<form method="post" id="adess-event-form" class="adess-event-form" enctype="multipart/form-data">
    <?php wp_nonce_field('adess_event', 'adess_event_nonce'); ?>

    <?php if ($eventId > 0): ?>
        <input type="hidden" name="event_id" value="<?php echo esc_attr($eventId); ?>">
    <?php endif; ?>

    <p>
        <label for="adess_title"><?php esc_html_e("Titre de l'événement :", 'adess-resa'); ?></label><br>
        <input type="text" name="title" id="adess_title"
            value="<?php echo esc_attr($data['title']); ?>" required>
    </p>

    <p>
        <label for="adess_location"><?php esc_html_e("Lieu :", 'adess-resa'); ?></label><br>
        <input type="text" name="location" id="adess_location"
            value="<?php echo esc_attr($data['location']); ?>" required>
    </p>

    <p>
        <label for="adess_start_date"><?php esc_html_e("Date de début :", 'adess-resa'); ?></label><br>
        <input type="date" name="start_date" id="adess_start_date"
            value="<?php echo esc_attr($data['start_date']); ?>" required>
    </p>

    <p>
        <label for="adess_participant_count"><?php esc_html_e("Places disponibles :", 'adess-resa'); ?></label><br>
        <input type="number" name="participant_count" id="adess_participant_count" min="1"
            value="<?php echo esc_attr($data['participant_count']); ?>">
    </p>

    <p>
        <label for="adess_estimated_cost"><?php esc_html_e("Coût estimé (€) :", 'adess-resa'); ?></label><br>
        <input type="number" name="estimated_cost" id="adess_estimated_cost" step="0.01"
            value="<?php echo esc_attr($data['estimated_cost']); ?>">
    </p>

    <?php if ($context === 'admin'): ?>
  <p>
    <label for="subsidy_amount"><?php esc_html_e('Subvention (€)', 'adess-resa'); ?></label><br>
    <input
      type="text"
      name="subsidy_amount"
      id="subsidy_amount"
      value="<?php echo esc_attr($data['subsidy_amount'] ?? ''); ?>"
    >
  </p>
<?php endif; ?>


    <p>
        <label for="adess_notes"><?php esc_html_e("Notes complémentaires :", 'adess-resa'); ?></label><br>
        <textarea name="notes" id="adess_notes" rows="4"><?php echo esc_textarea($data['notes']); ?></textarea>
    </p>

    <?php if (current_user_can('manage_options')): ?>
        <p>
            <label for="adess_status"><?php esc_html_e("Statut :", 'adess-resa'); ?></label><br>
            <select name="status" id="adess_status">
                <?php foreach (['pending' => 'En attente', 'validated' => 'Validé', 'cancelled' => 'Annulé'] as $val => $lab): ?>
                    <option value="<?php echo esc_attr($val); ?>"
                        <?php selected($data['status'], $val); ?>>
                        <?php echo esc_html($lab); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
    <?php endif; ?>

    <?php if (current_user_can('manage_options')): ?>

        <p>
            <label for="event_pdf"><?php esc_html_e('Fiche PDF (optionnel) :', 'adess-resa'); ?></label><br>
            <input type="file" name="event_pdf" id="event_pdf" accept="application/pdf">
        </p>
    <?php endif; ?>

    <p>
        <input type="submit"
            value="<?php echo $eventId > 0
                        ? esc_attr__('Mettre à jour', 'adess-resa')
                        : esc_attr__('Créer l’événement', 'adess-resa'); ?>">
    </p>
</form>