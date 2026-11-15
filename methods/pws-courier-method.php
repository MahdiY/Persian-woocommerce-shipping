<?php
/**
 * Developer : MahdiY
 * Web Site  : MahdiY.IR
 * E-Mail    : M@hdiY.IR
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'WC_Courier_Method' ) ) {
	return;
} // Stop if the class already exists

class WC_Courier_Method extends PWS_Shipping_Method {

	/**
	 * @var array
	 */
	public array $delivery_areas = [];

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

		$this->id                 = 'WC_Courier_Method';
		$this->method_title       = __( 'پیک موتوری' );
		$this->method_description = __( 'ارسال با استفاده از پیک موتوری' );

		parent::__construct( $instance_id );
	}

	public function init() {

		parent::init();

		$this->delivery_areas = $this->get_option( 'delivery_areas', [] );
		$this->base_cost      = intval( $this->get_option( 'base_cost' ) );
		$this->per_cost       = intval( $this->get_option( 'per_cost' ) );

		if ( $this->instance_id && empty( $this->delivery_areas ) ) {
			$this->method_description .= '<p style="color: #a00;">⚠️ برای نمایش این روش پیک موتوری در تسویه حساب، از بخش ویرایش روبرو، مناطق سرویس‌دهی پیک موتوری را تعیین کنید.</p>';
		}

		add_action( 'woocommerce_update_options_shipping_' . $this->id, [ $this, 'process_admin_options' ] );
	}

	public function init_form_fields() {

		$currency_symbol = get_woocommerce_currency_symbol();

		$delivery_areas = [];

		foreach ( PWS()::states() as $state_id => $state ) {

			foreach ( PWS()::cities( $state_id ) as $city_id => $city ) {

				$delivery_areas[ $city_id ] = $state . ' - ' . $city;

				foreach ( PWS()::districts( $city_id ) as $district_id => $district ) {
					$delivery_areas[ $district_id ] = $state . ' - ' . $city . ' - ' . $district;
				}

			}

		}

		// @todo delivery areas are in zone criteria?

		$this->instance_form_fields += [
			'delivery_areas' => [
				'title'       => 'مناطق سرویس‌دهی',
				'type'        => 'multiselect',
				'description' => 'انتخاب کنید پیک موتوری در کدام شهرها و محله‌ها فعال باشد. در صورت خالی گذاشتن این بخش، روش پیک موتوری فعال نخواهد شد.',
				'default'     => null,
				'options'     => $delivery_areas,
				'desc_tip'    => true,
			],
			'base_cost'      => [
				'title'       => 'هزینه پایه',
				'type'        => 'price',
				'description' => 'مبلغ حمل و نقل به روش پیک موتوری را به ' . $currency_symbol . ' وارد نمائید.',
				'default'     => 0,
				'desc_tip'    => true,
			],
			'per_cost'       => [
				'title'       => 'هزینه به ازای هر کیلوگرم',
				'type'        => 'price',
				'description' => 'در صورتی که قصد دارید به ازای هر کیلوگرم هزینه اضافی دریافت شود هزینه را به ' . $currency_symbol . ' وارد نمائید.',
				'default'     => 0,
				'desc_tip'    => true,
			],
		];

	}

	public function is_available( $package = [] ): bool {

		$city_id     = $package['destination']['city'] ?? 0;
		$district_id = $package['destination']['district'] ?? 0;

		$this->is_available = in_array( $city_id, $this->delivery_areas ) || in_array( $district_id, $this->delivery_areas );

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
