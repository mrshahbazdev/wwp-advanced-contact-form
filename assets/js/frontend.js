jQuery(document).ready(function($) {
    $('.ccf-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $submitBtn = $form.find('.ccf-submit-button');
        var $message = $form.find('.ccf-form-message');
        
        // Reset UI
        $form.find('.ccf-field-error').hide().empty();
        $message.hide().removeClass('success error').empty();
        $submitBtn.addClass('loading');
        
        // Collect form data
        var formData = new FormData($form[0]);
        
        // Add AJAX action
        formData.append('action', 'ccf_submit_form');
        formData.append('nonce', ccf_ajax.nonce);
        
        $.ajax({
            url: ccf_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json'
        })
        .done(function(response) {
            if (response.success) {
                $message.text(response.data.message).addClass('success').show();
                $form[0].reset();
            } else {
                if (response.data.errors) {
                    // Display field errors
                    $.each(response.data.errors, function(field, error) {
                        $('#ccf-error-' + field.replace('field_', '')).text(error).show();
                    });
                } else {
                    $message.text(response.data || 'An error occurred').addClass('error').show();
                }
            }
        })
        .fail(function(xhr, status, error) {
            $message.text('An error occurred: ' + error).addClass('error').show();
        })
        .always(function() {
            $submitBtn.removeClass('loading');
            
            // Scroll to message if there's one
            if ($message.is(':visible')) {
                $('html, body').animate({
                    scrollTop: $message.offset().top - 100
                }, 500);
            }
        });
    });
});