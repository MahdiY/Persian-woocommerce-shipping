<?php
/**
 * Developer : MahdiY
 * Web Site  : MahdiY.IR
 * E-Mail    : M@hdiY.IR
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'PWS_Map_Mapp' ) ) {
	return;
} // Stop if the class already exists

final class PWS_Map_Mapp extends PWS_Map_Service {

	public function __construct() {
		parent::__construct();

		$this->set_api_key( PWS()->get_option( 'map.mapp_api_key', '' ) );
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

		wp_enqueue_style( 'pws-map-mapp-style-main', PWS_URL . 'assets/maps/mapp/css/mapp.min.css', [], PWS_VERSION );
		wp_enqueue_style( 'pws-map-mapp-style-lang', PWS_URL . 'assets/maps/mapp/css/fa/style.css', [], PWS_VERSION );

		wp_enqueue_script( 'pws-map-mapp', PWS_URL . 'assets/maps/mapp/mapp-leaflet.js', [ 'pws-map-general', 'pws-map-leaflet', 'jquery', ], PWS_VERSION );
		wp_localize_script( 'pws-map-mapp', 'pws_map_params', $this->get_map_params() );

		wp_enqueue_script( 'pws-map-mapp-env', PWS_URL . 'assets/maps/mapp/js/mapp.env.js', [ 'pws-map-mapp', 'jquery', ], PWS_VERSION );
		wp_enqueue_script( 'pws-map-mapp-source', PWS_URL . 'assets/maps/mapp/js/mapp.min.js', [ 'pws-map-mapp', 'pws-map-mapp-env', 'jquery', ], PWS_VERSION );

		return true;
	}

}
