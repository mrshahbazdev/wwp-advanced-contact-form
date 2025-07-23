<?php
class CCF_Database {
    
    /**
     * Create the necessary database tables on plugin activation.
     */
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = array();
        
        // Using uppercase keywords and removing "IF NOT EXISTS" for better dbDelta compatibility.
        $sql[] = "CREATE TABLE {$wpdb->prefix}ccf_forms (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            title varchar(200) NOT NULL,
            description text,
            submit_button_text varchar(100) DEFAULT 'Submit',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        $sql[] = "CREATE TABLE {$wpdb->prefix}ccf_form_fields (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            form_id mediumint(9) NOT NULL,
            label varchar(200) NOT NULL,
            type varchar(50) NOT NULL,
            required tinyint(1) DEFAULT 0,
            placeholder varchar(200),
            options text,
            class varchar(100),
            field_order smallint(5) DEFAULT 0,
            PRIMARY KEY  (id),
            KEY form_id (form_id)
        ) $charset_collate;";
        
        $sql[] = "CREATE TABLE {$wpdb->prefix}ccf_submissions (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            form_id mediumint(9) NOT NULL,
            data text NOT NULL,
            ip_address varchar(45),
            user_agent varchar(255),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY form_id (form_id)
        ) $charset_collate;";
        
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
    
    public static function create_form($name, $title, $description = '', $submit_button_text = 'Submit') {
        global $wpdb;
        
        $wpdb->insert(
            "{$wpdb->prefix}ccf_forms",
            array(
                'name' => $name,
                'title' => $title,
                'description' => $description,
                'submit_button_text' => $submit_button_text
            ),
            array('%s', '%s', '%s', '%s')
        );
        
        return $wpdb->insert_id;
    }
    
    public static function update_form($id, $name, $title, $description = '', $submit_button_text = 'Submit') {
        global $wpdb;
        
        return $wpdb->update(
            "{$wpdb->prefix}ccf_forms",
            array(
                'name' => $name,
                'title' => $title,
                'description' => $description,
                'submit_button_text' => $submit_button_text
            ),
            array('id' => $id),
            array('%s', '%s', '%s', '%s'),
            array('%d')
        );
    }
    
    public static function get_form($id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}ccf_forms WHERE id = %d",
            $id
        ));
    }
    
    public static function get_forms() {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ccf_forms ORDER BY created_at DESC");
    }
    
    public static function delete_form($id) {
        global $wpdb;
        
        self::delete_form_fields($id);
        
        return $wpdb->delete(
            "{$wpdb->prefix}ccf_forms",
            array('id' => $id),
            array('%d')
        );
    }
    
    public static function add_form_field($form_id, $field_data) {
        global $wpdb;
        
        $wpdb->insert(
            "{$wpdb->prefix}ccf_form_fields",
            array(
                'form_id' => $form_id,
                'label' => $field_data['label'],
                'type' => $field_data['type'],
                'required' => $field_data['required'],
                'placeholder' => $field_data['placeholder'],
                'options' => maybe_serialize($field_data['options']),
                'class' => $field_data['class'],
                'field_order' => $field_data['order']
            ),
            array('%d', '%s', '%s', '%d', '%s', '%s', '%s', '%d')
        );
        
        return $wpdb->insert_id;
    }
    
    public static function get_form_fields($form_id) {
        global $wpdb;
        
        $fields = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}ccf_form_fields WHERE form_id = %d ORDER BY field_order ASC",
            $form_id
        ));
        
        foreach ($fields as $field) {
            if (!empty($field->options)) {
                $field->options = maybe_unserialize($field->options);
            }
        }
        
        return $fields;
    }
    
    public static function delete_form_fields($form_id) {
        global $wpdb;
        
        return $wpdb->delete(
            "{$wpdb->prefix}ccf_form_fields",
            array('form_id' => $form_id),
            array('%d')
        );
    }
    
    public static function save_submission($form_id, $data) {
        global $wpdb;
        
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        
        $wpdb->insert(
            "{$wpdb->prefix}ccf_submissions",
            array(
                'form_id' => $form_id,
                'data' => maybe_serialize($data),
                'ip_address' => $ip_address,
                'user_agent' => $user_agent
            ),
            array('%d', '%s', '%s', '%s')
        );
        
        return $wpdb->insert_id;
    }
    
    public static function get_submissions($form_id = 0, $limit = 20, $offset = 0) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ccf_submissions';

        if ($form_id) {
            $query = $wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE form_id = %d ORDER BY created_at DESC LIMIT %d OFFSET %d",
                $form_id, $limit, $offset
            );
        } else {
            $query = $wpdb->prepare(
                "SELECT * FROM {$table_name} ORDER BY created_at DESC LIMIT %d OFFSET %d",
                $limit, $offset
            );
        }
        
        $submissions = $wpdb->get_results($query);
        
        foreach ($submissions as $submission) {
            $submission->data = maybe_unserialize($submission->data);
        }
        
        return $submissions;
    }

    public static function count_submissions($form_id = 0) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'ccf_submissions';

        if ($form_id) {
            return $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM {$table_name} WHERE form_id = %d", $form_id));
        } else {
            return $wpdb->get_var("SELECT COUNT(id) FROM {$table_name}");
        }
    }
    
    public static function get_submission($id) {
        global $wpdb;
        
        $submission = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}ccf_submissions WHERE id = %d",
            $id
        ));
        
        if ($submission) {
            $submission->data = maybe_unserialize($submission->data);
        }
        
        return $submission;
    }
}
