<?php
/**
 * Map implementation
 * The map configurator class
 * @since 4.0.4
 */

defined( 'ABSPATH' ) || exit;

class PWS_Map {
	/**
	 * All shipping methods
	 *
	 * @var array
	 */
	public static array $all_shipping_methods = [];

	/**
	 * Shipping zones
	 *
	 * @var array
	 */
	public static array $all_shipping_zones = [];

	public static bool $initialize;


	public function __construct() {
		$this->load_engines();
		$this->initialize();
	}

	public function initialize() {

		if ( PWS_Map_Service::get_checkout_placement() !== 'none' ) {
			// Action hooks for admin and woocommerce
			add_action( 'add_meta_boxes', [ $this, 'add_order_meta_box' ], 100 );
			add_action( 'woocommerce_admin_order_data_after_billing_address', [ $this, 'add_location_field_to_order_form', ], 100 );
			add_action( 'woocommerce_process_shop_order_meta', [ $this, 'save_location_order_meta' ], 100 );
			add_action( 'woocommerce_order_details_after_customer_details', [ $this, 'my_account_show_callback' ], 100 );
		}

		// Set active map
		$provider = PWS()->get_option( 'map.provider', 'OSM' );
		switch ( $provider ) {
			case 'OSM' :
				new PWS_Map_OSM();
				break;
			case 'neshan' :
				new PWS_Map_Neshan();
				break;
			case 'mapp' :
				new PWS_Map_Mapp();
				break;
			default:
				new PWS_Map_OSM();
		}
	}

	/**
	 * Load the map engines
	 * @since 4.0.4
	 */
	public function load_engines() {
		require_once PWS_DIR . '/maps/class-map-service.php';
		require_once PWS_DIR . '/maps/class-neshan.php';
		require_once PWS_DIR . '/maps/class-mapp.php';
		require_once PWS_DIR . '/maps/class-osm.php';
	}

	public static function is_valid_page(): bool {
		$screen_id               = is_admin() && function_exists( 'get_current_screen' ) ? get_current_screen()->id : null;
		$post_has_shortcode      = self::post_has_shortcode();
		$is_wc_orders_admin_page = ! empty( $screen_id ) && ( $screen_id == 'shop_order' || $screen_id == 'woocommerce_page_wc-orders' );
		$is_checkout_page        = ! is_admin() && function_exists( 'is_checkout' ) && is_checkout();
		$is_my_account_page      = is_account_page();

		return PWS_Map::is_admin_tools_page() || $is_wc_orders_admin_page || $is_checkout_page || $is_my_account_page || $post_has_shortcode;
	}

	/**
	 * Check if pws_map shortcode is presenting in current post
	 *
	 * @return bool
	 */
	public static function post_has_shortcode(): bool {
		global $post;

		return is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'pws_map' );
	}


	public static function is_admin_tools_page(): bool {
		return is_admin() && isset( $_GET['page'] ) && $_GET['page'] == 'pws-tools';
	}

	/**
	 * List all shipping zones exists and configured in WooCommerce
	 *
	 * @return array
	 */
	public static function get_all_shipping_zones(): array {

		if ( ! empty( self::$all_shipping_zones ) ) {
			return self::$all_shipping_zones;
		}

		// @var WC_Shipping_Zone_Data_Store
		$data_store = WC_Data_Store::load( 'shipping-zone' );

		foreach ( $data_store->get_zones() as $raw_zone ) {
			self::$all_shipping_zones[] = new WC_Shipping_Zone( $raw_zone );
		}

		self::$all_shipping_zones[] = new WC_Shipping_Zone( 0 );

		return self::$all_shipping_zones;
	}

	/**
	 * List all shipping methods in all shipping zones in WooCommerce
	 *
	 * @return array
	 */
	public static function get_all_shipping_methods(): array {

		if ( ! empty( self::$all_shipping_methods ) ) {
			return self::$all_shipping_methods;
		}

		foreach ( WC()->shipping()->load_shipping_methods() as $method ) {

			self::$all_shipping_methods[ $method->id ] = sprintf( 'همه روش‌های "%s"', $method->get_method_title() );

			foreach ( self::get_all_shipping_zones() as $zone ) {

				$shipping_method_instances = $zone->get_shipping_methods();

				foreach ( $shipping_method_instances as $shipping_method_instance_id => $shipping_method_instance ) {

					if ( $shipping_method_instance->id !== $method->id ) {
						continue;
					}

					$option_id = $shipping_method_instance->get_rate_id();

					$option_instance_title = sprintf( '%1$s (#%2$s)', $shipping_method_instance->get_title(), $shipping_method_instance_id );

					$option_title = sprintf( '%1$s - %2$s', $zone->get_id() ? $zone->get_zone_name() : __( 'Other locations', 'woocommerce' ), $option_instance_title );

					self::$all_shipping_methods[ $option_id ] = $option_title;
				}
			}
		}

		return self::$all_shipping_methods;
	}

	/**
	 * Retrieve store location as (non-associative) array
	 * ['lat', 'long']
	 *
	 * @return array
	 */
	public static function get_store_location(): array {
		$default_location_json  = self::get_default_location_json();
		$default_location_array = self::get_default_location_assoc_array();

		$store_location = PWS()->get_option( 'map.store_location', $default_location_json );
		$store_location = json_decode( $store_location, true );

		if ( json_last_error() || ! isset( $store_location['lat'], $store_location['long'] ) ) {
			$store_location_array = [ $default_location_array['lat'], $default_location_array['long'] ];
		} else {
			$store_location_array = [ $store_location['lat'], $store_location['long'] ];
		}

		return $store_location_array;
	}

	/**
	 * Retrieve user location as associative array
	 * ['lat' => '...', 'long' => '...']
	 *
	 * @return array
	 */
	public static function get_user_location(): array {
		$user_location = get_user_meta( get_current_user_id(), 'pws_map_location', true );
		//$user_location = json_decode( $user_location, true );

		if ( empty( $user_location ) || ! empty( json_last_error() ) ) {
			$user_location = [];
		}

		return $user_location;
	}

	/**
	 * Returns the base data for the location in json format,
	 * It'll be used in both store and user locations
	 *
	 * @return string
	 */
	public static function get_default_location_json(): string {
		return '{"lat":"35.6997006457524","long":"51.33774439566025"}';
	}

	/**
	 * Returns the associative array of default json location with 'lat' and 'long' keys
	 *
	 * @return array
	 */
	public static function get_default_location_assoc_array(): array {
		return json_decode( self::get_default_location_json(), true );
	}

	/**
	 * Returns the pure array of default location
	 * first index is latitude and second one is longitude
	 *
	 * @return array
	 */
	public static function get_default_location_array(): array {
		$default_location = self::get_default_location_assoc_array();

		return [ $default_location['lat'], $default_location['long'] ];
	}

	/**
	 * Creates link of map to share as sms or qrcode ,...
	 *
	 * @param float|null $lat
	 * @param float|null $long
	 * @param string $type The map type
	 *
	 * @return string
	 */
	public static function get_share_link( ?float $lat, ?float $long, string $type = 'neshan' ): string {

		[ $store_lat, $store_long ] = self::get_store_location();

		if ( is_null( $lat ) || is_null( $long ) ) {
			[ $lat, $long ] = [ $store_lat, $store_long ];
		}

		switch ( $type ) {
			case 'neshan' :
				$url = "https://neshan.org/maps/routing/car/origin/$store_lat,$store_long/destination/$lat,$long";
				break;
			case 'balad':
				$url = "https://balad.ir/directions/driving?origin=$store_long,$store_lat&destination=$long,$lat";
				break;
			case 'google':
				$url = "https://www.google.com/maps/dir/$store_lat,$store_long/$lat,$long";
				break;
			default:
				$url = "https://neshan.org/maps/routing/car/origin/$store_lat,$store_long/destination/$lat,$long";
		}

		return $url;
	}


	/**
	 * Get location from order
	 *
	 * @param WC_Order $order
	 * @param array|null $default
	 *
	 * @return array
	 */
	public static function get_order_location( WC_Order $order, array $default = null ): ?array {
		$location = $order->get_meta( 'pws_map_location' );

		if ( ! isset( $location['lat'], $location['long'] ) ) {
			return $default;
		}

		return [ (float) $location['lat'], (float) $location['long'] ];
	}

	/**
	 * Save admin changed map location to the order
	 * @HPOS_COMPATIBLE
	 *
	 * @param $order_id int
	 *
	 * @return void
	 */
	public function save_location_order_meta( int $order_id ): void {
		$location_json  = $_POST['pws_map_location'] ?? '';
		$location_json  = stripslashes( $location_json );
		$location_array = json_decode( $location_json, true );

		if ( empty( $location_array ) ) {
			return;
		}

		$order = wc_get_order( $order_id );

		if ( is_a( $order, 'WC_Order' ) ) {
			$order->update_meta_data( 'pws_map_location', $location_array );
			$order->save_meta_data();
		}

	}

	public function add_location_field_to_order_form( $post_or_order_object ) {
		$order          = ( $post_or_order_object instanceof WP_Post ) ? wc_get_order( $post_or_order_object->ID ) : $post_or_order_object;
		$current_screen = get_current_screen();

		// HPOS Compatible
		$is_edit_order = isset( $order ) && is_a( $order, 'WC_Order' );
		$is_add_order  = $current_screen && 'shop_order' === $current_screen->post_type && ( 'add' === $current_screen->action || ( isset( $_GET['action'] ) && $_GET['action'] == 'new' ) );

		$default_location = self::get_default_location_array();

		// There's only two condition which order will add pws_map_location field
		// Otherwise it should return nothing and abort
		if ( $is_edit_order ) {
			$map_location = self::get_order_location( $order, $default_location );
		}

		if ( $is_add_order ) {
			$map_location = $default_location;
		}

		if ( empty( $map_location ) || ! is_array( $map_location ) ) {
			return;
		}

		$map_location_json = wp_json_encode( $map_location, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

		echo <<<LOCATION_INPUT
                   <div class="custom-hidden-input-field">
                       <input type="hidden"
                       id="pws_map_location"
                        name="pws_map_location"
                        value="$map_location_json" 
                        >
                    </div>
        LOCATION_INPUT;
	}

	public function add_order_meta_box() {
		add_meta_box( 'pws-map-order-meta-box', __( 'نقشه' ), [ $this, 'order_meta_box_callback', ], [
			'woocommerce_page_wc-orders',
			wc_get_page_screen_id( 'shop-order' ),
		], 'advanced', 'high' );
	}

	/**
	 *
	 * Show map in admin order area
	 *
	 * @HPOS_COMPATIBLE
	 *
	 * @param WC_Order|WP_Post $post_or_order_object
	 *
	 * @return void
	 *
	 */
	public function order_meta_box_callback( $post_or_order_object ) {
		$order = ( $post_or_order_object instanceof WP_Post ) ? wc_get_order( $post_or_order_object->ID ) : $post_or_order_object;

		if ( ! is_a( $order, 'WC_Order' ) ) {
			return;
		}

		[ $center_lat, $center_long ] = self::get_order_location( $order );

		$enable_edit       = $default_center_lat = $default_center_long = '';
		$user_has_location = '1';

		if ( empty( $center_lat ) || empty( $center_long ) ) {
			$enable_edit = 'checked';
			// Zoom only
			$user_has_location = '0';
			[ $default_center_lat, $default_center_long ] = self::get_default_location_array();
		}

		$map_center_lat  = empty( $center_lat ) ? $default_center_lat : $center_lat;
		$map_center_long = empty( $center_long ) ? $default_center_long : $center_long;

		$map = do_shortcode( "[pws_map center-lat='$map_center_lat' center-long='$map_center_long' min-width='200px' min-height='200px' user-has-location='$user_has_location']" );

		$share_link_html = '';
		if ( ! empty( $center_lat ) && ! empty( $center_long ) ) {

			$neshan_share_link = self::get_share_link( $center_lat, $center_long );
			$neshan_logo_link  = PWS_URL . 'assets/images/neshan.png';

			$balad_share_link = self::get_share_link( $center_lat, $center_long, 'balad' );
			$balad_logo_link  = PWS_URL . 'assets/images/balad.png';

			$share_link_html = <<<SHARE_LINK_HTML
                                <div class="pws-order__map__neshan__share__link" title="برای کپی، کلیک کنید."><img src="$neshan_logo_link" alt="neshan"><span class="url">$neshan_share_link</span></div>
                                <div class="pws-order__map__balad__share__link" title="برای کپی، کلیک کنید."><img src="$balad_logo_link" alt="balad"><span class="url">$balad_share_link</span></div>
                                SHARE_LINK_HTML;

		}

		echo <<<ORDER_MAP_SECTION
                    <div class="pws-order__map__shipping_section">
                        <div class="value map">$map</div>
                        <div class="info">
                            <div class="pws-order__map__coords"></div>
                            <div class="pws-order__map__shipping__information"></div>
                             
                            <div class="pws-order__map__share__links__container">
                                 <span class="pws-order__map__share__links__custom__alert">لینک مسیریابی سفارش، با موفقیت کپی شد!</span>
                                 $share_link_html
                            </div>
                        </div>  
                        <div class="action">
                            <input id="pws-map-admin-edit" type="checkbox" $enable_edit/>
                            <label for="pws-map-admin-edit" class="button">ویرایش نقشه</label>
                        </div>
                    </div>
            ORDER_MAP_SECTION;
	}

	/**
	 * Show map only in my-account/orders
	 * $name is based on two type of addresses in the address area
	 * Billing, Shipping
	 *
	 * @return void
	 */
	public function my_account_show_callback( $post_or_order_object ): void {
		$order = ( $post_or_order_object instanceof WP_Post ) ? wc_get_order( $post_or_order_object->ID ) : $post_or_order_object;

		if ( ! is_a( $order, 'WC_Order' ) || empty( $order ) ) {
			return;
		}

		$customer_id = get_current_user_id();

		if ( empty( $customer_id ) ) {
			return;
		}

		[ $center_lat, $center_long ] = self::get_order_location( $order );

		if ( empty( $center_lat ) || empty( $center_long ) ) {
			return;
		}

		$map = do_shortcode( "[pws_map center-lat='$center_lat' center-long='$center_long' min-width='200px' min-height='200px']" );

		echo <<<MYACCOUNT_MAP_SECTION
                    <div class="pws-account__map__shipping_section" style="width: 100%;">
                        <div class="value">$map</div>  
                    </div>
            MYACCOUNT_MAP_SECTION;
	}

}


new PWS_Map();

