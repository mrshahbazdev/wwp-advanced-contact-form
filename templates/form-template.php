<div class="ccf-form-container">
    <form id="ccf-form-<?php echo $form->id; ?>" class="ccf-form" method="post" enctype="multipart/form-data">
        <input type="hidden" name="form_id" value="<?php echo $form->id; ?>">
        
        <?php if (!empty($form->title)): ?>
        <h3 class="ccf-form-title"><?php echo esc_html($form->title); ?></h3>
        <?php endif; ?>
        
        <?php if (!empty($form->description)): ?>
        <div class="ccf-form-description"><?php echo wpautop(esc_html($form->description)); ?></div>
        <?php endif; ?>
        
        <div class="ccf-form-fields">
            <?php foreach ($fields as $field): ?>
            <div class="ccf-field ccf-field-<?php echo esc_attr($field->type); ?> <?php echo esc_attr($field->class); ?>">
                <label for="ccf-field-<?php echo $field->id; ?>">
                    <?php echo esc_html($field->label); ?>
                    <?php if ($field->required): ?>
                    <span class="ccf-required">*</span>
                    <?php endif; ?>
                </label>
                
                <?php $this->render_field($field); ?>
                
                <div class="ccf-field-error" id="ccf-error-<?php echo $field->id; ?>"></div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="ccf-form-footer">
            <button type="submit" class="ccf-submit-button">
                <span class="ccf-submit-text">Submit</span>
                <span class="ccf-spinner"></span>
            </button>
        </div>
        
        <div class="ccf-form-message"></div>
    </form>
</div>