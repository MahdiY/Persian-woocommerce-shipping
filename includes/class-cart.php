<?php
/**
 * Developer : MahdiY
 * Web Site  : MahdiY.IR
 * E-Mail    : M@hdiY.IR
 */

defined( 'ABSPATH' ) || exit;

class PWS_Cart {

	public static function get_shipping_items(): array {

		$cart_items = WC()->cart ? WC()->cart->get_cart() : [];

		foreach ( $cart_items as $cart_id => $cart_item ) {
			if ( ! $cart_item['data']->needs_shipping() ) {
				unset( $cart_items[ $cart_id ] );
			}
		}

		return $cart_items;
	}

	public static function get_weight(): float {

		$weight = floatval( PWS()->get_option( 'tools.package_weight', 500 ) );

		foreach ( self::get_shipping_items() as $cart_item ) {
			$weight += PWS_Product::get_weight( $cart_item['data'] ) * $cart_item['quantity'];
		}

		return floatval( apply_filters( 'pws_cart_weight', $weight ) );
	}

	public static function get_items_qty(): int {

		$items_qty = 0;

		foreach ( self::get_shipping_items() as $cart_item ) {
			$items_qty += intval( $cart_item['quantity'] );
		}

		return $items_qty;
	}

	public static function get_items_type_qty(): int {
		return count( self::get_shipping_items() );
	}

	/**
	 * @param string $shipping_class Shipping class slug
	 *
	 * @return bool
	 */
	public static function has_shipping_class( string $shipping_class ): bool {

		foreach ( self::get_shipping_items() as $cart_item ) {

			/** @var WC_Product $product */
			$product = $cart_item['data'];

			if ( $product->get_shipping_class() == $shipping_class ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param int $shipping_class_id Shipping class id
	 *
	 * @return bool
	 */
	public static function has_shipping_class_id( int $shipping_class_id ): bool {

		foreach ( self::get_shipping_items() as $cart_item ) {

			/** @var WC_Product $product */
			$product = $cart_item['data'];

			if ( $product->get_shipping_class_id() == $shipping_class_id ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param string   $shipping_method
	 * @param int|null $instance_id
	 *
	 * @return bool
	 */
	public static function has_shipping_method( string $shipping_method, int $instance_id = null ): bool {

		$methods = WC()->session->get( 'chosen_shipping_methods', [] );

		foreach ( $methods as $method ) {

			if ( is_null( $instance_id ) ) {

				if ( strpos( $method, "{$shipping_method}:" ) === 0 ) {
					return true;
				}

			} elseif ( "{$shipping_method}:{$instance_id}" === $method ) {
				return true;
			}

		}

		return false;
	}

	/**
	 * @param int $product_id
	 *
	 * @return bool
	 */
	public static function has_product( int $product_id ): bool {

		foreach ( self::get_shipping_items() as $cart_item ) {

			/** @var WC_Product $product */
			$product = $cart_item['data'];

			if ( in_array( $product_id, [ $product->get_id(), $product->get_parent_id() ] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param int $category_id
	 *
	 * @return bool
	 */
	public static function has_category( int $category_id ): bool {

		foreach ( self::get_shipping_items() as $cart_item ) {

			/** @var WC_Product $product */
			$product = $cart_item['data'];

			if ( $product->get_parent_id() ) {
				/** @var WC_Product $product */
				$product = wc_get_product( $product->get_parent_id() );
			}

			$categories = $product->get_category_ids();

			foreach ( $categories as $category ) {
				$categories = array_merge( $categories, get_ancestors( $category, 'product_cat', 'taxonomy' ) );
			}

			if ( in_array( $category_id, $categories ) ) {
				return true;
			}
		}

		return false;
	}

}
