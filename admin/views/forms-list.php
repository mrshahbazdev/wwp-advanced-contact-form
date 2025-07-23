<div class="wrap">
    <h1 class="wp-heading-inline">Contact Forms</h1>
    <a href="<?php echo admin_url('admin.php?page=ccf-add-new'); ?>" class="page-title-action">Add New</a>
    
    <hr class="wp-header-end">
    
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th scope="col" class="manage-column">Title</th>
                <th scope="col" class="manage-column">Shortcode</th>
                <th scope="col" class="manage-column">Submissions</th>
                <th scope="col" class="manage-column">Date</th>
            </tr>
        </thead>
        
        <tbody>
            <?php
            $forms = CCF_Database::get_forms();
            if ($forms) {
                foreach ($forms as $form) {
                    $edit_url = admin_url('admin.php?page=ccf-add-new&form_id=' . $form->id);
                    $submissions_url = admin_url('admin.php?page=ccf-submissions&form_id=' . $form->id);
                    ?>
                    <tr>
                        <td class="title column-title has-row-actions column-primary">
                            <strong><a class="row-title" href="<?php echo esc_url($edit_url); ?>"><?php echo esc_html($form->title); ?></a></strong>
                            <div class="row-actions">
                                <span class="edit"><a href="<?php echo esc_url($edit_url); ?>">Edit</a> | </span>
                                <span class="view"><a href="<?php echo esc_url($submissions_url); ?>">View Submissions</a> | </span>
                                <span class="trash"><a href="#" class="ccf-delete-form" data-form-id="<?php echo $form->id; ?>" style="color:#a00;">Delete</a></span>
                            </div>
                        </td>
                        <td>
                            <input type="text" readonly="readonly" onfocus="this.select();" value="[advanced_contact_form id=&quot;<?php echo $form->id; ?>&quot;]">
                        </td>
                        <td>
                            <a href="<?php echo esc_url($submissions_url); ?>">View Submissions</a>
                        </td>
                        <td>
                            <?php echo date('Y/m/d', strtotime($form->created_at)); ?>
                        </td>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr>
                    <td colspan="4">No forms found. <a href="<?php echo admin_url('admin.php?page=ccf-add-new'); ?>">Create one now</a>.</td>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>
</div>