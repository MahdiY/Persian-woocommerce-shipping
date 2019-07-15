<?php
/**
 * Developer : MahdiY
 * Web Site  : MahdiY.IR
 * E-Mail    : M@hdiY.IR
 */

class Persian_Woocommerce_Shipping {

	public $selected_city = array();

	/**
	 * Shipping methods.
	 *
	 * @var array
	 */
	public static $methods = array();

	/**
	 * The single instance of the class.
	 *
	 * @var Persian_Woocommerce_Shipping
	 */
	protected static $_instance = null;

	/**
	 * Ensures only one instance of ersian_Woocommerce_Shipping is loaded or can be loaded.
	 *
	 * @see PWS()
	 * @return Persian_Woocommerce_Shipping
	 */
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Persian_Woocommerce_Shipping constructor.
	 */
	public function __construct() {

		self::$methods = array(
			'WC_Courier_Method',
			'WC_Custom_Method',
			'WC_Forehand_Method',
			'WC_Tipax_Method',
		);

		$this->init_hooks();
	}

	/**
	 * Hook into actions and filters.
	 */
	private function init_hooks() {
		// Actions
		add_action( 'init', array( $this, 'state_city_taxonomy' ), 0 );
		add_action( 'admin_menu', array( $this, 'state_city_admin_menu' ) );
		add_action( 'woocommerce_after_order_notes', array( $this, 'load_child_term' ) );
		add_action( 'wp_ajax_sabira_load_cities', array( $this, 'sabira_load_cities_callback' ) );
		add_action( 'wp_ajax_nopriv_sabira_load_cities', array( $this, 'sabira_load_cities_callback' ) );
		add_action( 'wp_ajax_sabira_load_districts', array( $this, 'sabira_load_districts_callback' ) );
		add_action( 'wp_ajax_nopriv_sabira_load_districts', array( $this, 'sabira_load_districts_callback' ) );
		add_action( 'woocommerce_shipping_init', array( $this, 'load_shipping_init' ) );
		add_action( 'woocommerce_admin_field_pws_single_select_country', array(
			$this,
			'pws_single_select_country'
		), 10, 1 );

		// Filters
		add_filter( 'woocommerce_shipping_methods', array( $this, 'add_shipping_method' ) );
		add_filter( 'woocommerce_get_settings_general', array( $this, 'get_settings_general' ), 10, 1 );
		add_filter( 'woocommerce_states', array( $this, 'iran_states' ), 20, 1 );
		add_filter( 'manage_edit-state_city_columns', array( $this, 'edit_state_city_columns_taxonomy' ), 10, 1 );
		add_filter( 'manage_state_city_custom_column', array( $this, 'edit_state_city_rows_taxonomy' ), 10, 3 );
		add_filter( 'woocommerce_checkout_fields', array( $this, 'edit_checkout_cities_field' ), 20, 1 );
		add_filter( 'woocommerce_checkout_update_order_meta', array( $this, 'checkout_update_order_meta' ), 20, 1 );
		add_filter( 'woocommerce_checkout_process', array( $this, 'checkout_process' ), 20, 1 );
		add_filter( 'woocommerce_form_field_billing_sabira_cities', array( $this, 'checkout_cities_field' ), 11, 4 );
		add_filter( 'woocommerce_form_field_shipping_sabira_cities', array( $this, 'checkout_cities_field' ), 11, 4 );
		add_filter( 'woocommerce_form_field_billing_sabira_district', array( $this, 'checkout_cities_field' ), 11, 4 );
		add_filter( 'woocommerce_form_field_shipping_sabira_district', array( $this, 'checkout_cities_field' ), 11, 4 );
		add_filter( 'woocommerce_cart_shipping_packages', array(
			$this,
			'add_district_cart_shipping_packages'
		), 10, 1 );
		add_filter( 'woocommerce_cart_shipping_method_full_label', array(
			$this,
			'add_image_before_shipping_labels'
		), 10, 2 );
		add_filter( 'woocommerce_localisation_address_formats', array( $this, 'localisation_address_formats' ), 20, 1 );
		add_filter( 'woocommerce_order_formatted_shipping_address', array(
			$this,
			'order_formatted_shipping_address'
		), 20, 2 );
		add_filter( 'woocommerce_order_formatted_billing_address', array(
			$this,
			'order_formatted_billing_address'
		), 00, 2 );
		add_filter( 'woocommerce_formatted_address_replacements', array(
			$this,
			'formatted_address_replacements'
		), 10, 2 );
		add_filter( 'woocommerce_my_account_my_address_formatted_address', array(
			$this,
			'my_account_my_address_formatted_address'
		), 10, 3 );
		add_filter( 'persian_woo_sms_content_replace', array( $this, 'persian_woo_sms_content_replace' ), 10, 6 );
	}

	// Actions

	public function state_city_taxonomy() {

		$labels = array(
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
		);

		register_taxonomy( 'state_city', null, array(
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
		) );

		if ( function_exists( 'PW' ) && PW()->get_options( 'enable_iran_cities' ) != 'no' ) {
			$settings                       = PW()->get_options();
			$settings['enable_iran_cities'] = 'no';
			update_option( 'PW_Options', $settings );
		}

		if ( get_option( 'sabira_set_iran_cities', 0 ) ) {
			return false;
		}

		foreach ( PWS_get_states() as $key => $state ) {
			$term = wp_insert_term( $state, 'state_city', array( 'slug' => $key, 'description' => "استان $state" ) );

			if ( is_wp_error( $term ) ) {
				continue;
			}

			foreach ( PWS_get_state_city( $key ) as $city ) {
				wp_insert_term( $city, 'state_city', array(
					'parent'      => $term['term_id'],
					'description' => "$state - $city"
				) );
			}
		}

		update_option( "sabira_set_iran_cities", 1 );
	}

	public function state_city_admin_menu() {
		$title = 'شهر های حمل و نقل';

		add_submenu_page( 'woocommerce', $title, $title, 'manage_woocommerce', 'edit-tags.php?taxonomy=state_city&post_type=shop_order' );
	}

	public function load_child_term() {

		$types = $this->types();

		?>
        <script type="text/javascript">
			var sabira_ajax_url = '<?php echo admin_url( 'admin-ajax.php' ); ?>';

			jQuery(document).ready(function ( $ ) {

				<?php foreach( $types as $type ) { ?>

				function <?php echo $type; ?>_sabira_state_changed() {
					var data = {
						'action': 'sabira_load_cities',
						'state_id': $('#<?php echo $type; ?>_state').val(),
						'name': '<?php echo $type; ?>'
					};

					$.post(sabira_ajax_url, data, function ( response ) {
						if( response == "" )
							response = "<option value='0'><?php _e( 'لطفا استان خود را انتخاب کنید' ); ?><option>";
						$('select#<?php echo $type; ?>_sabira_cities').html(response);
						$('body').trigger('pws_city_loaded');
					});

					$('select#<?php echo $type; ?>_sabira_cities').select2();
					$('p#<?php echo $type; ?>_sabira_district_field').slideUp();
					$('select#<?php echo $type; ?>_sabira_district').html("");
				}

				$('body').on('change', 'select#<?php echo $type; ?>_state, input#<?php echo $type; ?>_state', function () {
					<?php echo $type; ?>_sabira_state_changed();
				});

				function <?php echo $type; ?>_sabira_city_changed() {
					var data = {
						'action': 'sabira_load_districts',
						'city_id': $('#<?php echo $type; ?>_sabira_cities').val(),
						'name': '<?php echo $type; ?>'
					};

					$.post(sabira_ajax_url, data, function ( response ) {
						if( response == "" )
							$('p#<?php echo $type; ?>_sabira_district_field').slideUp();
						else
							$('p#<?php echo $type; ?>_sabira_district_field').slideDown();

						$('select#<?php echo $type; ?>_sabira_district').html(response);
						$('body').trigger('update_checkout');
						$('body').trigger('pws_city_loaded');
					});

					$('select#<?php echo $type; ?>_sabira_district').select2();
				}

				$('body').on('change', 'select#<?php echo $type; ?>_sabira_cities, input#<?php echo $type; ?>_sabira_cities', function () {
					<?php echo $type; ?>_sabira_city_changed();
				});

				<?php echo $type; ?>_sabira_state_changed();
				<?php echo $type; ?>_sabira_city_changed();

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

	public function sabira_load_cities_callback() {

		if ( ! isset( $_POST['state_id'] ) ) {
			die();
		}

		$state_id = absint( $_POST['state_id'] );

		if ( ! $state_id ) {
			die();
		}

		$states = get_terms( array(
			'taxonomy'   => 'state_city',
			'hide_empty' => false,
			'parent'     => $state_id
		) );

		if ( is_wp_error( $states ) ) {
			die();
		}

		$term_id = 0;

		if ( is_user_logged_in() ) {
			$name    = isset( $_POST['name'] ) && $_POST['name'] == 'billing' ? 'billing' : 'shipping';
			$term_id = get_user_meta( get_current_user_id(), $name . '_city', true );
		}

		$method = isset( $_POST['name'] ) && $_POST['name'] == 'billing' ? 'set_billing_state' : 'set_shipping_state';
		WC()->customer->$method( $state_id );

		foreach ( $states as $state ) {
			printf( "<option value='%d' %s>%s</option>", $state->term_id, selected( $term_id, $state->term_id, false ), $state->name );
		}

		die();
	}

	public function sabira_load_districts_callback() {

		if ( ! isset( $_POST['city_id'] ) ) {
			die();
		}

		$city_id = absint( $_POST['city_id'] );

		if ( ! $city_id ) {
			die();
		}

		$cities = get_terms( array(
			'taxonomy'   => 'state_city',
			'hide_empty' => false,
			'child_of'   => $city_id
		) );

		if ( is_wp_error( $cities ) ) {
			die();
		}

		$term_id = 0;

		if ( is_user_logged_in() ) {
			$name    = isset( $_POST['name'] ) && $_POST['name'] == 'billing' ? 'billing' : 'shipping';
			$term_id = get_user_meta( get_current_user_id(), $name . '_district', true );
		}

		$method = isset( $_POST['name'] ) && $_POST['name'] == 'billing' ? 'set_billing_city' : 'set_shipping_city';
		WC()->customer->$method( $city_id );

		if ( count( $cities ) ) {
			$city = get_term( $city_id, 'state_city' );
			printf( "<option value='%d' %s>%s</option>", $city->term_id, selected( $term_id, $city->term_id, false ), $city->name );
		}

		foreach ( $cities as $city ) {
			printf( "<option value='%d' %s>%s</option>", $city->term_id, selected( $term_id, $city->term_id, false ), str_repeat( "- ", count( get_ancestors( $city->term_id, 'state_city' ) ) - 2 ) . $city->name );
		}

		die();
	}

	public function load_shipping_init() {
		include plugin_dir_path( __FILE__ ) . 'courier-method.php';
		include plugin_dir_path( __FILE__ ) . 'custom-method.php';
		include plugin_dir_path( __FILE__ ) . 'forehand-method.php';
		include plugin_dir_path( __FILE__ ) . 'tipax-method.php';
	}

	public function pws_single_select_country( $value ) {
		$country_setting = get_option( $value['id'] );

		if ( strstr( $country_setting, ':' ) ) {
			$country_setting = explode( ':', $country_setting );
			$country         = current( $country_setting );
			$state           = intval( end( $country_setting ) );
		} else {
			$country = $country_setting;
			$state   = '*';
		}
		?>
        <tr valign="top">
        <th scope="row" class="titledesc">
            <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
        </th>
        <td class="forminp"><select name="<?php echo esc_attr( $value['id'] ); ?>"
                                    style="<?php echo esc_attr( $value['css'] ); ?>"
                                    data-placeholder="<?php esc_attr_e( 'Choose a country&hellip;', 'woocommerce' ); ?>"
                                    aria-label="<?php esc_attr_e( 'Country', 'woocommerce' ) ?>"
                                    class="wc-enhanced-select">
				<?php WC()->countries->country_dropdown_options( $country, $state ); ?>
            </select>
        </td>
        </tr><?php
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
				$setting['type'] = 'pws_single_select_country';
			}

		}

		return $settings;
	}

	public function iran_states( $states ) {

		$_states = get_terms( array(
			'taxonomy'   => 'state_city',
			'hide_empty' => false,
			'parent'     => 0
		) );

		$states['IR'] = wp_list_pluck( $_states, 'name', 'term_id' );

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

			$class = is_array( $fields[ $type ][ $type . '_city' ]['class'] ) ? $fields[ $type ][ $type . '_city' ]['class'] : array();

			$fields[ $type ][ $type . '_postcode' ]['clear'] = false;

			$fields[ $type ][ $type . '_city' ] = array(
				'type'        => $type . '_sabira_cities',
				'label'       => 'شهر',
				'placeholder' => __( 'یک شهر انتخاب کنید' ),
				'required'    => true,
				'id'          => $type . '_sabira_cities',
				'class'       => apply_filters( 'pws_city_class', $class ),
				'default'     => 0,
				'priority'    => apply_filters( 'pws_city_priority', $fields[ $type ][ $type . '_city' ]['priority'] ),
			);

			$fields[ $type ][ $type . '_district' ] = array(
				'type'        => $type . '_sabira_district',
				'label'       => 'محله',
				'placeholder' => __( 'یک محله انتخاب کنید' ),
				'required'    => false,
				'id'          => $type . '_sabira_district',
				'class'       => apply_filters( 'pws_district_class', $class ),
				'clear'       => true,
				'default'     => 0,
				'priority'    => apply_filters( 'pws_district_priority', $fields[ $type ][ $type . '_city' ]['priority'] + 1 ),
			);

		}

		return $fields;
	}

	public function checkout_update_order_meta( $order_id ) {

		$types  = $this->types();
		$fields = array( 'state', 'city', 'district' );

		foreach ( $types as $type ) {

			foreach ( $fields as $field ) {

				$term_id = get_post_meta( $order_id, "_{$type}_{$field}", true );
				$term    = get_term( intval( $term_id ) );;

				if ( ! is_wp_error( $term ) && ! is_null( $term ) ) {
					update_post_meta( $order_id, "_{$type}_{$field}", $term->name );
					update_post_meta( $order_id, "_{$type}_{$field}_id", $term_id );
				}

			}
		}

	}

	public function checkout_process() {

		$types = $this->types();

		$fields = array(
			'state'    => 'استان',
			'city'     => 'شهر',
			'district' => 'محله',
		);

		foreach ( $types as $type ) {

			foreach ( $fields as $field => $name ) {

				if ( isset( $_POST[ $type . '_' . $field ] ) && ! empty( $_POST[ $type . '_' . $field ] ) ) {

					$term = get_term( intval( $_POST[ $type . '_' . $field ] ) );

					if ( is_wp_error( $term ) || is_null( $term ) ) {
						wc_add_notice( $name . ' انتخاب شده معتبر نمی باشد.', 'error' );
					}

				}

			}

		}
	}

	public function checkout_cities_field( $field, $key, $args, $value ) {

		$field_html = '';
		$options    = array();

		if ( $args['type'] == 'billing_sabira_cities' || $args['type'] == 'shipping_sabira_cities' ) {

			$state_cc = WC()->checkout->get_value( 'billing_city' === $key ? 'billing_state' : 'shipping_state' );

			if ( $state_cc ) {
				$options = get_terms( array(
					'taxonomy'   => 'state_city',
					'hide_empty' => false,
					'parent'     => $state_cc
				) );
			}

		} elseif ( $args['type'] == 'billing_sabira_district' || $args['type'] == 'shipping_sabira_district' ) {

			$city_cc = WC()->checkout->get_value( 'billing_district' === $key ? 'billing_city' : 'shipping_city' );

			if ( $city_cc ) {
				$options = get_terms( array(
					'taxonomy'   => 'state_city',
					'hide_empty' => false,
					'child_of'   => $city_cc
				) );
			}

		}

		if ( $args['required'] ) {
			$args['class'][] = 'validate-required';
		}

		$required = $args['required'] ? ' <abbr class="required" title="' . esc_attr__( 'required', 'woocommerce' ) . '">*</abbr>' : '';

		$custom_attributes = array();

		if ( ! empty( $value ) ) {
			$this->selected_city[ current( explode( '_', $key ) ) . '_value' ] = $value;
		}

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

		$field_container = '<p class="form-row %1$s" id="%2$s">%3$s</p>';

		if ( is_array( $options ) ) {

			if ( empty( $options ) && isset( $city_cc ) ) {
				$field_container = '<p class="form-row %1$s" id="%2$s" style="display: none">%3$s</p>';
			}

			$field .= '<select name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" class="' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" ' . implode( ' ', $custom_attributes ) . ' data-placeholder="' . esc_attr( $args['placeholder'] ) . '">
				<option value="">' . esc_attr( $args['placeholder'] ) . '&hellip;</option>';

			foreach ( $options as $option ) {
				if ( $args['type'] == 'billing_sabira_cities' || $args['type'] == 'shipping_sabira_cities' ) {
					$field .= '<option value="' . esc_attr( $option->term_id ) . '" ' . selected( $value, $option->term_id, false ) . '>' . $option->name . '</option>';
				} elseif ( $args['type'] == 'billing_sabira_district' || $args['type'] == 'shipping_sabira_district' ) {
					$field .= '<option value="' . esc_attr( $option->term_id ) . '" ' . selected( $value, $option->term_id, false ) . '>' . str_repeat( "- ", count( get_ancestors( $option->term_id, 'state_city' ) ) - 2 ) . $option->name . '</option>';
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

	public function add_district_cart_shipping_packages( $packages ) {

		if ( isset( $_POST['post_data'] ) ) {
			parse_str( $_POST['post_data'], $data );
		}

		if ( isset( $data['billing_city'] ) ) {
			$packages[0]['destination']['city'] = $data['billing_city'];
		}

		$packages[0]['destination']['district'] = isset( $data['billing_district'] ) ? $data['billing_district'] : 0;

		if ( isset( $_POST['billing_city'] ) ) {
			$packages[0]['destination']['city'] = $_POST['billing_city'];
		}

		if ( isset( $_POST['billing_district'] ) ) {
			$packages[0]['destination']['district'] = $_POST['billing_district'];
		}

		return $packages;
	}

	public function add_image_before_shipping_labels( $label, $method ) {

		$method_id = str_replace( ':', '_', $method->id );
		$option    = get_option( "woocommerce_{$method_id}_settings" );

		if ( isset( $option['img_url'] ) && ! empty( $option['img_url'] ) ) {
			return sprintf( '<img src="%s" class="%s %s" style="max-width: 100px;display: inline;"/>', $option['img_url'], $method_id, strtok( $method->id, ':' ) ) . $label;
		}

		return $label;
	}

	function localisation_address_formats( $formats ) {

		$formats['IR'] = "{company}\n{first_name} {last_name}\n{country}\n{state}\n{city}{district}\n{address_1} - {address_2}\n{postcode}";

		return $formats;
	}

	public function order_formatted_shipping_address( $data, $args ) {

		$data['district'] = get_post_meta( $args->get_id(), '_shipping_district', true );

		return $data;
	}

	public function order_formatted_billing_address( $data, $args ) {

		$data['district'] = get_post_meta( $args->get_id(), '_billing_district', true );

		return $data;
	}

	public function formatted_address_replacements( $replace, $args ) {

		if ( ctype_alnum( $replace['{state}'] ) && strlen( $replace['{state}'] ) == 2 ) {
			$state              = get_term_by( 'slug', $replace['{state}'], 'state_city' );
			$replace['{state}'] = $state == false ? $replace['{state}'] : $state->name;
		}

		if ( ctype_digit( $args['city'] ) ) {
			$city              = get_term( $args['city'] );
			$replace['{city}'] = is_wp_error( $city ) || is_null( $city ) ? $args['city'] : $city->name;
		}

		if ( ctype_digit( $args['district'] ) ) {
			$district              = get_term( $args['district'] );
			$replace['{district}'] = is_wp_error( $district ) ? '' : ( strlen( $district->name ) ? ' - ' : '' ) . $district->name;
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

	// Functions

	public static function install() {

		if ( class_exists( 'WC_Shipping_Zones' ) ) {

			foreach ( WC_Shipping_Zones::get_zones() as $zone ) {

				foreach ( $zone['shipping_methods'] as $instance_id => $method ) {

					if ( in_array( $method->id, self::$methods ) ) {
						$option_name = "woocommerce_{$method->id}_{$instance_id}_settings";
						$settings    = get_option( $option_name );

						if ( $settings != false && isset( $settings['insurance_cost'] ) ) {
							$settings['insurance_cost'] = 6500;
							update_option( $option_name, $settings );
						}
					}
				}
			}

		}

	}

	public function types() {

		$types = array( 'billing' );

		if ( get_option( 'woocommerce_ship_to_destination' ) != 'billing_only' ) {
			$types[] = 'shipping';
		}

		return $types;
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

	public function donate() {
		return '<p class="widefat">[<a href="http://sabira.ir/plugins/persian-woocommerce-shipping/" target="_blank">افزونه پست پیشتاز و سفارشی</a> به صورت رایگان توسط <a href="http://woocommerce.ir/" target="_blank">ووکامرس فارسی</a> منتشر شده است. برای حمایت مالی از این پروژه <a href="http://donate.woocommerce.ir/persian-woocommerce-shipping" target="_blank">اینجا کلیک کنید</a>]<p>';
	}

	function get_term_options( $term_id ) {

		$term_option = array(
			'tipax_on'      => 0,
			'tipax_cost'    => null,
			'courier_on'    => 0,
			'courier_cost'  => null,
			'custom_cost'   => null,
			'forehand_cost' => null
		);

		$terms_meta = get_option( "sabira_taxonomy_" . absint( $term_id ), array() );

		return wp_parse_args( $terms_meta, $term_option );
	}

	function get_terms_option( $term_id ) {

		$options = array();

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

}
