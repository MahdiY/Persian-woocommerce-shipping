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

class WC_Courier_Method extends PWS_Shipping_Method {

	public function __construct( $instance_id = 0 ) {

		$this->id                 = 'WC_Courier_Method';
		$this->instance_id        = absint( $instance_id );
		$this->method_title       = __( 'پیک موتوری' );
		$this->method_description = __( 'ارسال با استفاده از پیک موتوری' );

		parent::__construct();

		if ( PWS_Tapin::is_enable() ) {
			$this->supports = [
				'shipping-zones',
				'instance-settings',
			];
		}
	}

	public function init() {

		parent::init();

		$this->base_cost   = intval( $this->get_option( 'base_cost' ) );
		$this->per_cost    = intval( $this->get_option( 'per_cost' ) );
		$this->destination = $this->get_option( 'destination', [] );

		add_action( 'woocommerce_update_options_shipping_' . $this->id, [ $this, 'process_admin_options' ] );
	}

	public function init_form_fields() {

		$currency_symbol = get_woocommerce_currency_symbol();

		$this->instance_form_fields += [
			'base_cost' => [
				'title'       => 'هزینه پایه',
				'type'        => 'price',
				'description' => 'مبلغ حمل و نقل به روش پیک موتوری را به ' . $currency_symbol . ' وارد نمائید.',
				'default'     => 0
			],
			'per_cost'  => [
				'title'       => 'هزینه به ازای هر کیلوگرم',
				'type'        => 'price',
				'description' => 'در صورتی که قصد دارید به ازای هر کیلوگرم هزینه اضافی دریافت شود هزینه را به ' . $currency_symbol . ' وارد نمائید.',
				'default'     => 0,
				'desc_tip'    => true,
			]
		];

		if ( PWS_Tapin::is_enable() ) {

			$options = [];

			foreach ( PWS()::zone() as $state_id => $state ) {

				$options[ $state_id ] = $state['title'];

				foreach ( $state['cities'] as $city_id => $city ) {
					$options[ $city_id ] = $state['title'] . ' - ' . $city;
				}

			}

			$this->instance_form_fields['destination'] = [
				'title'       => 'مقصد پیک موتوری',
				'type'        => 'multiselect',
				'options'     => $options,
				'description' => 'تعیین کنید پیک موتوری برای کدام شهرها فعال باشد.',
				'default'     => [],
				'desc_tip'    => true,
			];

		}

	}

	public function is_available( $package = array() ) {

		if ( PWS_Tapin::is_enable() ) {

			if ( array_search( $package['destination']['city'], $this->destination ) === false ) {
				return false;
			}

			return parent::is_available( $package );
		}

		$term_id = $package['destination']['district'] ?? $package['destination']['city'];
		$terms   = PWS()->get_terms_option( $term_id );

		if ( $terms === false || is_wp_error( $terms ) ) {
			return false;
		} else {

			foreach ( (array) $terms as $term ) {
				if ( $term['courier_on'] == 0 ) {
					return false;
				}
			}

		}

		return parent::is_available( $package );
	}

	public function calculate_shipping( $package = array() ) {

		if ( $this->free_shipping( $package ) ) {
			return true;
		}

		$cost    = $this->base_cost;
		$term_id = $package['destination']['district'] ?? $package['destination']['city'];
		$terms   = PWS()->get_terms_option( $term_id );

		foreach ( $terms as $term ) {
			if ( $term['courier_cost'] != "" ) {
				$cost = $term['courier_cost'];
				break;
			}
		}

		$weight = $this->cart_weight / 1000;

		$cost += ceil( $weight ) * $this->per_cost;

		$this->add_rate_cost( $cost, $package );
	}
}
