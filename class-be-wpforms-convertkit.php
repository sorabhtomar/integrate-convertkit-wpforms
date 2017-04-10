<?php
/**
 * ConvertKit for WPForms
 *
 * @package    BE_WPForms_ConvertKit
 * @since      1.0.0
 * @copyright  Copyright (c) 2017, Bill Erickson
 * @license    GPL-2.0+
 */

class BE_WPForms_ConvertKit {

    /**
     * Primary Class Constructor
     *
     */
    public function __construct() {

        add_filter( 'wpforms_builder_settings_sections', array( $this, 'settings_section' ), 20, 2 );
        add_filter( 'wpforms_form_settings_panel_content', array( $this, 'settings_section_content' ), 20 );
        add_action( 'wpforms_process_complete', array( 'send_data_to_convertkit' ), 10, 4 );

    }


     /**
      * Add Settings Section
      *
      */
     function settings_section( $sections, $form_data ) {
         $sections['be_convertkit'] = __( 'ConvertKit', 'be_wpforms_convertkit' );
         return $sections;
     }


     /**
      * ConvertKit Settings Content
      *
      */
     function settings_section_content( $instance ) {
         echo '<div class="wpforms-panel-content-section wpforms-panel-content-section-be_convertkit">';
         echo '<div class="wpforms-panel-content-section-title">' . __( 'ConvertKit', 'be_wpforms_convertkit' ) . '</div>';

         wpforms_panel_field(
             'text',
             'settings',
             'be_convertkit_api',
             $instance->form_data,
             __( 'ConvertKit API Key', 'be_wpforms_convertkit' )
         );

         wpforms_panel_field(
             'text',
             'settings',
             'be_convertkit_form_id',
             $instance->form_data,
             __( 'ConvertKit Form ID', 'be_wpforms_convertkit' )
         );

         echo '</div>';
     }

     /**
      * Integrate WPForms with ConvertKit
      *
      */
     function send_data_to_convertkit( $fields, $entry, $form_data, $entry_id ) {

         // Get API key and CK Form ID
         $api_key = $ck_form_id = false;
         if( !empty( $form_data['settings']['be_convertkit_api'] ) )
             $api_key = esc_html( $form_data['settings']['be_convertkit_api'] );
         if( !empty( $form_data['settings']['be_convertkit_form_id'] ) )
             $ck_form_id = intval( $form_data['settings']['be_convertkit_form_id'] );

         if( ! ( $api_key && $ck_form_id ) )
             return;

         // Get email and first name
         $args = array( 'api_key' => $api_key, 'email' => false, 'first_name' => false );
         foreach( $form_data['fields'] as $i => $field ) {

             $classes = !empty( $field['css'] ) ? explode( ' ', $field['css'] ) : array();

             if( in_array( 'ck-first-name', $classes ) ) {
                 $args['first_name'] = $fields[$i]['value'];
             }

             if( in_array( 'ck-email', $classes ) ) {
                 $args['email'] = $fields[$i]['value'];
             }

         }

         if( empty( $args['email'] ) || empty( $args['first_name'] ) )
             return;

         // Submit to ConvertKit
         $request = wp_remote_post( add_query_arg( $args, 'https://api.convertkit.com/v3/forms/' . $ck_form_id . '/subscribe' ) );

     }

}
new BE_WPForms_ConvertKit;