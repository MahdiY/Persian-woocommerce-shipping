<?php
/**
 * Developer : MahdiY
 * Web Site  : MahdiY.IR
 * E-Mail    : M@hdiY.IR
 */

defined( 'ABSPATH' ) || exit;

class PWS_Version {

	public const VERSION_KEY = 'pws_version';

	public function __construct() {

		$installed_version = get_option( self::VERSION_KEY, '2.1.6' );

		if ( in_array( $installed_version, [ '3.0.10', '3.0.11' ] ) ) {
			$installed_version = '3.1.0';
		}

		if ( $installed_version == PWS_VERSION ) {
			return;
		}

		if ( 'yes' === get_transient( 'pws_admin_updating' ) ) {
			return;
		}

		set_transient( 'pws_admin_updating', 'yes', MINUTE_IN_SECONDS * 10 );

		if ( function_exists( 'set_time_limit' ) ) {
			set_time_limit( 0 );
		}

		$installed_version = (int) str_replace( '.', '', $installed_version );
		$pws_version       = (int) str_replace( '.', '', PWS_VERSION );

		for ( $version = $installed_version; $version <= $pws_version; $version ++ ) {
			if ( method_exists( $this, "update_{$version}" ) ) {
				$this->{"update_{$version}"}();
			}
		}

		delete_transient( 'pws_admin_updating' );

		update_option( self::VERSION_KEY, PWS_VERSION );
	}

	public function update_219() {
		global $wpdb;

		// Update zone methods

		$table = $wpdb->prefix . 'woocommerce_shipping_zone_methods';

		$sql = "SELECT * FROM {$table}";

		$methods = $wpdb->get_results( $sql );

		foreach ( $methods as $method ) {

			if ( $method->method_id != 'WC_Tapin_Method' ) {
				continue;
			}

			$settings = get_option( "woocommerce_WC_Tapin_Method_{$method->instance_id}_settings" );

			$new_method = 'Tapin_Pishtaz_Method';

			unset( $settings['post_type'] );

			if ( isset( $settings['pay_type'] ) ) {
				unset( $settings['pay_type'] );
			}

			if ( isset( $settings['default_weight'] ) ) {
				unset( $settings['default_weight'] );
			}

			if ( isset( $settings['package_weight'] ) ) {
				unset( $settings['package_weight'] );
			}

			if ( isset( $settings['post_gateway'] ) ) {
				unset( $settings['post_gateway'] );
			}

			$wpdb->update( $table, [
				'method_id' => $new_method,
			], [
				'zone_id'     => $method->zone_id,
				'instance_id' => $method->instance_id,
			] );

			update_option( "woocommerce_{$new_method}_{$method->instance_id}_settings", $settings );
			delete_option( "woocommerce_WC_Tapin_Method_{$method->instance_id}_settings" );
		}

		// Update order shipping method

		$order_items    = $wpdb->prefix . 'woocommerce_order_items';
		$order_itemmeta = $wpdb->prefix . 'woocommerce_order_itemmeta';

		$sql = "SELECT * FROM {$wpdb->postmeta} WHERE `meta_key` = 'tapin_method'";

		$post_meta = $wpdb->get_results( $sql );

		$post_meta = wp_list_pluck( $post_meta, 'meta_value', 'post_id' );

		if ( ! count( $post_meta ) ) {
			return;
		}

		$order_ids = implode( ',', array_keys( $post_meta ) );

		$sql = "SELECT * FROM {$order_itemmeta} INNER JOIN {$order_items}
				WHERE {$order_itemmeta}.meta_value = 'WC_Tapin_Method'
				AND {$order_items}.`order_item_type` = 'shipping'
				AND {$order_items}.`order_id` IN ({$order_ids})";

		$items = $wpdb->get_results( $sql );

		$items = wp_list_pluck( $items, 'meta_id', 'order_id' );

		foreach ( $post_meta as $order_id => $settings ) {

			$new_method = 'Tapin_Pishtaz_Method';

			$wpdb->update( $order_itemmeta, [
				'meta_value' => $new_method,
			], [
				'meta_id' => $items[ $order_id ],
			] );

			delete_post_meta( $order_id, 'tapin_method' );
		}

	}

	public function update_220() {
		// Update settings

		if ( ! function_exists( 'PW' ) ) {
			return;
		}

		$keys = [
			// Tools
			'pws_status_enable'       => 'tools.status_enable',
			'pws_hide_when_free'      => 'tools.hide_when_free',
			'pws_hide_when_courier'   => 'tools.hide_when_courier',

			// Tapin
			'pws_tapin_enable'        => 'tapin.enable',
			'pws_tapin_enable_credit' => 'tapin.show_credit',
			'pws_tapin_gateway'       => 'tapin.gateway',
			'pws_product_weight'      => 'tapin.product_weight',
			'pws_package_weight'      => 'tapin.package_weight',
			'pws_tapin_token'         => 'tapin.token',
			'pws_tapin_shop_id'       => 'tapin.shop_id',
		];

		foreach ( $keys as $old_key => $new_key ) {

			$value = PW()->get_options( $old_key );
			$value = str_replace( 'yes', 1, $value );
			$value = str_replace( 'no', 0, $value );
			PWS()->set_option( $new_key, $value );

		}

	}

	public function update_230() {

		if ( get_option( 'sabira_set_iran_cities', 0 ) ) {
			update_option( 'pws_install_cities', 1 );
			delete_option( 'sabira_set_iran_cities' );
		}

	}

	public function update_300() {
		global $wp_filter, $wpdb;

		unset( $wp_filter['delete_state_city'] );
		unset( $wp_filter['edited_state_city'] );
		unset( $wp_filter['created_state_city'] );

		require_once( PWS_DIR . '/data/state_city.php' );

		foreach ( PWS_get_states() as $key => $state ) {
			$term = get_term_by( 'slug', $key, 'state_city' );

			if ( $term === false ) {
				continue;
			}

			$cities = PWS_Core::cities( $term->term_id );

			foreach ( PWS_get_state_city( $key ) as $city ) {

				$arabic_city = str_replace( [ 'ک', 'ی' ], [ 'ك', 'ي', ], $city );

				if ( in_array( $city, $cities ) || in_array( $arabic_city, $cities ) ) {
					continue;
				}

				wp_insert_term( $city, 'state_city', [
					'parent'      => $term->term_id,
					'slug'        => $city,
					'description' => "$state - $city",
				] );
			}
		}

		PWS_City::flush_cache();

		PWS()->set_option( 'tools.product_weight', intval( PWS()->get_option( 'tapin.product_weight' ) ) );
		PWS()->set_option( 'tools.package_weight', intval( PWS()->get_option( 'tapin.package_weight' ) ) );

		PWS()->set_option( 'tapin.roundup_price', 1 );

		$options = $wpdb->get_col( "SELECT option_name FROM `{$wpdb->options}` WHERE `option_name` LIKE ('sabira_taxonomy_%');" );

		foreach ( $options as $option ) {
			$data = get_option( $option );

			if ( $data ) {
				add_option( str_replace( 'sabira', 'nabik', $option ), $data );
			}

			delete_option( $option );
		}
	}

	public function update_414() {
		global $wpdb;
		$meta_key = 'pws_map_location';

		// Define the transformation function
		$transform_meta_value = function ( $meta_value ) {
			$array = maybe_unserialize( $meta_value );

			if ( is_array( $array ) && isset( $array[0] ) ) {
				// $array[0] is lat, I'm assuming that till this version all the clients have iran shipping
				// iran lat is between  25.078237,39.777672
				// Transform the simple array to an array with keys
				if ( isset( $array[1] ) && (float) $array[0] > 40 ) {
					// if lat is more than maximum for safety we'll change lat and long
					$array = array_reverse( $array );
				}
				$transformed = [
					'lat'  => $array[0] ?? null,
					'long' => $array[1] ?? null,
				];

				return maybe_serialize( $transformed );
			}

			return $meta_value;
		};

		// Fetch and update post meta
		$post_meta_results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT meta_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s",
				$meta_key
			)
		);

		foreach ( $post_meta_results as $meta ) {

			$new_meta_value = $transform_meta_value( $meta->meta_value );

			if ( $new_meta_value !== $meta->meta_value ) {
				$wpdb->update(
					$wpdb->postmeta,
					[ 'meta_value' => $new_meta_value ],
					[ 'meta_id' => $meta->meta_id ]
				);
			}

		}

		// Fetch and update order meta
		$order_meta_results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, meta_value FROM {$wpdb->prefix}wc_orders_meta WHERE meta_key = %s",
				$meta_key
			)
		);

		foreach ( $order_meta_results as $meta ) {

			$new_meta_value = $transform_meta_value( $meta->meta_value );

			if ( $new_meta_value !== $meta->meta_value ) {
				$wpdb->update(
					"{$wpdb->prefix}wc_orders_meta",
					[ 'meta_value' => $new_meta_value ],
					[ 'id' => $meta->id ]
				);
			}

		}

		// Fetch and update user meta
		$user_meta_results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT umeta_id, meta_value FROM {$wpdb->usermeta} WHERE meta_key = %s",
				$meta_key
			)
		);

		foreach ( $user_meta_results as $meta ) {
			$new_meta_value = $transform_meta_value( $meta->meta_value );

			if ( $new_meta_value !== $meta->meta_value ) {
				$wpdb->update(
					$wpdb->usermeta,
					[ 'meta_value' => $new_meta_value ],
					[ 'umeta_id' => $meta->umeta_id ]
				);
			}

		}

	}

	public function update_440() {
		global $wpdb;

		// Make dynamic methods
		$options = $wpdb->get_results(
			"SELECT * FROM `{$wpdb->options}` WHERE `option_name` LIKE ('nabik_taxonomy_%');",
			ARRAY_A
		);

		foreach ( wp_list_pluck( $options, 'option_value', 'option_id' ) as $option_id => $option_value ) {

			$data = maybe_unserialize( $option_value );

			foreach ( PWS_City::zones() as $zone ) {

				foreach ( PWS_City::methods( $zone ) as $shipping_method_instance_id => $shipping_method_instance ) {

					$method_id = $shipping_method_instance->get_rate_id();

					if ( $shipping_method_instance instanceof WC_Courier_Method ) {

						if ( isset( $data['courier_on'] ) && $data['courier_on'] ) {
							$data[ $method_id . '_on' ] = 1;
						}

						if ( isset( $data['courier_cost'] ) && $data['courier_cost'] != '' ) {
							$data[ $method_id ] = $data['courier_cost'];
						}

					} elseif ( $shipping_method_instance instanceof WC_Tipax_Method ) {

						if ( isset( $data['tipax_on'] ) && $data['tipax_on'] ) {
							$data[ $method_id . '_on' ] = 1;
						}

						if ( isset( $data['tipax_cost'] ) && $data['tipax_cost'] != '' ) {
							$data[ $method_id ] = $data['tipax_cost'];
						}

					} elseif ( $shipping_method_instance instanceof WC_Forehand_Method || $shipping_method_instance instanceof Tapin_Pishtaz_Method ) {

						if ( isset( $data['forehand_cost'] ) && $data['forehand_cost'] != '' ) {
							$data[ $method_id ] = $data['forehand_cost'];
						}

					}
				}
			}

			if ( maybe_serialize( $data ) != $option_value ) {
				$wpdb->update(
					$wpdb->options,
					[
						'option_value' => maybe_serialize( $data ),
						'autoload'     => 'off',
					],
					[ 'option_id' => $option_id ]
				);
			}
		}

		// Move Courier delivery areas to method options
		$methods = [];

		$options = $wpdb->get_results(
			"SELECT * FROM `{$wpdb->options}` WHERE `option_name` LIKE ('nabik_taxonomy_%');",
			ARRAY_A
		);

		foreach ( wp_list_pluck( $options, 'option_value', 'option_name' ) as $option_name => $option_value ) {

			if ( str_contains( $option_name, 'state' ) ) {
				continue;
			}

			$data = maybe_unserialize( $option_value );

			$parts   = explode( '_', $option_name );
			$term_id = end( $parts );

			foreach ( $data as $method_instance_id => $value ) {

				if ( str_contains( $method_instance_id, 'WC_Courier_Method' ) && str_contains( $method_instance_id, '_on' ) ) {
					$methods[ str_replace( '_on', '', $method_instance_id ) ][] = $term_id;
				}

			}

		}

		foreach ( $methods as $method_id => $delivery_areas ) {

			$method_id   = str_replace( ':', '_', $method_id );
			$option_name = "woocommerce_{$method_id}_settings";

			$data                   = (array) get_option( $option_name, [] );
			$data['delivery_areas'] = $delivery_areas;
			update_option( $option_name, $data );

		}
	}

}

add_action( 'admin_init', function () {
	new PWS_Version();
}, 110 );
