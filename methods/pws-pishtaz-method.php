<?php
/**
 * Developer : MahdiY
 * Web Site  : MahdiY.IR
 * E-Mail    : M@hdiY.IR
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'WC_Forehand_Method' ) ) {
	return;
} // Stop if the class already exists

class WC_Forehand_Method extends PWS_Shipping_Method {

	const INSURANCE = 8000;

	const SERVICE = 16000;

	const TAX = 10;

	/**
	 * @var mixed
	 */
	public $extra_cost;

	/**
	 * @var mixed
	 */
	public $extra_cost_percent;

	/**
	 * @var mixed
	 */
	public $source_state;

	public function __construct( $instance_id = 0 ) {

		$this->id                 = 'WC_Forehand_Method';
		$this->method_title       = 'پست پیشتاز';
		$this->method_description = 'ارسال کالا با استفاده از پست پیشتاز';

		parent::__construct( $instance_id );
	}

	public function init() {

		parent::init();

		$this->extra_cost         = $this->get_option( 'extra_cost', 0 );
		$this->extra_cost_percent = $this->get_option( 'extra_cost_percent', 0 );
		$this->source_state       = $this->get_option( 'source_state' );

		add_action( 'woocommerce_update_options_shipping_' . $this->id, [ $this, 'process_admin_options' ] );
	}

	public function init_form_fields() {

		$this->instance_form_fields += [
			'extra_cost'         => [
				'title'       => 'هزینه های اضافی',
				'type'        => 'price',
				'description' => 'هزینه های اضافی علاوه بر نرخ پستی را می توانید وارد نمائید، (مثل: هزینه های بسته بندی و ... ) مبلغ ثابت را به ریال وارد نمائید',
				'default'     => 0,
				'desc_tip'    => true,
			],
			'extra_cost_percent' => [
				'title'       => 'هزینه های اضافی به درصد',
				'type'        => 'price',
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

	public function is_available( $package = [] ): bool {

		$weight = PWS_Cart::get_weight();

		$post_weight_limit = intval( PWS()->get_option( 'tools.post_weight_limit', 30000 ) );

		if ( $post_weight_limit && $weight > $post_weight_limit ) {
			$this->is_available = false;
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

		// calculate
		if ( $weight_indicator != '9999' ) {
			$cost = $rate_price[ $weight_indicator ]['out'];
		} else {
			$cost = $rate_price['2000']['out'] + ( $rate_price[ $weight_indicator ] * ceil( ( $weight - 2000 ) / 1000 ) );
		}

		$cost += self::INSURANCE;
		$cost += self::SERVICE;
		$cost += $cost * self::TAX / 100;
		$cost += $cost * $this->extra_cost_percent / 100;
		$cost += $this->extra_cost;

		// Round Up
		$cost = ceil( $cost / 1000 ) * 1000;

		$this->add_rate_cost( PWS()->convert_currency_from_IRR( $cost ), $package );
	}
}
