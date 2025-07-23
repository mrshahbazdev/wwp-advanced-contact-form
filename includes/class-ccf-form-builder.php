<?php
class CCF_Form_Builder {
    
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
    
    public function __construct() {
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        add_action('wp_ajax_ccf_save_form', array($this, 'save_form'));
    }
    
    public function admin_scripts($hook) {
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
    
   public function save_form() {
    check_ajax_referer('ccf_ajax_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }
    
    $form_data = $_POST['form_data'];
    $form_id = isset($form_data['form_id']) ? intval($form_data['form_id']) : 0;
    
    // Validate and sanitize form data
    $form_title = sanitize_text_field($form_data['form_title']);
    // Agar title khaali hai to error dein
    if (empty($form_title)) {
        wp_send_json_error('Form Title is required.');
    }
    $form_name = sanitize_title($form_title);

    // *** YEH LINE ADD KAREIN ***
    // Submit button text ko get aur sanitize karein
    $submit_button_text = isset($form_data['submit_button_text']) ? sanitize_text_field($form_data['submit_button_text']) : 'Submit';
    
    // Description ko safely get karein
    $form_description = isset($form_data['form_description']) ? sanitize_textarea_field($form_data['form_description']) : '';
    
    // Save form to database
    if ($form_id) {
        // Update existing form
        $result = CCF_Database::update_form($form_id, $form_name, $form_title, $form_description, $submit_button_text);
    } else {
        // Create new form
        $form_id = CCF_Database::create_form($form_name, $form_title, $form_description, $submit_button_text);
        // *** IS LINE KO BEHTAR BANAYEIN ***
        // Check karein ke form ID 0 se bari hai
        $result = $form_id && $form_id > 0;
    }
    
    if ($result && !empty($form_data['fields'])) {
        // Save form fields
        $this->save_form_fields($form_id, $form_data['fields']);
    }
    
    if ($result) {
        wp_send_json_success(array('form_id' => $form_id));
    } else {
        // Database error ka message dein
        global $wpdb;
        wp_send_json_error('Error saving form to the database. ' . $wpdb->last_error);
    }
}
    
    private function save_form_fields($form_id, $fields) {
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
    
    private function sanitize_options($options) {
        if (is_array($options)) {
            return array_map('sanitize_text_field', $options);
        }
        return array();
    }
    
    public function get_field_types() {
        return $this->field_types;
    }
}