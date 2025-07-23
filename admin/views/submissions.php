<div class="wrap">
    <h1 class="wp-heading-inline">Form Submissions</h1>

    <hr class="wp-header-end">

    <?php
    $form_id = isset($_GET['form_id']) ? intval($_GET['form_id']) : 0;
    $submissions = CCF_Database::get_submissions($form_id);
    ?>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th scope="col">Date</th>
                <th scope="col">Form</th>
                <th scope="col">Submitted Data</th>
                <th scope="col">IP Address</th>
            </tr>
        </thead>
        
        <tbody>
            <?php
            if ($submissions) {
                foreach ($submissions as $submission) {
                    $form = CCF_Database::get_form($submission->form_id);
                    ?>
                    <tr>
                        <td><?php echo date('F j, Y, g:i a', strtotime($submission->created_at)); ?></td>
                        <td><?php echo $form ? esc_html($form->title) : 'N/A'; ?></td>
                        <td>
                            <?php
                            if (!empty($submission->data) && is_array($submission->data)) {
                                echo '<ul>';
                                foreach ($submission->data as $field_data) {
                                    $value = is_array($field_data['value']) ? implode(', ', $field_data['value']) : $field_data['value'];
                                    echo '<li><strong>' . esc_html($field_data['label']) . ':</strong> ' . esc_html(wp_trim_words($value, 15, '...')) . '</li>';
                                }
                                echo '</ul>';
                            }
                            ?>
                        </td>
                        <td><?php echo esc_html($submission->ip_address); ?></td>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr>
                    <td colspan="4">No submissions found.</td>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>
</div>