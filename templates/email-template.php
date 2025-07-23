<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Form Submission from <?php echo esc_html(get_bloginfo('name')); ?></title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';
            background-color: #f4f4f7;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .wrapper {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e0e0e0;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .header {
            background-color: #4A90E2; /* A nice blue color */
            color: #ffffff;
            padding: 25px;
            text-align: center;
        }
        .header h2 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .content {
            padding: 30px;
        }
        .intro-text {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 25px;
        }
        .field-row {
            margin-bottom: 18px;
            padding-bottom: 18px;
            border-bottom: 1px solid #eeeeee;
        }
        .field-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .field-label {
            font-weight: 600;
            margin-bottom: 8px;
            color: #4A90E2;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .field-value {
            font-size: 16px;
            line-height: 1.6;
            word-break: break-word;
        }
        .field-value a {
            color: #4A90E2;
        }
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #999999;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h2>New Form Submission</h2>
        </div>
        <div class="content">
            <p class="intro-text">You have received a new submission from your website <strong><?php echo esc_html(get_bloginfo('name')); ?></strong>.</p>
            
            <?php foreach ($data as $field_id => $field_data): ?>
            <div class="field-row">
                <div class="field-label"><?php echo esc_html($field_data['label']); ?></div>
                <div class="field-value">
                    <?php 
                        $value = $field_data['value'];
                        if (is_array($value)) {
                            echo esc_html(implode(', ', $value));
                        } elseif (filter_var($value, FILTER_VALIDATE_URL)) {
                            echo '<a href="' . esc_url($value) . '" target="_blank">' . esc_html($value) . '</a>';
                        } else {
                            echo nl2br(esc_html($value));
                        }
                    ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="footer">
            <p>This email was sent from your WordPress site on <?php echo date('F j, Y'); ?>.</p>
        </div>
    </div>
</body>
</html>
