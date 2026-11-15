<?php
/**
 * Developer : MahdiY
 * Web Site  : MahdiY.IR
 * E-Mail    : M@hdiY.IR
 */

defined( 'ABSPATH' ) || exit;

class PWS_SMS {

	public function __construct() {

		if ( ! PWS()::get_option( 'sms_config.enable' ) ) {
			return false;
		}

		add_action( 'woocommerce_order_status_changed', [ $this, 'order_status_changed' ], 100, 3 );
		add_action( 'pws_save_order_post_barcode', [ $this, 'save_order_post_barcode' ], 100, 2 );

		return true;
	}

	public function order_status_changed( $order_id, $old_status = '', $new_status = 'created' ) {

		if ( current_action() == 'woocommerce_process_shop_order_meta' ) {
			if ( ! is_admin() ) {
				return false;
			}
		}

		if ( ! $order_id ) {
			return false;
		}

		$data = PWS()::get_option( 'sms_order.wc-' . $new_status );

		if ( empty( $data ) ) {
			return false;
		}

		$order = new WC_Order( $order_id );
		$phone = $order->get_billing_phone();

		$sms = self::send( $order, $data );

		if ( $sms === true ) {
			$note = sprintf( 'پیامک "%s" با موفقیت به مشتری با شماره %s ارسال گردید.', wc_get_order_status_name( $new_status ), $phone );
		} else {
			$note = sprintf( 'پیامک "%s" بخاطر خطا به مشتری با شماره %s ارسال نشد.<br>پاسخ وبسرویس: %s', wc_get_order_status_name( $new_status ), $phone, $sms );
		}

		$order->add_order_note( $note );
	}

	public function save_order_post_barcode( WC_Order $order, $barcode ) {

		$data = PWS()::get_option( 'sms_event.barcode' );

		if ( empty( $data ) ) {
			return false;
		}

		$phone = $order->get_billing_phone();

		$sms = self::send( $order, $data );

		if ( $sms === true ) {
			$note = sprintf( 'پیامک "بارکد پستی" با موفقیت به مشتری با شماره %s ارسال گردید.', $phone );
		} else {
			$note = sprintf( 'پیامک "بارکد پستی" بخاطر خطا به مشتری با شماره %s ارسال نشد.<br>پاسخ وبسرویس: %s', $phone, $sms );
		}

		$order->add_order_note( $note );
	}

	public static function send( WC_Order $order, $message ) {

		$phone = $order->get_billing_phone();

		$data = explode( ';', $message );
		$code = $data[0];
		array_shift( $data );

		$param["username"] = PWS()::get_option( 'sms_config.username' );
		$param["password"] = PWS()::get_option( 'sms_config.password' );
		$param["text"]     = self::tags( $order, implode( ';', $data ) );
		$param["to"]       = self::sanitize_phone( $phone );
		$param["bodyId"]   = $code;

		$sms = wp_remote_post( 'http://rest.payamak-panel.com/api/SendSMS/BaseServiceNumber', [
			'body'    => $param,
			'headers' => [
				'content-type'  => 'application/x-www-form-urlencoded',
				'cache-control' => 'no-cache',
			],
		] );

		if ( is_wp_error( $sms ) ) {
			return 'خطا در برقراری ارتباط';
		}

		$sms = json_decode( wp_remote_retrieve_body( $sms ), true );

		if ( isset( $sms['Message'] ) ) {
			return $sms['Message'];
		}

		return $sms['RetStatus'] == 1 ? true : $sms['Value'];
	}

	public static function sanitize_phone( $phone ) {
		return str_replace( [
			'+98',
			'۰',
			'۱',
			'۲',
			'۳',
			'۴',
			'۵',
			'۶',
			'۷',
			'۸',
			'۹',
			'٠',
			'١',
			'٢',
			'٣',
			'٤',
			'٥',
			'٦',
			'٧',
			'٨',
			'٩',
		], [ 0, 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 0, 1, 2, 3, 4, 5, 6, 7, 8, 9 ], $phone );
	}

	public static function tags( WC_Order $order, $data ) {

		$tags = [
			'{order}'      => $order->get_id(),
			'{first_name}' => $order->get_billing_first_name(),
			'{last_name}'  => $order->get_billing_last_name(),
			'{barcode}'    => $order->get_meta( 'post_barcode' ),
			'{total}'      => $order->get_total(),
		];

		$tags = apply_filters( 'pws_sms_tags', $tags, $order, $data );

		return str_replace( array_keys( $tags ), array_values( $tags ), $data );
	}

}

new PWS_SMS();
