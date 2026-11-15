<?php
/**
 * Developer : MahdiY
 * Web Site  : MahdiY.IR
 * E-Mail    : M@hdiY.IR
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'Tapin_Special_Method' ) ) {
	return;
} // Stop if the class already exists

/**
 * Class WC_Tapin_Method
 *
 * @author mahdiy
 *
 */
class Tapin_Special_Method extends PWS_Tapin_Method {

	public function __construct( $instance_id = 0 ) {

		$this->id                 = 'Tapin_Special_Method';
		$this->method_title       = 'پست ویژه - تاپین';
		$this->method_description = 'ارسال کالا با استفاده از پست ويژه - تاپین (پست ویژه مخصوص مراکز استان است)';

		$this->supports[] = 'postpaid';

		parent::__construct( $instance_id );
	}

	public function is_available( $package = [] ): bool {

		$city_id = intval( $package['destination']['city'] );

		if ( ! PWS_Tapin::has_service( $city_id, 'irpost', 3 ) ) {
			return false;
		}

		return parent::is_available( $package );
	}

	public static function calculate_rates( array $args ): int {

		$weight = $args['weight'];

		$gateway = $args['gateway'] ?? 'tapin';

		$additions = [ 1 ];

		$box_size = max( 1, min( 10, $args['box_size'] ) );

		if ( $args['from_province'] == $args['to_province'] ) {
			$vicinity = 'in';
		} elseif ( PWS()->check_states_beside( $args['from_province'], $args['to_province'] ) ) {
			$vicinity = 'beside';
		} else {
			$vicinity = 'out';
		}

		$box_rates = include PWS_DIR . '/data/rates/tapin-special.php';

		if ( in_array( $args['from_province'], [ 3, 4, 5, 7, 15, 16, 18, 19, 21, 23, 26, 27, 29, 30 ] ) ) {
			$box_rates = include PWS_DIR . '/data/rates/tapin-special-border.php';
		}

		$weight_index = min( ceil( $weight / 1000 ) * 1000, 30000 );
		$weight_index = max( 1000, $weight_index );

		$base_cost = $box_rates[ $weight_index ][ $box_size ][ $vicinity ];

		if ( in_array( $args['to_city'], [ 61, 51, 41, 481 ] ) ) {
			$base_cost *= 1.15;
		} else if ( in_array( $args['to_city'], [ 1, 31, 71, 81, 91 ] ) ) {
			$base_cost *= 1.20;
		}

		if ( in_array( $args['to_province'], [ 5, 6, 7, 9, 22, 25, 26, 30 ] ) ) {
			$base_cost *= 1.05;
		}

		if ( in_array( $args['to_city'], [ 79351, 7951, 75461, 79551, 7941, 79591, 79781 ] ) ) {
			$base_cost += 255_000 * ( $weight_index / 1000 );
		}

		if ( $gateway == 'posteketab' ) {
			$cost = $base_cost * 0.7;
		} else {
			$cost = $base_cost;
		}

		if ( $args['content_type'] != 1 ) {
			$cost += $base_cost * 0.25;
		}

		// INSURANCE
		if ( $args['price'] > 100_000_000 ) {

			switch ( true ) {
				case $args['price'] >= 2_000_000_000:
					$insurance_rate = 0.004;
					break;
				case $args['price'] >= 1_000_000_000:
					$insurance_rate = 0.0035;
					break;
				case $args['price'] >= 500_000_000:
					$insurance_rate = 0.003;
					break;
				case $args['price'] >= 300_000_000:
					$insurance_rate = 0.0025;
					break;
				default:
					$insurance_rate = 0.002;
					break;
			}

			$cost += $args['price'] * $insurance_rate;
			$cost += 135_000;

		} else {
			$cost += 100_000;
		}

		// COD
		if ( $args['is_cod'] ) {

			switch ( true ) {
				case $args['price'] >= 500_000_000:
					$cost += 450_000;
					break;
				case $args['price'] >= 200_000_000:
					$cost += 350_000;
					break;
				case $args['price'] >= 100_000_000:
					$cost += 300_000;
					break;
				case $args['price'] >= 50_000_000:
					$cost += 200_000;
					break;
				case $args['price'] >= 10_000_000:
					$cost += 150_000;
					break;
				default:
					$cost += $args['price'] * 0.01;
					break;
			}

			$cost += 80_000;
		}

		// TAX
		$cost += $cost * 0.1;

		return intval( $cost );
	}
}
