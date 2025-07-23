<?php
/**
 * Plugin Name: Advanced Custom Contact Form
 * Description: A customizable contact form with AJAX submission, field management, and email templates.
 * Version: 2.1
 * Author: Mr Shahbaz
 */

if (!defined('ABSPATH')) exit;

// Plugin constants
define('CCF_VERSION', '2.1');
define('CCF_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CCF_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
require_once CCF_PLUGIN_DIR . 'includes/class-ccf-form-builder.php';
require_once CCF_PLUGIN_DIR . 'includes/class-ccf-ajax-handler.php';
require_once CCF_PLUGIN_DIR . 'includes/class-ccf-database.php';


/**
 * Plugin activation hook function.
 */
function ccf_activate_plugin($network_wide) {
    if (is_multisite() && $network_wide) {
        global $wpdb;
        $blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
        foreach ($blog_ids as $blog_id) {
            switch_to_blog($blog_id);
            CCF_Database::create_tables();
            restore_current_blog();
        }
    } else {
        CCF_Database::create_tables();
    }
}
register_activation_hook(__FILE__, 'ccf_activate_plugin');


// Main plugin class
class CustomContactForm {

    public function __construct() {
        new CCF_Form_Builder();
        new CCF_Ajax_Handler();
        
        add_action('admin_init', array($this, 'admin_init'));
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        add_shortcode('advanced_contact_form', array($this, 'render_contact_form'));
    }
    
    public function admin_init() {
        // Register all settings for the plugin
        register_setting('ccf_settings', 'ccf_email_to');
        register_setting('ccf_settings', 'ccf_email_subject');
        register_setting('ccf_settings', 'ccf_success_message');
        register_setting('ccf_settings', 'ccf_custom_css', 'sanitize_textarea_field');
        // Register the new "From Email" setting
        register_setting('ccf_settings', 'ccf_from_email', 'sanitize_email');
    }
    
    public function admin_menu() {
        // Main menu
        add_menu_page(
            'Contact Forms',
            'Contact Forms',
            'manage_options',
            'ccf-forms',
            array($this, 'render_forms_page'),
            'dashicons-email-alt',
            30
        );
        
        // Submenus
        add_submenu_page(
            'ccf-forms',
            'All Forms',
            'All Forms',
            'manage_options',
            'ccf-forms',
            array($this, 'render_forms_page')
        );
        
        add_submenu_page(
            'ccf-forms',
            'Add New Form',
            'Add New',
            'manage_options',
            'ccf-add-new',
            array($this, 'render_form_builder_page')
        );
        
        add_submenu_page(
            'ccf-forms',
            'Form Submissions',
            'Submissions',
            'manage_options',
            'ccf-submissions',
            array($this, 'render_submissions_page')
        );
        
        add_submenu_page(
            'ccf-forms',
            'Settings',
            'Settings',
            'manage_options',
            'ccf-settings',
            array($this, 'render_settings_page')
        );
    }
    
    public function enqueue_scripts() {
        // Frontend CSS
        wp_enqueue_style(
            'ccf-frontend',
            CCF_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            CCF_VERSION
        );
        $custom_css = get_option('ccf_custom_css');
    	if (!empty($custom_css)) {
        	wp_add_inline_style('ccf-frontend', $custom_css);
    	}
        // Frontend JS
        wp_enqueue_script(
            'ccf-frontend',
            CCF_PLUGIN_URL . 'assets/js/frontend.js',
            array('jquery'),
            CCF_VERSION,
            true
        );
        
        // Localize script for AJAX
        wp_localize_script('ccf-frontend', 'ccf_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ccf_ajax_nonce')
        ));
    }

    public function render_field($field) {
        $field_name = 'form_data[field_' . esc_attr($field->id) . ']';
        $field_id = 'ccf-field-' . esc_attr($field->id);
        $placeholder = esc_attr($field->placeholder);
        $required = $field->required ? 'required' : '';

        switch ($field->type) {
            case 'text':
            case 'email':
            case 'tel':
                echo "<input type='{$field->type}' name='{$field_name}' id='{$field_id}' placeholder='{$placeholder}' {$required}>";
                break;

            case 'textarea':
                echo "<textarea name='{$field_name}' id='{$field_id}' placeholder='{$placeholder}' {$required}></textarea>";
                break;

            case 'select':
                echo "<select name='{$field_name}' id='{$field_id}' {$required}>";
                if (!empty($field->options) && is_array($field->options)) {
                    foreach ($field->options as $option) {
                        $option_val = esc_attr($option);
                        echo "<option value='{$option_val}'>{$option_val}</option>";
                    }
                }
                echo "</select>";
                break;

            case 'radio':
                if (!empty($field->options) && is_array($field->options)) {
                    echo '<div class="ccf-radio-group">';
                    foreach ($field->options as $index => $option) {
                        $option_val = esc_attr($option);
                        $radio_id = $field_id . '-' . $index;
                        echo "<div class='ccf-radio-item'><input type='radio' name='{$field_name}' id='{$radio_id}' value='{$option_val}' {$required}><label for='{$radio_id}'>{$option_val}</label></div>";
                    }
                    echo '</div>';
                }
                break;

            case 'checkbox':
                 if (!empty($field->options) && is_array($field->options)) {
                    echo '<div class="ccf-checkbox-group">';
                    foreach ($field->options as $index => $option) {
                        $option_val = esc_attr($option);
                        $checkbox_id = $field_id . '-' . $index;
                        // For multiple checkboxes, use array in name
                        echo "<div class='ccf-checkbox-item'><input type='checkbox' name='{$field_name}[]' id='{$checkbox_id}' value='{$option_val}'><label for='{$checkbox_id}'>{$option_val}</label></div>";
                    }
                    echo '</div>';
                } else {
                     // Single checkbox
                     echo "<input type='checkbox' name='{$field_name}' id='{$field_id}' value='1'>";
                }
                break;

            case 'file':
                echo "<input type='file' name='{$field_name}' id='{$field_id}'>";
                break;

            default:
                echo "<input type='text' name='{$field_name}' id='{$field_id}' placeholder='{$placeholder}' {$required}>";
                break;
        }
    }
    public function render_contact_form($atts) {
        $form_id = isset($atts['id']) ? intval($atts['id']) : 0;
        
        if (!$form_id) {
            return '<p>Please specify a form ID using the id attribute.</p>';
        }
        
        // Get form data from database
        $form = CCF_Database::get_form($form_id);
        
        if (!$form) {
            return '<p>The requested form does not exist.</p>';
        }
        
        // Get form fields
        $fields = CCF_Database::get_form_fields($form_id);
        
        ob_start();
        include CCF_PLUGIN_DIR . 'templates/form-template.php';
        return ob_get_clean();
    }
    
    public function render_forms_page() {
        include CCF_PLUGIN_DIR . 'admin/views/forms-list.php';
    }
    
    public function render_form_builder_page() {
        include CCF_PLUGIN_DIR . 'admin/views/form-builder.php';
    }
    
    public function render_submissions_page() {
        include CCF_PLUGIN_DIR . 'admin/views/submissions.php';
    }
    
    public function render_settings_page() {
        include CCF_PLUGIN_DIR . 'admin/views/settings.php';
    }
}

// Initialize the plugin
new CustomContactForm();