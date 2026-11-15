<?php
/**
 * Developer : MahdiY
 * Web Site  : MahdiY.IR
 * E-Mail    : M@hdiY.IR
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'WC_Tipax_Method' ) ) {
	return;
} // Stop if the class already exists

class WC_Tipax_Method extends PWS_Shipping_Method {

	/**
	 * Base calculation cost
	 *
	 * @var int
	 */
	public $base_cost = 0;

	/**
	 * Cost per KG
	 *
	 * @var int
	 */
	public $per_cost = 0;

	public function __construct( $instance_id = 0 ) {

		$this->id                 = 'WC_Tipax_Method';
		$this->method_title       = __( 'تیپاکس' );
		$this->method_description = __( 'ارسال کالا با استفاده از تیپاکس' );

		parent::__construct( $instance_id );
	}

	public function init() {

		parent::init();

		$this->base_cost = intval( $this->get_option( 'base_cost' ) );
		$this->per_cost  = intval( $this->get_option( 'per_cost' ) );

		add_action( 'woocommerce_update_options_shipping_' . $this->id, [ $this, 'process_admin_options' ] );
	}

	public function init_form_fields() {

		$currency_symbol = get_woocommerce_currency_symbol();

		$this->instance_form_fields += [
			'base_cost' => [
				'title'       => 'هزینه پایه',
				'type'        => 'price',
				'description' => 'مبلغ حمل و نقل به روش تیپاکس را به ' . $currency_symbol . ' وارد نمائید.',
				'default'     => 0,
			],
			'per_cost'  => [
				'title'       => 'هزینه به ازای هر کیلوگرم',
				'type'        => 'price',
				'description' => 'در صورتی که قصد دارید به ازای هر کیلوگرم هزینه اضافی دریافت شود هزینه را به ' . $currency_symbol . ' وارد نمائید.',
				'default'     => 0,
				'desc_tip'    => true,
			],
		];

	}

	public function is_available( $package = [] ): bool {

		$options = PWS()->get_terms_option( $this->get_destination( $package ) );

		$this->is_available = count( array_column( $options, $this->get_rate_id() . '_on' ) ) == count( $options );

		return parent::is_available( $package );
	}

	public function calculate_shipping( $package = [] ) {

		if ( $this->free_shipping( $package ) ) {
			return;
		}

		$cost = $this->base_cost;

		$options = PWS()->get_terms_option( $this->get_destination( $package ) );
		$options = array_column( $options, $this->get_rate_id() );

		foreach ( $options as $option ) {
			if ( $option != '' ) {
				$cost = intval( $option );
				break;
			}
		}

		$cost += ceil( $this->cart_weight / 1000 ) * $this->per_cost;

		$this->add_rate_cost( $cost, $package );
	}
}
