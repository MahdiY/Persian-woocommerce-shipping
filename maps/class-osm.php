<?php
/**
 * OpenStreet map module
 * @since 4.0.4
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'PWS_Map_OSM' ) ) {
	return;
} // Stop if the class already exists

final class PWS_Map_OSM extends PWS_Map_Service {

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

		wp_enqueue_script( 'pws-map-OSM', PWS_URL . 'assets/maps/osm/osm-leaflet.js', [
			'pws-map-general',
			'pws-map-leaflet',
			'jquery',
		], PWS_VERSION );

		wp_localize_script( 'pws-map-OSM', 'pws_map_params', $this->get_map_params() );

		return true;
	}

	/**
	 * Add extra info html in OSM map to show custom messages
	 *
	 * @return string
	*/
	public function get_extra_html(): string {
		return "<div class='pws-map__OSM__info' style='display:none;'></div>";
	}

}
