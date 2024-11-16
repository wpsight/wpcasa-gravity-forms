<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * WPSight_Gravity_Forms_Admin class
 */
class WPSight_Gravity_Forms_Admin {

	/**
	 *	Constructor
	 */
	public function __construct() {
		
		// Add add-on options to general plugin settings
		add_filter( 'wpsight_options', array( $this, 'options' ) );

	}

	/**
	 *	options()
	 *
	 *	Add add-on options tab to
	 *	general plugin settings.
	 *
	 *	@param	array	Incoming plugin options
	 *	@uses	GFAPI::get_forms()
	 *	@uses	wpsight_get_option()
	 *	@return array	Updated options array
	 *
	 *	@since 1.0.0
	 */
	public function options( $options ) {
        $icon = 'dashicons dashicons-email';
        $name = __( 'Gravity Forms', 'wpcasa-gravity-forms' );

		// Prepare forms option
		$forms = array( '' => __( 'None', 'wpcasa-gravity-forms' ) );
		
		foreach ( GFAPI::get_forms() as $key => $form ) {			
			$id = $form['id'];
			$forms[ $id ] = $form['title'];
		}

		$options_gravity = array(
            'gravityforms_pageheading' => array(
                'name' 		=> $name,
                'desc' 		=> '',
                'icon'		=> $icon,
                'link'		=> 'https://docs.wpcasa.com/article/wpcasa-gravity-forms/',
                'id' 		=> 'gravity_pageheading',
                'type' 		=> 'pageheading'
            ),
			'gravityforms_listing_form_id' => array(
				'name'		=> __( 'Listing Form', 'wpcasa-gravity-forms' ),
				'desc'		=> __( 'Select the form that you want to use on listing pages.', 'wpcasa-gravity-forms' ),
				'id'		=> 'gravityforms_listing_form_id',
				'type'		=> 'select',
				'options'	=> $forms
			)

		);
		
		$form_id = wpsight_get_option( 'gravityforms_listing_form_id' );
		
		if( $form_id ) {
		
			$options_gravity['gravityforms_listing_form_display'] = array(
				'name'		=> __( 'Form Display', 'wpcasa-gravity-forms' ),
				'desc'		=> __( 'Select where to display the listing form or choose to manually add the form via shortcode or function.', 'wpcasa-gravity-forms' ),
				'id'		=> 'gravityforms_listing_form_display',
				'type'		=> 'select',
				'options'	=> array(
					'wpsight_listing_single_after'				=> __( 'At the end', 'wpcasa-gravity-forms' ),
					'wpsight_listing_single_details_after'		=> __( 'After details', 'wpcasa-gravity-forms' ),
					'wpsight_listing_single_description_after'	=> __( 'After description', 'wpcasa-gravity-forms' ),
					'wpsight_listing_single_features_after'		=> __( 'After features', 'wpcasa-gravity-forms' ),
					'wpsight_listing_single_location_after'		=> __( 'After location', 'wpcasa-gravity-forms' ),
					'wpsight_listing_single_agent_after'		=> __( 'After agent', 'wpcasa-gravity-forms' ),
					''											=> __( 'Do not display', 'wpcasa-gravity-forms' )
				)
			);
			
		}
		
		$options_gravity['gravityforms_listing_form_css'] = array(
			'name'		=> __( 'Form CSS', 'wpcasa-gravity-forms' ),
			'cb_label'	=> __( 'Please uncheck the box to disable the plugin from outputting CSS.', 'wpcasa-gravity-forms' ),
			'id'		=> 'gravityforms_listing_form_css',
			'type'		=> 'checkbox'
		);

        if ( version_compare( '1.1.0', WPSIGHT_VERSION, '<' ) ) {
            $name = '<span class="' . $icon . '"></span>' . $name;
        }

		$options['gravityforms'] = array(
            $name,
			apply_filters( 'wpsight_options_gravityforms', $options_gravity )
		);

		return $options;

	}

}
