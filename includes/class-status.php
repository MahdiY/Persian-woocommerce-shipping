<?php
/**
 * Developer : MahdiY
 * Web Site  : MahdiY.IR
 * E-Mail    : M@hdiY.IR
 */

use Automattic\WooCommerce\Utilities\OrderUtil;

defined( 'ABSPATH' ) || exit;

class PWS_Status {

	public static array $status = [
		2 => 'wc-pws-ready-to-ship',

		1 => 'wc-pws-packaged',

		10  => 'wc-pws-returned',
		11  => 'wc-pws-returned',
		83  => 'wc-pws-returned',
		102 => 'wc-pws-returned',

		7  => 'wc-completed',
		70 => 'wc-completed',
		71 => 'wc-completed',
		72 => 'wc-completed',

		80 => 'wc-pws-deleted',

		5  => 'wc-pws-shipping',
		13 => 'wc-pws-shipping',
		14 => 'wc-pws-shipping',
		15 => 'wc-pws-shipping',
		16 => 'wc-pws-shipping',
		17 => 'wc-pws-shipping',
		50 => 'wc-pws-shipping',

		3  => 'wc-pws-need-review',
		4  => 'wc-pws-need-review',
		6  => 'wc-pws-need-review',
		8  => 'wc-pws-need-review',
		9  => 'wc-pws-need-review',
		12 => 'wc-pws-need-review',
		81 => 'wc-pws-need-review',
		82 => 'wc-pws-need-review',
		18 => 'wc-pws-need-review',
	];

	public function __construct() {
		add_action( 'init', [ $this, 'register_order_statuses' ] );
		add_filter( 'wc_order_statuses', [ $this, 'add_order_statuses' ], 10, 1 );
		add_filter( 'woocommerce_reports_order_statuses', [ $this, 'reports_statuses' ], 10, 1 );
		add_filter( 'woocommerce_order_is_paid_statuses', [ $this, 'paid_statuses' ], 10, 1 );
		add_filter( 'bulk_actions-edit-shop_order', [ $this, 'bulk_actions' ], 20, 1 );
		add_filter( 'bulk_actions-woocommerce_page_wc-orders', [ $this, 'bulk_actions' ], 20, 1 );

		if ( function_exists( 'is_plugin_active' ) && is_plugin_active( 'persian-woocommerce-shipping-dokan/pws-dokan.php' ) ) {
			return;
		}

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

		if ( PWS_Tapin::is_enable() ) {
			add_action( 'add_meta_boxes', [ $this, 'order_meta_box' ] );
			add_action( 'save_post', [ $this, 'save_order_meta_box' ], 1000, 3 );
			add_action( 'manage_posts_extra_tablenav', [ $this, 'top_order_list' ], 20, 1 );
			add_action( 'woocommerce_order_list_table_extra_tablenav', [ $this, 'top_order_list' ], 20, 1 );
			add_action( 'wp_ajax_pws_change_order_status', [ $this, 'change_status_callback' ] );
			add_filter( 'cron_schedules', [ $this, 'cron_schedules' ] );
			add_action( 'wp', [ $this, 'check_status_scheduled' ] );
			add_action( 'pws_check_status', [ $this, 'check_status_callback' ] );
			add_action( 'woocommerce_orders_table_query_clauses', [ $this, 'orders_query_order_by_rand' ], 10, 3 );
		}
	}

	public static function get_statues(): array {

		$statuses = [];

		if ( PWS()->get_option( 'tools.status_enable', 1 ) == 1 ) {

			$statuses['wc-pws-in-stock'] = __( 'ارسال شده به انبار' );
			$statuses['wc-pws-packaged'] = __( 'بسته بندی شده' );
			$statuses['wc-pws-courier']  = __( 'تحویل پیک' );
			$statuses['wc-pws-post']     = __( 'تحویل پست' );
			$statuses['wc-pws-tipax']    = __( 'تحویل تیپاکس' );

		}

		if ( PWS_Tapin::is_enable() ) {
			$statuses['wc-pws-packaged']      = __( 'بسته بندی شده' );
			$statuses['wc-pws-ready-to-ship'] = __( 'آماده به ارسال' );
			$statuses['wc-pws-returned']      = __( 'برگشتی' );
			$statuses['wc-pws-deleted']       = __( 'حذف شده' );
			$statuses['wc-pws-shipping']      = __( 'در حال ارسال' );
			$statuses['wc-pws-need-review']   = __( 'نیازمند بررسی' );
		}

		return apply_filters( 'pws_get_order_statuses', $statuses );
	}

	public function register_order_statuses() {

		foreach ( $this->get_statues() as $status => $label ) {
			register_post_status( $status, [
				'label'                     => $label,
				'public'                    => false,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( $label . ' <span class="count">(%s)</span>', $label . ' <span class="count">(%s)</span>' ),
			] );
		}

	}

	public function add_order_statuses( $order_statuses ): array {
		$new_order_statuses = [];

		foreach ( $order_statuses as $key => $status ) {
			$new_order_statuses[ $key ] = $status;

			if ( 'wc-processing' === $key ) {

				foreach ( $this->get_statues() as $status => $label ) {
					$new_order_statuses[ $status ] = $label;
				}

			}
		}

		return $new_order_statuses;
	}

	public function reports_statuses( $order_status ) {

		if ( ! is_array( $order_status ) ) {
			return $order_status;
		}

		$dont_report = [
			'wc-pws-returned',
			'wc-pws-deleted',
		];

		foreach ( $this->get_statues() as $status => $label ) {
			if ( ! in_array( $status, $dont_report ) ) {
				$order_status[] = str_replace( 'wc-', '', $status );
			}
		}

		return $order_status;
	}

	public function paid_statuses( $order_status ) {

		$dont_paid = [
			'wc-pws-returned',
			'wc-pws-deleted',
		];

		foreach ( $this->get_statues() as $status => $label ) {
			if ( ! in_array( $status, $dont_paid ) ) {
				$order_status[] = str_replace( 'wc-', '', $status );
			}
		}

		return $order_status;
	}

	public function bulk_actions( $actions ) {

		foreach ( $this->get_statues() as $status => $label ) {
			$key                       = str_replace( 'wc-', '', $status );
			$actions[ 'mark_' . $key ] = 'تغییر وضعیت به ' . $label;
		}

		return $actions;
	}

	public function enqueue_scripts() {

		wp_enqueue_style( 'pws_order_status', PWS_URL . 'assets/css/status.css' );

		if ( ! PWS_Tapin::is_enable() ) {
			return;
		}

		$screen = get_current_screen();

		if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {

			$page = $_GET['page'] ?? '';

			if ( $page != 'wc-orders' ) {
				return;
			}

			$action = $_GET['action'] ?? '';

			if ( in_array( $action, [ 'new', 'edit' ] ) ) {
				wp_enqueue_script( 'pws_tapin_order', PWS_URL . 'assets/js/tapin-order.js' );
				wp_localize_script( 'pws_tapin_order', 'pws_tapin', [
					'order_id' => intval( $_GET['id'] ?? 0 ),
					'nonce'    => wp_create_nonce( 'pws_change_status' ),
				] );
			} else {
				wp_enqueue_script( 'pws_tapin_list', PWS_URL . 'assets/js/tapin-list.js' );
				wp_localize_script( 'pws_tapin_list', 'pws_tapin', [
					'order_field'  => 'id',
					'status_field' => 'tr#order-',
					'nonce'        => wp_create_nonce( 'pws_change_status' ),
				] );
			}

		} else {

			if ( $screen->id == 'shop_order' ) {
				wp_enqueue_script( 'pws_tapin_order', PWS_URL . 'assets/js/tapin-order.js' );
				wp_localize_script( 'pws_tapin_order', 'pws_tapin', [
					'order_id' => intval( $_GET['post'] ),
					'nonce'    => wp_create_nonce( 'pws_change_status' ),
				] );
			}

			if ( $screen->id == 'edit-shop_order' ) {
				wp_enqueue_script( 'pws_tapin_list', PWS_URL . 'assets/js/tapin-list.js' );
				wp_localize_script( 'pws_tapin_list', 'pws_tapin', [
					'order_field'  => 'post',
					'status_field' => 'tr#post-',
					'nonce'        => wp_create_nonce( 'pws_change_status' ),
				] );
			}

		}
	}

	public function top_order_list( $which ) {

		if ( ! in_array( get_current_screen()->id, [
			'edit-shop_order',
			wc_get_page_screen_id( 'shop-order' ),
		] ) ) {
			return;
		}

		if ( ! in_array( $which, [
			'top',
			'shop_order',
		] ) ) {
			return;
		}

		?>
		<div class="alignleft actions custom">
			<button type="button" id="pws-tapin-submit" class="button-primary"
			        title="جهت ثبت سفارشات انتخاب شده در پنل تاپین و دریافت بارکد پستی، کلیک کنید.">ثبت در تاپین
			</button>
			<?php
			if ( PWS()->get_option( 'tapin.register_type', 1 ) == 1 ) {
				?>
				<button type="button" id="pws-tapin-ship" class="button-primary"
				        title="پس از ثبت سفارش در پنل، جهت اعلام به پست برای جمع آوری بسته اینجا کلیک کنید.">آماده ارسال
				</button>
				<?php
			} ?>
		</div>
		<?php
	}

	public function order_meta_box() {
		add_meta_box( 'tapin_order', 'تاپین', [
			$this,
			'order_meta_box_callback',
		], [
			'shop_order',
			wc_get_page_screen_id( 'shop-order' ),
		], 'side' );
	}

	/**
	 * @param WC_Order|WP_Post $post_or_order_object
	 *
	 * @return void
	 */
	public function order_meta_box_callback( $post_or_order_object ) {

		$order = ( $post_or_order_object instanceof WP_Post ) ? wc_get_order( $post_or_order_object->ID ) : $post_or_order_object;

		$order_uuid = $order->get_meta( 'tapin_order_uuid' );

		$tapin_weight    = PWS_Order::get_weight( $order );
		$content_type    = PWS_Order::get_content_type( $order );
		$box_size        = PWS_Order::get_box_size( $order );
		$shipping_method = PWS_Order::get_shipping_method( $order )

		?>

		<?php if ( empty( $order_uuid ) ) { ?>

			<p class="form-field-wide">
				<label for="tapin_weight">وزن سفارش:</label>
				<input type="number" name="tapin_weight" id="tapin_weight" style="width: 100%"
				       value="<?php echo intval( $tapin_weight ); ?>">
			</p>

			<p class="form-field-wide">
				<label>نوع مرسوله:</label>
				<select style="width: 100%" name="tapin_content_type" id="tapin_content_type">
					<option value="1" <?php selected( 1, $content_type ); ?>>عادی</option>
					<option value="2" <?php selected( 2, $content_type ); ?>>شکستنی</option>
					<option value="3" <?php selected( 3, $content_type ); ?>>مایعات</option>
				</select>
			</p>

			<p class="form-field-wide">
				<label>حجم مرسوله:</label>
				<select style="width: 100%" name="tapin_box_size" id="tapin_box_size">
					<?php

					foreach ( PWS_Tapin::box_sizes() as $value => $label ) {
						printf( '<option value="%d" %s>%s</option>',
							$value,
							selected( $value, $box_size, false ),
							$label
						);
					}

					?>
				</select>
			</p>

			<button type="button" id="pws-tapin-submit" class="button-primary"
			        title="جهت ثبت سفارشات انتخاب شده در پنل تاپین و دریافت بارکد پستی، کلیک کنید.">ثبت در تاپین
			</button>
		<?php } else { ?>

			<p class="form-field-wide">
				<label>وزن سفارش:</label>
				<input type="number" style="width: 100%"
				       value="<?php echo intval( $tapin_weight ); ?>" disabled="disabled">
			</p>

			<p class="form-field-wide">
				<label>نوع ارسال:</label>
				<select style="width: 100%" disabled="disabled">
					<option value="" <?php selected( null, $shipping_method ); ?>>غیرپستی</option>
					<option value="0" <?php selected( 0, $shipping_method ); ?>>پست سفارشی</option>
					<option value="1" <?php selected( 1, $shipping_method ); ?>>پست پیشتاز</option>
					<option value="3" <?php selected( 3, $shipping_method ); ?>>پست ویژه</option>
					<option value="tipax" <?php selected( 'tipax', $shipping_method ); ?>>تیپاکس</option>
					<option value="alonomic" <?php selected( 'alonomic', $shipping_method ); ?>>الونومیک</option>
				</select>
			</p>

			<p class="form-field-wide">
				<label>نوع مرسوله:</label>
				<select style="width: 100%" disabled="disabled">
					<option value="1" <?php selected( 1, $content_type ); ?>>عادی</option>
					<option value="2" <?php selected( 2, $content_type ); ?>>شکستنی</option>
					<option value="3" <?php selected( 3, $content_type ); ?>>مایعات</option>
				</select>
			</p>

			<?php
			if ( PWS()->get_option( 'tapin.register_type', 1 ) == 1 ) {
				?>
				<button type="button" id="pws-tapin-ship" class="button-primary"
				        title="پس از ثبت سفارش در پنل، جهت اعلام به پست برای جمع آوری بسته اینجا کلیک کنید.">آماده ارسال
				</button>
				<?php
			}
		}

		?>
		<div class="pws-tips" style="margin-top: 15px;"></div>
		<?php
	}

	public function save_order_meta_box( $order_id, $post, $update ) {

		$order = wc_get_order( $order_id );

		if ( is_bool( $order ) ) {
			return;
		}

		$order_uuid = $order->get_meta( 'tapin_order_uuid' );

		if ( ! empty( $order_uuid ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( get_post_status( $order_id ) === 'auto-draft' ) {
			return;
		}

		if ( ! isset( $_POST['tapin_weight'], $_POST['tapin_content_type'], $_POST['tapin_box_size'] ) ) {
			return;
		}

		$order->update_meta_data( 'tapin_weight', floatval( $_POST['tapin_weight'] ) );
		$order->update_meta_data( 'tapin_content_type', intval( $_POST['tapin_content_type'] ) );
		$order->update_meta_data( 'tapin_box_size', intval( $_POST['tapin_box_size'] ) );
		$order->save_meta_data();
	}

	public function change_status_callback() {

		check_ajax_referer( 'pws_change_status' );

		if ( ! current_user_can( 'edit_shop_orders' ) ) {
			wp_die( - 1 );
		}

		$status = sanitize_text_field( $_POST['status'] ?? null );

		if ( ! wc_is_order_status( 'wc-' . $status ) ) {

			echo json_encode( [
				'success' => false,
				'message' => 'وضعیت انتخاب شده معتبر نمی باشد.',
			] );

			die();
		}

		$order_id = intval( $_POST['id'] ?? null );

		if ( empty( $order_id ) ) {

			echo json_encode( [
				'success' => false,
				'message' => 'سفارش انتخاب شده معتبر نمی باشد.',
			] );

			die();
		}

		/** @var WC_Order $order */
		$order = wc_get_order( $order_id );

		if ( is_bool( $order ) ) {

			echo json_encode( [
				'success' => false,
				'message' => 'سفارش انتخاب شده وجود ندارد.',
			] );

			die();
		}

		if ( isset( $_POST['weight'] ) ) {
			$order->add_meta_data( 'tapin_weight', floatval( $_POST['weight'] ), true );
		}

		if ( isset( $_POST['content_type'] ) ) {
			$order->add_meta_data( 'tapin_content_type', intval( $_POST['content_type'] ), true );
		}

		if ( isset( $_POST['box_size'] ) ) {
			$order->add_meta_data( 'tapin_box_size', intval( $_POST['box_size'] ), true );
		}

		$order->save_meta_data();

		$tapin_post_type = PWS_Order::get_shipping_method( $order );

		if ( is_null( $tapin_post_type ) ) {

			echo json_encode( [
				'success' => false,
				'message' => 'روش ارسال این سفارش تاپین نیست.',
			] );

			die();
		}

		do_action( 'pws_tapin_pre_submit_order', $order );

		$tapin_order_uuid = $order->get_meta( 'tapin_order_uuid' );

		if ( $status == 'pws-packaged' ) { // Submit & get post barcode

			if ( ! empty( $tapin_order_uuid ) ) {

				echo json_encode( [
					'success' => false,
					'message' => 'این سفارش قبلا در پنل ثبت شده است.',
				] );

				die();
			}

			$products           = PWS_Order::tapin_post_products( $order, PWS()->get_option( 'tapin.product_title' ) );
			$order_weight       = PWS_Order::get_weight( $order );
			$tapin_content_type = PWS_Order::get_content_type( $order );
			$tapin_box_size     = PWS_Order::get_box_size( $order );

			$tapin_pay_type = PWS_Order::get_shipping_payment_type( $order ) == 'postpaid' ? 2 : 1;

			$tapin_packet_type = 2;

			if ( in_array( $tapin_box_size, [ 11, 12, 13 ] ) ) {
				$tapin_packet_type = 3;

				if ( $order_weight > 2_000 ) {

					echo json_encode( [
						'success' => false,
						'message' => 'وزن مرسوله‌های پاکت A4، A5 و A3 باید کمتر از ۲۰۰۰ گرم باشد.',
					] );

					die();
				}
			}

			if ( $order->get_payment_method() == 'cod' ) {
				$tapin_pay_type = 3;

				if ( $order->get_shipping_total() ) {

					$products['shipping'] = [
						'count'      => 1,
						'discount'   => 0,
						'price'      => PWS()->convert_currency_to_IRR( $order->get_shipping_total() ),
						'title'      => __( 'هزینه ارسال + بسته بندی' ),
						'weight'     => 0,
						'product_id' => null,
					];

				}

			}

			if ( wc_ship_to_billing_address_only() ) {
				$address       = $order->get_billing_address_1() . ' ' . $order->get_billing_address_2();
				$city_code     = $order->get_meta( '_billing_city_id' );
				$province_code = $order->get_meta( '_billing_state_id' );
				$first_name    = $order->get_billing_first_name();
				$last_name     = $order->get_billing_last_name();
				$postcode      = $order->get_billing_postcode();
			} else {
				$address       = $order->get_shipping_address_1() . ' ' . $order->get_shipping_address_2();
				$city_code     = $order->get_meta( '_shipping_city_id' );
				$province_code = $order->get_meta( '_shipping_state_id' );
				$first_name    = $order->get_shipping_first_name();
				$last_name     = $order->get_shipping_last_name();
				$postcode      = $order->get_shipping_postcode();
			}

			$data = apply_filters( 'pws_tapin_submit_order', [
				'register_type'  => PWS()->get_option( 'tapin.register_type', 1 ),
				'shop_id'        => PWS()->get_option( 'tapin.shop_id' ),
				'address'        => $address,
				'city_code'      => $city_code,
				'province_code'  => $province_code,
				'description'    => empty( $order->get_customer_note() ) ? null : $order->get_customer_note(),
				'email'          => null,
				'employee_code'  => '-1',
				'first_name'     => $first_name,
				'last_name'      => $last_name,
				'mobile'         => str_replace( '+98', '0', $order->get_billing_phone() ),
				'phone'          => null,
				'postal_code'    => $postcode,
				'pay_type'       => $tapin_pay_type,
				'order_type'     => $tapin_post_type,
				'packet_type'    => $tapin_packet_type,
				'content_type'   => $tapin_content_type,
				'box_id'         => $tapin_box_size,
				'package_weight' => $order_weight,
				'products'       => $products,
				'manual_id'      => $order_id,
			], $order );

			$data['presenter_code'] = 1025;
			$data['products']       = array_values( $data['products'] );

			PWS_Tapin::set_gateway( PWS()->get_option( 'tapin.gateway' ) );

			$response = PWS_Tapin::request( 'v2/public/order/post/register', $data );

			if ( is_wp_error( $response ) ) {

				echo json_encode( [
					'success' => false,
					'message' => implode( '<br>', $response->get_error_messages() ),
				] );

				die();

			} else if ( ! in_array( $response->returns->status, [ 200, 770 ] ) ) {

				PWS()->log( __METHOD__ . ' Line: ' . __LINE__ );
				PWS()->log( $data );
				PWS()->log( $response );

				$errors = [];

				foreach ( (array) $response->entries as $key => $message ) {
					if ( is_string( $message[0] ) ) {
						$errors[] = "{$key} > {$message[0]}";
					}
				}

				echo json_encode( [
					'success' => false,
					'message' => $response->returns->message . '<br>' . implode( '<br>', $errors ),
				] );

				die();
			}

			if ( empty( $response->entries->barcode ) ) {
				echo json_encode( [
					'success' => false,
					'message' => 'بارکد صادر نشد، لطفا مجددا تلاش کنید.',
				] );

				die();
			}

			$order->update_meta_data( 'tapin_order_uuid', $response->entries->id );
			$order->update_meta_data( 'tapin_order_id', $response->entries->order_id );
			$order->update_meta_data( 'tapin_send_price', $response->entries->send_price );
			$order->update_meta_data( 'tapin_send_price_tax', $response->entries->send_price_tax );
			$order->update_meta_data( 'tapin_send_time', time() );
			$order->update_meta_data( 'tapin_weight', $order_weight );
			$order->update_meta_data( 'tapin_content_type', $tapin_content_type );
			$order->update_meta_data( 'tapin_box_size', $tapin_box_size );
			$order->update_meta_data( 'post_barcode', $response->entries->barcode );

			$note = "بارکد پستی مرسوله شما: {$response->entries->barcode}
                        می توانید مرسوله خود را از طریق لینک https://radgir.net رهگیری نمایید.";

			$order->set_status( $status, 'تاپین -' );
			$order->save();

			$order->add_order_note( $note, 1 );

			do_action( 'pws_save_order_post_barcode', $order, $response->entries->barcode );

			echo json_encode( [
				'success' => true,
				'message' => 'بسته بندی شده',
			] );

			die();

		} else if ( $status == 'pws-ready-to-ship' ) {

			if ( empty( $tapin_order_uuid ) ) {

				echo json_encode( [
					'success' => false,
					'message' => 'سفارش در تاپین ثبت نشده است.',
				] );

				die();
			}

			$tapin_order_id = $order->get_meta( 'tapin_order_id' );

			$data = [
				'shop_id'  => PWS()->get_option( 'tapin.shop_id' ),
				'order_id' => $tapin_order_id,
				'status'   => 2,
			];

			PWS_Tapin::set_gateway( PWS()->get_option( 'tapin.gateway' ) );

			$response = PWS_Tapin::request( 'v2/public/order/post/change-status', $data );

			if ( is_wp_error( $response ) ) {

				echo json_encode( [
					'success' => false,
					'message' => implode( '<br>', $response->get_error_messages() ),
				] );

				die();

			} else if ( $response->returns->status != 200 ) {

				PWS()->log( __METHOD__ . ' Line: ' . __LINE__ );
				PWS()->log( $data );
				PWS()->log( $response );

				$errors = [];

				foreach ( (array) $response->entries as $key => $message ) {
					if ( is_string( $message[0] ) ) {
						$errors[] = "{$key} > {$message[0]}";
					}
				}

				echo json_encode( [
					'success' => false,
					'message' => $response->returns->message . '<br>' . implode( '<br>', $errors ),
				] );

				die();
			}

			$order->set_status( $status, 'تاپین -' );
			$order->save();

			echo json_encode( [
				'success' => true,
				'message' => 'آماده به ارسال',
			] );

			die();

		} else {

			echo json_encode( [
				'success' => false,
				'message' => "ابتدا باید به 'بسته بندی شده' تغییر وضعیت دهید.",
			] );

			die();

		}

	}

	public function cron_schedules( array $schedules ): array {
		$schedules['per_quarter'] = [
			'interval' => 15 * MINUTE_IN_SECONDS,
			'display'  => __( 'A Quarter' ),
		];

		return $schedules;
	}

	public function check_status_scheduled() {
		if ( ! wp_next_scheduled( 'pws_check_status' ) ) {
			wp_schedule_event( time(), 'per_quarter', 'pws_check_status' );
		}
	}

	public static function check_status_callback() {

		$args = apply_filters( 'pws_tapin_check_status_orders', [
			'type'       => [ 'shop_order' ],
			'status'     => [
				'wc-pws-packaged',
				'wc-pws-ready-to-ship',
				'wc-pws-shipping',
				'wc-pws-deleted',
				'wc-pws-need-review',
			],
			'limit'      => 100,
			'orderby'    => 'rand',
			'pwsorderby' => 'rand', // Remove after OrdersTableQuery::sanitize_order_orderby mapping completed
			'meta_query' => [
				[
					'key' => 'tapin_order_uuid',
				],
			],
		] );

		$orders = wc_get_orders( $args );

		$orders = array_map( function ( WC_Order $order ) {
			return [
				'order_uuid' => $order->get_meta( 'tapin_order_uuid' ),
				'order'      => $order,
			];
		}, $orders );
		$orders = array_filter( $orders, function ( $row ) {
			return ! empty( $row['order_uuid'] );
		} );

		/** @var WC_Order[] $orders */
		$orders = array_column( $orders, 'order', 'order_uuid' );

		if ( count( $orders ) == 0 ) {
			return false;
		}

		PWS_Tapin::set_gateway( PWS()->get_option( 'tapin.gateway' ) );

		$statuses = PWS_Tapin::request( 'v2/public/order/post/get-status/bulk', [
			'shop_id' => PWS()->get_option( 'tapin.shop_id' ),
			'orders'  => array_map( function ( string $uuid ) {
				return [ 'id' => $uuid ];
			}, array_keys( $orders ) ),
		] );

		if ( is_wp_error( $statuses ) || ! isset( $statuses->entries->list ) || ! is_array( $statuses->entries->list ) ) {
			return false;
		}

		$order_statuses = array_column( $statuses->entries->list, 'status', 'id' );

		foreach ( $orders as $order_uuid => $order ) {

			if ( ! isset( $order_statuses[ $order_uuid ] ) ) {
				continue;
			}

			$status = $order_statuses[ $order_uuid ];
			$status = self::$status[ $status ] ?? null;

			if ( is_null( $status ) ) {
				continue;
			}

			if ( $order->get_status() != $status ) {
				$order->set_status( $status, 'بروزرسانی خودکار تاپین -' );
				$order->save();
			}

		}

	}

	public function orders_query_order_by_rand( $clauses, $query, $args ) {
		// Remove after OrdersTableQuery::sanitize_order_orderby mapping completed

		if ( isset( $args['pwsorderby'] ) && $args['pwsorderby'] == 'rand' ) {
			$clauses['orderby'] = 'RAND()';
		}

		return $clauses;
	}

}

new PWS_Status();
