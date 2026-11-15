<?php
/**
 * Map implementation
 * The map configurator class
 * @since 4.0.4
 */

defined( 'ABSPATH' ) || exit;

abstract class PWS_Map_Service {

	/**
	 * General
	 * @string
	 */
	protected string $provider;

	/**
	 * General
	 * @string
	 */
	protected string $api_key;

	/**
	 * Specific values based on each api will be gathered in this property
	 * @array
	 */
	protected array $map_params;

	/**
	 * Used to attach map placement in specific hook
	 * General
	 * @string
	 */
	protected string $checkout_placement;

	/**
	 * Force user to select location or not
	 * @string
	 */
	protected string $required_location;

	public function __construct() {
		// Set general options as class properties
		$this->checkout_placement = self::get_checkout_placement();
		$this->required_location  = self::required_location();

		$this->set_map_params( 'ORS_token', PWS()->get_option( 'map.ORS_token', true ) );
		$this->set_map_params( 'is_admin', is_admin() );
		$this->set_map_params( 'checkout_placement', $this->checkout_placement );
		$this->set_map_params( 'pws_url', PWS_URL );

		add_action( 'wp_loaded', function () {
			// The rest_url() depends on WordPress environment
			$this->set_map_params( 'rest_url', rest_url( 'pws/map/' ) );
		} );

		add_action( 'woocommerce_cart_loaded_from_session', function () {
			// Needs shipping works only with 'virtual' products.
			$this->set_map_params( 'needs_shipping', WC()->cart->needs_shipping() );
		} );

		// Action and Filter WordPress Integration
		add_action( 'init', [ $this, 'initialize_hooks' ] );

	}

	/**
	 * Check if map is enabled and showing in checkout
	 *
	 * @return bool
	 */
	public static function is_enable(): bool {
		return in_array( self::get_checkout_placement(), [ 'after_form', 'before_form' ] );
	}

	/**
	 * Get the map placement in checkout form
	 * Map feature is disabled by default
	 *
	 * @return string
	 */
	public static function get_checkout_placement(): string {

		if ( is_a( WC()->cart, WC_Cart::class ) && ! WC()->cart->needs_shipping() ) {
			return 'none';
		}

		return apply_filters( 'pws_map_checkout_placement', PWS()->get_option( 'map.checkout_placement', 'none' ) );
	}

	/**
	 * Map should only load in this shipping methods
	 * Contains a list of shipping methods
	 *
	 * @return array
	 */
	public static function get_enabled_shipping_methods(): array {
		return apply_filters( 'pws_map_enabled_shipping_methods', PWS()->get_option( 'map.shipping_methods', [ 'all_shipping_methods' ] ) );
	}

	/**
	 * Get the map location requirement status
	 *
	 * @return bool
	 */
	public static function required_location(): bool {
		$is_location_required = PWS()->get_option( 'map.required_location', 1 ) == 1;

		return apply_filters( 'pws_map_required_location', $is_location_required );
	}

	public function initialize_hooks() {

		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

		// Rest API registration
		add_action( 'rest_api_init', [ $this, 'register_rest_api' ] );

		// Enable shortcode as [pws_map]
		add_action( 'init', [ $this, 'add_map_shortcode' ], 100 );

		if ( $this->checkout_placement !== 'none' ) {
			// Add hidden inputs to the checkout form
			add_filter( 'woocommerce_checkout_fields', [ $this, 'add_map_location_field_to_checkout_form' ], 100 );
			add_filter( 'woocommerce_checkout_get_value', [ $this, 'disable_map_location_field_get_value' ], 101, 2 );

			// Save the location order meta
			add_action( 'woocommerce_checkout_create_order', [ $this, 'save_map_location_meta' ], 100 );
		}

		// Filters for customization
		add_filter( 'pws_map_store_marker_image', [ $this, 'store_marker_image' ] );
		add_filter( 'pws_map_user_marker_image', [ $this, 'user_marker_image' ] );
		add_filter( 'pws_map_user_marker_color', [ $this, 'user_marker_color' ] );
		add_filter( 'pws_map_store_marker_color', [ $this, 'store_marker_image' ] );

		// Validate the location if its required
		if ( $this->required_location ) {
			add_action( 'woocommerce_checkout_process', [ $this, 'validate_map_location_field' ] );
		}

		if ( $this->checkout_placement !== 'none' ) {

			switch ( $this->checkout_placement ) {
				case 'before_form':
					$hook_names = [
						'woocommerce_before_checkout_billing_form',
						'woocommerce_before_checkout_shipping_form',
					];
					break;
				case 'after_form':
					$hook_names = [
						'woocommerce_after_checkout_billing_form',
						'woocommerce_after_checkout_shipping_form',
					];
					break;
				default:
					$hook_names = [
						'woocommerce_after_checkout_billing_form',
						'woocommerce_after_checkout_shipping_form',
					];
			}

			foreach ( $hook_names as $hook_name ) {
				add_action( $hook_name, [ $this, 'do_map_shortcode' ], 1000 );
			}

		}
	}

	/**
	 * Callback for pws_user_marker_filter which shows on map
	 */
	public function user_marker_image( $input ) {
		if ( ! empty( $input ) ) {
			return $input;
		}

		return PWS_URL . 'assets/images/map-marker.png';
	}

	public function user_marker_color( $input ) {
		if ( ! empty( $input ) ) {
			return $input;
		}

		return '#FF8330';
	}

	/**
	 * Callback for pws_store_marker_filter to pass custom icon as store marker
	 */
	public function store_marker_image( $input ) {
		if ( ! empty( $input ) ) {
			return $input;
		}

		return PWS_URL . 'assets/images/store-marker.png';
	}

	/**
	 * General styles and scripts
	 *
	 * @param string $hook_suffix
	 *
	 * @return bool
	 */
	public function enqueue_scripts( string $hook_suffix = '' ): bool {
		// Return early if user is not on either of these pages
		if ( ! PWS_Map::is_valid_page() ) {
			return false;
		}

		wp_enqueue_script( 'pws-map-leaflet', PWS_URL . 'assets/maps/leaflet/leaflet.js', [], PWS_VERSION );

		wp_enqueue_style( 'pws-map-leaflet', PWS_URL . 'assets/maps/leaflet/leaflet.css', [], PWS_VERSION );

		wp_enqueue_script( 'pws-map-general', PWS_URL . 'assets/maps/map.js', [ 'jquery' ], PWS_VERSION );

		return true;

	}

	/**
	 * The map shortcode pure html
	 *
	 * @param array $atts Shortcode attributes
	 *
	 * @return string
	 */
	public function shortcode_callback( array $atts ): string {
		$store_marker_enable = PWS()->get_option( 'map.store_marker_enable', true );

		[ $store_lat, $store_long ] = PWS_Map::get_default_location_array();

		if ( is_admin() || $store_marker_enable ) {
			[ $store_lat, $store_long ] = PWS_Map::get_store_location();
		}

		$center_lat  = $store_lat;
		$center_long = $store_long;

		$store_marker_image    = apply_filters( 'pws_map_store_marker_image', PWS_URL . 'assets/images/store-marker.png' );
		$store_marker_color    = apply_filters( 'pws_map_store_marker_color', '#6678FF' );
		$store_draw_line_color = apply_filters( 'pws_map_store_draw_line_color', '#00FF00' );

		$show_distance_type = PWS()->get_option( 'map.store_calculate_distance', 'none' );
		$user_marker_image  = apply_filters( 'pws_map_user_marker_image', PWS_URL . 'assets/images/map-marker.png' );
		$user_marker_color  = apply_filters( 'pws_map_user_marker_color', '#FF8330' );
		$user_has_location  = false;
		$required_location  = PWS()->get_option( 'map.required_location', true );
		$map_location       = [];

		if ( is_user_logged_in() && ! PWS_Map::is_admin_tools_page() ) {
			$map_location = PWS_Map::get_user_location();
		}

		if ( isset( $map_location['lat'], $map_location['long'] ) ) {
			$center_lat        = $map_location['lat'];
			$center_long       = $map_location['long'];
			$user_has_location = true;
		}



		// Define shortcode attributes with default values
		$atts = shortcode_atts( [
			'min-width'             => '400px',
			'min-height'            => '400px',
			'width'                 => '100%',
			'height'                => '400px',
			'zoom'                  => '12',
			'editable'              => false,
			'user-marker-color'     => $user_marker_color,
			'store-marker-color'    => $store_marker_color,
			'center-lat'            => $center_lat,
			'center-long'           => $center_long,
			'store-lat'             => $store_lat,
			'store-long'            => $store_long,
			'user-has-location'     => $user_has_location,
			'user-marker-url'       => $user_marker_image,
			'store-marker-url'      => $store_marker_image,
			'show-distance-type'    => $show_distance_type,
			'store-draw-line-color' => $store_draw_line_color,
			'poi'                   => true,
			'traffic'               => false,
			'type'                  => 'vector',
		], $atts, 'pws_map' );

		// No enabled shipping method? Then map always loads in all shipping methods
		$enabled_shipping_methods = PWS_Map_Service::get_enabled_shipping_methods();
		$enabled_shipping_methods = wp_json_encode( $enabled_shipping_methods, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );

		// Add dynamic parts that are specific to the map type
		$generated_id = rand( 0, 300 );
		$map_name     = $this->get_current_map_name();
		$map_class    = "pws-map__" . $map_name;
		$map_id       = "pws-map-" . $map_name . "-container-" . $generated_id;
		$extra_html   = $this->get_extra_html();

		return sprintf(
			"<div class='pws-map__container %s'
						id='%s'
						style='width: %s; height: %s;'
						data-min-width='%s' 
						data-min-height='%s'  
						data-zoom='%s'
						data-type='%s'
						data-editable='%s'
						data-user-has-location='%s'
						data-center-lat='%s'
						data-center-long='%s'
						data-store-lat='%s'
						data-store-long='%s'
						data-user-marker-url='%s'
						data-user-marker-color='%s'
						data-store-marker-url='%s'
						data-store-marker-color='%s'
						data-store-marker-enable='%s'
						data-store-draw-line-color='%s'
						data-show-distance-type='%s'
						data-poi='%s'
						data-traffic='%s'>
						%s
						<input type='hidden' value='%s' name='pws_map_enabled_shipping_methods'>
						<input type='hidden' value='%s' name='pws_map_required_location'>
					</div>",
			$map_class,
			$map_id,
			$this->sanitize_size( $atts['width'] ),
			$this->sanitize_size( $atts['height'] ),
			$this->sanitize_size( $atts['min-width'] ),
			$this->sanitize_size( $atts['min-height'] ),
			intval( $atts['zoom'] ),
			esc_attr( $atts['type'] ),
			filter_var( $atts['editable'], FILTER_VALIDATE_BOOLEAN ),
			filter_var( $atts['user-has-location'], FILTER_VALIDATE_BOOLEAN ),
			floatval( $atts['center-lat'] ),
			floatval( $atts['center-long'] ),
			floatval( $atts['store-lat'] ),
			floatval( $atts['store-long'] ),
			esc_url( $atts['user-marker-url'] ),
			sanitize_hex_color( $atts['user-marker-color'] ),
			esc_url( $atts['store-marker-url'] ),
			sanitize_hex_color( $atts['store-marker-color'] ),
			$store_marker_enable,
			sanitize_hex_color( $atts['store-draw-line-color'] ),
			$show_distance_type,
			filter_var( $atts['poi'], FILTER_VALIDATE_BOOLEAN ),
			filter_var( $atts['traffic'], FILTER_VALIDATE_BOOLEAN ),
			$extra_html,
			$enabled_shipping_methods,
			$required_location
		);


	}

	/**
	 * Get map name based on it's class (Inheriting from this class)
	 * Converts PWS_Map_OSM to OSM
	 * Converts PWS_Map_Neshan to neshan
	 *
	 * @return string
	 */
	public function get_current_map_name(): string {
		$map_name = get_called_class();

		if ( ! str_contains( $map_name, 'PWS_Map_' ) ) {
			return $map_name;
		}

		$map_name = str_replace( 'PWS_Map_', '', $map_name );

		return ctype_upper( $map_name ) ? $map_name : strtolower( $map_name );
	}


	/**
	 * Sanitize digits with measuring units like px or %
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public function sanitize_size( string $value ): string {
		$unit = str_contains( $value, 'px' ) ? 'px' : '%';

		return intval( $value ) . $unit;
	}

	/**
	 * Set extra html in map container
	 *
	 * @return string
	 */
	public function get_extra_html(): string {
		return '';
	}

	/**
	 * Create shortcode from the shortcode() template
	 * @return void
	 */
	public function add_map_shortcode() {
		add_shortcode( 'pws_map', [ $this, 'shortcode_callback' ] );
	}

	/**
	 * Method to run map shortcode
	 *
	 * @return void
	 */
	public function do_map_shortcode() {
		echo do_shortcode( '[pws_map]' );
	}

	/**
	 * Add hidden input to store user location selection latitude and longitude.
	 *
	 * @return array
	 */
	public function add_map_location_field_to_checkout_form( $fields ) {
		$fields['order']['pws_map_location'] = [
			'type'       => 'hidden',
			'label'      => '',
			'novalidate' => true,
		];

		return $fields;
	}

	/**
	 * Convert array to string manually to prevent error in woocommerce field processing
	 *
	 * @param mixed $value
	 * @param string input
	 *
	 * @return string
	 */
	public function disable_map_location_field_get_value( $value, $input ) {

		if ( $input !== 'pws_map_location' ) {
			return $value;
		}

		return wp_json_encode( $value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	}

	/**
	 * If the required location is enabled, the pws_map_location would have value
	 *
	 * @return void
	 */
	public function validate_map_location_field() {
		$required_location = ( isset( $_POST ) && ! empty( $_POST['pws_map_required_location'] ) && $_POST['pws_map_required_location'] === '1' );

		if ( ( ! isset( $_POST ) || ( empty( $_POST['pws_map_location'] ) ) && $required_location ) ) {
			wc_add_notice( __( 'لطفا موقعیت خود را روی نقشه انتخاب کنید.' ), 'error' );

			return;
		}

		// If it's not required, then it'll pass the validation!
		if ( ! $required_location ) {
			return;
		}

		// We need to fix json first, the JSON.stringify and input value will create html with special characters
		$map_location_json = $_POST['pws_map_location'] ?? '';
		$map_location_json = stripslashes( $map_location_json );
		$map_location      = json_decode( $map_location_json, true );

		if ( ! empty( json_last_error() ) || ! isset( $map_location['lat'], $map_location['long'] ) ) {
			wc_add_notice( __( 'مقصد تعیین شده صحیح نمی باشد. لطفاً موقعیت دیگری را روی نقشه انتخاب کنید.' ), 'error' );
			error_log( 'PWS error parsing JSON : ' . json_last_error_msg() );

			return;
		}


		if ( ! $this->is_iran_location( $map_location['lat'], $map_location['long'] ) ) {
			wc_add_notice( __( 'موقعیت مقصد خود را روی نقشه، در ایران ثبت کنید.' ), 'error' );

			return;
		}
	}

	/**
	 * Method to check given coordinates lies in iran.
	 *
	 * @param float $latitude
	 * @param float $longitude
	 *
	 * @return bool
	 */
	public function is_iran_location( float $latitude, float $longitude ): bool {
		$iran_boundary = [
			'min_latitude'  => 25.078237,
			'max_latitude'  => 39.777672,
			'min_longitude' => 44.032688,
			'max_longitude' => 63.322166,
		];
		/* Check if coordinates not in the area! */
		$invalid_latitude  = $latitude < $iran_boundary['min_latitude'] || $latitude > $iran_boundary['max_latitude'];
		$invalid_longitude = $longitude < $iran_boundary['min_longitude'] || $longitude > $iran_boundary['max_longitude'];

		if ( $invalid_longitude || $invalid_latitude ) {
			return false;
		}

		return true;
	}

	/**
	 * Save the order map location
	 * @HPOS_COMPATIBLE
	 *
	 * @param $order WC_Order
	 */
	public function save_map_location_meta( $order ) {
		// We need to fix json first, the JSON.stringify and input value will create html with special characters
		// Without strip and converting to array, It'll save as 'string', Serialization won't work.
		$map_location_json  = $_POST['pws_map_location'] ?? '';
		$map_location_json  = stripslashes( $map_location_json );
		$map_location_array = json_decode( $map_location_json, true );

		if ( empty( $map_location_array ) ) {
			return;
		}

		$order->update_meta_data( 'pws_map_location', $map_location_array );

		// Also update this meta for user
		if ( is_user_logged_in() ) {
			update_user_meta( get_current_user_id(), 'pws_map_location', $map_location_array );
		}

	}

	public function get_provider() {
		return $this->provider;
	}

	public function set_provider( $provider ) {
		$this->provider = $provider;
	}

	public function get_api_key() {
		return $this->api_key;
	}

	public function set_api_key( $key ) {
		$this->api_key = $key;
	}

	public function get_map_params() {
		return $this->map_params;
	}

	public function set_map_params( $key, $value ) {
		$this->map_params[ $key ] = $value;
	}

	/**
	 * Register map api
	 */
	public function register_rest_api() {
		register_rest_route( 'pws/map', '/distance/', [
			'methods'             => 'POST',
			'callback'            => [ $this, 'calculate_user_distance' ],
			'permission_callback' => '__return_true',
			'args'                => [
				'user_coords' => [
					'required' => true,
				],
				'type'        => [
					'required' => true,
				],
			],
		] );

	}


	public function calculate_user_distance( WP_REST_Request $request ): WP_REST_Response {
		header( 'Content-Type: application/json; charset=utf-8' );

		$user_coords = $request->get_param( 'user_coords' );
		$type        = $request->get_param( 'type' );

		if ( ! isset( $user_coords['lat'], $user_coords['long'] ) || empty( $type ) ) {
			return new WP_REST_Response( [
				'success' => false,
				'message' => 'پارامترهای نامعتبر برای محاسبه فاصله',
			], 400 );
		}

		$user_coords = json_decode( $user_coords, true );

		$cache_key = 'order_distance_' . $user_coords['lat'] . '_' . $user_coords['long'];
		$distance  = get_transient( $cache_key );

		if ( ! empty( $distance ) ) {

			return new WP_REST_Response( [
				'success'  => true,
				'distance' => $distance,
				'message'  => 'فاصله با موفقیت دریافت شد',
			], 200 );

		}

		switch ( $type ) {
			case 'direct' :
				$distance = $this->calculate_direct_distance( $user_coords );
				break;
			case 'real' :
				$distance = $this->calculate_real_distance( $user_coords );
				break;
			case 'default':
				$distance = $this->calculate_direct_distance( $user_coords );
		}

		return new WP_REST_Response( [
			'success'  => true,
			'distance' => $distance,
			'message'  => 'فاصله با موفقیت محاسبه شد',
		], 200 );
	}


	/**
	 * Calculate direct distance between user and store
	 *
	 * @param array $user_coords
	 *
	 * @return string
	 */
	public function calculate_direct_distance( array $user_coords ): string {
		$store_coords = PWS()->get_option( 'map.store_location', '' );

		if ( ! isset( $store_coords['lat'], $store_coords['long'] ) || ! isset( $user_coords['lat'], $user_coords['long'] ) ) {
			return '';
		}

		$user_lat = $user_coords['lat'];
		$user_lng = $user_coords['long'];

		$store_coords = json_decode( $store_coords, true );
		$store_lat    = $store_coords['lat'];
		$store_lng    = $store_coords['long'];

		// Earth's radius in kilometers
		$earth_radius = 6371;

		// Convert degrees to radians
		$user_lat_rad = deg2rad( $user_lat );
		$user_lng_rad = deg2rad( $user_lng );

		$store_lat_rad = deg2rad( $store_lat );
		$store_lng_rad = deg2rad( $store_lng );

		// Haversine formula to calculate the distance between two points
		$dlat = $store_lat_rad - $user_lat_rad;
		$dlng = $store_lng_rad - $user_lng_rad;

		$a = sin( $dlat / 2 ) * sin( $dlat / 2 ) + cos( $user_lat_rad ) * cos( $store_lat_rad ) * sin( $dlng / 2 ) * sin( $dlng / 2 );
		$c = 2 * atan2( sqrt( $a ), sqrt( 1 - $a ) );

		// Calculate the distance in meters
		$distance = $earth_radius * $c * 1000;

		if ( $distance < 1000 ) {
			$distance = number_format( $distance, 2 ) . ' متر';
		} else {
			$distance = number_format( $distance / 1000, 2 ) . ' کیلومتر';
		}

		$distance_display = sprintf( 'فاصله %1$s تا کاربر: %2$s', 'شعاعی (مستقیم)', $distance );

		// Set cache of distance
		$cache_key = 'order_distance_' . $user_lat . '_' . $user_lng;
		set_transient( $cache_key, $distance_display, 24 * 60 * 60 * 30 ); // 30 days cache expiration

		return $distance_display;
	}


	public function calculate_real_distance( array $user_coords ): string {
		$store_coords = PWS()->get_option( 'map.store_location', '' );

		if ( empty( $store_coords ) || empty( $user_coords ) ) {
			return '';
		}

		$user_lat = $user_coords['lat'];
		$user_lng = $user_coords['long'];

		$store_coords = json_decode( $store_coords, true );
		$store_lat    = $store_coords['lat'];
		$store_lng    = $store_coords['long'];

		$url     = 'https://api.openrouteservice.org/v2/directions/driving-car';
		$api_key = PWS()->get_option( 'map.ORS_token', '' );

		$body = [
			'coordinates' => [
				[ $user_lng, $user_lat ],
				[ $store_lng, $store_lat ],
			],
		];

		$args = [
			'method'  => 'POST',
			'body'    => json_encode( $body ),
			'headers' => [
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $api_key,
			],
		];

		$response = wp_remote_request( $url, $args );

		if ( is_wp_error( $response ) ) {
			return 'خطا: ' . $response->get_error_message();
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! isset( $data['routes'][0]['summary']['distance'] ) ) {
			return 'خطا در محاسبه فاصله';
		}

		$distance = $data['routes'][0]['summary']['distance'];

		if ( $distance < 1000 ) {
			$distance = number_format( $distance, 2 ) . ' متر';
		} else {
			$distance = number_format( $distance / 1000, 2 ) . ' کیلومتر';
		}

		$distance_display = sprintf( 'فاصله %1$s تا کاربر: %2$s', 'مسیریابی (واقعی)', $distance );

		// Set cache of distance
		$cache_key = 'order_distance_' . $user_lat . '_' . $user_lng;
		set_transient( $cache_key, $distance_display, 24 * 60 * 60 * 30 ); // 30 days cache expiration

		return $distance_display;
	}

}


