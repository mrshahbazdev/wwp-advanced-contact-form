<?php
// includes/predefined-forms.php
function get_ccf_predefined_forms() {
    return array(
        'simple_contact' => array(
            'name' => 'Simple Contact Form',
            'title' => 'Contact Us',
            'description' => 'A simple form for basic inquiries.',
            'fields' => array(
                array('label' => 'Your Name', 'type' => 'text', 'required' => 1, 'placeholder' => 'John Doe'),
                array('label' => 'Your Email', 'type' => 'email', 'required' => 1, 'placeholder' => 'john.doe@example.com'),
                array('label' => 'Subject', 'type' => 'text', 'required' => 0),
                array('label' => 'Your Message', 'type' => 'textarea', 'required' => 1, 'placeholder' => 'Write your message here...'),
            )
        ),
        'detailed_quote' => array(
            'name' => 'Request a Quote',
            'title' => 'Get a Free Quote',
            'description' => 'Fill this form to get a detailed quote for our services.',
            'fields' => array(
                // ... define fields for this form ...
            )
        )
    );
}