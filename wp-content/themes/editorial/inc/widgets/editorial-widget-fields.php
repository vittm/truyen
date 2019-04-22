<?php
/**
 * Define custom fields for widgets
 * 
 * @package Mystery Themes
 * @subpackage Editorial
 * @since 1.0.0
 */

function editorial_widgets_show_widget_field( $instance = '', $widget_field = '', $athm_field_value = '' ) {
    
    extract( $widget_field );

    switch ( $editorial_widgets_field_type ) {

    	// Standard text field
        case 'text' :
        ?>
            <p>
                <label for="<?php echo esc_attr( $instance->get_field_id( $editorial_widgets_name ) ); ?>"><?php echo esc_html( $editorial_widgets_title ); ?>:</label>
                <input class="widefat" id="<?php echo esc_attr ( $instance->get_field_id( $editorial_widgets_name ) ); ?>" name="<?php echo esc_attr ( $instance->get_field_name( $editorial_widgets_name ) ); ?>" type="text" value="<?php echo esc_html( $athm_field_value ); ?>" />

                <?php if ( isset( $editorial_widgets_description ) ) { ?>
                    <br />
                    <small><?php echo esc_html( $editorial_widgets_description ); ?></small>
                <?php } ?>
            </p>
        <?php
            break;

        // Standard url field
        case 'url' :
        ?>
            <p>
                <label for="<?php echo esc_attr( $instance->get_field_id( $editorial_widgets_name ) ); ?>"><?php echo esc_html( $editorial_widgets_title ); ?>:</label>
                <input class="widefat" id="<?php echo esc_attr ( $instance->get_field_id( $editorial_widgets_name ) ); ?>" name="<?php echo esc_attr( $instance->get_field_name( $editorial_widgets_name ) ); ?>" type="text" value="<?php echo esc_html( $athm_field_value ); ?>" />

                <?php if ( isset( $editorial_widgets_description ) ) { ?>
                    <br />
                    <small><?php echo esc_html( $editorial_widgets_description ); ?></small>
                <?php } ?>
            </p>
        <?php
            break;

        // Checkbox field
        case 'checkbox' :
            ?>
            <p>
                <input id="<?php echo esc_attr( $instance->get_field_id( $editorial_widgets_name ) ); ?>" name="<?php echo esc_attr( $instance->get_field_name( $editorial_widgets_name ) ); ?>" type="checkbox" value="1" <?php checked( '1', $athm_field_value ); ?>/>
                <label for="<?php echo esc_attr( $instance->get_field_id( $editorial_widgets_name ) ); ?>"><?php echo esc_html( $editorial_widgets_title ); ?>:</label>

                <?php if ( isset( $editorial_widgets_description ) ) { ?>
                    <br />
                    <small><?php echo wp_kses_post( $editorial_widgets_description ); ?></small>
                <?php } ?>
            </p>
            <?php
            break;

        // Radio fields
        case 'radio' :
        	if( empty( $athm_field_value ) ) {
        		$athm_field_value = $editorial_widgets_default;
        	}
        ?>
            <p>
                <label for="<?php echo esc_attr( $instance->get_field_id( $editorial_widgets_name ) ); ?>"><?php echo esc_html( $editorial_widgets_title ); ?>:</label>
                <div class="radio-wrapper">
                    <?php
                        foreach ( $editorial_widgets_field_options as $athm_option_name => $athm_option_title ) {
                    ?>
                        <input id="<?php echo esc_attr( $instance->get_field_id( $athm_option_name ) ); ?>" name="<?php echo esc_attr( $instance->get_field_name( $editorial_widgets_name ) ); ?>" type="radio" value="<?php echo esc_html( $athm_option_name ); ?>" <?php checked( $athm_option_name, $athm_field_value ); ?> />
                        <label for="<?php echo esc_attr( $instance->get_field_id( $athm_option_name ) ); ?>"><?php echo esc_html( $athm_option_title ); ?>:</label>
                    <?php } ?>
                </div>

                <?php if ( isset( $editorial_widgets_description ) ) { ?>
                    <small><?php echo esc_html( $editorial_widgets_description ); ?></small>
                <?php } ?>
            </p>
        <?php
            break;

        // Select field
        case 'select' :
            if( empty( $athm_field_value ) ) {
                $athm_field_value = $editorial_widgets_default;
            }
        ?>
            <p>
                <label for="<?php echo esc_attr( $instance->get_field_id( $editorial_widgets_name ) ); ?>"><?php echo esc_html( $editorial_widgets_title ); ?>:</label>
                <select name="<?php echo esc_attr( $instance->get_field_name( $editorial_widgets_name ) ); ?>" id="<?php echo esc_attr( $instance->get_field_id( $editorial_widgets_name ) ); ?>" class="widefat">
                    <?php foreach ( $editorial_widgets_field_options as $athm_option_name => $athm_option_title ) { ?>
                        <option value="<?php echo esc_attr( $athm_option_name ); ?>" id="<?php echo esc_attr( $instance->get_field_id($athm_option_name) ); ?>" <?php selected( $athm_option_name, $athm_field_value ); ?>><?php echo esc_html( $athm_option_title ); ?></option>
                    <?php } ?>
                </select>

                <?php if ( isset( $editorial_widgets_description ) ) { ?>
                    <br />
                    <small><?php echo esc_html( $editorial_widgets_description ); ?></small>
                <?php } ?>
            </p>
        <?php
            break;

        case 'number' :
        	if( empty( $athm_field_value ) ) {
        		$athm_field_value = $editorial_widgets_default;
        	}
        ?>
            <p>
                <label for="<?php echo esc_attr( $instance->get_field_id( $editorial_widgets_name ) ); ?>"><?php echo esc_html( $editorial_widgets_title ); ?>:</label><br />
                <input name="<?php echo esc_attr( $instance->get_field_name( $editorial_widgets_name ) ); ?>" type="number" step="1" min="1" id="<?php echo esc_attr( $instance->get_field_id( $editorial_widgets_name ) ); ?>" value="<?php echo esc_html( $athm_field_value ); ?>" />

                <?php if ( isset( $editorial_widgets_description ) ) { ?>
                    <br />
                    <small><?php echo esc_html( $editorial_widgets_description ); ?></small>
                <?php } ?>
            </p>
       	<?php
            break;

        /**
         * Section Header field
         */

        case 'widget_section_header':
        ?>
        	<span class="section-header"><?php echo esc_html( $editorial_widgets_title ); ?></span>
        <?php
        	break;


        /**
         * Upload field
         */
        case 'upload':
            $image = $image_class = "";
            if( $athm_field_value ){ 
                $image = '<img src="'.esc_url( $athm_field_value ).'" style="max-width:100%;"/>';    
                $image_class = ' hidden';
            }
        ?>
            <div class="attachment-media-view">

            <p><span class="field-label"><label for="<?php echo esc_attr( $instance->get_field_id( $editorial_widgets_name ) ); ?>"><?php echo esc_html( $editorial_widgets_title ); ?>:</label></span></p>
            
                <div class="placeholder<?php echo esc_attr( $image_class ); ?>">
                    <?php esc_html_e( 'No image selected', 'editorial' ); ?>
                </div>
                <div class="thumbnail thumbnail-image">
                    <?php echo $image; ?>
                </div>

                <div class="actions np-clearfix">
                    <button type="button" class="button mt-delete-button align-left"><?php esc_html_e( 'Remove', 'editorial' ); ?></button>
                    <button type="button" class="button mt-upload-button alignright"><?php esc_html_e( 'Select Image', 'editorial' ); ?></button>
                    
                    <input name="<?php echo esc_attr( $instance->get_field_name( $editorial_widgets_name ) ); ?>" id="<?php echo esc_attr( $instance->get_field_id( $editorial_widgets_name ) ); ?>" class="upload-id" type="hidden" value="<?php echo esc_url( $athm_field_value ) ?>"/>
                </div>

            <?php if ( isset( $editorial_widgets_description ) ) { ?>
                <br />
                <em><?php echo wp_kses_post( $editorial_widgets_description ); ?></em>
            <?php } ?>

            </div><!-- .attachment-media-view -->
        <?php
            break;
    }
}

function editorial_widgets_updated_field_value( $widget_field, $new_field_value ) {

    extract( $widget_field );
    
    if ( $editorial_widgets_field_type == 'number') {
        return intval( $new_field_value );
    } elseif ( $editorial_widgets_field_type == 'url' ) {
        return esc_url_raw( $new_field_value );
    } else {
        return sanitize_text_field( $new_field_value );
    }
}