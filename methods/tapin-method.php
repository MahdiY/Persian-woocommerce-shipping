<?php
/**
 * Developer : MahdiY
 * Web Site  : MahdiY.IR
 * E-Mail    : M@hdiY.IR
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'PWS_Tapin_Method' ) ) {
	return;
} // Stop if the class already exists

/**
 * Class WC_Tapin_Method
 *
 * @author mahdiy
 *
 */
class PWS_Tapin_Method extends PWS_Shipping_Method {

	/**
	 * @var int
	 */
	public int $extra_cost;

	/**
	 * @var string
	 */
	public string $fixed_cost;

	public function init() {

		parent::init();

		$this->extra_cost = intval( $this->get_option( 'extra_cost', 0 ) );
		$this->fixed_cost = $this->get_option( 'fixed_cost' );

		add_action( 'woocommerce_update_options_shipping_' . $this->id, [ $this, 'process_admin_options' ] );
	}

	public function init_form_fields() {

		$currency_symbol = get_woocommerce_currency_symbol();

		$this->instance_form_fields += [
			'extra_cost' => [
				'title'       => 'هزینه های اضافی',
				'type'        => 'price',
				'description' => 'هزینه های اضافی علاوه بر نرخ پستی را می توانید وارد نمائید، (مثل: هزینه های بسته بندی و ...) مبلغ ثابت را به ' . $currency_symbol . ' وارد نمائید',
				'default'     => 0,
				'desc_tip'    => true,
			],
			'fixed_cost' => [
				'title'       => 'هزینه ثابت',
				'type'        => 'price',
				'description' => "<b>توجه:</b>
								<ul>
									<li>1. برای محاسبه هزینه توسط فرمول تاپین/پست کتاب خالی بگذارید.</li>
									<li>2. صفر به معنی رایگان است. یعنی هزینه حمل و نقل برعهده فروشگاه شما است.</li>
									<li>3. در صورت تعیین هزینه ثابت حمل و نقل این قیمت دقیقا به مشتری نمایش داده می شود.</li>
									<li>4. این گزینه مناسب فروشگاه هایی است که وزن محصولات خود را وارد نکرده اند.</li>
								</ul>
								",
				'default'     => '',
			],
		];
	}

	public function is_available( $package = [] ): bool {

		$weight = PWS_Cart::get_weight();

		$post_weight_limit = intval( PWS()->get_option( 'tools.post_weight_limit', 30000 ) );

		if ( $post_weight_limit && $weight > $post_weight_limit ) {
			return false;
		}

		if ( PWS()->get_option( 'tapin.gateway', 'tapin' ) == 'posteketab' ) {

			$payment_method = WC()->session->get( 'chosen_payment_method' );
			$is_cod         = $payment_method === 'cod';

			if ( $is_cod ) {
				return false;
			}

			if ( $this->payment_type == 'postpaid' ) {
				return false;
			}

		}

		return parent::is_available( $package );
	}

	public function calculate_shipping( $package = [] ) {

		if ( $this->free_shipping( $package ) ) {
			return;
		}

		$options = PWS()->get_terms_option( $this->get_destination( $package ) );
		$options = array_column( $options, $this->get_rate_id() );

		foreach ( $options as $option ) {
			if ( $option != '' ) {
				$this->add_rate_cost( $option, $package );

				return;
			}
		}

		if ( $this->fixed_cost !== '' ) {
			$this->add_rate_cost( intval( $this->fixed_cost ) + $this->extra_cost, $package );

			return;
		}

		if ( $this->payment_type == 'postpaid' ) {
			$this->add_rate_cost( $this->extra_cost, $package, [
				'payment_type' => 'postpaid',
			] );

			return;
		}

		$weight = PWS_Cart::get_weight();

		$price = 0;

		foreach ( WC()->cart->get_cart() as $cart_item ) {

			/** @var WC_Product $product */
			$product = $cart_item['data'];

			if ( $product->is_virtual() ) {
				continue;
			}

			$price += $product->get_price() * $cart_item['quantity'];
		}

		$destination = $package['destination'];

		$payment_method = WC()->session->get( 'chosen_payment_method' );

		$is_cod = $payment_method === 'cod';

		$price = PWS()->convert_currency_to_IRR( $price );

		$shop = PWS_Tapin::shop();

		if ( isset( $shop['total_price'] ) ) {
			$price += $shop['total_price'];
		}

		$args = [
			'gateway'       => PWS()->get_option( 'tapin.gateway', 'tapin' ),
			'price'         => min( $price, 5_000_000_000 ), // Max insurance: 500MT
			'weight'        => ceil( $weight ),
			'is_cod'        => $is_cod,
			'to_province'   => intval( $destination['state'] ),
			'from_province' => intval( $shop['province_code'] ?? 1 ),
			'to_city'       => intval( $destination['city'] ),
			'from_city'     => intval( $shop['city_code'] ?? 1 ),
			'content_type'  => PWS()->get_option( 'tapin.content_type', 1 ),
			'box_size'      => PWS()->get_option( 'tapin.box_size', 1 ),
		];

		$args = apply_filters( 'pws_tapin_calculate_rates_args', $args, $package, $this );

		$shipping_total = $this->calculate_rates( $args );

		if ( isset( $shop['total_price'] ) ) {
			$shipping_total += $shop['total_price'];
		}

		if ( isset( $shop['has_first_mile_request'] ) && $shop['has_first_mile_request'] ) {
			$shipping_total += 16500;
		}

		if ( PWS()->get_option( 'tapin.roundup_price' ) ) {
			$shipping_total = ceil( $shipping_total / 1000 ) * 1000;
		}

		$shipping_total = PWS()->convert_currency_from_IRR( $shipping_total );

		$shipping_total += $this->extra_cost;

		$this->add_rate_cost( $shipping_total, $package );
	}

}
