<?php
/**
 * Developer : MahdiY
 * Web Site  : MahdiY.IR
 * E-Mail    : M@hdiY.IR
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'PWS_Map_Neshan' ) ) {
	return;
} // Stop if the class already exists

final class PWS_Map_Neshan extends PWS_Map_Service {

	public function __construct() {
		parent::__construct();

		$this->set_api_key( PWS()->get_option( 'map.neshan_api_key', '' ) );

		$this->set_map_params( 'api_key', base64_encode( $this->get_api_key() ) );

	}

	public function initialize_hooks() {
		parent::initialize_hooks();
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ], 1000 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Enqueue specific map script and styles
	 *
	 * @param string $hook_suffix
	 *
	 * @return bool
	 */
	public function enqueue_scripts( string $hook_suffix = '' ): bool {

		if ( ! parent::enqueue_scripts( $hook_suffix ) ) {
			return false;
		};

		wp_enqueue_style( 'pws-map-neshan-sdk', 'https://static.neshan.org/sdk/leaflet/v1.9.4/neshan-sdk/v1.0.8/index.css', [], PWS_VERSION );

		wp_enqueue_script( 'pws-map-neshan-sdk', 'https://static.neshan.org/sdk/leaflet/v1.9.4/neshan-sdk/v1.0.8/index.js', [ 'jquery' ], PWS_VERSION );

		wp_enqueue_script( 'pws-map-neshan', PWS_URL . 'assets/maps/neshan/neshan-leaflet.js', [ 'pws-map-general', 'pws-map-neshan-sdk', 'jquery' ], PWS_VERSION );

		wp_localize_script( 'pws-map-neshan', 'pws_map_params', $this->get_map_params() );

		return true;
	}

	/**
	 * Customize shortcode based on neshan specific settings
	 *
	 * @param array $atts
	 *
	 * @return string
	 */
	public function shortcode_callback( array $atts ): string {
		$atts['type']    = PWS()->get_option( 'map.neshan_type', 'vector' );
		$atts['poi']     = 'true';
		$atts['traffic'] = 'false';

		return parent::shortcode_callback( $atts );
	}

}
