<?php
// Check if we are editing an existing form
$form_id = isset($_GET['form_id']) ? intval($_GET['form_id']) : 0;
$form_data = null;
$fields_data = array();

if ($form_id) {
    $form_data = CCF_Database::get_form($form_id);
    $fields_data = CCF_Database::get_form_fields($form_id);
}
?>
<div class="wrap" id="ccf-form-builder">
    <h1><?php echo $form_id ? 'Edit Form' : 'Add New Form'; ?></h1>

    <form id="ccf-builder-form">
        <input type="hidden" id="ccf-form-id" value="<?php echo $form_id; ?>">
        
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                
                <div id="post-body-content">
                    <div id="titlediv">
                        <div id="titlewrap">
                            <input type="text" name="form_title" id="title" placeholder="Enter Form Title Here" value="<?php echo $form_data ? esc_attr($form_data->title) : ''; ?>" required>
                        </div>
                    </div>
<div class="postbox">
    <h2 class="hndle"><span>Form Settings</span></h2>
    <div class="inside">
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row"><label for="ccf-submit-button-text">Submit Button Text</label></th>
                    <td><input name="submit_button_text" type="text" id="ccf-submit-button-text" value="<?php echo $form_data ? esc_attr($form_data->submit_button_text) : 'Submit'; ?>" class="regular-text"></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
                    <div class="postbox">
                        <h2 class="hndle"><span>Form Fields</span></h2>
                        <div class="inside">
                            <ul id="ccf-fields-container" class="sortable-fields">
                                <?php if (!empty($fields_data)): ?>
                                    <?php foreach($fields_data as $field): ?>
                                        <?php // Field template will be populated here by JavaScript ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                             <p class="description">Drag and drop fields from the right panel to add them to the form. Click on a field to see its settings.</p>
                        </div>
                    </div>
                </div>

                <div id="postbox-container-1" class="postbox-container">
                     <div class="postbox">
                        <h2 class="hndle"><span>Save Form</span></h2>
                        <div class="inside">
                            <button type="submit" class="button button-primary button-large" id="ccf-save-form-button">Save Form</button>
                             <span class="spinner"></span>
                        </div>
                    </div>
                    <div class="postbox">
                        <h2 class="hndle"><span>Available Fields</span></h2>
                        <div class="inside" id="ccf-available-fields">
                            <?php
                            $form_builder = new CCF_Form_Builder();
                            $field_types = $form_builder->get_field_types();
                            foreach ($field_types as $type => $label) {
                                echo '<button type="button" class="button ccf-add-field-btn" data-type="' . esc_attr($type) . '">' . esc_html($label) . '</button>';
                            }
                            ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

<script type="text/html" id="tmpl-ccf-field">
    <li class="ccf-field-item" data-type="{{ data.type }}">
        <div class="ccf-field-header">
            <span class="field-label-preview">{{ data.label }}</span>
            <span class="field-type-badge">{{ data.typeName }}</span>
            <div class="field-actions">
                <a href="#" class="field-clone">Clone</a> | 
                <a href="#" class="field-delete">Delete</a>
            </div>
        </div>
        <div class="ccf-field-settings" style="display: none;">
            <p>
                <label>Label</label>
                <input type="text" class="widefat field-setting" data-setting="label" value="{{ data.label }}">
            </p>
            <p>
                <label>Placeholder</label>
                <input type="text" class="widefat field-setting" data-setting="placeholder" value="">
            </p>
            <# if (data.type === 'select' || data.type === 'radio' || data.type === 'checkbox') { #>
            <p>
                <label>Options (one per line)</label>
                <textarea class="widefat field-setting" data-setting="options" rows="4"></textarea>
            </p>
            <# } #>
            <p>
                <label><input type="checkbox" class="field-setting" data-setting="required"> Required</label>
            </p>
        </div>
    </li>
</script>

<script>
    // Pass existing fields data to JavaScript
    var ccf_existing_fields = <?php echo json_encode($fields_data); ?>;
</script>