<?php
/**
 * Developer : MahdiY
 * Web Site  : MahdiY.IR
 * E-Mail    : M@hdiY.IR
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( class_exists( 'WC_Courier_Method' ) ) {
	return;
} // Stop if the class already exists

/**
 * Class PWS_Shipping_Method
 *
 * @author mahdiy
 *
 *
 */
class PWS_Shipping_Method extends WC_Shipping_Method {

	/**
	 * Free shipping if order total is grater than free fee
	 *
	 * @var string
	 */
	public $free_fee = '';

	/**
	 * Cart total
	 *
	 * @var string
	 */
	public $cart_total = 0;

	/**
	 * Cart weight
	 *
	 * @var string
	 */
	public $cart_weight = 0;

	public function __construct() {

		$this->supports = [
			'shipping-zones',
			'instance-settings',
			'instance-settings-modal',
		];

		$this->init();
	}

	public function init() {

		// Prepend
		$this->instance_form_fields = [
			'title' => [
				'title'   => 'عنوان روش',
				'type'    => 'text',
				'default' => $this->method_title
			],
		];

		$this->init_form_fields();

		$this->instance_form_fields += [
			'minimum_fee' => [
				'title'       => 'حداقل خرید',
				'type'        => 'text',
				'description' => 'در صورتی که مبلغ سفارش کمتر از این مبلغ باشد، این روش حمل و نقل مخفی می شود.',
				'default'     => 0,
				'desc_tip'    => true,
			],
			'free_fee'    => [
				'title'       => 'آستانه حمل و نقل رایگان',
				'type'        => 'text',
				'description' => 'در صورتی که مبلغ سفارش بیشتر از این مبلغ باشد، هزینه حمل و نقل برای مشتری رایگان می شود.',
				'default'     => '',
				'desc_tip'    => true,
			],
			'img_url'     => [
				'title'       => 'تصویر روش حمل و نقل',
				'type'        => 'text',
				'description' => 'آدرس تصویر مورد نظر برای این روش حمل و نقل را وارد کنید',
				'default'     => '',
				'css'         => 'direction: ltr;',
				'desc_tip'    => true,
			]
		];

		$this->init_settings();

		$this->title       = $this->get_option( 'title', $this->method_title );
		$this->minimum_fee = $this->get_option( 'minimum_fee', 0 );
		$this->free_fee    = $this->get_option( 'free_fee', '' );
		$this->cart_total  = isset( WC()->cart ) ? WC()->cart->get_cart_contents_total() : 0;
		$this->cart_weight = isset( WC()->cart ) ? wc_get_weight( WC()->cart->get_cart_contents_weight(), 'g' ) : 0;
	}

	public function is_available( $package ) {

		$available = $this->is_enabled();

		if ( empty( $package ) ) {
			$available = false;
		}

		if ( $package['destination']['country'] != 'IR' ) {
			$available = false;
		}

		if ( is_null( PWS()->get_state( $package['destination']['state'] ) ) ) {
			$available = false;
		}

		if ( is_null( PWS()->get_city( $package['destination']['city'] ) ) ) {
			$available = false;
		}

		if ( $this->minimum_fee > $this->cart_total ) {
			$available = false;
		}

		return apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', $available, $package, $this );
	}

	public function free_shipping( $package = array() ) {

		if ( $this->free_fee !== '' && $this->free_fee <= $this->cart_total ) {

			$this->add_rate_cost( 0, $package );

			return true;
		}

		return false;
	}

	public function add_rate_cost( $cost, $package ) {

		$rate = apply_filters( 'pws_add_rate', [
			'id'    => $this->get_rate_id(),
			'label' => $this->title,
			'cost'  => $cost
		], $package, $this );

		return $this->add_rate( $rate );
	}
}
