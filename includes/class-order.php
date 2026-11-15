<?php
/**
 * Developer : MahdiY
 * Web Site  : MahdiY.IR
 * E-Mail    : M@hdiY.IR
 */

defined( 'ABSPATH' ) || exit;

class PWS_Order {

	public static function get_weight( WC_Order $order ): float {

		$weight = $order->get_meta( 'tapin_weight' );

		if ( empty( $weight ) ) {

			$weight = floatval( PWS()->get_option( 'tools.package_weight', 500 ) );

			foreach ( $order->get_items() as $order_item ) {

				/** @var WC_Product $product */
				$product = $order_item->get_product();

				if ( is_bool( $product ) || $product->is_virtual() ) {
					continue;
				}

				$weight += PWS_Product::get_weight( $product ) * $order_item->get_quantity();
			}

		}

		return apply_filters( 'pws_order_weight', $weight, $order );
	}

	public static function get_shipping_method( WC_Order $order, $label = false ) {

		$shipping_method = null;

		foreach ( $order->get_shipping_methods() as $shipping_item ) {
			if ( str_contains( $shipping_item->get_method_id(), 'Tapin_Pishtaz_Method' ) ) {
				$shipping_method = 1;
			}

			if ( str_contains( $shipping_item->get_method_id(), 'Tapin_Special_Method' ) ) {
				$shipping_method = 3;
			}

			if ( str_contains( $shipping_item->get_method_id(), 'Tapin_Tipax_Method' ) ) {
				$shipping_method = 'tipax';
			}

			if ( str_contains( $shipping_item->get_method_id(), 'Tapin_Alonomic_Method' ) ) {
				$shipping_method = 'alonomic';
			}
		}

		$labels = [
			0          => 'سفارشی',
			1          => 'پیشتاز',
			3          => 'ویژه',
			'tipax'    => 'تیپاکس',
			'alonomic' => 'الونومیک',
		];

		if ( $label ) {
			return $labels[ $shipping_method ] ?? null;
		}

		return $shipping_method;
	}

	public static function get_shipping_payment_type( WC_Order $order ): string {

		$payment_type = 'prepaid';

		foreach ( $order->get_shipping_methods() as $shipping_item ) {

			if ( $shipping_item->get_meta( 'payment_type' ) == 'postpaid' ) {
				$payment_type = 'postpaid';
			}

		}

		return $payment_type;
	}

	public static function get_content_type( WC_Order $order ) {

		$content_type = $order->get_meta( 'tapin_content_type' );

		if ( empty( $content_type ) ) {
			$content_type = PWS()->get_option( 'tapin.content_type', 1 );
		}

		return $content_type;
	}

	public static function get_box_size( WC_Order $order ) {

		$box_size = $order->get_meta( 'tapin_box_size' );

		if ( empty( $box_size ) ) {
			$box_size = PWS()->get_option( 'tapin.box_size', 1 );
		}

		return $box_size;
	}

	public static function tapin_post_products( WC_order $order, $default_product_title = null ): array {

		$products = [];

		foreach ( $order->get_items() as $order_item ) {

			/** @var WC_Product $product */
			$product = $order_item->get_product();

			if ( $product && $product->is_virtual() ) {
				continue;
			}

			$price = ( $order_item->get_total() + $order_item->get_total_tax() ) / $order_item->get_quantity();
			$price = ceil( $price );

			$price = PWS()->convert_currency_to_IRR( $price );

			$title = trim( $default_product_title );

			if ( empty( $title ) ) {
				$title = $order_item->get_name();

				foreach ( $order_item->get_formatted_meta_data() as $meta_data ) {
					$title .= ' | ' . strip_tags( $meta_data->display_value );
				}
			}

			if ( function_exists( 'mb_substr' ) ) {
				$title = mb_substr( $title, 0, 50 );
			}

			$products[] = [
				'count'      => $order_item->get_quantity(),
				'discount'   => 0,
				'price'      => intval( $price ),
				'title'      => $title,
				'weight'     => 0,
				'product_id' => null,
			];
		}

		return $products;
	}

	public static function tapin_tipax_products( WC_order $order, $default_product_title = null ): array {
		return self::tapin_v4_products( $order, $default_product_title );
	}

	public static function tapin_v4_products( WC_order $order, $default_product_title = null ): array {

		$products = [];

		foreach ( $order->get_items() as $order_item ) {

			/** @var WC_Product $product */
			$product = $order_item->get_product();

			if ( $product && $product->is_virtual() ) {
				continue;
			}

			$price = ( $order_item->get_total() + $order_item->get_total_tax() ) / $order_item->get_quantity();
			$price = ceil( $price );

			$price = PWS()->convert_currency_to_IRR( $price );

			$title = trim( $default_product_title );

			if ( empty( $title ) ) {
				$title = $order_item->get_name();

				foreach ( $order_item->get_formatted_meta_data() as $meta_data ) {
					$title .= ' | ' . strip_tags( $meta_data->display_value );
				}
			}

			if ( function_exists( 'mb_substr' ) ) {
				$title = mb_substr( $title, 0, 50 );
			}

			$products[] = [
				'count'              => $order_item->get_quantity(),
				'discount_per_count' => 0,
				'amount_per_count'   => intval( $price ),
				'title'              => $title,
				'weight_per_count'   => 0,
			];
		}

		return $products;
	}
}
