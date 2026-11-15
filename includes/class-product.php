<?php
/**
 * Developer : MahdiY
 * Web Site  : MahdiY.IR
 * E-Mail    : M@hdiY.IR
 */

defined( 'ABSPATH' ) || exit;

class PWS_Product {

	public static function get_weight( WC_Product $product ): float {

		if ( $product->is_virtual() ) {
			$weight = 0;
		} else if ( $product->has_weight() ) {
			$weight = wc_get_weight( $product->get_weight(), 'g' );
		} else {
			$weight = PWS()->get_option( 'tools.product_weight', 500 );
		}

		return floatval( apply_filters( 'pws_product_weight', $weight, $product ) );
	}

}
