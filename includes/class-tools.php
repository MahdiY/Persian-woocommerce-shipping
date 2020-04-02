<?php
/**
 * Developer : MahdiY
 * Web Site  : MahdiY.IR
 * E-Mail    : M@hdiY.IR
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class PWS_Tools {

	public function __construct() {
		add_filter( 'PW_Tools_tabs', [ $this, 'tabs' ] );
		add_filter( 'PW_Tools_settings', [ $this, 'tools' ] );

		if ( PWS()->get_options( 'pws_tapin_enable_credit' ) == 'yes' ) {
			add_action( 'admin_bar_menu', [ $this, 'admin_bar_menu' ], 999 );
		}

		if ( PWS()->get_options( 'pws_hide_when_free' ) == 'yes' ) {
			add_filter( 'woocommerce_package_rates', [ $this, 'hide_when_free' ], 100 );
		}

		if ( PWS()->get_options( 'pws_hide_when_courier' ) == 'yes' ) {
			add_filter( 'woocommerce_package_rates', [ $this, 'hide_when_courier' ], 100 );
		}
	}

	public function tabs( $tabs ) {

		$tabs['pws'] = 'حمل و نقل';

		return $tabs;
	}

	public function tools( $tools ) {

		$tools['pws'] = [
			[
				'title' => 'تنظیمات افزونه حمل و نقل',
				'type'  => 'title',
				'id'    => 'pws_options'
			],
			[
				'title'   => 'وضعیت سفارشات کمکی',
				'id'      => 'PW_Options[pws_status_enable]',
				'default' => 'no',
				'type'    => 'checkbox',
				'css'     => 'width: 350px;',
				'desc'    => 'جهت مدیریت بهتر سفارشات فروشگاه، وضعیت های زیر به پنل اضافه خواهد شد.
			<ol>
				<li>ارسال شده به انبار</li>
				<li>بسته بندی شده</li>
				<li>تحویل پیک</li>
			</ol>
			'
			],
			[
				'title'   => 'فقط روش ارسال رایگان',
				'id'      => 'PW_Options[pws_hide_when_free]',
				'default' => 'no',
				'type'    => 'checkbox',
				'css'     => 'width: 350px;',
				'desc'    => 'در صورتی که یک روش ارسال رایگان در دسترس باشد، بقیه روش های ارسال مخفی می شوند.'
			],
			[
				'title'   => 'فقط روش ارسال پیک موتوری',
				'id'      => 'PW_Options[pws_hide_when_courier]',
				'default' => 'no',
				'type'    => 'checkbox',
				'css'     => 'width: 350px;',
				'desc'    => 'در صورتی که پیک موتوری برای کاربر در دسترس باشد، بقیه روش های ارسال مخفی می شوند.'
			],
			[
				'type' => 'sectionend',
				'id'   => 'pws_options'
			],

			[
				'title' => 'پیشخوان مجازی تاپین',
				'type'  => 'title',
				'id'    => 'tapin_options'
			],
			[
				'title'   => 'فعالسازی تاپین',
				'id'      => 'PW_Options[pws_tapin_enable]',
				'default' => 'no',
				'type'    => 'checkbox',
				'css'     => 'width: 350px;',
				'desc'    => 'در صورت فعالسازی پیشخوان مجازی تاپین، لیست استان ها و شهرها از وب سرویس های این سامانه بارگذاری می شود.',
			],
			[
				'title'   => 'نمایش اعتبار تاپین',
				'id'      => 'PW_Options[pws_tapin_enable_credit]',
				'default' => 'no',
				'type'    => 'checkbox',
				'css'     => 'width: 350px;',
				'desc'    => 'اعتبار پنل تاپین در منو بالا مدیریت نمایش داده می شود.',
			],
			[
				'title'   => 'درگاه پست',
				'id'      => 'PW_Options[pws_tapin_gateway]',
				'default' => 'tapin',
				'type'    => 'select',
				'css'     => 'width: 350px;',
				'desc'    => 'در صورتی که فروشگاه کتاب است، پست کتاب و در غیر اینصورت پست تاپین را انتخاب کنید.',
				'options' => [
					'tapin'      => 'پست تاپین',
					'posteketab' => 'پست کتاب',
				],
			],
			[
				'title'   => 'توکن',
				'id'      => 'PW_Options[pws_tapin_token]',
				'default' => '',
				'type'    => 'text',
				'css'     => 'width: 350px;',
				'desc'    => 'توکن خود را از <a href="https://my.tapin.ir/" target="_blank">پیشخوان مجازی تاپین</a> دریافت کنید. آدرس آی.پی شما: ' . $_SERVER['SERVER_ADDR'],
			],
			[
				'title'   => 'شناسه فروشگاه',
				'id'      => 'PW_Options[pws_tapin_shop_id]',
				'default' => '',
				'type'    => 'text',
				'css'     => 'width: 350px;',
			],
			[
				'type' => 'sectionend',
				'id'   => 'tapin_options'
			],

		];

		return $tools;
	}

	public function admin_bar_menu( $wp_admin_bar ) {

		if ( ! PWS_Tapin::is_enable() ) {
			return false;
		}

		$credit = get_transient( 'pws_tapin_credit' );

		if ( $credit === false ) {

			PWS_Tapin::set_gateway( PW()->get_options( 'pws_tapin_gateway' ) );

			$credit = PWS_Tapin::request( 'v2/public/transaction/credit/', [
				'shop_id' => PWS()->get_options( 'pws_tapin_shop_id' )
			] );

			if ( is_wp_error( $credit ) ) {
				return false;
			}

			$credit = PWS()->convert_currency( $credit->entries->credit ?? 0 );

			set_transient( 'pws_tapin_credit', $credit, MINUTE_IN_SECONDS * 2 );
		}

		$args = array(
			'id'    => 'tapin_charge',
			'title' => "اعتبار تاپین: " . wc_price( $credit ),
			'meta'  => array( 'class' => 'tapin' )
		);

		$wp_admin_bar->add_node( $args );
	}

	public function hide_when_free( $rates ) {
		$free = array(); // snippets.ir

		foreach ( $rates as $rate_id => $rate ) {
			if ( 0 == $rate->cost ) {
				$free[ $rate_id ] = $rate;
				break;
			}
		}

		return ! empty( $free ) ? $free : $rates;
	}

	public function hide_when_courier( $rates ) {
		$courier = array(); // snippets.ir

		foreach ( $rates as $rate_id => $rate ) {
			if ( 'WC_Courier_Method' === $rate->method_id ) {
				$courier[ $rate_id ] = $rate;
				break;
			}
		}

		return ! empty( $courier ) ? $courier : $rates;
	}
}

add_action( 'init', function() {
	new PWS_Tools();
} );
