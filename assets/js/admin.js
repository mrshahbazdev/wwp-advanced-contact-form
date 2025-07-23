jQuery(document).ready(function ($) {
     // Handle form deletion
    $('.ccf-delete-form').on('click', function(e) {
        e.preventDefault();
        if (confirm('Are you sure you want to delete this form?')) {
            var formId = $(this).data('form-id');
            $.post(ajaxurl, {
                action: 'ccf_delete_form',
                nonce: ccf_ajax.nonce,
                form_id: formId
            }, function(response) {
                if (response.success) {
                    // Reload the page to show the updated list of forms
                    location.reload();
                } else {
                    alert('Error: ' + response.data);
                }
            });
        }
    });
    // Check if we are on the form builder page
    if ($('#ccf-form-builder').length) {

        var fieldTypes = {
            'text': 'Text',
            'email': 'Email',
            'tel': 'Phone',
            'textarea': 'Textarea',
            'select': 'Dropdown',
            'checkbox': 'Checkbox',
            'radio': 'Radio Buttons',
            'file': 'File Upload'
        };

        // Make fields sortable
        $('#ccf-fields-container').sortable();

        // Load existing fields if editing a form
        if (typeof ccf_existing_fields !== 'undefined' && ccf_existing_fields.length > 0) {
            $.each(ccf_existing_fields, function (i, fieldData) {
                var options_str = '';
                if ($.isArray(fieldData.options)) {
                    options_str = fieldData.options.join('\n');
                }

                var newField = addFieldToBuilder({
                    type: fieldData.type,
                    label: fieldData.label,
                    placeholder: fieldData.placeholder,
                    required: fieldData.required == '1',
                    options: options_str
                });
            });
        }

        // Add field button click
        $('#ccf-available-fields').on('click', '.ccf-add-field-btn', function () {
            var type = $(this).data('type');
            var label = fieldTypes[type] || 'New Field';
            addFieldToBuilder({
                type: type,
                label: label
            });
        });

        // Toggle field settings
        $('#ccf-fields-container').on('click', '.ccf-field-header', function () {
            $(this).next('.ccf-field-settings').slideToggle();
        });

        // Update field preview label
        $('#ccf-fields-container').on('keyup', '.field-setting[data-setting="label"]', function () {
            var newLabel = $(this).val();
            $(this).closest('.ccf-field-item').find('.field-label-preview').text(newLabel || 'Untitled Field');
        });

        // Delete a field
        $('#ccf-fields-container').on('click', '.field-delete', function (e) {
            e.preventDefault();
            e.stopPropagation(); // Prevent toggling settings
            if (confirm('Are you sure you want to delete this field?')) {
                $(this).closest('.ccf-field-item').remove();
            }
        });

        // Save form
        $('#ccf-builder-form').on('submit', function (e) {
            e.preventDefault();
            var $button = $('#ccf-save-form-button');
            var $spinner = $button.next('.spinner');

            $button.prop('disabled', true);
            $spinner.addClass('is-active');

            var formData = {
                form_id: $('#ccf-form-id').val(),
                form_title: $('#title').val(),
                submit_button_text: $('#ccf-submit-button-text').val(), // Add this line
                form_description: '',
                fields: []
            };

            $('#ccf-fields-container .ccf-field-item').each(function (index) {
                var $field = $(this);
                var fieldData = {
                    type: $field.data('type'),
                    order: index
                };

                $field.find('.field-setting').each(function () {
                    var $setting = $(this);
                    var key = $setting.data('setting');
                    var value = $setting.is(':checkbox') ? $setting.is(':checked') : $setting.val();

                    if (key === 'options') {
                        value = value.split('\n').filter(Boolean); // Split by newline and remove empty lines
                    }

                    fieldData[key] = value;
                });
                formData.fields.push(fieldData);
            });

            // AJAX request to save the form
            $.post(ajaxurl, {
                    action: 'ccf_save_form',
                    nonce: ccf_ajax.nonce, // You need to localize this nonce for admin
                    form_data: formData
                }, function (response) {
                    if (response.success) {
                        // Redirect to the forms list page after successful save
                        window.location.href = 'admin.php?page=ccf-forms&ccf-message=saved';
                    } else {
                        alert('Error: ' + response.data || 'An unknown error occurred.');
                    }
                })
                .always(function () {
                    $button.prop('disabled', false);
                    $spinner.removeClass('is-active');
                });
        });

        function addFieldToBuilder(data) {
            var template = wp.template('ccf-field');
            var fieldData = $.extend({
                label: 'New Field',
                placeholder: '',
                required: false,
                options: ''
            }, data);

            fieldData.typeName = fieldTypes[fieldData.type];
            var $newField = $(template(fieldData));

            // Set values after appending
            $('#ccf-fields-container').append($newField);
            $newField.find('.field-setting[data-setting="label"]').val(fieldData.label);
            $newField.find('.field-setting[data-setting="placeholder"]').val(fieldData.placeholder);
            $newField.find('.field-setting[data-setting="required"]').prop('checked', fieldData.required);
            $newField.find('.field-setting[data-setting="options"]').val(fieldData.options);

            return $newField;
        }
    }
});