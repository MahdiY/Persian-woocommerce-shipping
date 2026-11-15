<?php
/**
 * Developer : MahdiY
 * Web Site  : MahdiY.IR
 * E-Mail    : M@hdiY.IR
 */

class PWS_Settings_SMS extends PWS_Settings {

	protected static $_instance = null;

	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function get_sections(): array {
		return [
			[
				'id'    => 'pws_sms_config',
				'title' => 'پیکربندی',
			],
			[
				'id'    => 'pws_sms_order',
				'title' => 'وضعیت سفارشات',
			],
			[
				'id'    => 'pws_sms_event',
				'title' => 'رویدادهای خاص',
			],
		];
	}

	public function get_fields(): array {

		$order_statuses = wc_get_order_statuses();

		$pws_sms_order = [
			[
				'name' => 'help',
				'desc' => '
لطفا به نکات زیر توجه نمایید:<ol>
								<li>برای ارسال نکردن پیامک در وضعیت خاص، روبروی آن را خالی بگذارید.</li>
								<li>بجای CODE، کد متن دریافت شده از پنل پیامک را قرار دهید.</li>
								<li>برای ثبت پترن جدید و دریافت کد متن، <a href="https://l.nabik.net/melipayamak-pattern?utm_source=pws" target="_blank">اینجا</a> کلیک کنید.</li>
								</ol>',
				'type' => 'html',
			],
			[
				'name' => 'shortcode',
				'desc' => '
می توانید از متغیرهای زیر استفاده کنید:<ul>
								<li><strong>{order}</strong> شماره سفارش</li>
								<li><strong>{first_name}</strong> نام مشتری</li>
								<li><strong>{last_name}</strong> نام خانوادگی مشتری</li>
								<li><strong>{barcode}</strong> بارکد پستی</li>
								<li><strong>{total}</strong> مبلغ کل سفارش</li>
								</ul>',
				'type' => 'html',
			],
		];

		$suggest_text = [
			'wc-processing'   => [
				'desc'    => 'مشتری گرامی، سفارش {0} ثبت و در حال پردازش است.',
				'default' => 'CODE;{order}',
			],
			'wc-pws-courier'  => [
				'desc'    => 'مشتری گرامی، سفارش {0} تحویل پیک شد.',
				'default' => 'CODE;{order}',
			],
			'wc-pws-packaged' => [
				'desc'    => 'مشتری گرامی، سفارش {0} بسته بندی و آماده ارسال شد.',
				'default' => 'CODE;{order}',
			],
			'wc-refunded'     => [
				'desc'    => 'مشتری گرامی، سفارش {0} مسترد شد.',
				'default' => 'CODE;{order}',
			],
			'wc-cancelled'    => [
				'desc'    => 'مشتری گرامی، سفارش {0} لغو شد.',
				'default' => 'CODE;{order}',
			],
			'wc-pws-in-stock' => [
				'desc'    => 'مشتری گرامی، سفارش {0} در حال آماده سازی در انبار می باشد.',
				'default' => 'CODE;{order}',
			],
		];

		foreach ( $order_statuses as $key => $order_status ) {
			$pws_sms_order[] = [
				'name'              => $key,
				'label'             => $order_status,
				'desc'              => isset( $suggest_text[ $key ] ) ? 'متن پیشنهادی:<br>' . get_bloginfo( 'name' ) . '<br>' . $suggest_text[ $key ]['desc'] : '',
				'type'              => 'text',
				'default'           => isset( $suggest_text[ $key ] ) ? $suggest_text[ $key ]['default'] : '',
				'placeholder'       => 'Example: CODE;{order}',
				'sanitize_callback' => 'sanitize_text_field',
				'field_class'       => 'ltr',
			];
		}

		return [
			'pws_sms_config' => [
				[
					'name' => 'html',
					'desc' => '<b>ملی پیامک:</b> پنل پیامک حرفه ای و پر سرعت را با کد تخفیف 10 درصدی <b>pws10</b> از <a href="https://l.nabik.net/melipayamak?utm_source=pws10" target="_blank">ملی پیامک</a> تهیه کنید.',
					'type' => 'html',
				],
				[
					'name' => 'tutorial',
					'desc' => '<b>آموزش:</b> آموزش پیکربندی استاندارد پیامک افزونه حمل و نقل را از <a href="https://l.nabik.net/melipayamak-pws" target="_blank">اینجا</a> مشاهده کنید.',
					'type' => 'html',
				],
				[
					'name'  => 'enable',
					'label' => 'فعالسازی ارسال پیامک',
					'desc'  => 'پیامک در وضعیت های تنظیم شده برای مشتری ارسال می شود.',
					'type'  => 'checkbox',
				],
//				[
//					'name'  => 'credit',
//					'label' => 'نمایش اعتبار پیامک',
//					'desc'  => 'اعتبار پنل پیامک در منو بالا مدیریت نمایش داده می شود.',
//					'type'  => 'checkbox'
//				],
				[
					'name'              => 'username',
					'label'             => 'نام کاربری',
					'desc'              => 'نام کاربری پنل پیامک <a href="https://l.nabik.net/melipayamak?utm_source=pws" target="_blank">ملی پیامک</a>',
					'type'              => 'text',
					'default'           => '',
					'sanitize_callback' => 'sanitize_text_field',
				],
				[
					'name'              => 'password',
					'label'             => 'کلمه عبور',
					'desc'              => '',
					'type'              => 'password',
					'default'           => '',
					'sanitize_callback' => 'sanitize_text_field',
				],
			],
			'pws_sms_order'  => $pws_sms_order,
			'pws_sms_event'  => [
				[
					'name'              => 'barcode',
					'label'             => 'ثبت بارکد پستی سفارش',
					'desc'              => 'متن پیشنهادی:<br>' . get_bloginfo( 'name' ) . '<br>مشتری گرامی، سفارش {0} به اداره پست تحویل داده شد.
<br>شما می توانید کد مرسوله {1} را از طریق لینک https://radgir.net رهگیری نمایید.',
					'type'              => 'text',
					'default'           => 'CODE;{order};{barcode}',
					'placeholder'       => 'Example: CODE;{order};{barcode}',
					'sanitize_callback' => 'sanitize_text_field',
					'field_class'       => 'ltr',
				],
			],
		];
	}

	public static function output() {

		$instance = self::instance();

		echo '<div class="wrap">';

		$instance->show_navigation();
		$instance->show_forms();

		echo '</div>';
	}
}
