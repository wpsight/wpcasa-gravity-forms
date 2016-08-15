<?php
/*
Plugin Name: WPCasa Gravity Forms
Plugin URI: https://wpcasa.com/downloads/wpcasa-gravityforms
Description: Add support for Gravity Forms to attach property details to the contact email sent from WPCasa listing pages.
Version: 1.0.0
Author: WPSight
Author URI: http://wpsight.com
Requires at least: 4.0
Tested up to: 4.6
Text Domain: wpcasa-gravityforms
Domain Path: /languages

	Copyright: 2015 Simon Rimkus
	License: GNU General Public License v2.0 or later
	License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * WPSight_Gravity_Forms class
 */
class WPSight_Gravity_Forms {

	/**
	 * Constructor
	 */
	public function __construct() {

		// Define constants
		
		if ( ! defined( 'WPSIGHT_NAME' ) )
			define( 'WPSIGHT_NAME', 'WPCasa' );

		if ( ! defined( 'WPSIGHT_DOMAIN' ) )
			define( 'WPSIGHT_DOMAIN', 'wpcasa' );

		define( 'WPSIGHT_GRAVITYFORMS_NAME', 'WPCasa Gravity Forms' );
		define( 'WPSIGHT_GRAVITYFORMS_DOMAIN', 'wpcasa-gravityforms' );
		define( 'WPSIGHT_GRAVITYFORMS_VERSION', '1.0.0' );
		define( 'WPSIGHT_GRAVITYFORMS_PLUGIN_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
		define( 'WPSIGHT_GRAVITYFORMS_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );

		if ( is_admin() ){
			include( WPSIGHT_GRAVITYFORMS_PLUGIN_DIR . '/includes/admin/class-wpsight-gravityforms-admin.php' );
			$this->admin = new WPSight_Gravity_Forms_Admin();
		}

		// Actions

		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );
		add_action( 'template_redirect', array( $this, 'listing_form_display' ) );
		
		// Filters
		
		add_filter( 'gform_field_value_listing_title', array( $this, 'listing_title' ) );
		add_filter( 'gform_field_value_listing_id', array( $this, 'listing_id' ) );
		add_filter( 'gform_field_value_listing_url', array( $this, 'listing_url' ) );
		add_filter( 'gform_field_value_listing_agent', array( $this, 'listing_agent' ) );		
		add_filter( 'gform_email_fields_notification_admin', array( $this, 'email_to_field' ), 10, 2 );

	}

	/**
	 *	init()
	 *
	 *  Initialize the plugin when WPCasa is loaded
	 *
	 *  @param  object  $wpsight
	 *	@uses	do_action_ref_array()
	 *  @return object
	 *
	 *	@since	1.0.0
	 */
	public static function init( $wpsight ) {
		if ( ! isset( $wpsight->gravityforms ) ) {
			$wpsight->gravityforms = new self();
		}
		do_action_ref_array( 'wpsight_init_gravityforms', array( &$wpsight ) );

		return $wpsight->gravityforms;
	}

	/**
	 *	load_plugin_textdomain()
	 *	
	 *	Set up localization for this plugin
	 *	loading the text domain.
	 *	
	 *	@uses	load_plugin_textdomain()
	 *	
	 *	@since	1.0.0
	 */

	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'wpcasa-gravityforms', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
	
	/**
	 *	frontend_scripts()
	 *	
	 *	Register and enqueue scripts and css.
	 *	
	 *	@uses	wpsight_post_type()
	 *	@uses	wp_enqueue_style()
	 *	@uses	wpsight_get_option()
	 *	
	 *	@since	1.0.0
	 */
	public function frontend_scripts() {
		
		// Script debugging?
		$suffix = SCRIPT_DEBUG ? '' : '.min';
		
		if( is_singular( wpsight_post_type() ) && wpsight_get_option( 'gravityforms_listing_form_css' ) )
			wp_enqueue_style( 'wpsight-gravityforms', WPSIGHT_GRAVITYFORMS_PLUGIN_URL . '/assets/css/wpsight-gravityforms' . $suffix . '.css' );

	}
	
	/**
	 *	listing_title()
	 *
	 *	Prepopulate listing_title field
	 *	with current post title.
	 *	
	 *	@return	string
	 *
	 *	@since	1.0.0
	 */	
	function listing_title() {
	    global $post;
	
	    return $post->post_title;
	
	}
	
	/**
	 *	listing_id()
	 *
	 *	Prepopulate listing_id field
	 *	with current listing ID.
	 *	
	 *	@uses	wpsight_get_listing_id()
	 *	@return	string
	 *
	 *	@since	1.0.0
	 */	
	function listing_id() {
	    global $post;
	
	    return wpsight_get_listing_id( $post->ID );
	
	}
	
	/**
	 *	listing_url()
	 *
	 *	Prepopulate listing_url field
	 *	with current listing URL.
	 *	
	 *	@uses	get_permalink()
	 *	@return	string
	 *
	 *	@since	1.0.0
	 */	
	function listing_url() {
	    global $post;
	
	    return get_permalink( $post->ID );
	
	}
	
	/**
	 *	listing_agent()
	 *
	 *	Prepopulate agent email field
	 *	with current post authors email.
	 *	
	 *	@uses	get_the_author_meta()
	 *	@uses	antispambot()
	 *	
	 *	@return	string
	 *
	 *	@since	1.0.0
	 */	
	function listing_agent() {
	    global $post;
	
	    $author_email = antispambot( get_the_author_meta( 'email', $post->post_author ) );
	
	    return $author_email;
	
	}
	
	/**
	 *	email_to_field()
	 *
	 *  Add our agent email hidden field to the
	 *	list of Sent To fields. By default only
	 *	fields of type email are listed there.
	 *
	 *  @param  array 	$email_fields	Array of email field objects
	 *	@param	array	$form			Array of form object
	 *  @return array
	 *
	 *	@since	1.0.0
	 */
	public function email_to_field( $email_fields, $form ) {
		
		if( isset( $form['fields'] ) && is_array( $form['fields'] ) ) {
		
			foreach( $form['fields'] as $key => $field ) {
				
				if( isset( $field['inputName'] ) && 'listing_agent' == $field['inputName'] )
					$email_fields[] = $field;
				
			}
		
		}
		
		return $email_fields;
		
	}
	
	/**
	 *	default_form()
	 *
	 *  Create the default listing contact
	 *	form that is created when this
	 *	add-on is activated.
	 *
	 *	@uses	file_get_contents()
	 *	@uses	json_decode()	
	 *	@return	string
	 *
	 *	@since	1.0.0
	 */
	public static function default_form() {

		// Get starter form
		$file = file_get_contents( untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/wpcasa-gravityforms-starter.json' );
		
		// Convert into array
		$form = json_decode( $file, true );
		
		// And return the first (and only) one
		return $form[0];

	}
	
	/**
	 *	listing_form_display()
	 *
	 *  Gets display option and current page.
	 *	Then fires corresponding action hook
	 *	to display the form on the listing page.
	 *
	 *	@uses	wpsight_post_type()
	 *	@uses	wpsight_get_option()
	 *	@uses	add_action()
	 *
	 *	@since 1.0.0
	 */
	public function listing_form_display() {
		
		if( is_singular( wpsight_post_type() ) && wpsight_get_option( 'gravityforms_listing_form_display' ) )
			add_action( wpsight_get_option( 'gravityforms_listing_form_display' ), array( $this, 'listing_form' ) );
		
	}
	
	/**
	 *	listing_form()
	 *
	 *  Displays a form when there is one
	 *	selected on the settings page.
	 *
	 *	@uses	wpsight_get_option()
	 *	@uses	gravity_form()
	 *
	 *	@since 1.0.0
	 */
	public function listing_form() {
		
		if( wpsight_get_option( 'gravityforms_listing_form_id' ) )			
			gravity_form( wpsight_get_option( 'gravityforms_listing_form_id' ), true, true, false, null, true, 1, true );
		
	}

	/**
	 *	activation()
	 *	
	 *	Callback for register_activation_hook
	 *	to create some default options to be
	 *	used by this plugin.
	 *
	 *	@uses	self::default_form()
	 *	@uses	GFAPI::add_form()
	 *	@uses	wpsight_get_option()
	 *	@uses	wpsight_add_option()
	 *	
	 *	@since	1.0.0
	 */
	public static function activation() {
		
		$contact_form_id	= false;
		$default_form		= self::default_form();
		
		if( class_exists( 'GFAPI' ) ) {		
			$contact_form_id = ! wpsight_get_option( 'gravityforms_listing_form_id' ) ? GFAPI::add_form( $default_form ) : false;		
		}

		// Add some default options

		$options = array(
			'gravityforms_listing_form_id'		=> $contact_form_id,
			'gravityforms_listing_form_css'		=> '1',
			'gravityforms_listing_form_display'	=> 'wpsight_listing_single_after'
		);

		foreach( $options as $option => $value ) {

			if( wpsight_get_option( $option ) )
				continue;

			wpsight_add_option( $option, $value );

		}

	}
	
}

/**
 *	Check if Gravity Forms plugin is active
 *	and activate our add-on if yes.
 *
 *	@since	1.0.0
 */
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

if( is_plugin_active( 'gravityforms/gravityforms.php' ) ) {

	// Run activation hook
	register_activation_hook( __FILE__, array( 'WPSight_Gravity_Forms', 'activation' ) );
		
	// Initialize plugin on wpsight_init
	add_action( 'wpsight_init', array( 'WPSight_Gravity_Forms', 'init' ) );

}
