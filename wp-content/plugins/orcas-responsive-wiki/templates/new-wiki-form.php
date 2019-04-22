<div id="responsive-wiki-new-form" class="new-wiki-form">
    <div>
        <button id="wiki-form-back-btn" title="<?php echo __('Back', 'orcas-responsive-wiki'); ?>" class="btn btn-back back-from-form"><?php echo __('Back', 'orcas-responsive-wiki'); ?></button>
        <h3 id="responsive-wiki-form" class="detail-header" data-new="<?php echo __('New wiki', 'orcas-responsive-wiki'); ?>" data-edit="<?php echo __('Update wiki', 'orcas-responsive-wiki'); ?>"><?php echo __('New wiki', 'orcas-responsive-wiki'); ?></h3>
    </div>
    <form method="post">
        <?php
            if(count($wikiFieldList) > 0) {
                foreach($wikiFieldList as $field) {
                        ?>
            <div class="input-set <?php echo isset($field['container']) && isset($field['container']['class']) ? $field['container']['class'] : ''?>">
                <?php
                    if(isset($field['type']) && $field['type'] == 'textarea') {
                        ?>
                        <textarea id="wiki-<?php echo esc_attr($field['name']); ?>" class="wiki-<?php echo esc_attr($field['name']); ?> <?php echo isset($field['class']) ? esc_attr($field['class']) : ''; ?>" name="wiki-<?php echo esc_attr($field['name']); ?>"><?php echo (isset($field['default']) ? esc_html($field['default']) : '')?></textarea>
                        <?php
                        echo '<span class="small">' . apply_filters('wiki_' . $field['name'] . '_field_description', isset($field['description']) ? $field['description'] : '') . '</span>';
                    } else if(isset($field['type']) && $field['type'] == 'select') {
                        ?>

                        <select <?php echo isset($field['required']) ? 'required' : ''; ?> id="wiki-<?php echo esc_attr($field['name']); ?>" class="wiki-<?php echo esc_attr($field['name']); ?> <?php echo isset($field['class']) ? esc_attr($field['class']) : ''; ?>" name="wiki-<?php echo esc_attr($field['name']); ?>">
                            <option value=""><?php echo __('Choose a value', 'orcas-responsive-wiki'); ?></option>
                            <?php
                                if(is_array($field['values']) && $field['values']) {
                                    foreach($field['values'] as $option) {
                                        $option = esc_html($option);
                                        echo "<option " . (isset($field['default']) && $field['default'] == $option ? ' selected' : '') . " value='$option'>$option</option>";
                                    }
                                }
                            ?>
                        </select>

                        <?php
                        echo '<span class="small">' . apply_filters('wiki_' . $field['name'] . '_field_description', isset($field['description']) ? $field['description'] : '') . '</span>';
                    } else if(isset($field['type']) && $field['type'] == 'custom') { ?>
                       <div <?php echo isset($field['required']) ? 'required' : ''; ?> id="wiki-<?php echo esc_attr($field['name']); ?>" class="wiki-<?php echo esc_attr($field['name']); ?> <?php echo isset($field['class']) ? esc_attr($field['class']) : ''; ?>" name="wiki-<?php echo esc_attr($field['name']); ?>">
                           <?php echo $field['html']; ?>
                       </div>
                    <?php }else {
                ?>
                <input
                    <?php echo isset($field['required']) ? 'required' : ''; ?>
                        type="<?php echo isset($field['type']) ? esc_attr($field['type']) : 'text'?>"
                        class="wiki-<?php echo esc_attr($field['name']); ?> <?php echo isset($field['class']) ? esc_attr($field['class']) : ''; ?>"
                        name="wiki-<?php echo esc_attr($field['name']); ?>"
                        id="wiki-<?php echo esc_attr($field['name']); ?>"
                        <?php echo (isset($field['default']) ? "value='" . esc_attr($field['default']) . "'" : '')?>>
                        <?php echo '<span class="small">' . esc_html(apply_filters('wiki_' . $field['name'] . '_field_description', isset($field['description']) ? $field['description'] : '')) . '</span>'; ?>
                    <?php } ?>
                        <label for="wiki-<?php echo esc_attr($field['name']); ?>"><?php echo esc_html($field['label']); ?></label>
            </div>

        <?php
                }
            }

            wp_nonce_field( 'new-wiki');
        ?>

        <div class="input-set wiki-save">
            <input type="hidden" id="wiki-id" name="wiki-id" class="wiki-id">
            <button title="<?php echo __('Save', 'orcas-responsive-wiki'); ?>" type="submit" name="new-wiki-submit" class="btn btn-new-wiki-submit"><?php echo __('Save', 'orcas-responsive-wiki'); ?></button>
        </div>
        <div id="form-create-close-btn" class="btn" style="display: none"><?php echo __('Abort', 'orcas-responsive-wiki'); ?></div>
    </form>
    <form method="post" class="wiki-delete-beside-save">
        <?php wp_nonce_field( 'delete-wiki'); ?>
        <input type="hidden" id="wiki-delete-id" name="wiki-delete-id" class="wiki-id">
        <input type="hidden" id="wiki-page-delete" name="wiki-page-delete" value="1" class="wiki-id">
        <button title="<?php echo __('Delete', 'orcas-responsive-wiki'); ?>" type="submit" name="delete-wiki-submit" class="btn btn-delete-wiki-submit"><?php echo __('Delete', 'orcas-responsive-wiki'); ?></button>
    </form>
    <figure class="loader">
        <div></div><div></div>
        <div></div><div></div>
        <div></div><div></div>
        <div></div><div></div>
    </figure>
</div>