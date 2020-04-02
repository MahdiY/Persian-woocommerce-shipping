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
			'WC_Tipax_Method',
			'WC_Tapin_Method',
		];

		add_filter( 'wooi_ticket_header_path', function() {
			return PWS_DIR . '/assets/template/header.php';
		}, 100 );
		add_filter( 'wooi_ticket_body_path', function() {
			return PWS_DIR . '/assets/template/body.php';
		}, 100 );
		add_filter( 'wooi_ticket_footer_path', function() {
			return PWS_DIR . '/assets/template/footer.php';
		}, 100 );
		add_filter( 'wooi_ticket_per_page', function() {
			return 10000;
		}, 100 );

		add_action( 'admin_footer', [ $this, 'admin_footer' ] );

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

			$fields[ $type ][ $type . '_state' ]['placeholder'] = __( 'استان خود را انتخاب نمایید' );

			$class = is_array( $fields[ $type ][ $type . '_city' ]['class'] ) ? $fields[ $type ][ $type . '_city' ]['class'] : array();

			$fields[ $type ][ $type . '_postcode' ]['clear'] = false;

			$fields[ $type ][ $type . '_city' ] = array(
				'type'        => $type . '_mahdiy_cities',
				'label'       => 'شهر',
				'placeholder' => __( 'لطفا ابتدا استان خود را انتخاب نمایید' ),
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
		$fields = [ 'state', 'city' ];

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

		if ( wc_ship_to_billing_address_only() ) {

			foreach ( $fields as $field ) {

				$label = get_post_meta( $order_id, "_billing_{$field}", true );
				$id    = get_post_meta( $order_id, "_billing_{$field}_id", true );

				update_post_meta( $order_id, "_shipping_{$field}", $label );
				update_post_meta( $order_id, "_shipping_{$field}_id", $id );

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

		$type_label = [
			'billing'  => 'صورتحساب',
			'shipping' => 'حمل و نقل'
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

	function localisation_address_formats( $formats ) {

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

	public function persian_woo_sms_content_replace( $content, $find, $replace, $order_id, $order, $product_ids ) {

		$city                = self::get_city( $replace[6] );
		$pws_tag['{b_city}'] = is_null( $city ) ? $replace[6] : $city;

		$city                 = self::get_city( $replace[15] );
		$pws_tag['{sh_city}'] = is_null( $city ) ? $replace[15] : $city;

		return strtr( $content, $pws_tag );
	}

	public function admin_footer() {

		if ( ! isset( $_GET['page'], $_GET['tab'], $_GET['instance_id'] ) || $_GET['tab'] != 'shipping' ) {
			return false;
		}

		?>
        <script type="text/javascript">
			let courier = jQuery("#woocommerce_WC_Courier_Method_destination");

			if( courier.length ) {
				courier.select2();
			}

			let tipax = jQuery("#woocommerce_WC_Tipax_Method_destination");

			if( tipax.length ) {
				tipax.select2();
			}
        </script>
		<?php
	}

	public static function is_enable() {
		$options = get_option( 'PW_Options' );

		return isset( $options['pws_tapin_enable'] ) && $options['pws_tapin_enable'] == 'yes';
	}

	public static function request( $path, $data = [], $absolute_url = null ) {

		$path = trim( $path, ' / ' );

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
				"Authorization: " . PWS()->get_options( 'pws_tapin_token' )
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

	public static function zone() {

		$zone = get_transient( 'pws_tapin_zone' );

		if ( $zone === false ) {

			$response = wp_remote_get( 'https://public.api.tapin.ir/api/v1/public/state/tree/' );

			if ( is_wp_error( $response ) ) {
				$zone = get_option( 'pws_tapin_zone', null );

				if ( is_null( $zone ) ) {
					$zone = file_get_contents( PWS_DIR . '/data/tapin.json' );
				}

			} else {
				$data = $response['body'];

				$data = json_decode( $data, true )['entries'];

				$zone = [];

				foreach ( $data as $state ) {

					$zone[ $state['code'] ] = [
						'title'  => trim( $state['title'] ),
						'cities' => []
					];

					foreach ( $state['cities'] as $city ) {
						$title = trim( str_replace( '-' . $state['title'], '', $city['title'] ) );

						$zone[ $state['code'] ]['cities'][ $city['code'] ] = $title;
					}

				}

				set_transient( 'pws_tapin_zone', $zone, WEEK_IN_SECONDS );
				update_option( 'pws_tapin_zone', $zone );
			}
		}

		return $zone;
	}

	public static function states() {

		$states = get_transient( 'pws_tapin_states' );

		if ( $states === false ) {

			$zone = self::zone();

			$states = [];

			foreach ( $zone as $code => $state ) {
				$states[ $code ] = trim( $state['title'] );
			}

			uasort( $states, [ self::class, 'pws_sort_state' ] );

			set_transient( 'pws_tapin_states', $states, DAY_IN_SECONDS );
		}

		return $states;
	}

	public static function cities( $state_id = null ) {

		$cities = get_transient( 'pws_tapin_cities_' . $state_id );

		if ( $cities === false ) {

			$zone = self::zone();

			if ( is_null( $state_id ) ) {

				$state_cities = array_column( self::zone(), 'cities' );

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
				'shop_id' => PWS()->get_options( 'pws_tapin_shop_id' )
			] );

			if ( is_wp_error( $shop ) ) {
				return get_option( 'pws_tapin_shop' );
			} else {
				$shop = $shop->entries;
			}

			set_transient( 'pws_tapin_shop', $shop, DAY_IN_SECONDS );
			update_option( 'pws_tapin_shop', $shop );
		}

		return $shop;
	}

	public static function price( $data ) {

		$url = 'https://public.api.tapin.ir/api/v1/public/check-price/';

		$data['rate_type'] = self::$gateway;

		return self::request( '', $data, $url );
	}

	public static function set_gateway( string $gateway ) {

		if ( in_array( $gateway, array_keys( self::$gateways ) ) ) {
			self::$gateway = $gateway;
		}

	}

}