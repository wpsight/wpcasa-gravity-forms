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

		// Prepare forms option
		$forms = array( '' => __( 'None', 'wpcasa-gravityforms' ) );
		
		foreach ( GFAPI::get_forms() as $key => $form ) {			
			$id = $form['id'];
			$forms[ $id ] = $form['title'];
		}

		$options_gravity = array(

			'gravityforms_listing_form_id' => array(
				'name'		=> __( 'Listing Form', 'wpcasa-gravityforms' ),
				'desc'		=> __( 'Select the form that you want to use on listing pages.', 'wpcasa-gravityforms' ),
				'id'		=> 'gravityforms_listing_form_id',
				'type'		=> 'select',
				'options'	=> $forms
			)

		);
		
		$form_id = wpsight_get_option( 'gravityforms_listing_form_id' );
		
		if( $form_id ) {
		
			$options_gravity['gravityforms_listing_form_display'] = array(
				'name'		=> __( 'Form Display', 'wpcasa-gravityforms' ),
				'desc'		=> __( 'Select where to display the listing form or choose to manually add the form via shortcode or function.', 'wpcasa-gravityforms' ),
				'id'		=> 'gravityforms_listing_form_display',
				'type'		=> 'select',
				'options'	=> array(
					'wpsight_listing_single_after'				=> __( 'At the end', 'wpcasa-gravityforms' ),
					'wpsight_listing_single_details_after'		=> __( 'After details', 'wpcasa-gravityforms' ),
					'wpsight_listing_single_description_after'	=> __( 'After description', 'wpcasa-gravityforms' ),
					'wpsight_listing_single_features_after'		=> __( 'After features', 'wpcasa-gravityforms' ),
					'wpsight_listing_single_location_after'		=> __( 'After location', 'wpcasa-gravityforms' ),
					'wpsight_listing_single_agent_after'		=> __( 'After agent', 'wpcasa-gravityforms' ),
					''											=> __( 'Do not display', 'wpcasa-gravityforms' )
				)
			);
			
		}
		
		$options_gravity['gravityforms_listing_form_css'] = array(
			'name'		=> __( 'Form CSS', 'wpcasa-gravityforms' ),
			'cb_label'	=> __( 'Please uncheck the box to disable the plugin from outputting CSS.', 'wpcasa-gravityforms' ),
			'id'		=> 'gravityforms_listing_form_css',
			'type'		=> 'checkbox'
		);

		$options['gravityforms'] = array(
			__( 'Gravity Forms', 'wpcasa-gravityforms' ),
			apply_filters( 'wpsight_options_gravityforms', $options_gravity )
		);

		return $options;

	}

}
