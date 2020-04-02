<?php
/**
 * Developer : MahdiY
 * Web Site  : MahdiY.IR
 * E-Mail    : M@hdiY.IR
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( class_exists( 'WC_Tapin_Method' ) ) {
	return;
} // Stop if the class already exists

/**
 * Class WC_Tapin_Method
 *
 * @author mahdiy
 *
 *
 */
class WC_Tapin_Method extends PWS_Shipping_Method {

	public function __construct( $instance_id = 0 ) {

		$this->id                 = 'WC_Tapin_Method';
		$this->instance_id        = absint( $instance_id );
		$this->method_title       = __( 'پست تاپین' );
		$this->method_description = 'پیشخوان مجازی تاپین - ارسال کالا با استفاده از پست پیشتاز و سفارشی';

		parent::__construct();
	}

	public function init() {

		parent::init();

		$this->post_type      = $this->get_option( 'post_type', 1 );
		$this->extra_cost     = $this->get_option( 'extra_cost', 0 );
		$this->fixed_cost     = $this->get_option( 'fixed_cost' );
		$this->default_weight = $this->get_option( 'default_weight' );
		$this->package_weight = $this->get_option( 'package_weight' );

		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	public function init_form_fields() {

		$currency_symbol = get_woocommerce_currency_symbol();

		$this->instance_form_fields += [
			'post_type'      => [
				'title'   => 'نوع پست',
				'type'    => 'select',
				'default' => 1,
				'options' => [
					0 => 'سفارشی',
					1 => 'پیشتاز',
				],
			],
			'extra_cost'     => [
				'title'       => 'هزینه های اضافی',
				'type'        => 'text',
				'description' => 'هزینه های اضافی علاوه بر نرخ پستی را می توانید وارد نمائید، (مثل: هزینه های بسته بندی و ...) مبلغ ثابت را به ' . $currency_symbol . ' وارد نمائید',
				'default'     => 0,
				'desc_tip'    => true,
			],
			'fixed_cost'     => [
				'title'       => 'هزینه ثابت',
				'type'        => 'text',
				'description' => "<b>توجه:</b>
								<ul>
									<li>1. برای محاسبه آنلاین هزینه توسط تاپین خالی بگذارید.</li>
									<li>2. صفر به معنی رایگان است. یعنی هزینه حمل و نقل برعهده فروشگاه شما است.</li>
									<li>3. در صورت تعیین هزینه ثابت حمل و نقل این قیمت دقیقا به مشتری نمایش داده می شود.</li>
									<li>4. این گزینه مناسب فروشگاه هایی است که وزن محصولات خود را وارد نکرده اند.</li>
								</ul>
								",
				'default'     => ''
			],
			'default_weight' => [
				'title'       => 'وزن پیشفرض هر محصول',
				'type'        => 'text',
				'description' => "در صورتی که برای محصول وزنی وارد نشده بود، بصورت پیشفرض وزن محصول چند گرم در نظر گرفته شود؟",
				'default'     => 1000,
				'desc_tip'    => true,
			],
			'package_weight' => [
				'title'       => 'وزن بسته بندی',
				'type'        => 'text',
				'description' => "بطور میانگین وزن بسته بندی ها چند گرم در نظر گرفته شود؟",
				'default'     => 500,
				'desc_tip'    => true,
			],
		];
	}

	public function is_available( $package = array() ) {

		if ( ! PWS_Tapin::is_enable() ) {
			return false;
		}

		return parent::is_available( $package );
	}

	public function calculate_shipping( $package = array() ) {

		if ( $this->free_shipping( $package ) ) {
			return true;
		}

		if ( ! empty( $this->fixed_cost ) ) {

			$shipping_total = $this->fixed_cost;

		} else {

			$weight = $this->package_weight;

			$price = 0;

			foreach ( WC()->cart->get_cart() as $cart_item ) {

				if ( $cart_item['data']->is_virtual() ) {
					continue;
				}

				if ( $cart_item['data']->has_weight() ) {
					$weight += wc_get_weight( (float) $cart_item['data']->get_weight() * $cart_item['quantity'], 'g' );
				} else {
					$weight += $this->default_weight;
				}

				$price += $cart_item['data']->get_price() * $cart_item['quantity'];
			}

			$destination = $package['destination'];

			$payment_method = WC()->session->get( 'chosen_payment_method' );

			$pay_type = 0;

			if ( $payment_method !== 'cod' ) {
				$pay_type = 1;
			}

			$shop = PWS_Tapin::shop();

			if ( get_woocommerce_currency() == 'IRT' ) {
				$price *= 10;
			}

			$data = [
				"price"         => $price,
				"weight"        => ceil( $weight ),
				"order_type"    => $this->post_type,
				"pay_type"      => $pay_type,
				"to_province"   => intval( $destination['state'] ),
				"from_province" => intval( $shop->province_code ?? 1 ),
				"to_city"       => intval( $destination['city'] ),
				"from_city"     => intval( $shop->city_code ?? 1 ),
			];

			// Cache price for one hour
			$sign = md5( serialize( $data ) . serialize( $this ) . serialize( $shop ) );

			$total = WC()->session->get( 'tapin_method_total', [ 'time' => 0 ] );

			if ( $total['time'] < time() ) {
				$total = [
					'time' => time() + HOUR_IN_SECONDS
				];
			}

			if ( ! isset( $total[ $sign ] ) ) {

				PWS_Tapin::set_gateway( PW()->get_options( 'pws_tapin_gateway' ) );

				$response = PWS_Tapin::price( $data );

				if ( $response->returns->status != 200 ) {
					PWS()->log( __METHOD__ . ' Line: ' . __LINE__ );
					PWS()->log( $data );
					PWS()->log( $response );

					return false;
				}

				$total[ $sign ] = $response->entries->total;

				WC()->session->set( 'tapin_method_total', $total );

			}

			$shipping_total = $total[ $sign ] + $shop->total_price;

			$shipping_total = ceil( $shipping_total / 1000 ) * 1000;

			$shipping_total = PWS()->convert_currency( $shipping_total );
		}

		$this->add_rate_cost( $shipping_total + $this->extra_cost, $package );
	}
}
