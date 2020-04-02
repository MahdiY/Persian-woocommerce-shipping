<?php
/**
 * Developer : MahdiY
 * Web Site  : MahdiY.IR
 * E-Mail    : M@hdiY.IR
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( class_exists( 'WC_Tipax_Method' ) ) {
	return;
} // Stop if the class already exists

class WC_Tipax_Method extends PWS_Shipping_Method {

	public function __construct( $instance_id = 0 ) {

		$this->id                 = 'WC_Tipax_Method';
		$this->instance_id        = absint( $instance_id );
		$this->method_title       = __( 'تیپاکس' );
		$this->method_description = __( 'ارسال کالا با استفاده از تیپاکس' );

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

		$this->base_cost = intval( $this->get_option( 'base_cost' ) );
		$this->per_cost  = intval( $this->get_option( 'per_cost' ) );

		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	public function init_form_fields() {

		$currency_symbol = get_woocommerce_currency_symbol();

		$this->instance_form_fields += [
			'base_cost' => [
				'title'       => 'هزینه پایه',
				'type'        => 'price',
				'description' => 'مبلغ حمل و نقل به روش تیپاکس را به ' . $currency_symbol . ' وارد نمائید.',
				'default'     => 0
			],
			'per_cost'  => [
				'title'       => 'هزینه به ازای هر کیلوگرم',
				'type'        => 'price',
				'description' => 'در صورتی که قصد دارید به ازای هر کیلوگرم هزینه اضافی دریافت شود هزینه را به ' . $currency_symbol . ' وارد نمائید.',
				'default'     => 0,
				'desc_tip'    => true,
			],
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
				'title'       => 'مقصد تیپاکس',
				'type'        => 'multiselect',
				'options'     => $options,
				'description' => 'تعیین کنید تیپاکس برای کدام شهرها فعال باشد.',
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

		$term_id      = $package['destination']['district'] !== 0 ? $package['destination']['district'] : $package['destination']['city'];
		$terms        = PWS()->get_terms_option( $term_id );
		$is_available = true;

		if ( $terms === false || is_wp_error( $terms ) ) {
			$is_available = false;
		} else {

			foreach ( (array) $terms as $term ) {
				if ( $term['tipax_on'] == 0 ) {
					$is_available = false;
				}
			}

		}

		return apply_filters( 'woocommerce_is_available_shipping_' . $this->id, $is_available, $package );
	}

	public function calculate_shipping( $package = array() ) {

		if ( $this->free_shipping( $package ) ) {
			return true;
		}

		$cost    = $this->base_cost;
		$term_id = $package['destination']['district'] !== 0 ? $package['destination']['district'] : $package['destination']['city'];
		$terms   = PWS()->get_terms_option( $term_id );

		foreach ( $terms as $term ) {
			if ( $term['tipax_cost'] != "" ) {
				$cost = $term['tipax_cost'];
				break;
			}
		}

		$weight = $this->cart_weight / 1000;

		$cost += ceil( $weight ) * $this->per_cost;

		$this->add_rate_cost( $cost, $package );
	}
}
