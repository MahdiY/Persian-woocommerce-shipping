<?php
/**
 * Developer : MahdiY
 * Web Site  : MahdiY.IR
 * E-Mail    : M@hdiY.IR
 */

class PWS_Settings_Tools extends PWS_Settings {

	protected static $_instance = null;

	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function get_sections(): array {
		return apply_filters( 'pws_settings_sections', [
			[
				'id'    => 'pws_tools',
				'title' => 'ابزارهای کاربردی',
			],
			[
				'id'    => 'pws_map',
				'title' => 'نقشه',
			],
		] );
	}

	public function get_fields(): array {

		$has_pro = defined( 'PWS_PRO_VERSION' );

		$all_shipping_methods = [];

		if ( ( $_GET['page'] ?? null ) == 'pws-tools' ) {
			$all_shipping_methods = PWS_Map::get_all_shipping_methods();
		}

		return apply_filters( 'pws_settings_fields', [
			'pws_tools' => [
				[
					'name' => 'html',
					'desc' => '<b>آموزش:</b> برای پیکربندی حمل و نقل می توانید از <a href="https://l.nabik.net/pws-video" target="_blank">اینجا</a> فیلم های آموزشی افزونه را مشاهده کنید.',
					'type' => 'html',
				],
				[
					'label'   => 'وضعیت سفارشات کمکی',
					'name'    => 'status_enable',
					'default' => 1,
					'type'    => 'checkbox',
					'desc'    => 'جهت مدیریت بهتر سفارشات فروشگاه، وضعیت های زیر به پنل اضافه خواهد شد.
					<ol>
						<li>ارسال شده به انبار</li>
						<li>بسته بندی شده</li>
						<li>تحویل پیک</li>
						<li>تحویل پست</li>
						<li>تحویل تیپاکس</li>
					</ol>
					',
				],
				[
					'label'   => 'فقط روش ارسال رایگان',
					'name'    => 'hide_when_free',
					'default' => '0',
					'type'    => 'checkbox',
					'desc'    => 'در صورتی که یک روش ارسال رایگان در دسترس باشد، بقیه روش های ارسال مخفی می شوند.',
				],
				[
					'label'   => 'فقط روش ارسال پیک موتوری',
					'name'    => 'hide_when_courier',
					'default' => '0',
					'type'    => 'checkbox',
					'desc'    => 'در صورتی که پیک موتوری برای کاربر در دسترس باشد، بقیه روش های ارسال مخفی می شوند.',
				],
				[
					'label'   => 'وزن پیشفرض هر محصول',
					'name'    => 'product_weight',
					'default' => 0,
					'type'    => 'number',
					'desc'    => "در صورتی که برای محصول وزنی وارد نشده بود، بصورت پیشفرض وزن محصول چند گرم در نظر گرفته شود؟",
				],
				[
					'label'   => 'وزن بسته بندی',
					'name'    => 'package_weight',
					'default' => 0,
					'type'    => 'number',
					'desc'    => "بطور میانگین وزن بسته بندی ها چند گرم در نظر گرفته شود؟",
				],
				[
					'label'   => 'محدودیت وزنی ارسال پستی',
					'name'    => 'post_weight_limit',
					'default' => 30000,
					'type'    => 'number',
					'desc'    => "روش‌های ارسال پستی، برای سبدهای خرید با وزن بالای این مقدار (به گرم) مخفی خواهند شد. (اداره پست بسته‌های بالای ۳۰،۰۰۰ گرم ارسال نمی‌کند)",
				],
				[
					'label'   => 'جابه‌جایی فیلد استان و شهر',
					'name'    => 'swap_state_city_field',
					'default' => 0,
					'type'    => 'checkbox',
					'desc'    => 'در صفحه‌ی تسویه حساب، فیلد استان بالای فیلد شهر قرار می‌گیرد. ' . ( $has_pro ? '' : '(این امکان فقط در <a href="' . PWS()->pws_pro_url( 'swap_state_city_field' ) . '" target="_blank">نسخه حرفه‌ای</a> فعال می‌باشد)' ),
				],
				[
					'label'   => 'حذف حمل و نقل پیشفرض',
					'name'    => 'remove_chosen_shipping_method',
					'default' => 0,
					'type'    => 'checkbox',
					'desc'    => 'به صورت پیشفرض هیچ یک از روش‌های حمل و نقل انتخاب (فعال) نخواهند شد. ' . ( $has_pro ? '' : '(این امکان فقط در <a href="' . PWS()->pws_pro_url( 'remove_chosen_shipping_method' ) . '" target="_blank">نسخه حرفه‌ای</a> فعال می‌باشد)' ),
				],
			],

			/**
			 * Map Settings
			 * @since 4.0.4
			 */
			'pws_map'   => [
				[
					'name' => 'pws_map_description',
					'type' => 'html',
					'desc' => '<p>نقشه را در صفحه تسویه حساب و یا با استفاده از کد کوتاه [pws_map]، در صفحات دلخواه نمایش دهید.</p>',
					'id'   => 'pws_map_description',
				],
				[
					'label'   => 'سرویس نمایش نقشه',
					'name'    => 'provider',
					'type'    => 'select',
					'default' => 'OSM',
					'options' => [
						'OSM'    => 'OpenStreetMap.org',
						'neshan' => 'Neshan.org (نشان)',
						'mapp'   => 'Map.ir (مپ)',
					],
					'desc'    => '<p class="pws-map__info-OSM">OpenStreetMap یک سرویس جهانی است با قابلیت‌های متنوع که برای نمایش نقشه و ارائه داده‌های جغرافیایی به‌صورت آزاد و قابل دسترس برای همه طراحی شده است.</p>' . '<p class="pws-map__info-neshan">نشان یک سرویس ایرانی با قابلیت‌های متنوع است که برای نمایش نقشه و ارائه خدمات جغرافیایی طراحی شده است.</p>' . '<p class="pws-map__info-mapp">زیرساخت نقشهٔ مپ یک پلتفرم قدرتمند است که نتیجهٔ ۲۰ سال فعالیت در حوزهٔ GIS است و برای ارائه بهترین سرویس‌های نقشه با تمرکز بر نیازهای محلی کسب‌وکارهای مکان-مبنا توسعه یافته.</p>',

				],

				/*Neshan api keys*/ [
					'label'   => ' کلید دسترسی نشان',
					'name'    => 'neshan_api_key',
					'default' => null,
					'type'    => 'text',
					'desc'    => '<p class="pws-map__help-neshan">&nbsp;لطفا برای دریافت کلید دسترسی نمایش نقشه پلتفرم نشان&nbsp;<a href="https://platform.neshan.org/panel/api-key">اینجا کلیک کنید.</a> برای مثال: web.706963....</p>',
				],
				/*Mapp (map.ir) api key*/ [
					'label'   => 'کلید دسترسی مپ',
					'name'    => 'mapp_api_key',
					'default' => null,
					'type'    => 'text',
					'desc'    => '<p class="pws-map__help-mapp">&nbsp;لطفا برای دریافت کلید دسترسی نمایش نقشه پلتفرم مپ&nbsp;<a href="https://accounts.map.ir/token-details">اینجا کلیک کنید.</a> برای مثال: ojWHertr2BCHtKXHQZL_YWwEgMw...</p>',
				],

				/*OpenRouteService Token*/ [
					'label'   => 'کلید دسترسی OpenRouteService',
					'name'    => 'ORS_token',
					'type'    => 'text',
					'default' => '',
					'desc'    => 'این سرویس دهنده برای محاسبه فاصله و مسیریابی بین فروشگاه و کاربر مورد استفاده قرار می‌گیرد.<br>برای دریافت کلید دسترسی <a href="https://openrouteservice.org/dev/#/home">اینجا</a> کلیک کنید. برای مثال: 5b3ce3597851110001cf6248836ed90b1...',
				],

				/*Neshan Map Type*/ [
					'label'   => 'نوع نقشه',
					'name'    => 'neshan_type',
					'type'    => 'select',
					'default' => 'neshan',
					'options' => [
						'osm-bright'     => 'روشن',
						'standard-night' => 'شب',
						'standard-day'   => 'روز',
						'neshan'         => 'نشان',
						'dreamy-gold'    => 'طلایی',
						'dreamy'         => 'رویایی',
					],
				],
				[
					'label'   => 'محل قرارگیری',
					'name'    => 'checkout_placement',
					'type'    => 'select',
					'default' => 'none',
					'options' => [
						'none'        => 'عدم نمایش خودکار در فرم تسویه حساب',
						'after_form'  => 'بعد از فرم تسویه حساب',
						'before_form' => 'قبل از فرم تسویه حساب',
					],
					'desc'    => 'جایگاه نمایش (یا عدم نمایش) خودکار نقشه در فرم تسویه حساب کاربر را تعیین کنید.<br>در صورت غیرفعال سازی نمایش خودکار، همچنان کد کوتاه [pws_map] کار می‌کند.',
				],
				[
					'label'   => 'الزام به انتخاب موقعیت',
					'name'    => 'required_location',
					'default' => 1,
					'type'    => 'checkbox',
					'desc'    => 'در صورت فعال بودن این قابلیت، کاربران موظف به انتخاب مکان خود روی نقشه هستند.',
				],
				[
					'label'   => 'نمایش در روش حمل و نقل',
					'name'    => 'shipping_methods',
					'default' => '',
					'type'    => 'multiselect',
					'options' => $all_shipping_methods,
					'desc'    => 'عدم انتخاب روش حمل و نقل به منزله نمایش نقشه در تمامی روش ها می‌باشد.',
				],
				[
					'label'   => 'نمایش فروشگاه',
					'name'    => 'store_marker_enable',
					'default' => '0',
					'type'    => 'checkbox',
					'desc'    => 'برای نمایش محل فروشگاه روی نقشه جهت نمایش به کاربر، فعال کنید.',
				],
				[
					'label' => 'مکان فروشگاه',
					'name'  => 'store_location',
					'type'  => 'map',
					'desc'  => 'مکان جغرافیایی فروشگاه خود را روی نقشه انتخاب کنید تا در محاسبات و سرویس های نقشه مورد استفاده قرار گیرد.',
				],
				[
					'label'   => 'محاسبه فاصله',
					'name'    => 'store_calculate_distance',
					'type'    => 'select',
					'default' => 'none',
					'options' => [
						'none'   => 'محاسبه نکن',
						'direct' => 'شعاعی (سریع‌تر)',
						'real'   => 'مسیریابی (دقیق‌تر)',
					],
					'desc'    => 'با نمایش فاصله فروشگاه تا کاربر؛ اطلاعات بیشتری در رابطه با سفارش برای مدیر سایت نمایش دهید.',
				],
			],
		] );
	}

	public static function output() {

		$instance = self::instance();

		echo '<div class="wrap">';

		$instance->show_navigation();
		$instance->show_forms();

		echo '</div>';
	}
}