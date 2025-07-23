<?php
class CCF_Ajax_Handler {
    
    public function __construct() {
        add_action('wp_ajax_ccf_submit_form', array($this, 'handle_form_submission'));
        add_action('wp_ajax_nopriv_ccf_submit_form', array($this, 'handle_form_submission'));
    }
    
    public function handle_form_submission() {
        check_ajax_referer('ccf_ajax_nonce', 'nonce');
        
        $form_id = isset($_POST['form_id']) ? intval($_POST['form_id']) : 0;
        // It's better to get form_data directly from $_POST if it's not nested.
        // Assuming 'form_data' is an array within $_POST.
        $form_data = isset($_POST['form_data']) ? $_POST['form_data'] : array();
        
        if (!$form_id) {
            wp_send_json_error('Invalid form ID');
        }
        
        $fields = CCF_Database::get_form_fields($form_id);
        
        $errors = $this->validate_form_data($form_data, $fields);
        
        if (!empty($errors)) {
            wp_send_json_error(array('errors' => $errors));
        }
        
        $sanitized_data = $this->sanitize_form_data($form_data, $fields);
        
        $submission_id = CCF_Database::save_submission($form_id, $sanitized_data);
        
        if ($submission_id) {
            // Send email notification and check if it was successful.
            $email_sent = $this->send_email_notification($form_id, $sanitized_data, $fields);
            
            $success_message = get_option('ccf_success_message', 'Thank you! Your message has been sent successfully.');

            // Add a note if the email failed to send, for debugging purposes.
            if (!$email_sent) {
                $success_message .= ' (Note: Email could not be sent. Please check your hosting email configuration.)';
            }
            
            wp_send_json_success(array(
                'message' => $success_message,
                'submission_id' => $submission_id
            ));
        } else {
            wp_send_json_error('Error saving submission');
        }
    }
    
    private function validate_form_data($form_data, $fields) {
        $errors = array();
        
        foreach ($fields as $field) {
            $field_name = 'field_' . $field->id;
            $value = isset($form_data[$field_name]) ? wp_unslash($form_data[$field_name]) : '';
            
            if ($field->required && empty($value)) {
                $errors[$field_name] = sprintf('%s is required', $field->label);
                continue;
            }
            
            if (!empty($value)) {
                switch ($field->type) {
                    case 'email':
                        if (!is_email($value)) {
                            $errors[$field_name] = 'Please enter a valid email address';
                        }
                        break;
                        
                    case 'tel':
                        if (!preg_match('/^[\d\s\+\-\(\)]{7,}$/', $value)) {
                            $errors[$field_name] = 'Please enter a valid phone number';
                        }
                        break;
                }
            }
        }
        
        return $errors;
    }
    
    private function sanitize_form_data($form_data, $fields) {
        $sanitized = array();
        
        foreach ($fields as $field) {
            $field_name = 'field_' . $field->id;
            $value = isset($form_data[$field_name]) ? wp_unslash($form_data[$field_name]) : '';
            
            switch ($field->type) {
                case 'email':
                    $value = sanitize_email($value);
                    break;
                case 'textarea':
                    $value = sanitize_textarea_field($value);
                    break;
                case 'checkbox':
                    $value = is_array($value) ? array_map('sanitize_text_field', $value) : sanitize_text_field($value);
                    break;
                default:
                    $value = sanitize_text_field($value);
            }
            
            $sanitized[$field->id] = array(
                'label' => $field->label,
                'value' => $value
            );
        }
        
        return $sanitized;
    }
    
    /**
     * Sends the email notification.
     *
     * @param int   $form_id The ID of the form.
     * @param array $data    The sanitized form data.
     * @param array $fields  The form fields configuration.
     * @return bool True if the email was sent successfully, false otherwise.
     */
    private function send_email_notification($form_id, $data, $fields) {
        $to = get_option('ccf_email_to', get_option('admin_email'));
        $subject = get_option('ccf_email_subject', 'New form submission from ' . get_bloginfo('name'));
        
        // Find the user's email from submitted data to use as a Reply-To address
        $reply_to_email = '';
        foreach ($fields as $field) {
            if ($field->type === 'email') {
                if (isset($data[$field->id]) && is_email($data[$field->id]['value'])) {
                    $reply_to_email = $data[$field->id]['value'];
                    break; // Use the first email field found
                }
            }
        }

        $email_body = $this->build_email_body($form_id, $data);
        
        // Build email headers for better deliverability
        $headers = array();
        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        
        // Set a proper From address from your domain to avoid spam filters
        $site_name = get_bloginfo('name');
        $site_domain = preg_replace('#^www\.#', '', strtolower($_SERVER['SERVER_NAME']));
        $from_email = 'wordpress@' . $site_domain;
        $headers[] = "From: {$site_name} <{$from_email}>";

        // Add Reply-To header if a user email was found in the form
        if (!empty($reply_to_email)) {
            $headers[] = "Reply-To: <{$reply_to_email}>";
        }
        
        // Send the email and return the result
        return wp_mail($to, $subject, $email_body, $headers);
    }
    
    private function build_email_body($form_id, $data) {
        ob_start();
        // Pass data to the template via a variable
        $email_data = $data;
        include CCF_PLUGIN_DIR . 'templates/email-template.php';
        return ob_get_clean();
    }
}
