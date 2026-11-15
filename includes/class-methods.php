<?php

defined( 'ABSPATH' ) || exit;

class PWS_Methods {

	public function __construct() {
		add_filter( 'admin_head', [ $this, 'admin_head' ] );
		add_filter( 'woocommerce_shipping_method_add_rate', [ $this, 'method_args' ], 10, 3 );
		add_filter( 'woocommerce_order_item_get_formatted_meta_data', [ $this, 'add_payment_type' ], 10, 2 );
	}

	public function admin_head() {

		$page = $_GET['page'] ?? null;
		$tab  = $_GET['tab'] ?? null;

		if ( $page != 'wc-settings' || $tab != 'shipping' ) {
			return;
		}

		wp_add_inline_script( 'wc-backbone-modal', "
            jQuery(document.body).on('wc_backbone_modal_loaded', () => {
                jQuery('#woocommerce_WC_Courier_Method_delivery_areas').select2();
            });
		" );
	}

	public function method_args( WC_Shipping_Rate $rate, array $args, WC_Shipping_Method $method ): WC_Shipping_Rate {

		if ( ! is_a( $method, 'PWS_Shipping_Method' ) || is_a( $method, 'PWS_Dokan_Method' ) ) {
			return $rate;
		}

		$meta_data = $rate->get_meta_data();

		$payment_type = trim( strval( $meta_data['payment_type'] ?? 'prepaid' ) );
		$rate->add_meta_data( 'payment_type', $payment_type );

		return $rate;
	}

	public function add_payment_type( array $formatted_meta, WC_Order_Item $item ): array {

		if ( ! is_a( $item, WC_Order_Item_Shipping::class ) ) {
			return $formatted_meta;
		}

		$method_id = $item->get_method_id();

		if ( ! is_a( $method_id, WC_Shipping_Method::class, true ) ) {
			return $formatted_meta;
		}

		/** @var WC_Shipping_Method $method */
		$method = new $method_id();

		if ( ! $method->supports( 'postpaid' ) ) {
			return $formatted_meta;
		}

		foreach ( $formatted_meta as &$value ) {

			if ( $value->key == 'payment_type' ) {

				$value->display_key = 'نوع پرداخت';

				$value->display_value = str_replace(
					[
						'prepaid',
						'postpaid',
					],
					[
						'پرداخت آنلاین',
						'پس کرایه',
					],
					$value->display_value
				);

				return $formatted_meta;
			}

		}

		$payment_type = $item->get_meta( 'payment_type' ) == 'postpaid' ? 'postpaid' : 'prepaid';

		$display_value = str_replace(
			[
				'prepaid',
				'postpaid',
			],
			[
				'پرداخت آنلاین',
				'پس کرایه',
			],
			$payment_type
		);

		$formatted_meta[] = (object) [
			'key'           => 'payment_type',
			'value'         => $payment_type,
			'display_key'   => 'نوع پرداخت',
			'display_value' => $display_value,
		];

		return $formatted_meta;
	}
}

new PWS_Methods();