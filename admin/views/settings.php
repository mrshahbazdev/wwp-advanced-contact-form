<div class="wrap">
    <h1>Contact Form Settings</h1>
    
    <form method="post" action="options.php">
        <?php
        settings_fields('ccf_settings');
        do_settings_sections('ccf_settings');
        ?>
        
        <h2>Email Settings</h2>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">
                    <label for="ccf_email_to">Recipient Email</label>
                </th>
                <td>
                    <input type="email" id="ccf_email_to" name="ccf_email_to" value="<?php echo esc_attr(get_option('ccf_email_to', get_option('admin_email'))); ?>" class="regular-text" />
                    <p class="description">The email address where form submissions will be sent.</p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="ccf_from_email">"From" Email</label>
                </th>
                <td>
                    <?php
                        $site_domain = preg_replace('#^www\.#', '', strtolower($_SERVER['SERVER_NAME']));
                        $default_from_email = 'wordpress@' . $site_domain;
                    ?>
                    <input type="email" id="ccf_from_email" name="ccf_from_email" value="<?php echo esc_attr(get_option('ccf_from_email', $default_from_email)); ?>" class="regular-text" />
                    <p class="description">The email address messages are sent from. Using an address from your domain (e.g., <?php echo $default_from_email; ?>) is highly recommended.</p>
                </td>
            </tr>
            
            <tr valign="top">
                <th scope="row">
                    <label for="ccf_email_subject">Email Subject</label>
                </th>
                <td>
                    <input type="text" id="ccf_email_subject" name="ccf_email_subject" value="<?php echo esc_attr(get_option('ccf_email_subject', 'New Form Submission')); ?>" class="regular-text" />
                    <p class="description">The subject line for the notification email.</p>
                </td>
            </tr>
        </table>

        <h2>Messages & Styling</h2>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">
                    <label for="ccf_success_message">Success Message</label>
                </th>
                <td>
                    <textarea id="ccf_success_message" name="ccf_success_message" rows="4" class="large-text"><?php echo esc_textarea(get_option('ccf_success_message', 'Thank you! Your message has been sent successfully.')); ?></textarea>
                    <p class="description">The message shown to the user after a successful form submission.</p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="ccf_custom_css">Custom CSS</label>
                </th>
                <td>
                    <textarea id="ccf_custom_css" name="ccf_custom_css" rows="10" class="large-text code"><?php echo esc_textarea(get_option('ccf_custom_css')); ?></textarea>
                    <p class="description">Add custom CSS to style your forms.</p>
                </td>
            </tr>
        </table>
        
        <?php submit_button(); ?>
    </form>
</div>
