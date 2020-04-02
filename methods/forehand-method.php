<?php
/**
 * Developer : MahdiY
 * Web Site  : MahdiY.IR
 * E-Mail    : M@hdiY.IR
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( class_exists( 'WC_Forehand_Method' ) ) {
	return;
} // Stop if the class already exists

class WC_Forehand_Method extends PWS_Shipping_Method {

	const INSURANCE = 8000;

	const SERVICE = 16000;

	const TAX = 9;

	public function __construct( $instance_id = 0 ) {

		$this->id                 = 'WC_Forehand_Method';
		$this->instance_id        = absint( $instance_id );
		$this->method_title       = __( 'پست پیشتاز' );
		$this->method_description = __( 'ارسال کالا با استفاده از پست پیشتاز' );

		parent::__construct();
	}

	public function init() {

		parent::init();

		$this->extra_cost         = $this->get_option( 'extra_cost', 0 );
		$this->extra_cost_percent = $this->get_option( 'extra_cost_percent', 0 );
		$this->source_state       = $this->get_option( 'source_state' );

		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	public function init_form_fields() {

		$this->instance_form_fields += [
			'extra_cost'         => [
				'title'       => 'هزینه های اضافی',
				'type'        => 'text',
				'description' => 'هزینه های اضافی علاوه بر نرخ پستی را می توانید وارد نمائید، (مثل: هزینه های بسته بندی و ... ) مبلغ ثابت را به ریال وارد نمائید',
				'default'     => 0,
				'desc_tip'    => true,
			],
			'extra_cost_percent' => [
				'title'       => 'هزینه های اضافی به درصد',
				'type'        => 'text',
				'description' => 'هزینه های اضافی علاوه بر نرخ پستی را می توانید به درصد وارد نمائید (مثال: برای 2%، عدد 2 را وارد نمائید)',
				'default'     => 0,
				'desc_tip'    => true,
			],
			'source_state'       => [
				'title'       => 'استان مبدا (فروشنده)',
				'type'        => 'select',
				'description' => 'لطفا در این قسمت استانی که محصولات از آنجا ارسال می شوند را انتخاب نمائید',
				'options'     => PWS()::states(),
				'desc_tip'    => true,
			],
		];
	}

	public function calculate_shipping( $package = array() ) {

		if ( $this->free_shipping( $package ) ) {
			return true;
		}

		$cost    = "";
		$term_id = $package['destination']['district'] !== 0 ? $package['destination']['district'] : $package['destination']['city'];
		$terms   = PWS()->get_terms_option( $term_id );

		foreach ( (array) $terms as $term ) {
			if ( $term['forehand_cost'] != "" ) {
				$cost = $term['forehand_cost'];
				break;
			}
		}

		if ( $cost !== "" ) {

			$this->add_rate_cost( $cost, $package );

			return true;
		}

		$cost = 0;

		$weight = $this->cart_weight;

		// Rate Table
		$rate_price['500']['in']     = 57500;
		$rate_price['500']['beside'] = 78000;
		$rate_price['500']['out']    = 84000;

		$rate_price['1000']['in']     = 74000;
		$rate_price['1000']['beside'] = 100000;
		$rate_price['1000']['out']    = 112000;

		$rate_price['2000']['in']     = 98000;
		$rate_price['2000']['beside'] = 127000;
		$rate_price['2000']['out']    = 140000;

		$rate_price['9999'] = 25000;

		$weight_indicator = '9999';

		switch ( true ) {
			case $weight <= 500:
				$weight_indicator = '500';
				break;
			case $weight > 500 && $weight <= 1000:
				$weight_indicator = '1000';
				break;
			case $weight > 1000 && $weight <= 2000:
				$weight_indicator = '2000';
				break;
		}

		$checked_state = PWS()->check_states_beside( $this->source_state, $package['destination']['state'] );

		if ( $checked_state == false ) {
			return false;
		}

		// calculate
		if ( $weight_indicator != '9999' ) {
			$cost = $rate_price[ $weight_indicator ][ $checked_state ];
		} elseif ( $weight_indicator == '9999' ) {
			$cost = $rate_price['2000'][ $checked_state ] + ( $rate_price[ $weight_indicator ] * ceil( ( $weight - 2000 ) / 1000 ) );
		}

		$cost += self::INSURANCE;
		$cost += self::SERVICE;
		$cost += $cost * self::TAX / 100;
		$cost += $cost * $this->extra_cost_percent / 100;
		$cost += $this->extra_cost;

		// Round Up
		$cost = ceil( $cost / 1000 ) * 1000;

		$this->add_rate_cost( PWS()->convert_currency( $cost ), $package );
	}
}
