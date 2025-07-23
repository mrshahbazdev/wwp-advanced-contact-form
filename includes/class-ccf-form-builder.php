<?php
class CCF_Form_Builder
{

    private $field_types = array(
        'text' => 'Text',
        'email' => 'Email',
        'tel' => 'Phone',
        'textarea' => 'Textarea',
        'select' => 'Dropdown',
        'checkbox' => 'Checkbox',
        'radio' => 'Radio Buttons',
        'file' => 'File Upload'
    );

    public function __construct()
    {
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        add_action('wp_ajax_ccf_save_form', array($this, 'save_form'));
        add_action('wp_ajax_ccf_delete_form', array($this, 'delete_form'));
    }
    // Add this new function to the class
    public function delete_form()
    {
        check_ajax_referer('ccf_ajax_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $form_id = isset($_POST['form_id']) ? intval($_POST['form_id']) : 0;

        if ($form_id > 0) {
            $result = CCF_Database::delete_form($form_id);
            if ($result) {
                wp_send_json_success();
            } else {
                wp_send_json_error('Could not delete form from the database.');
            }
        } else {
            wp_send_json_error('Invalid form ID.');
        }
    }
    public function admin_scripts($hook)
    {
        // Check if we are on one of our plugin's pages
        if (strpos($hook, 'ccf-') !== false) {
            // Enqueue Admin CSS
            wp_enqueue_style('ccf-admin', CCF_PLUGIN_URL . 'assets/css/admin.css', array(), CCF_VERSION);

            // Enqueue Admin JS with dependencies
            wp_enqueue_script('ccf-admin', CCF_PLUGIN_URL . 'assets/js/admin.js', array('jquery', 'jquery-ui-sortable', 'wp-util'), CCF_VERSION, true);

            // *** YEH HISSA ADD KAREIN ***
            // Localize the script with data for AJAX
            wp_localize_script('ccf-admin', 'ccf_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('ccf_ajax_nonce')
            ));
        }
    }

    public function save_form()
    {
        check_ajax_referer('ccf_ajax_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $form_data = $_POST['form_data'];
        $form_id = isset($form_data['form_id']) ? intval($form_data['form_id']) : 0;

        // Validate and sanitize form data
        $form_title = sanitize_text_field($form_data['form_title']);
        if (empty($form_title)) {
            wp_send_json_error('Form Title is required.');
        }
        $form_name = sanitize_title($form_title);

        $submit_button_text = isset($form_data['submit_button_text']) ? sanitize_text_field($form_data['submit_button_text']) : 'Submit';

        $form_description = isset($form_data['form_description']) ? sanitize_textarea_field($form_data['form_description']) : '';

        global $wpdb;

        // Save form to database
        if ($form_id) {
            // Update existing form
            CCF_Database::update_form($form_id, $form_name, $form_title, $form_description, $submit_button_text);

            // Check for a specific database error after attempting to update.
            if ($wpdb->last_error !== '') {
                wp_send_json_error('Database error on form update: ' . $wpdb->last_error);
                return;
            }
        } else {
            // Create new form
            $form_id = CCF_Database::create_form($form_name, $form_title, $form_description, $submit_button_text);
            if (!$form_id || $wpdb->last_error !== '') {
                wp_send_json_error('Error creating new form in database. ' . $wpdb->last_error);
                return;
            }
        }

        // If we have a valid form ID, proceed to save fields
        if ($form_id > 0 && isset($form_data['fields']) && is_array($form_data['fields'])) {
            $this->save_form_fields($form_id, $form_data['fields']);
            if ($wpdb->last_error !== '') {
                wp_send_json_error('Database error while saving form fields: ' . $wpdb->last_error);
                return;
            }
        }

        // If we've reached here without errors, it's a success
        wp_send_json_success(array('form_id' => $form_id));
    }

    private function save_form_fields($form_id, $fields)
    {
        // First delete all existing fields for this form
        CCF_Database::delete_form_fields($form_id);

        foreach ($fields as $field) {
            $field_data = array(
                'label' => sanitize_text_field($field['label']),
                'type' => sanitize_text_field($field['type']),
                'required' => isset($field['required']) ? 1 : 0,
                'placeholder' => sanitize_text_field($field['placeholder']),
                'options' => isset($field['options']) ? $this->sanitize_options($field['options']) : '',
                'class' => sanitize_text_field($field['class']),
                'order' => intval($field['order'])
            );

            CCF_Database::add_form_field($form_id, $field_data);
        }
    }

    private function sanitize_options($options)
    {
        if (is_array($options)) {
            return array_map('sanitize_text_field', $options);
        }
        return array();
    }

    public function get_field_types()
    {
        return $this->field_types;
    }
}
