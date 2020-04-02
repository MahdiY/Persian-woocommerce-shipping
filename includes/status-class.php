<?php
/**
 * Developer : MahdiY
 * Web Site  : MahdiY.IR
 * E-Mail    : M@hdiY.IR
 */

class PWS_Status {

	public function __construct() {
		add_action( 'init', [ $this, 'register_order_statuses' ] );
		add_filter( 'wc_order_statuses', [ $this, 'add_order_statuses' ], 10, 1 );
		add_filter( 'bulk_actions-edit-shop_order', [ $this, 'bulk_actions' ], 20, 1 );
		add_action( 'admin_head', [ $this, 'status_colors' ] );
		add_action( 'admin_footer', [ $this, 'change_status' ] );
		add_action( 'in_admin_header', [ $this, 'tapin_help' ] );
		add_action( 'wp_ajax_pws_change_order_status', [ $this, 'change_status_callback' ] );
		add_action( 'wp_ajax_nopriv_pws_change_order_status', [ $this, 'change_status_callback' ] );
	}

	public function get_statues( $list = false ) {

		$statuses = [];

		if ( PW()->get_options( 'pws_status_enable' ) == 'yes' ) {

			$statuses['wc-pws-in-stock'] = 'ارسال شده به انبار';

			if ( ! PWS_Tapin::is_enable() ) {
				$statuses['wc-pws-packaged'] = 'بسته بندی شده';
			}

			$statuses['wc-pws-courier'] = 'تحویل پیک';

		}

		if ( PWS_Tapin::is_enable() && ! $list ) {
			$statuses['wc-pws-packaged']      = 'بسته بندی شده';
			$statuses['wc-pws-ready-to-ship'] = 'آماده به ارسال';
			$statuses['wc-pws-returned']      = 'برگشتی';
			$statuses['wc-pws-deleted']       = 'حذف شده';
			$statuses['wc-pws-shipping']      = 'در حال ارسال';
			$statuses['wc-pws-need-review']   = 'نیازمند بررسی';
		}

		return $statuses;
	}

	public function register_order_statuses() {

		foreach ( $this->get_statues() as $status => $label ) {
			register_post_status( $status, array(
				'label'                     => $label,
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( $label . ' <span class="count">(%s)</span>', $label . ' <span class="count">(%s)</span>' )
			) );
		}

	}

	public function add_order_statuses( $order_statuses ) {
		$new_order_statuses = [];

		$list = false;

		$screen = '';

		if ( function_exists( 'get_current_screen' ) ) {
			$screen = get_current_screen()->id ?? null;
		}

		if ( $screen == 'shop_order' ) {
			$list = true;
		}

		foreach ( $order_statuses as $key => $status ) {
			$new_order_statuses[ $key ] = $status;

			if ( 'wc-processing' === $key ) {

				foreach ( $this->get_statues( $list ) as $status => $label ) {
					$new_order_statuses[ $status ] = $label;
				}

			}
		}

		return $new_order_statuses;
	}

	public function bulk_actions( $actions ) {

		if ( PWS_Tapin::is_enable() ) {
			$actions['pws-packaged']      = 'تغییر وضعیت به بسته بندی شده';
			$actions['pws-ready-to-ship'] = 'تغییر وضعیت به آماده به ارسال';
		}

		foreach ( $this->get_statues( true ) as $status => $label ) {
			$key                       = str_replace( 'wc-', '', $status );
			$actions[ 'mark_' . $key ] = 'تغییر وضعیت به ' . $label;
		}

		return $actions;
	}

	public function status_colors() {
		echo '<style>
		    mark.status-pws-in-stock {
				background: #547F2D;
				color: #fff;
			}
		    mark.status-pws-packaged {
				background: #307079;
				color: #fff;
			}
		    mark.status-pws-ready-to-ship {
				background: #1D76DB;
				color: #fff;
			}
		    mark.status-pws-returned {
				background: rgba(182,2,4,0.74);
				color: #fff;
			}
		    mark.status-pws-deleted {
				background: #B60204;
				color: #fff;
			}
		    mark.status-pws-shipping {
				background: #0052CC;
				color: #fff;
			}
		    mark.status-pws-need-review {
				background: #FBC904;
				color: #fff;
			}
		    mark.status-pws-courier {
				background: #ec2b2ba1;
				color: #681818;
			}
		  </style>';
	}

	public function change_status() {

		$screen = get_current_screen();

		if ( $screen->id == 'edit-shop_order' ) {
			$this->change_status_list();
		}

		if ( $screen->id == 'shop_order' ) {
			$this->change_status_order();
		}

	}

	private function change_status_list() {
		?>
        <script>
			jQuery(document).ready(function ( $ ) {

				let pws_IDs = [];
				let pws_button_top = $(".bulkactions #doaction");
				let pws_button_bottom = $(".bulkactions #doaction2");

				pws_button_top.click(function () {
					let status = $("#bulk-action-selector-top").val();

					if( status.indexOf('pws-') == 0 ) {
						pws_change_status(status);
						return false;
					}
				});

				pws_button_bottom.click(function () {
					let status = $("#bulk-action-selector-bottom").val();

					if( status.indexOf('pws-') == 0 ) {
						pws_change_status(status);
						return false;
					}
				});

				function pws_change_status( status ) {

					pws_IDs = [];

					$('.check-column input[name="post[]"]:checked').each(function () {
						pws_IDs.push($(this).val());
					});

					if( pws_IDs.length === 0 ) {
						alert('سفارشی جهت پردازش انتخاب نشده است.');
						return false;
					}

					// Start
					pws_button_top.attr('disabled', 'disabled');
					pws_button_bottom.attr('disabled', 'disabled');
					$('.pws-tips').remove();

					pws_change_status_ajax(status);
				}

				function pws_change_status_ajax( status ) {

					let id = pws_IDs.shift();

					if( id == undefined ) {
						// End
						pws_button_top.removeAttr('disabled');
						pws_button_bottom.removeAttr('disabled');
						return true;
					}

					let data = {
						'action': 'pws_change_order_status',
						'status': status,
						'id': id
					};

					$("tr#post-" + id + " td.order_status").html(`
                        <mark class="order-status">
                            <span>...</span>
                        </mark>
                    `);

					$.post(ajaxurl, data).then(function ( response ) {

						response = JSON.parse(response);

						if( response.success ) {

							$("tr#post-" + id + " td.order_status").html(`
                                <mark class="order-status status-processing">
                                    <span>${response.message}</span>
                                </mark>
                            `);

						} else {

							$("tr#post-" + id + " td.order_status").html(`
                                <mark class="order-status status-pws-returned">
                                    <span>خطا در پردازش</span>
                                </mark>
                            `);

							$("tr#post-" + id + " td.column-order_number").append(`
                                <mark class="order-status status-pws-returned pws-tips"
                                        style="margin-top: 10px; font-size: 11px;">
                                    <span>${response.message}</span>
                                </mark>
                            `);

						}

						pws_change_status_ajax(status);
					});

				}
			});
        </script>
		<?php
	}

	private function change_status_order() {
		?>

		<?php
	}

	public function change_status_callback() {

		$status = $_POST['status'] ?? null;

		if ( is_null( $status ) || ! in_array( 'wc-' . $status, array_keys( wc_get_order_statuses() ) ) ) {

			echo json_encode( [
				'success' => false,
				'message' => 'وضعیت انتخاب شده معتبر نمی باشد.'
			] );

			die();
		}

		$order_id = $_POST['id'] ?? null;

		if ( is_null( $order_id ) || ! is_numeric( $order_id ) ) {

			echo json_encode( [
				'success' => false,
				'message' => 'سفارش انتخاب شده معتبر نمی باشد.'
			] );

			die();
		}

		/** @var WC_Order $order */
		$order = wc_get_order( $order_id );

		if ( $order == false ) {

			echo json_encode( [
				'success' => false,
				'message' => 'سفارش انتخاب شده وجود ندارد.'
			] );

			die();
		}

		$tapin_method = get_post_meta( $order_id, 'tapin_method', true );

		if ( empty( $tapin_method ) ) {

			echo json_encode( [
				'success' => false,
				'message' => 'روش ارسال این سفارش تاپین نیست.'
			] );

			die();
		}

		$step = get_post_meta( $order_id, 'tapin_step', true );

		$step = empty( $step ) ? 0 : intval( $step );

		$steps = [
			1 => [
				'status'       => 'pws-packaged',
				'label'        => 'بسته بندی شده',
				'tapin_status' => 1
			],
			2 => [
				'status'       => 'pws-ready-to-ship',
				'label'        => 'آماده به ارسال',
				'tapin_status' => 2
			],
		];

		if ( $status == 'pws-packaged' ) { // Submit & get post barcode

			$tapin_order_id = get_post_meta( $order_id, 'tapin_order_id', true );

			if ( ! empty( $tapin_order_id ) ) {

				echo json_encode( [
					'success' => false,
					'message' => 'این سفارش قبلا در پنل ثبت شده است.'
				] );

				die();
			}

			$products = [];

			foreach ( $order->get_items() as $order_item ) {

				/** @var WC_Product $product */
				$product = $order_item->get_product();

				if ( $product->is_virtual() ) {
					continue;
				}

				if ( $product->has_weight() ) {
					$weight = wc_get_weight( $product->get_weight(), 'g' );
				} else {
					$weight = $tapin_method['default_weight'];
				}

				$price = $order_item->get_total() / $order_item->get_quantity();

				if ( get_woocommerce_currency() == 'IRT' ) {
					$price *= 10;
				}

				$products[] = [
					'count'      => $order_item->get_quantity(),
					'discount'   => 0,
					'price'      => $price,
					'title'      => $product->get_title(),
					'weight'     => $weight,
					'product_id' => null,
				];
			}

			$data = [
				'register_type'  => 1,
				'shop_id'        => PW()->get_options( 'pws_tapin_shop_id' ),
				'address'        => $order->get_shipping_address_1() . ' ' . $order->get_shipping_address_2(),
				'city_code'      => $order->get_meta( '_shipping_city_id' ),
				'province_code'  => $order->get_meta( '_shipping_state_id' ),
				'description'    => null,
				'email'          => null,
				'employee_code'  => '-1',
				'first_name'     => $order->get_shipping_first_name(),
				'last_name'      => $order->get_shipping_last_name(),
				'mobile'         => $order->get_billing_phone(),
				'phone'          => null,
				'postal_code'    => $order->get_shipping_postcode(),
				'pay_type'       => $order->get_payment_method() == 'cod' ? 0 : 1,
				'order_type'     => $tapin_method['post_type'],
				'package_weight' => $tapin_method['package_weight'],
				'presenter_code' => 1025,
				'products'       => $products
			];

			PWS_Tapin::set_gateway( $tapin_method['post_gateway'] );

			$response = PWS_Tapin::request( 'v2/public/order/post/register', $data );

			if ( is_wp_error( $response ) || $response->returns->status != 200 ) {

				echo json_encode( [
					'success' => false,
					'message' => 'خطا در ثبت در پنل تاپین',
				] );

				die();
			}

			update_post_meta( $order_id, 'tapin_order_id', $response->entries->order_id );
			update_post_meta( $order_id, 'post_barcode', $response->entries->barcode );
			update_post_meta( $order_id, 'tapin_step', 1 );

			$note = "بارکد پستی مرسوله شما: {$response->entries->barcode}
                        می توانید مرسوله خود را از طریق لینک https://newtracking.post.ir رهگیری نمایید.";

			$order->set_status( $status );
			$order->save();
			$order->add_order_note( $note );

			echo json_encode( [
				'success' => true,
				'message' => $steps[ $step + 1 ]['label'],
			] );

			die();

		} else if ( $status == 'pws-ready-to-ship' && $step == 1 ) {

			echo json_encode( [
				'success' => false,
				'message' => 'test'
			] );

			die();

		} else {

			echo json_encode( [
				'success' => false,
				'message' => "ابتدا باید به 'بسته بندی شده' تغییر وضعیت دهید."
			] );

			die();

		}

	}

	public function tapin_help() {

		$screen = get_current_screen();

		if ( $screen->id == 'edit-shop_order' ) {

			$help_tabs = $screen->get_help_tabs();
			$screen->remove_help_tabs();

			$screen->add_help_tab( array(
				'id'      => 'tapin_help',
				'title'   => 'تاپین',
				'content' => '<h2>جهت ثبت سفارش در پیشخوان مجازی تاپین به مراحل زیر توجه نمایید:</h2>
							<p>
								<ol>
									<li>ارسال به انبار => ثبت سفارش در پنل تاپین</li>
									<li>بسته بندی شده => آماده به پرینت و دریافت بارکد پستی</li>
									<li>آماده به ارسال => اعلام آمادگی جهت جمع آوری بسته ها</li>
								</ol>
								<b>توجه:</b> لطفا این مراحل را به ترتیب انجام دهید.
							</p>',
			) );

			if ( count( $help_tabs ) ) {
				foreach ( $help_tabs as $help_tab ) {
					$screen->add_help_tab( $help_tab );
				}
			}
		}
	}
}

new PWS_Status();
