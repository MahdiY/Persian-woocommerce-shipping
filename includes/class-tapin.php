<?php
/**
 * Developer : MahdiY
 * Web Site  : MahdiY.IR
 * E-Mail    : M@hdiY.IR
 * أَلسَّلٰامُ عَلَیکَ یٰا عَلی اِبنِ موسَی أَلرّضٰآ
 */

defined( 'ABSPATH' ) || exit;

class PWS_Tapin extends PWS_Core {

	protected static string $gateway = 'tapin';

	protected static array $gateways = [
		'tapin'      => 'tapin.ir',
		'posteketab' => 'postketab.com',
	];

	/**
	 * Ensures only one instance of PWS_Tapin is loaded or can be loaded.
	 *
	 * @return PWS_Tapin
	 * @see PWS()
	 */
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {

		self::$methods = [
			'WC_Courier_Method',
			'WC_Tipax_Method',
			'Tapin_Pishtaz_Method',
			'Tapin_Special_Method',
		];

		add_filter( 'get_ancestors', function ( $ancestors, $object_id, $object_type, $resource_type ) {

			if ( $object_type == 'state_city' ) {

				$ancestors = wp_cache_get( 'city_ancestors', 'pws' );

				if ( ! empty( $ancestors ) ) {
					return $ancestors;
				}

				$ancestors = [];

				$zone = wp_list_pluck( PWS()::zone(), 'cities', null );

				foreach ( $zone as $state_id => $cities ) {
					if ( isset( $cities[ $object_id ] ) ) {
						$ancestors = [ 'state_' . $state_id ];
						break;
					}
				}

				wp_cache_set( 'city_ancestors', $ancestors, 'pws' );

				return $ancestors;
			}

			return $ancestors;
		}, 10, 4 );

		parent::init_hooks();
	}

	public function enqueue_select2_scripts() {
		if ( ! is_checkout() ) {
			return false;
		}

		wp_register_script( 'selectWoo', WC()->plugin_url() . '/assets/js/selectWoo/selectWoo.full.min.js', [ 'jquery' ], '4.0.3' );
		wp_enqueue_script( 'selectWoo' );
		wp_register_style( 'select2', WC()->plugin_url() . '/assets/css/select2.css' );
		wp_enqueue_style( 'select2' );

		wp_register_script( 'pwsCheckout', PWS_URL . 'assets/js/pws-tapin.js', [ 'selectWoo' ], '1.0.0' );
		wp_localize_script( 'pwsCheckout', 'pws_settings', [
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'types'    => $this->types(),
			'is_cod'   => WC()->session->get( 'chosen_payment_method' ) == 'cod',
		] );
		wp_enqueue_script( 'pwsCheckout' );
	}

	public function checkout_update_order_meta( $order_id ) {

		$order = wc_get_order( $order_id );

		$types  = $this->types();
		$fields = [ 'state', 'city' ];

		foreach ( $types as $type ) {

			foreach ( $fields as $field ) {

				$term_id = $order->{"get_{$type}_{$field}"}();
				$term    = self::{'get_' . $field}( intval( $term_id ) );

				if ( ! is_null( $term ) ) {
					$order->{"set_{$type}_{$field}"}( $term );
					$order->update_meta_data( "_{$type}_{$field}_id", $term_id );
				}

			}
		}

		if ( wc_ship_to_billing_address_only() ) {

			foreach ( $fields as $field ) {

				$label = $order->{"get_billing_{$field}"}();
				$id    = $order->get_meta( "_billing_{$field}_id" );

				$order->{"set_shipping_{$field}"}( $label );
				$order->update_meta_data( "_shipping_{$field}_id", $id );

			}

		}

		$order->save();
	}

	public function checkout_process() {

		$types = $this->types();

		$fields = [
			'state' => 'استان',
			'city'  => 'شهر',
		];

		$type_label = [
			'billing'  => 'صورتحساب',
			'shipping' => 'حمل و نقل',
		];

		if ( ! isset( $_POST['ship_to_different_address'] ) && count( $types ) == 2 ) {
			unset( $types[1] );
		}

		foreach ( $types as $type ) {

			$label = $type_label[ $type ];

			foreach ( $fields as $field => $name ) {

				$key = $type . '_' . $field;

				if ( isset( $_POST[ $key ] ) && strlen( $_POST[ $key ] ) ) {

					$value = intval( $_POST[ $key ] );

					if ( $value == 0 ) {
						$message = sprintf( 'لطفا <b>%s %s</b> خود را انتخاب نمایید.', $name, $label );
						wc_add_notice( $message, 'error' );

						continue;
					}

					$invalid = is_null( self::{'get_' . $field}( $value ) );

					if ( $invalid ) {
						$message = sprintf( '<b>%s %s</b> انتخاب شده معتبر نمی باشد.', $name, $label );
						wc_add_notice( $message, 'error' );

						continue;
					}

					if ( $field == 'state' ) {

						$pkey = $type . '_city';

						$cities = self::cities( $value );

						if ( isset( $_POST[ $pkey ] ) && ! empty( $_POST[ $pkey ] ) && ! isset( $cities[ $_POST[ $pkey ] ] ) ) {
							$message = sprintf( '<b>استان</b> با <b>شهر</b> %s انتخاب شده همخوانی ندارند.', $label );
							wc_add_notice( $message, 'error' );

							continue;
						}
					}

				}

			}

		}
	}

	public function cart_shipping_packages( $packages ) {

		foreach ( $packages as $key => $package ) {
			$packages[ $key ]['destination']['is_cod'] = WC()->session->get( 'chosen_payment_method' ) == 'cod';
		}

		return $packages;
	}

	public function localisation_address_formats( $formats ) {

		$formats['IR'] = "{company}\n{first_name} {last_name}\n{country}\n{state}\n{city}\n{address_1}\n{address_2}\n{postcode}";

		return $formats;
	}

	public function formatted_address_replacements( $replace, $args ) {

		$replace = parent::formatted_address_replacements( $replace, $args );

		if ( ctype_digit( $args['city'] ) ) {
			$city              = $this->get_city( $args['city'] );
			$replace['{city}'] = is_null( $city ) ? $args['city'] : $city;
		}

		return $replace;
	}

	public static function is_enable(): bool {
		return self::get_option( 'tapin.enable', false ) == 1;
	}

	public static function request( $path, $data = [], $associative = null ) {

		$url = sprintf(
			'https://api.%s/api/%s/',
			self::$gateways[ self::$gateway ],
			trim( $path, ' /' )
		);

		$curl = curl_init();

		curl_setopt_array( $curl, [
			CURLOPT_URL            => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING       => "",
			CURLOPT_MAXREDIRS      => 1,
			CURLOPT_TIMEOUT        => 10,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST  => "POST",
			CURLOPT_POSTFIELDS     => json_encode( $data ),
			CURLOPT_HTTPHEADER     => [
				"Content-type: application/json",
				"Accept: application/json",
				"Authorization: " . PWS()->get_option( 'tapin.token' ),
			],
		] );

		$response  = curl_exec( $curl );
		$http_code = curl_getinfo( $curl, CURLINFO_HTTP_CODE );
		$error     = curl_error( $curl );

		if ( $response === false || $error ) {

			PWS()->log( __METHOD__ . ' Line: ' . __LINE__ );
			PWS()->log( $url );
			PWS()->log( $data );
			PWS()->log( $error );

			curl_close( $curl );

			return new WP_Error( '', $error );
		}

		if ( $http_code >= 300 ) {

			PWS()->log( __METHOD__ . ' Line: ' . __LINE__ );
			PWS()->log( $url );
			PWS()->log( $data );
			PWS()->log( $http_code );

			return new WP_Error( $http_code, "خطا {$http_code}" );
		}

		curl_close( $curl );

		return json_decode( $response, $associative );
	}

	public static function zone() {

		$zone = wp_cache_get( 'pws_tapin_zone', 'nabik' );

		if ( $zone === false ) {

			$zone = json_decode( file_get_contents( PWS_DIR . '/data/tapin.json' ), true );

			wp_cache_set( 'pws_tapin_zone', $zone, 'nabik' );
		}

		return $zone;
	}

	public static function states() {

		$states = get_transient( 'pws_tapin_states' );

		if ( $states === false || count( (array) $states ) == 0 ) {

			$zone = self::zone();

			$states = array_map( function ( $state ) {
				return trim( $state['title'] );
			}, $zone );

			uasort( $states, [ self::class, 'pws_sort_state' ] );

			set_transient( 'pws_tapin_states', array_filter( $states ), DAY_IN_SECONDS );
		}

		return apply_filters( 'pws_states', $states );
	}

	public static function cities( $state_id = null ) {

		$cities = get_transient( 'pws_tapin_cities_' . $state_id );

		if ( $cities === false || count( (array) $cities ) == 0 ) {

			$zone = self::zone();

			if ( is_null( $state_id ) ) {

				$state_cities = array_column( $zone, 'cities' );

				$cities = [];

				foreach ( $state_cities as $state_city ) {
					$cities += $state_city;
				}

			} else if ( isset( $zone[ $state_id ]['cities'] ) ) {
				$cities = $zone[ $state_id ]['cities'];

				asort( $cities );
			} else {
				return [];
			}

			set_transient( 'pws_tapin_cities_' . $state_id, $cities, DAY_IN_SECONDS );
		}

		if ( isset( $cities[376] ) ) {
			unset( $cities[376] );
		}

		return apply_filters( 'pws_cities', $cities, $state_id );
	}

	public static function get_city( $city_id ) {

		$cities = self::cities();

		return $cities[ $city_id ] ?? null;
	}

	public static function districts( $city_id ): array {
		return [];
	}

	public static function get_district( $district_id ): ?string {
		return null;
	}

	public function check_states_beside( $source, $destination ): bool {

		if ( $source == $destination ) {
			return false;
		}

		$is_beside[3][16] = true;
		$is_beside[3][15] = true;
		$is_beside[3][12] = true;

		$is_beside[16][3]  = true;
		$is_beside[16][18] = true;
		$is_beside[16][12] = true;

		$is_beside[15][3]  = true;
		$is_beside[15][2]  = true;
		$is_beside[15][12] = true;

		$is_beside[6][24] = true;
		$is_beside[6][20] = true;
		$is_beside[6][28] = true;
		$is_beside[6][11] = true;
		$is_beside[6][10] = true;
		$is_beside[6][9]  = true;
		$is_beside[6][30] = true;
		$is_beside[6][25] = true;
		$is_beside[6][5]  = true;

		$is_beside[31][1]  = true;
		$is_beside[31][11] = true;
		$is_beside[31][8]  = true;
		$is_beside[31][13] = true;

		$is_beside[27][19] = true;
		$is_beside[27][20] = true;
		$is_beside[27][4]  = true;

		$is_beside[21][28] = true;
		$is_beside[21][4]  = true;
		$is_beside[21][5]  = true;
		$is_beside[21][23] = true;

		$is_beside[1][31] = true;
		$is_beside[1][11] = true;
		$is_beside[1][10] = true;
		$is_beside[1][13] = true;
		$is_beside[1][9]  = true;

		$is_beside[24][28] = true;
		$is_beside[24][4]  = true;
		$is_beside[24][20] = true;
		$is_beside[24][6]  = true;

		$is_beside[30][26] = true;
		$is_beside[30][22] = true;
		$is_beside[30][25] = true;
		$is_beside[30][6]  = true;
		$is_beside[30][9]  = true;
		$is_beside[30][7]  = true;

		$is_beside[7][30] = true;
		$is_beside[7][29] = true;
		$is_beside[7][9]  = true;

		$is_beside[29][7]  = true;
		$is_beside[29][14] = true;
		$is_beside[29][9]  = true;

		$is_beside[4][27] = true;
		$is_beside[4][21] = true;
		$is_beside[4][20] = true;
		$is_beside[4][28] = true;
		$is_beside[4][24] = true;

		$is_beside[12][2]  = true;
		$is_beside[12][15] = true;
		$is_beside[12][3]  = true;
		$is_beside[12][16] = true;
		$is_beside[12][18] = true;
		$is_beside[12][17] = true;
		$is_beside[12][8]  = true;

		$is_beside[9][13] = true;
		$is_beside[9][1]  = true;
		$is_beside[9][10] = true;
		$is_beside[9][6]  = true;
		$is_beside[9][29] = true;
		$is_beside[9][7]  = true;
		$is_beside[9][30] = true;

		$is_beside[26][30] = true;
		$is_beside[26][22] = true;
		$is_beside[26][23] = true;

		$is_beside[5][6]  = true;
		$is_beside[5][25] = true;
		$is_beside[5][21] = true;
		$is_beside[5][23] = true;
		$is_beside[5][28] = true;
		$is_beside[5][22] = true;

		$is_beside[8][12] = true;
		$is_beside[8][17] = true;
		$is_beside[8][11] = true;
		$is_beside[8][31] = true;
		$is_beside[8][13] = true;
		$is_beside[8][2]  = true;

		$is_beside[10][1]  = true;
		$is_beside[10][11] = true;
		$is_beside[10][9]  = true;
		$is_beside[10][6]  = true;

		$is_beside[18][16] = true;
		$is_beside[18][19] = true;
		$is_beside[18][17] = true;
		$is_beside[18][12] = true;

		$is_beside[22][25] = true;
		$is_beside[22][5]  = true;
		$is_beside[22][23] = true;
		$is_beside[22][26] = true;
		$is_beside[22][30] = true;

		$is_beside[19][18] = true;
		$is_beside[19][17] = true;
		$is_beside[19][20] = true;
		$is_beside[19][27] = true;

		$is_beside[28][24] = true;
		$is_beside[28][4]  = true;
		$is_beside[28][21] = true;
		$is_beside[28][5]  = true;
		$is_beside[28][6]  = true;

		$is_beside[14][13] = true;
		$is_beside[14][29] = true;
		$is_beside[14][9]  = true;

		$is_beside[2][13] = true;
		$is_beside[2][15] = true;
		$is_beside[2][12] = true;
		$is_beside[2][8]  = true;

		$is_beside[20][27] = true;
		$is_beside[20][19] = true;
		$is_beside[20][17] = true;
		$is_beside[20][11] = true;
		$is_beside[20][6]  = true;
		$is_beside[20][24] = true;
		$is_beside[20][4]  = true;

		$is_beside[13][14] = true;
		$is_beside[13][9]  = true;
		$is_beside[13][1]  = true;
		$is_beside[13][31] = true;
		$is_beside[13][6]  = true;
		$is_beside[13][8]  = true;
		$is_beside[13][2]  = true;

		$is_beside[11][6]  = true;
		$is_beside[11][10] = true;
		$is_beside[11][1]  = true;
		$is_beside[11][31] = true;
		$is_beside[11][20] = true;
		$is_beside[11][8]  = true;
		$is_beside[11][17] = true;

		$is_beside[23][21] = true;
		$is_beside[23][5]  = true;
		$is_beside[23][22] = true;
		$is_beside[23][26] = true;

		$is_beside[17][19] = true;
		$is_beside[17][20] = true;
		$is_beside[17][18] = true;
		$is_beside[17][11] = true;
		$is_beside[17][8]  = true;
		$is_beside[17][12] = true;

		$is_beside[25][5]  = true;
		$is_beside[25][22] = true;
		$is_beside[25][30] = true;

		return $is_beside[ $source ][ $destination ] ?? false;
	}

	public function get_term_option( $term_id ): array {
		return get_option( 'nabik_taxonomy_tapin_' . $term_id, [] );
	}

	public function set_term_option( $term_id, array $option ) {
		update_option( 'nabik_taxonomy_tapin_' . $term_id, $option, false );
	}

	public function delete_term_option( $term_id ) {
		delete_option( 'nabik_taxonomy_tapin_' . $term_id );
	}

	public static function shop(): array {

		$shop_id = PWS()->get_option( 'tapin.shop_id' );

		if ( empty( $shop_id ) ) {
			return [];
		}

		$shop = get_transient( 'pws_tapin_shop_' . $shop_id );

		if ( $shop === false || count( (array) $shop ) == 0 ) {

			PWS_Tapin::set_gateway( PWS()->get_option( 'tapin.gateway' ) );

			$shop = self::request( 'v2/public/shop/detail', [
				'shop_id' => $shop_id,
			], true );

			if ( is_wp_error( $shop ) ) {
				return [];
			} else {
				$shop = $shop['entries'];
			}

			set_transient( 'pws_tapin_shop_' . $shop_id, $shop, DAY_IN_SECONDS );
		}

		return $shop;
	}

	public static function services(): array {

		if ( empty( PWS()->get_option( 'tapin.shop_id' ) ) ) {
			return [];
		}

		$services = get_transient( 'pws_tapin_services' );

		if ( empty( $services ) ) {

			PWS_Tapin::set_gateway( PWS()->get_option( 'tapin.gateway' ) );

			$_services = self::request( 'v4/location/public/all/city/filter/', [
				'shop_id' => PWS()->get_option( 'tapin.shop_id' ),
			], true );

			if ( is_wp_error( $_services ) || ! isset( $_services['entries']['cities'] ) || $_services['entries']['is_public'] ) {
				return get_option( 'pws_tapin_services', [] );
			}

			$services = [];

			foreach ( $_services['entries']['cities'] as $service ) {

				$services[ $service['pk'] ] = $service;

				unset( $services[ $service['pk'] ]['pk'] );
				unset( $services[ $service['pk'] ]['title'] );
				unset( $services[ $service['pk'] ]['province_pk'] );
				unset( $services[ $service['pk'] ]['province_title'] );

				foreach ( $services[ $service['pk'] ] as &$city_services ) {
					$city_services = array_filter( $city_services );
				}

				$services[ $service['pk'] ] = array_filter( $services[ $service['pk'] ] );

				foreach ( $services[ $service['pk'] ] as &$city_services ) {
					unset( $city_services['is_available'] );
				}
			}

			set_transient( 'pws_tapin_services', $services, DAY_IN_SECONDS );
			update_option( 'pws_tapin_services', $services );
		}

		return $services;
	}

	public static function has_service( int $city_id, string $service, $order_type = null ): bool {

		$services = self::services();

		if ( ! isset( $services[ $city_id ][ $service ] ) ) {
			return false;
		}

		$order_types = $services[ $city_id ][ $service ]['order_types'] ?? [ null ];

		if ( ! in_array( $order_type, $order_types ) ) {
			return false;
		}

		return true;
	}

	public static function set_gateway( string $gateway ) {

		if ( in_array( $gateway, array_keys( self::$gateways ) ) ) {
			self::$gateway = $gateway;
		}

	}

	public static function get_gateway(): string {
		return self::$gateway;
	}

	public static function box_sizes(): array {
		return [
			1  => 'کارتن پستی سایز ۱',
			2  => 'کارتن پستی سایز ۲',
			3  => 'کارتن پستی سایز ۳',
			4  => 'کارتن پستی سایز ۴',
			5  => 'کارتن پستی سایز ۵',
			6  => 'کارتن پستی سایز ۶',
			7  => 'کارتن پستی سایز ۷',
			8  => 'کارتن پستی سایز ۸',
			9  => 'کارتن پستی سایز ۹',
			10 => 'بزرگتر از کارتن پستی سایز ۹',
			11 => 'پاکت جوف A5',
			12 => 'پاکت جوف A4',
			13 => 'پاکت جوف A3',
		];
	}

}