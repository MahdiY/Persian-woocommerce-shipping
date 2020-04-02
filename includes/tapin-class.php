<?php
/**
 * Developer : MahdiY
 * Web Site  : MahdiY.IR
 * E-Mail    : M@hdiY.IR
 */

class PWS_Tapin extends PWS_Core {

	protected static $gateway = 'tapin';

	protected static $gateways = [
		'tapin'      => 'tapin.ir',
		'posteketab' => 'posteketab.com'
	];

	/**
	 * Ensures only one instance of PWS_Tapin is loaded or can be loaded.
	 *
	 * @see PWS()
	 * @return PWS_Tapin
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
			'WC_Tapin_Method',
		];

		parent::init_hooks();
	}

	public function state_city_admin_menu() {
		// Hide menu
	}

	public function load_child_term() {

		$types = $this->types();

		?>
        <script type="text/javascript">
			var mahdiy_ajax_url = '<?php echo admin_url( 'admin-ajax.php' ); ?>';

			jQuery(document).ready(function ( $ ) {

				<?php foreach( $types as $type ) { ?>

				function <?php echo $type; ?>_mahdiy_state_changed() {
					var data = {
						'action': 'mahdiy_load_cities',
						'state_id': $('#<?php echo $type; ?>_state').val(),
						'name': '<?php echo $type; ?>'
					};

					$.post(mahdiy_ajax_url, data, function ( response ) {
						if( response == "" )
							response = "<option value='0'><?php _e( 'لطفا استان خود را انتخاب کنید' ); ?><option>";
						$('select#<?php echo $type; ?>_mahdiy_cities').html(response);
						$('body').trigger('pws_city_loaded');
					});

					$('select#<?php echo $type; ?>_mahdiy_cities').select2();
					$('p#<?php echo $type; ?>_mahdiy_district_field').slideUp();
					$('select#<?php echo $type; ?>_mahdiy_district').html("");
				}

				$('body').on('change', 'select#<?php echo $type; ?>_state, input#<?php echo $type; ?>_state', function () {
					<?php echo $type; ?>_mahdiy_state_changed();
				});

				<?php echo $type; ?>_mahdiy_state_changed();

				<?php } ?>
			});
        </script>
        <style>
            .woocommerce form .form-row .select2-container {
                width: 100% !important;
            }
        </style>
		<?php
	}

	public function edit_checkout_cities_field( $fields ) {

		$types = $this->types();

		foreach ( $types as $type ) {

			if ( ! isset( $fields[ $type ][ $type . '_city' ] ) ) {
				continue;
			}

			$class = is_array( $fields[ $type ][ $type . '_city' ]['class'] ) ? $fields[ $type ][ $type . '_city' ]['class'] : array();

			$fields[ $type ][ $type . '_postcode' ]['clear'] = false;

			$fields[ $type ][ $type . '_city' ] = array(
				'type'        => $type . '_mahdiy_cities',
				'label'       => 'شهر',
				'placeholder' => __( 'یک شهر انتخاب کنید' ),
				'required'    => true,
				'id'          => $type . '_mahdiy_cities',
				'class'       => apply_filters( 'pws_city_class', $class ),
				'default'     => 0,
				'priority'    => apply_filters( 'pws_city_priority', $fields[ $type ][ $type . '_city' ]['priority'] ),
			);

		}

		return $fields;
	}

	public function checkout_update_order_meta( $order_id ) {

		$types  = $this->types();
		$fields = array( 'state', 'city' );

		foreach ( $types as $type ) {

			foreach ( $fields as $field ) {

				$term_id = get_post_meta( $order_id, "_{$type}_{$field}", true );
				$term    = self::{'get_' . $field}( intval( $term_id ) );

				if ( ! is_null( $term ) ) {
					update_post_meta( $order_id, "_{$type}_{$field}", $term );
					update_post_meta( $order_id, "_{$type}_{$field}_id", $term_id );
				}

			}
		}

		/** @var WC_Order $order */
		$order = wc_get_order( $order_id );

		foreach ( $order->get_shipping_methods() as $shipping_item ) {

			if ( $shipping_item->get_method_id() == 'WC_Tapin_Method' ) {

				$instance_id = $shipping_item->get_instance_id();

				$data = get_option( "woocommerce_WC_Tapin_Method_{$instance_id}_settings" );

				update_post_meta( $order_id, "tapin_method", $data );
			}
		}

	}

	public function checkout_process() {

		$types = $this->types();

		$fields = array(
			'state' => 'استان',
			'city'  => 'شهر',
		);

		foreach ( $types as $type ) {

			foreach ( $fields as $field => $name ) {

				if ( isset( $_POST[ $type . '_' . $field ] ) && ! empty( $_POST[ $type . '_' . $field ] ) ) {

					$value = intval( $_POST[ $type . '_' . $field ] );

					$invalid = is_null( self::{'get_' . $field}( $value ) );

					if ( $invalid ) {
						wc_add_notice( $name . ' انتخاب شده معتبر نمی باشد.', 'error' );
					}

				}

			}

		}
	}

	function localisation_address_formats( $formats ) {

		$formats['IR'] = "{company}\n{first_name} {last_name}\n{country}\n{state}\n{city}\n{address_1} - {address_2}\n{postcode}";

		return $formats;
	}

	public function formatted_address_replacements( $replace, $args ) {

		if ( ctype_digit( $args['city'] ) ) {
			$city              = $this->get_city( $args['city'] );
			$replace['{city}'] = is_null( $city ) ? $args['city'] : $city;
		}

		return $replace;
	}

	public function persian_woo_sms_content_replace( $content, $find, $replace, $order_id, $order, $product_ids ) {

		$city                = self::get_city( $replace[6] );
		$pws_tag['{b_city}'] = is_null( $city ) ? $replace[6] : $city;

		$city                 = self::get_city( $replace[15] );
		$pws_tag['{sh_city}'] = is_null( $city ) ? $replace[15] : $city;

		return strtr( $content, $pws_tag );
	}

	public static function is_enable() {
		$options = get_option( 'PW_Options' );

		return isset( $options['pws_tapin_enable'] ) && $options['pws_tapin_enable'] == 'yes';
	}

	public static function request( $path, $data = [], $absolute_url = null ) {

		$path = trim( $path, '/' );

		$url = sprintf( 'https://api.%s/api/%s/', self::$gateways[ self::$gateway ], $path );

		if ( ! is_null( $absolute_url ) ) {
			$url = $absolute_url;
		}

		$curl = curl_init();

		curl_setopt_array( $curl, [
			CURLOPT_URL            => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING       => "",
			CURLOPT_MAXREDIRS      => 10,
			CURLOPT_TIMEOUT        => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST  => "POST",
			CURLOPT_POSTFIELDS     => json_encode( $data ),
			CURLOPT_HTTPHEADER     => [
				"Content-type: application/json",
				"Accept: application/json",
				"Authorization: " . PW()->get_options( 'pws_tapin_token' )
			],
		] );

		$response = curl_exec( $curl );

		curl_close( $curl );

		if ( $response === false ) {

			$error = curl_error( $curl );

			PWS()->log( __METHOD__ . ' Line: ' . __LINE__ );
			PWS()->log( $data );
			PWS()->log( $error );

			return new WP_Error( $error );
		}

		return json_decode( $response );
	}

	private static function zone() {

		$zone = get_transient( 'pws_tapin_zone' );

		if ( $zone === false ) {

			$zone = wp_remote_get( 'https://public.api.tapin.ir/api/v1/public/state/tree/' );

			if ( is_wp_error( $zone ) ) {
				$zone = file_get_contents( PWS_DIR . '/data/tapin.json' );
			} else {
				$zone = $zone['body'];
			}

			$zone = json_decode( $zone, true );

			$zone = array_column( $zone['entries'], null, 'code' );

			set_transient( 'pws_tapin_zone', $zone, DAY_IN_SECONDS );
		}

		return $zone;
	}

	public static function states() {

		$states = get_transient( 'pws_tapin_states' );

		if ( $states === false ) {

			$zone = self::zone();

			$states = array_column( $zone, 'title', 'code' );

			uasort( $states, [ self::class, 'pws_sort_state' ] );

			set_transient( 'pws_tapin_states', $states, DAY_IN_SECONDS );
		}

		return $states;
	}

	public static function get_state( $state_id ) {

		$states = self::states();

		return $states[ $state_id ] ?? null;
	}

	public static function cities( $state_id = null ) {

		$cities = get_transient( 'pws_tapin_cities_' . $state_id );

		if ( $cities === false ) {

			$zone = self::zone();

			if ( is_null( $state_id ) ) {

				$state_cities = array_column( self::zone(), 'cities' );

				array_walk( $state_cities, function( &$value, $key ) {
					$value = array_column( $value, 'title', 'code' );
				} );

				$cities = [];

				foreach ( $state_cities as $state_city ) {
					$cities += $state_city;
				}

			} else {
				$cities = array_column( $zone[ $state_id ]['cities'], 'title', 'code' );

				asort( $cities );
			}

			set_transient( 'pws_tapin_cities_' . $state_id, $cities, DAY_IN_SECONDS );
		}

		return $cities;
	}

	public static function get_city( $city_id ) {

		$cities = self::cities();

		return $cities[ $city_id ] ?? null;
	}

	public static function shop() {

		$shop = get_transient( 'pws_tapin_shop' );

		if ( $shop === false ) {

			$shop = self::request( 'v2/public/shop/detail', [
				'shop_id' => PW()->get_options( 'pws_tapin_shop_id' )
			] )->entries;

			set_transient( 'pws_tapin_shop', $shop, DAY_IN_SECONDS );
		}

		return $shop;
	}

	public static function price( $data ) {

		$url = 'https://public.api.tapin.ir/api/v1/public/check-price/';

		$data['rate_type'] = self::$gateway;

		return self::request( '', $data, $url );
	}

	public static function set_gateway( string $gateway ): void {

		if ( in_array( $gateway, array_keys( self::$gateways ) ) ) {
			self::$gateway = $gateway;
		}

	}

	private static function pws_sort_state( $a, $b ) {

		if ( $a == $b ) {
			return 0;
		}

		$states = [
			'آذربایجان شرقی',
			'آذربایجان غربی',
			'اردبیل',
			'اصفهان',
			'البرز',
			'ایلام',
			'بوشهر',
			'تهران',
			'چهارمحال و بختیاری',
			'خراسان جنوبی',
			'خراسان رضوی',
			'خراسان شمالی',
			'خوزستان',
			'زنجان',
			'سمنان',
			'سیستان و بلوچستان',
			'فارس',
			'قزوین',
			'قم',
			'کردستان',
			'کرمان',
			'کرمانشاه',
			'کهگیلویه و بویراحمد',
			'گلستان',
			'گیلان',
			'لرستان',
			'مازندران',
			'مرکزی',
			'هرمزگان',
			'همدان',
			'یزد',
		];

		$a = str_replace( [ 'ي', 'ك', 'ة' ], [ 'ی', 'ک', 'ه' ], $a );
		$b = str_replace( [ 'ي', 'ك', 'ة' ], [ 'ی', 'ک', 'ه' ], $b );

		$a_key = array_search( trim( $a ), $states );
		$b_key = array_search( trim( $b ), $states );

		return $a_key < $b_key ? - 1 : 1;
	}

}