<?php
/**
 * Developer : MahdiY
 * Web Site  : MahdiY.IR
 * E-Mail    : M@hdiY.IR
 */

class PWS_Core {

	/**
	 * Shipping methods.
	 *
	 * @var array
	 */
	public static $methods = [];

	/**
	 * The single instance of the class.
	 *
	 * @var PWS_Core
	 */
	protected static $_instance = null;

	/**
	 * Ensures only one instance of PWS_Core is loaded or can be loaded.
	 *
	 * @see PWS()
	 * @return PWS_Core
	 */
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * PWS_Core constructor.
	 */
	public function __construct() {

		self::$methods = [
			'WC_Courier_Method',
			'WC_Custom_Method',
			'WC_Forehand_Method',
			'WC_Tipax_Method',
		];

		$this->init_hooks();
	}

	/**
	 * Hook into actions and filters.
	 */
	protected function init_hooks() {
		// Actions
		add_action( 'init', [ $this, 'state_city_taxonomy' ], 0 );
		add_action( 'admin_menu', [ $this, 'state_city_admin_menu' ] );
		add_action( 'wp_ajax_mahdiy_load_cities', [ PWS_Ajax::class, 'load_cities_callback' ] );
		add_action( 'wp_ajax_nopriv_mahdiy_load_cities', [ PWS_Ajax::class, 'load_cities_callback' ] );
		add_action( 'wp_ajax_mahdiy_load_districts', [ PWS_Ajax::class, 'load_districts_callback' ] );
		add_action( 'wp_ajax_nopriv_mahdiy_load_districts', [ PWS_Ajax::class, 'load_districts_callback' ] );
		add_action( 'woocommerce_shipping_init', [ $this, 'load_shipping_init' ] );
		add_action( 'woocommerce_process_shop_order_meta', [ $this, 'process_shop_order_meta' ], 1000, 2 );
		add_action( 'woocommerce_checkout_update_order_review', [ $this, 'checkout_update_order_review' ], 10, 1 );
		add_action( 'woocommerce_admin_field_pws_single_country', [ $this, 'pws_single_country_field' ], 10, 1 );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_select2_scripts' ], 1000 );

		// Filters
		add_filter( 'woocommerce_shipping_methods', [ $this, 'add_shipping_method' ] );
		add_filter( 'woocommerce_get_settings_general', [ $this, 'get_settings_general' ], 10, 1 );
		add_filter( 'woocommerce_states', [ $this, 'iran_states' ], 20, 1 );
		add_filter( 'manage_edit-state_city_columns', [ $this, 'edit_state_city_columns_taxonomy' ], 10, 1 );
		add_filter( 'manage_state_city_custom_column', [ $this, 'edit_state_city_rows_taxonomy' ], 10, 3 );
		add_filter( 'woocommerce_checkout_fields', [ $this, 'edit_checkout_cities_field' ], 20, 1 );
		add_filter( 'woocommerce_checkout_update_order_meta', [ $this, 'checkout_update_order_meta' ], 20, 1 );
		add_filter( 'woocommerce_checkout_process', [ $this, 'checkout_process' ], 20, 1 );
		add_filter( 'woocommerce_form_field_billing_city', [ $this, 'checkout_cities_field' ], 11, 4 );
		add_filter( 'woocommerce_form_field_shipping_city', [ $this, 'checkout_cities_field' ], 11, 4 );
		add_filter( 'woocommerce_form_field_billing_district', [ $this, 'checkout_cities_field' ], 11, 4 );
		add_filter( 'woocommerce_form_field_shipping_district', [ $this, 'checkout_cities_field' ], 11, 4 );
		add_filter( 'woocommerce_cart_shipping_packages', [ $this, 'cart_shipping_packages' ], 20, 1 );
		add_filter( 'woocommerce_cart_shipping_method_full_label', [ $this, 'shipping_method_image' ], 10, 2 );
		add_filter( 'woocommerce_localisation_address_formats', [ $this, 'localisation_address_formats' ], 20, 1 );
		add_filter( 'woocommerce_order_formatted_shipping_address', [
			$this,
			'order_formatted_shipping_address'
		], 20, 2 );
		add_filter( 'woocommerce_order_formatted_billing_address', [
			$this,
			'order_formatted_billing_address'
		], 00, 2 );
		add_filter( 'woocommerce_formatted_address_replacements', [
			$this,
			'formatted_address_replacements'
		], 10, 2 );
		add_filter( 'woocommerce_my_account_my_address_formatted_address', [
			$this,
			'my_account_my_address_formatted_address'
		], 10, 3 );
		add_filter( 'persian_woo_sms_content_replace', [ $this, 'persian_woo_sms_content_replace' ], 10, 6 );
		add_filter( 'woocommerce_admin_billing_fields', [ $this, 'admin_billing_fields' ] );
		// @todo uncomment after pull request accepted
		//add_filter( 'woocommerce_admin_shipping_fields', [ $this,'admin_shipping_fields'] );
		add_filter( 'woocommerce_checkout_get_value', [ $this, 'checkout_get_value' ], 10, 2 );
	}

	// Actions

	public function state_city_taxonomy() {

		$labels = [
			'name'              => __( 'شهر ها' ),
			'singular_name'     => __( 'شهر ها' ),
			'search_items'      => __( 'جستجو شهر' ),
			'all_items'         => __( 'همه شهر ها' ),
			'parent_item'       => __( 'استان' ),
			'parent_item_colon' => __( 'استان' ),
			'edit_item'         => __( 'ویرایش شهر' ),
			'update_item'       => __( 'بروزرسانی شهر' ),
			'add_new_item'      => __( 'افزودن شهر جدید' ),
			'new_item_name'     => __( 'نام شهر جدید' ),
			'menu_name'         => __( 'شهر های حمل و نقل' ),
		];

		register_taxonomy( 'state_city', null, [
			'hierarchical'       => true,
			'labels'             => $labels,
			'query_var'          => false,
			'rewrite'            => false,
			'public'             => false,
			'show_ui'            => true,
			'show_in_quick_edit' => false,
			'show_admin_column'  => false,
			'_builtin'           => true,
			'meta_box_cb'        => false
		] );

		if ( function_exists( 'PW' ) && PW()->get_options( 'enable_iran_cities' ) != 'no' ) {
			$settings                       = PW()->get_options();
			$settings['enable_iran_cities'] = 'no';
			update_option( 'PW_Options', $settings );
		}

		if ( get_option( 'sabira_set_iran_cities', 0 ) || get_option( 'pws_install_cities', 0 ) ) {
			return false;
		}

		foreach ( PWS_get_states() as $key => $state ) {
			$term = wp_insert_term( $state, 'state_city', [ 'slug' => $key, 'description' => "استان $state" ] );

			if ( is_wp_error( $term ) ) {
				continue;
			}

			foreach ( PWS_get_state_city( $key ) as $city ) {
				wp_insert_term( $city, 'state_city', [
					'parent'      => $term['term_id'],
					'description' => "$state - $city"
				] );
			}
		}

		update_option( "pws_version", PWS_VERSION );
		update_option( "pws_install_cities", 1 );
	}

	public function state_city_admin_menu() {
		$title = 'شهر های حمل و نقل';

		add_submenu_page( 'woocommerce', $title, $title, 'manage_woocommerce', 'edit-tags.php?taxonomy=state_city&post_type=shop_order' );
	}

	public function load_shipping_init() {
		require_once PWS_DIR . '/methods/pws-method.php';
		require_once PWS_DIR . '/methods/courier-method.php';
		require_once PWS_DIR . '/methods/custom-method.php';
		require_once PWS_DIR . '/methods/forehand-method.php';
		require_once PWS_DIR . '/methods/tipax-method.php';
		require_once PWS_DIR . '/methods/tapin-method.php';
	}

	public function process_shop_order_meta( $order_id, $post ) {

		$types  = [ 'billing' ]; // @todo add shipping after pull request accepted
		$fields = [ 'state', 'city' ];

		foreach ( $types as $type ) {

			foreach ( $fields as $field ) {

				$key = '_' . $type . '_' . $field;

				if ( isset( $_POST[ $key ] ) && strlen( $_POST[ $key ] ) ) {

					$id = intval( $_POST[ $key ] );

					if ( $field == 'state' ) {
						$name = PWS()::get_state( $id );
					} else {
						$name = PWS()::get_city( $id );
					}

					if ( ! is_null( $name ) ) {
						update_post_meta( $order_id, "{$key}", $name );
						update_post_meta( $order_id, "{$key}_id", $id );
					}

				}

			}
		}

	}

	public function checkout_update_order_review( $input ) {
		parse_str( $input, $data );

		$billing_district  = isset( $data['billing_district'] ) ? wc_clean( wp_unslash( $data['billing_district'] ) ) : null;
		$shipping_district = isset( $data['shipping_district'] ) ? wc_clean( wp_unslash( $data['shipping_district'] ) ) : null;

		if ( wc_ship_to_billing_address_only() ) {
			$shipping_district = $billing_district;
		}

		WC()->session->set( 'billing_district', $billing_district );
		WC()->session->set( 'shipping_district', $shipping_district );
	}

	public function pws_single_country_field( $value ) {
		$country_setting = get_option( $value['id'] );

		if ( strstr( $country_setting, ':' ) ) {
			list( $country, $state ) = explode( ':', $country_setting );

			if ( is_numeric( $state ) ) {
				$state = intval( $state );
			}
		} else {
			$country = $country_setting;
			$state   = '*';
		}

		?>
        <tr valign="top">
        <th scope="row" class="titledesc">
            <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
        </th>
        <td class="forminp">
            <select name="<?php echo esc_attr( $value['id'] ); ?>"
                    style="<?php echo esc_attr( $value['css'] ); ?>"
                    data-placeholder="<?php esc_attr_e( 'Choose a country&hellip;', 'woocommerce' ); ?>"
                    aria-label="<?php esc_attr_e( 'Country', 'woocommerce' ) ?>"
                    class="wc-enhanced-select">
				<?php WC()->countries->country_dropdown_options( $country, $state ); ?>
            </select>
        </td>
        </tr><?php
	}

	public function enqueue_select2_scripts() {
		if ( ! is_checkout() ) {
			return false;
		}

		wp_register_script( 'selectWoo', WC()->plugin_url() . '/assets/js/selectWoo/selectWoo.full.min.js', [ 'jquery' ], '4.0.3' );
		wp_enqueue_script( 'selectWoo' );
		wp_register_style( 'select2', WC()->plugin_url() . '/assets/css/select2.css' );
		wp_enqueue_style( 'select2' );

		wp_register_script( 'pwsCheckout', PWS_URL . 'assets/js/pws.js', [ 'selectWoo' ], '1.0.0' );
		wp_localize_script( 'pwsCheckout', 'pws_settings', [
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'types'    => $this->types()
		] );
		wp_enqueue_script( 'pwsCheckout' );
	}

	// Filters

	public function add_shipping_method( $methods ) {

		foreach ( self::$methods as $new_method ) {
			if ( class_exists( $new_method ) ) {
				$methods[ $new_method ] = $new_method;
			}
		}

		return $methods;
	}

	public function get_settings_general( $settings ) {

		foreach ( $settings as &$setting ) {

			if ( $setting['id'] == 'woocommerce_default_country' ) {
				$setting['type'] = 'pws_single_country';
			}

		}

		return $settings;
	}

	public function iran_states( $states ) {

		$states['IR'] = PWS()->states();

		return $states;
	}

	public function edit_state_city_columns_taxonomy( $original_columns ) {

		unset( $original_columns['posts'] );
		$original_columns['city_id'] = "شناسه شهر";

		return $original_columns;
	}

	public function edit_state_city_rows_taxonomy( $row, $column_name, $term_id ) {

		if ( 'city_id' === $column_name ) {
			return $term_id;
		}

		return $row;
	}

	public function edit_checkout_cities_field( $fields ) {

		$types = $this->types();

		foreach ( $types as $type ) {

			if ( ! isset( $fields[ $type ][ $type . '_city' ] ) ) {
				continue;
			}

			$fields[ $type ][ $type . '_state' ]['placeholder'] = __( 'استان خود را انتخاب نمایید' );

			$default_state_id = apply_filters( 'pws_default_state', 0, $type );

			if ( $default_state_id ) {
				$fields[ $type ][ $type . '_state' ]['default'] = $default_state_id;
			}

			$class = is_array( $fields[ $type ][ $type . '_city' ]['class'] ) ? $fields[ $type ][ $type . '_city' ]['class'] : [];

			$fields[ $type ][ $type . '_postcode' ]['clear'] = false;

			$fields[ $type ][ $type . '_city' ] = [
				'type'        => $type . '_city',
				'label'       => 'شهر',
				'placeholder' => __( 'لطفا ابتدا استان خود را انتخاب نمایید' ),
				'required'    => true,
				'id'          => $type . '_city',
				'class'       => apply_filters( 'pws_city_class', $class ),
				'default'     => apply_filters( 'pws_default_city', 0, $type, null ),
				'priority'    => apply_filters( 'pws_city_priority', $fields[ $type ][ $type . '_city' ]['priority'] ),
			];

			if ( ! PWS_Tapin::is_enable() ) {
				$fields[ $type ][ $type . '_district' ] = [
					'type'        => $type . '_district',
					'label'       => 'محله',
					'placeholder' => __( 'یک محله انتخاب نمایید' ),
					'required'    => false,
					'id'          => $type . '_district',
					'class'       => apply_filters( 'pws_district_class', $class ),
					'clear'       => true,
					'default'     => apply_filters( 'pws_default_district', 0, $type, null ),
					'priority'    => apply_filters( 'pws_district_priority', $fields[ $type ][ $type . '_city' ]['priority'] + 1 ),
				];
			}

		}

		return $fields;
	}

	public function checkout_update_order_meta( $order_id ) {

		$types  = $this->types();
		$fields = [ 'state', 'city', 'district' ];

		foreach ( $types as $type ) {

			foreach ( $fields as $field ) {

				$term_id = get_post_meta( $order_id, "_{$type}_{$field}", true );
				$term    = get_term( intval( $term_id ) );

				if ( ! is_wp_error( $term ) && ! is_null( $term ) ) {
					update_post_meta( $order_id, "_{$type}_{$field}", $term->name );
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

	}

	public function checkout_process() {

		$types = $this->types();

		$fields = [
			'state'    => 'استان',
			'city'     => 'شهر',
			'district' => 'محله',
		];

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

					$term_id = intval( $_POST[ $key ] );

					if ( $term_id == 0 ) {
						$message = sprintf( 'لطفا <b>%s %s</b> خود را انتخاب نمایید.', $name, $label );
						wc_add_notice( $message, 'error' );

						continue;
					}

					/** @var WP_Term $term */
					$term = get_term( $term_id, 'state_city' );

					if ( is_wp_error( $term ) || is_null( $term ) ) {
						$message = sprintf( '<b>%s %s</b> انتخاب شده معتبر نمی باشد.', $name, $label );
						wc_add_notice( $message, 'error' );

						continue;
					}

					if ( $field == 'city' ) {

						$pkey = $type . '_state';

						if ( isset( $_POST[ $pkey ] ) && ! empty( $_POST[ $pkey ] ) && $term->parent != $_POST[ $pkey ] ) {
							$message = sprintf( '<b>استان</b> با <b>شهر</b> %s انتخاب شده همخوانی ندارند.', $label );
							wc_add_notice( $message, 'error' );

							continue;
						}
					}

					if ( $field == 'district' ) {

						$pkey = $type . '_city';

						if ( isset( $_POST[ $pkey ] ) && ! empty( $_POST[ $pkey ] ) && $term->parent != $_POST[ $pkey ] && $term_id != $_POST[ $pkey ] ) {
							$message = sprintf( '<b>شهر</b> با <b>محله</b> %s انتخاب شده همخوانی ندارند.', $label );
							wc_add_notice( $message, 'error' );
						}
					}
				}

			}

		}
	}

	public function checkout_cities_field( $field, $key, $args, $value ) {

		$field_html = '';
		$options    = [];

		list( $type, $name ) = explode( '_', $args['type'] );

		if ( $name == 'city' ) {

			$state_id = WC()->checkout()->get_value( $type . '_state' );

			if ( $state_id ) {
				$options = PWS()->cities( $state_id );
			}

		} elseif ( $name == 'district' ) {

			$city_id = WC()->checkout()->get_value( $type . '_city' );

			if ( $city_id ) {
				$options = get_terms( [
					'taxonomy'   => 'state_city',
					'hide_empty' => false,
					'child_of'   => $city_id
				] );

				$city = get_term( $city_id, 'state_city' );

				$options = array_column( $options, 'name', 'term_id' );

				if ( count( $options ) ) {
					$options = [ $city->term_id => $city->name ] + $options;
				}
			}

		}

		if ( $args['required'] ) {
			$args['class'][] = 'validate-required';
			$required        = '&nbsp;<abbr class="required" title="' . esc_attr__( 'required', 'woocommerce' ) . '">*</abbr>';
		} else {
			$required = '&nbsp;<span class="optional">(' . esc_html__( 'optional', 'woocommerce' ) . ')</span>';
		}

		if ( is_string( $args['label_class'] ) ) {
			$args['label_class'] = array( $args['label_class'] );
		}

		if ( is_null( $value ) ) {
			$value = $args['default'];
		}

		$custom_attributes = [];

		if ( ! empty( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) ) {
			foreach ( $args['custom_attributes'] as $attribute => $attribute_value ) {
				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
			}
		}

		if ( ! empty( $args['validate'] ) ) {
			foreach ( $args['validate'] as $validate ) {
				$args['class'][] = 'validate-' . $validate;
			}
		}

		$sort            = $args['priority'] ? $args['priority'] : '';
		$field_container = '<p class="form-row %1$s" id="%2$s" data-priority="' . esc_attr( $sort ) . '">%3$s</p>';

		if ( is_array( $options ) ) {

			if ( empty( $options ) && $name == 'district' ) {
				$field_container = '<p class="form-row %1$s" id="%2$s" style="display: none">%3$s</p>';
			}

			$field .= '<select name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" class="' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" ' . implode( ' ', $custom_attributes ) . ' data-placeholder="' . esc_attr( $args['placeholder'] ) . '">
				<option value="">' . esc_attr( $args['placeholder'] ) . '&hellip;</option>';

			foreach ( $options as $id => $label ) {
				if ( $name == 'city' ) {
					$field .= '<option value="' . esc_attr( $id ) . '" ' . selected( $value, $id, false ) . '>' . $label . '</option>';
				} elseif ( $name == 'district' ) {
					$field .= '<option value="' . esc_attr( $id ) . '" ' . selected( $value, $id, false ) . '>' . str_repeat( "- ", count( get_ancestors( $id, 'state_city' ) ) - 2 ) . $label . '</option>';
				}
			}

			$field .= '</select>';

		}

		if ( $args['label'] ) {
			$field_html .= '<label for="' . esc_attr( $args['id'] ) . '" class="' . esc_attr( implode( ' ', $args['label_class'] ) ) . '">' . $args['label'] . $required . '</label>';
		}

		$field_html .= $field;

		if ( $args['description'] ) {
			$field_html .= '<span class="description">' . esc_html( $args['description'] ) . '</span>';
		}

		$container_class = 'form-row ' . esc_attr( implode( ' ', $args['class'] ) );
		$container_id    = esc_attr( $args['id'] ) . '_field';

		$after = ! empty( $args['clear'] ) ? '<div class="clear"></div>' : '';
		$field = sprintf( $field_container, $container_class, $container_id, $field_html ) . $after;

		return $field;
	}

	public function cart_shipping_packages( $packages ) {

		$type = 'billing';

		if ( isset( $_POST['post_data'] ) ) {
			parse_str( $_POST['post_data'], $data );

			if ( isset( $data['ship_to_different_address'] ) && in_array( 'shipping', $this->types() ) ) {
				$type = 'shipping';
			}

			for ( $i = 0; $i < count( $packages ); $i ++ ) {
				$packages[ $i ]['destination']['district'] = intval( $data[ $type . '_district' ] ) != 0 ? $data[ $type . '_district' ] : null;
			}
		}

		return $packages;
	}

	public function shipping_method_image( $label, $method ) {

		$method_id = str_replace( ':', '_', $method->id );
		$option    = get_option( "woocommerce_{$method_id}_settings" );

		if ( isset( $option['img_url'] ) && ! empty( $option['img_url'] ) ) {
			return sprintf( '<img src="%s" class="%s %s" style="max-width: 100px;display: inline;"/>', $option['img_url'], $method_id, strtok( $method->id, ':' ) ) . $label;
		}

		return $label;
	}

	public function localisation_address_formats( $formats ) {

		$formats['IR'] = "{company}\n{first_name} {last_name}\n{country}\n{state}\n{city}\n{district}\n{address_1}\n{address_2}\n{postcode}";

		return $formats;
	}

	public function order_formatted_shipping_address( $data, $args ) {

		if ( is_array( $data ) ) {
			$data['district'] = get_post_meta( $args->get_id(), '_shipping_district', true );
		}

		return $data;
	}

	public function order_formatted_billing_address( $data, $args ) {

		if ( is_array( $data ) ) {
			$data['district'] = get_post_meta( $args->get_id(), '_billing_district', true );
		}

		return $data;
	}

	public function formatted_address_replacements( $replace, $args ) {

		if ( ctype_alnum( $replace['{state}'] ) && strlen( $replace['{state}'] ) == 2 ) {
			$state              = get_term_by( 'slug', $replace['{state}'], 'state_city' );
			$replace['{state}'] = $state == false ? $replace['{state}'] : $state->name;
		}

		if ( ctype_alnum( $replace['{state}'] ) && strlen( $replace['{state}'] ) == 3 && function_exists( 'PW' ) ) {
			$replace['{state}'] = PW()->address->states[ $args['state'] ] ?? $args['state'];
		}

		if ( ctype_alnum( $replace['{state}'] ) && strlen( $replace['{state}'] ) == 3 ) {
			$state              = get_term_by( 'slug', $replace['{state}'], 'state_city' );
			$replace['{state}'] = $state == false ? $replace['{state}'] : $state->name;
		}

		if ( ctype_digit( $args['city'] ) ) {
			$city              = get_term( $args['city'] );
			$replace['{city}'] = is_wp_error( $city ) || is_null( $city ) ? $args['city'] : $city->name;
		}

		if ( isset( $args['district'] ) ) {
			$district              = get_term( intval( $args['district'] ) );
			$replace['{district}'] = is_wp_error( $district ) ? $args['district'] : $district->name;
		} else {
			$replace['{district}'] = null;
		}

		return $replace;
	}

	function my_account_my_address_formatted_address( $args, $customer_id, $name ) {

		$args['district'] = get_user_meta( $customer_id, $name . '_district', true );

		return $args;
	}

	public function persian_woo_sms_content_replace( $content, $find, $replace, $order_id, $order, $product_ids ) {

		$city                = get_term( $replace[6] );
		$pws_tag['{b_city}'] = is_wp_error( $city ) || is_null( $city ) ? $replace[6] : $city->name;

		$city                 = get_term( $replace[15] );
		$pws_tag['{sh_city}'] = is_wp_error( $city ) || is_null( $city ) ? $replace[15] : $city->name;

		return strtr( $content, $pws_tag );
	}

	public function admin_billing_fields( $fields ) {

		$screen = get_current_screen();

		if ( $screen->id != 'shop_order' ) {
			return $fields;
		}

		if ( ! isset( $_GET['post'] ) ) {
			return $fields;
		}

		$order_id = intval( $_GET['post'] );

		$state_id = get_post_meta( $order_id, '_billing_state_id', true );

		$fields['state'] = [
			'label'   => __( 'State', 'woocommerce' ),
			'show'    => false,
			'class'   => 'select short',
			'type'    => 'select',
			'value'   => $state_id,
			'options' => PWS()::states(),
		];

		$fields['city'] = [
			'label'   => __( 'City', 'woocommerce' ),
			'show'    => false,
			'class'   => 'select short',
			'type'    => 'select',
			'value'   => get_post_meta( $order_id, '_billing_city_id', true ),
			'options' => PWS()::cities( $state_id ),
		];

		return $fields;
	}

	public function admin_shipping_fields( $fields ) {

		$screen = get_current_screen();

		if ( $screen->id != 'shop_order' ) {
			return $fields;
		}

		if ( ! isset( $_GET['post'] ) ) {
			return $fields;
		}

		$order_id = intval( $_GET['post'] );

		$state_id = get_post_meta( $order_id, '_shipping_state_id', true );

		$fields['state'] = [
			'label'   => __( 'State', 'woocommerce' ),
			'show'    => false,
			'class'   => 'select short',
			'type'    => 'select',
			'value'   => $state_id,
			'options' => PWS()::states(),
		];

		$fields['city'] = [
			'label'   => __( 'City', 'woocommerce' ),
			'show'    => false,
			'class'   => 'select short',
			'type'    => 'select',
			'value'   => get_post_meta( $order_id, '_shipping_city_id', true ),
			'options' => PWS()::cities( $state_id ),
		];

		return $fields;
	}

	public function checkout_get_value( $value, $key ) {

		if ( ! in_array( $key, [ 'billing_district', 'shipping_district' ] ) ) {
			return $value;
		}

		return WC()->session->get( $key, 0 );
	}

	// Functions

	public function types() {

		$types = [ 'billing' ];

		if ( ! wc_ship_to_billing_address_only() ) {
			$types[] = 'shipping';
		}

		return $types;
	}

	public static function states() {

		$states = get_transient( 'pws_states' );

		if ( $states === false ) {

			$states = get_terms( [
				'taxonomy'   => 'state_city',
				'hide_empty' => false,
				'parent'     => 0
			] );

			$states = wp_list_pluck( $states, 'name', 'term_id' );

			uasort( $states, [ self::class, 'pws_sort_state' ] );

			set_transient( 'pws_states', $states, DAY_IN_SECONDS );
		}

		return $states;
	}

	public static function get_state( $state_id ) {

		$states = PWS()->states();

		return $states[ $state_id ] ?? null;
	}

	public static function cities( $state_id ) {

		$cities = get_transient( 'pws_cities_' . $state_id );

		if ( $cities === false ) {

			$cities = get_terms( [
				'taxonomy'   => 'state_city',
				'hide_empty' => false,
				'parent'     => $state_id
			] );

			if ( is_wp_error( $cities ) ) {
				$cities = [];
			} else {
				$cities = array_column( $cities, 'name', 'term_id' );
			}

			set_transient( 'pws_cities_' . $state_id, $cities, DAY_IN_SECONDS );
		}

		return $cities;
	}

	public static function get_city( $city_id ) {

		/** @var WP_Term $city */
		$city = get_term( $city_id, 'state_city' );

		return is_wp_error( $city ) || is_null( $city ) ? null : $city->name;
	}

	public function check_states_beside( $source, $destination ) {

		if ( $source == $destination ) {
			return 'in';
		}

		$is_beside["AE"]["AW"] = true;
		$is_beside["AE"]["AR"] = true;
		$is_beside["AE"]["ZA"] = true;

		$is_beside["AW"]["AE"] = true;
		$is_beside["AW"]["KD"] = true;
		$is_beside["AW"]["ZA"] = true;

		$is_beside["AR"]["AE"] = true;
		$is_beside["AR"]["GI"] = true;
		$is_beside["AR"]["ZA"] = true;

		$is_beside["IS"]["CM"] = true;
		$is_beside["IS"]["LO"] = true;
		$is_beside["IS"]["KB"] = true;
		$is_beside["IS"]["MK"] = true;
		$is_beside["IS"]["QM"] = true;
		$is_beside["IS"]["SM"] = true;
		$is_beside["IS"]["KJ"] = true;
		$is_beside["IS"]["YA"] = true;
		$is_beside["IS"]["FA"] = true;

		$is_beside["AL"]["TE"] = true;
		$is_beside["AL"]["MK"] = true;
		$is_beside["AL"]["QZ"] = true;
		$is_beside["AL"]["MN"] = true;

		$is_beside["IL"]["BK"] = true;
		$is_beside["IL"]["LO"] = true;
		$is_beside["IL"]["KZ"] = true;

		$is_beside["BU"]["KB"] = true;
		$is_beside["BU"]["KZ"] = true;
		$is_beside["BU"]["FA"] = true;
		$is_beside["BU"]["HG"] = true;

		$is_beside["TE"]["AL"] = true;
		$is_beside["TE"]["MK"] = true;
		$is_beside["TE"]["QM"] = true;
		$is_beside["TE"]["MN"] = true;
		$is_beside["TE"]["SM"] = true;

		$is_beside["CM"]["KB"] = true;
		$is_beside["CM"]["KZ"] = true;
		$is_beside["CM"]["LO"] = true;
		$is_beside["CM"]["IS"] = true;

		$is_beside["KJ"]["SB"] = true;
		$is_beside["KJ"]["KE"] = true;
		$is_beside["KJ"]["YA"] = true;
		$is_beside["KJ"]["IS"] = true;
		$is_beside["KJ"]["SM"] = true;
		$is_beside["KJ"]["KV"] = true;

		$is_beside["KV"]["KJ"] = true;
		$is_beside["KV"]["KS"] = true;
		$is_beside["KV"]["SM"] = true;

		$is_beside["KS"]["KV"] = true;
		$is_beside["KS"]["GO"] = true;
		$is_beside["KS"]["SM"] = true;

		$is_beside["KZ"]["IL"] = true;
		$is_beside["KZ"]["BU"] = true;
		$is_beside["KZ"]["LO"] = true;
		$is_beside["KZ"]["KB"] = true;
		$is_beside["KZ"]["CM"] = true;

		$is_beside["ZA"]["GI"] = true;
		$is_beside["ZA"]["AR"] = true;
		$is_beside["ZA"]["AE"] = true;
		$is_beside["ZA"]["AW"] = true;
		$is_beside["ZA"]["KD"] = true;
		$is_beside["ZA"]["HD"] = true;
		$is_beside["ZA"]["QZ"] = true;

		$is_beside["SM"]["MN"] = true;
		$is_beside["SM"]["TE"] = true;
		$is_beside["SM"]["QM"] = true;
		$is_beside["SM"]["IS"] = true;
		$is_beside["SM"]["KS"] = true;
		$is_beside["SM"]["KV"] = true;
		$is_beside["SM"]["KJ"] = true;

		$is_beside["SB"]["KJ"] = true;
		$is_beside["SB"]["KE"] = true;
		$is_beside["SB"]["HG"] = true;

		$is_beside["FA"]["IS"] = true;
		$is_beside["FA"]["YA"] = true;
		$is_beside["FA"]["BU"] = true;
		$is_beside["FA"]["HG"] = true;
		$is_beside["FA"]["KB"] = true;
		$is_beside["FA"]["KE"] = true;

		$is_beside["QZ"]["ZA"] = true;
		$is_beside["QZ"]["HD"] = true;
		$is_beside["QZ"]["MK"] = true;
		$is_beside["QZ"]["AL"] = true;
		$is_beside["QZ"]["MN"] = true;
		$is_beside["QZ"]["GI"] = true;

		$is_beside["QM"]["TE"] = true;
		$is_beside["QM"]["MK"] = true;
		$is_beside["QM"]["SM"] = true;
		$is_beside["QM"]["IS"] = true;

		$is_beside["KD"]["AW"] = true;
		$is_beside["KD"]["BK"] = true;
		$is_beside["KD"]["HD"] = true;
		$is_beside["KD"]["ZA"] = true;

		$is_beside["KE"]["YA"] = true;
		$is_beside["KE"]["FA"] = true;
		$is_beside["KE"]["HG"] = true;
		$is_beside["KE"]["SB"] = true;
		$is_beside["KE"]["KJ"] = true;

		$is_beside["BK"]["KD"] = true;
		$is_beside["BK"]["HD"] = true;
		$is_beside["BK"]["LO"] = true;
		$is_beside["BK"]["IL"] = true;

		$is_beside["KB"]["CM"] = true;
		$is_beside["KB"]["KZ"] = true;
		$is_beside["KB"]["BU"] = true;
		$is_beside["KB"]["FA"] = true;
		$is_beside["KB"]["IS"] = true;

		$is_beside["GO"]["MN"] = true;
		$is_beside["GO"]["KS"] = true;
		$is_beside["GO"]["SM"] = true;

		$is_beside["GI"]["MN"] = true;
		$is_beside["GI"]["AR"] = true;
		$is_beside["GI"]["ZA"] = true;
		$is_beside["GI"]["QZ"] = true;

		$is_beside["LO"]["IL"] = true;
		$is_beside["LO"]["BK"] = true;
		$is_beside["LO"]["HD"] = true;
		$is_beside["LO"]["MK"] = true;
		$is_beside["LO"]["IS"] = true;
		$is_beside["LO"]["CM"] = true;
		$is_beside["LO"]["KZ"] = true;

		$is_beside["MN"]["GO"] = true;
		$is_beside["MN"]["SM"] = true;
		$is_beside["MN"]["TE"] = true;
		$is_beside["MN"]["AL"] = true;
		$is_beside["MN"]["IS"] = true;
		$is_beside["MN"]["QZ"] = true;
		$is_beside["MN"]["GI"] = true;

		$is_beside["MK"]["IS"] = true;
		$is_beside["MK"]["QM"] = true;
		$is_beside["MK"]["TE"] = true;
		$is_beside["MK"]["AL"] = true;
		$is_beside["MK"]["LO"] = true;
		$is_beside["MK"]["QZ"] = true;
		$is_beside["MK"]["HD"] = true;

		$is_beside["HG"]["BU"] = true;
		$is_beside["HG"]["FA"] = true;
		$is_beside["HG"]["KE"] = true;
		$is_beside["HG"]["SB"] = true;

		$is_beside["HD"]["BK"] = true;
		$is_beside["HD"]["LO"] = true;
		$is_beside["HD"]["KD"] = true;
		$is_beside["HD"]["MK"] = true;
		$is_beside["HD"]["QZ"] = true;
		$is_beside["HD"]["ZA"] = true;

		$is_beside["YA"]["FA"] = true;
		$is_beside["YA"]["KE"] = true;
		$is_beside["YA"]["KJ"] = true;

		$source      = get_term( $source, 'state_city' );
		$destination = get_term( $destination, 'state_city' );

		if ( is_wp_error( $source ) || is_wp_error( $destination ) ) {
			return false;
		}

		$source      = $source->slug ?? null;
		$destination = $destination->slug ?? null;

		return isset( $is_beside[ strtoupper( $source ) ][ strtoupper( $destination ) ] ) && $is_beside[ strtoupper( $source ) ][ strtoupper( $destination ) ] === true ? 'beside' : 'out';
	}

	public function convert_currency( $price ) {

		switch ( get_woocommerce_currency() ) {
			case 'IRT':
				$price /= 10;
				break;
			case 'IRHR':
				$price /= 1000;
				break;
			case 'IRHT':
				$price /= 10000;
				break;
		}

		return ceil( $price );
	}

	public function get_term_options( $term_id ) {

		$term_option = [
			'tipax_on'      => 0,
			'tipax_cost'    => null,
			'courier_on'    => 0,
			'courier_cost'  => null,
			'custom_cost'   => null,
			'forehand_cost' => null
		];

		$terms_meta = get_option( "sabira_taxonomy_" . absint( $term_id ), [] );

		return wp_parse_args( $terms_meta, $term_option );
	}

	public function get_terms_option( $term_id ) {

		$options = [];

		if ( absint( $term_id ) == 0 ) {
			return false;
		}

		$term = get_term( $term_id, 'state_city' );

		if ( is_wp_error( $term ) ) {
			return false;
		}

		$options[] = PWS()->get_term_options( $term_id ) + (array) $term;

		foreach ( get_ancestors( $term_id, 'state_city' ) as $term_id ) {
			$options[] = $this->get_term_options( $term_id ) + (array) get_term( $term_id, 'state_city' );
		}

		return $options;
	}

	public function get_options( $option_name, $default = null ) {

		if ( ! function_exists( 'PW' ) ) {
			return $default;
		}

		return PW()->get_options( $option_name, $default );
	}

	public function log( ...$params ) {
		$log = '';

		foreach ( $params as $message ) {
			if ( is_array( $message ) || is_object( $message ) ) {
				$log .= date( '[r] ' ) . print_r( $message, true ) . "\n";
			} elseif ( is_bool( $message ) ) {
				$log .= date( '[r] ' ) . ( $message ? 'true' : 'false' ) . "\n";
			} else {
				$log .= date( '[r] ' ) . $message . "\n";
			}
		}

		file_put_contents( WP_CONTENT_DIR . '/pws.log', $log, FILE_APPEND );
	}

	public static function pws_sort_state( $a, $b ) {

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
