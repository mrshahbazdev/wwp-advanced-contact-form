<div class="wrap">
    <h1 class="wp-heading-inline">Form Submissions</h1>

    <?php
    // Get the list of all forms to populate the dropdown
    $all_forms = CCF_Database::get_forms();
    
    // Get the currently selected form ID from the URL.
    $form_id = isset($_GET['form_id']) ? intval($_GET['form_id']) : 0;
    ?>

    <div class="ccf-form-filter">
        <form method="GET" action="<?php echo admin_url('admin.php'); ?>">
            <input type="hidden" name="page" value="ccf-submissions">
            <label for="ccf-form-selector" class="screen-reader-text">Select a Form</label>
            <select name="form_id" id="ccf-form-selector">
                <option value="0">-- Select a Form --</option>
                <?php
                foreach ($all_forms as $form_item) {
                    // Check if this is the currently selected form
                    $selected = ($form_item->id == $form_id) ? 'selected' : '';
                    echo '<option value="' . esc_attr($form_item->id) . '" ' . $selected . '>' . esc_html($form_item->title) . '</option>';
                }
                ?>
            </select>
            <input type="submit" class="button" value="View Submissions">
        </form>
    </div>

    <?php
    // Check if we are viewing a specific form's submissions
    if ($form_id > 0) {
        $form = CCF_Database::get_form($form_id);
        if ($form) {
            // Create the export link with the correct form_id and nonce
            $export_link = add_query_arg(array(
                'page'       => 'ccf-submissions',
                'ccf_export' => 'excel',
                'form_id'    => $form_id,
                '_wpnonce'   => wp_create_nonce('ccf_export_nonce')
            ), admin_url('admin.php'));
            
            // Display the export button as a primary action
            echo '<a href="' . esc_url($export_link) . '" class="page-title-action">Export to Excel</a>';
        }
    }
    ?>
    
    <hr class="wp-header-end">

    <?php
    // Pagination and submission fetching logic
    $per_page = 10;
    $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $offset = ($current_page - 1) * $per_page;

    // Only fetch submissions if a form has been selected
    if ($form_id > 0) {
        $total_submissions = CCF_Database::count_submissions($form_id);
        $total_pages = ceil($total_submissions / $per_page);
        $submissions = CCF_Database::get_submissions($form_id, $per_page, $offset);
    } else {
        // If no form is selected, set submissions to an empty array
        $submissions = [];
        $total_pages = 0;
    }
    ?>

    <div id="ccf-submissions-list">
    <?php if (!empty($submissions)) : ?>
        <?php foreach ($submissions as $submission) : ?>
            <div class="ccf-submission-card">
                <div class="card-header">
                    <div class="submission-date">
                        <strong>Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($submission->created_at)); ?>
                    </div>
                    <div class="submission-ip">
                        <strong>IP:</strong> <?php echo esc_html($submission->ip_address); ?>
                    </div>
                </div>
                <div class="card-body">
                    <ul class="submission-data">
                    <?php
                    if (!empty($submission->data) && is_array($submission->data)) {
                        foreach ($submission->data as $field_data) {
                            $value = is_array($field_data['value']) ? implode(', ', $field_data['value']) : $field_data['value'];
                            echo '<li><strong>' . esc_html($field_data['label']) . ':</strong> ' . nl2br(esc_html($value)) . '</li>';
                        }
                    }
                    ?>
                    </ul>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else : ?>
        <div class="ccf-no-submissions">
            <p>
                <?php 
                if ($form_id > 0) {
                    echo 'No submissions found for this form.';
                } else {
                    echo 'Please select a form from the dropdown above to view its submissions.';
                }
                ?>
            </p>
        </div>
    <?php endif; ?>
    </div>

    <?php
    // Display pagination links if needed
    if ($total_pages > 1) {
        $page_links = paginate_links(array(
            'base' => add_query_arg('paged', '%#%'),
            'format' => '',
            'prev_text' => __('&laquo;'),
            'next_text' => __('&raquo;'),
            'total' => $total_pages,
            'current' => $current_page
        ));
        if ($page_links) {
            echo '<div class="tablenav"><div class="tablenav-pages">' . $page_links . '</div></div>';
        }
    }
    ?>
</div>