<?php
/**
 * Developer : MahdiY
 * Web Site  : MahdiY.IR
 * E-Mail    : M@hdiY.IR
 */

defined( 'ABSPATH' ) || exit;

class PWS_Notice {

	public function __construct() {
		add_action( 'admin_notices', [ $this, 'admin_notices' ], 5 );
		add_action( 'wp_ajax_pws_dismiss_notice', [ $this, 'dismiss_notice' ] );
		add_action( 'admin_init', [ $this, 'schedule_update_notice_cron' ] );
		add_action( 'hourly_update_notice', [ $this, 'update_notice' ] );
	}

	public function admin_notices() {

		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		if ( $this->is_dismiss( 'all' ) ) {
			return;
		}

		foreach ( $this->notices() as $notice ) {

			if ( $notice['condition'] == false || $this->is_dismiss( $notice['id'] ) ) {
				continue;
			}

			$dismissible    = $notice['dismiss'] ? 'is-dismissible' : '';
			$notice_id      = esc_attr( $notice['id'] );
			$notice_content = strip_tags( $notice['content'], '<p><a><b><img><ul><ol><li><input>' );
			$notice_type    = esc_attr( $notice['type'] ?? 'success' );

			printf( '<div class="notice pws_notice notice-%s %s" id="pws_%s"><p>%s</p></div>', $notice_type, $dismissible, $notice_id, $notice_content );

			break;
		}

		?>
		<script type="text/javascript">
            jQuery(document).ready(function ($) {

                jQuery(document.body).on('click', '.notice-dismiss', function () {

                    let notice = jQuery(this).closest('.pws_notice');
                    notice = notice.attr('id');

                    if (notice !== undefined && notice.indexOf('pws_') !== -1) {

                        notice = notice.replace('pws_', '');

                        jQuery.ajax({
                            url: "<?php echo admin_url( 'admin-ajax.php' ) ?>",
                            type: 'post',
                            data: {
                                notice: notice,
                                action: 'pws_dismiss_notice',
                                nonce: "<?php echo wp_create_nonce( 'pws_dismiss_notice' ); ?>"
                            }
                        });
                    }

                });

            });
		</script>
		<?php
	}

	public function notices(): array {
		global $pagenow;

		$page = sanitize_text_field( $_GET['page'] ?? null );
		$tab  = sanitize_text_field( $_GET['tab'] ?? null );

		$gateland_install_url = self::get_plugin_action_url( 'gateland/gateland.php' );

		$has_pws_pro = is_plugin_active( 'persian-woocommerce-shipping-pro/pws-pro.php' );

		$notices = [
			[
				'id'        => 'yith_alarm',
				'content'   => sprintf( '<b>هشدار:</b> شما در حال استفاده از افزونه Yith woocommerce checkout manager هستید. این افزونه دارای گزارش‌های اختلال بسیار زیادی است. از <a href="%s" target="_blank">اینجا</a> پیشنهاد جایگزین رایگان و قدرتمند آن را بخوانید.', 'https://t.me/nabik_net/210' ),
				'condition' => is_plugin_active( 'yith-woocommerce-checkout-manager/init.php' ),
				'dismiss'   => WEEK_IN_SECONDS,
			],
			[
				'id'        => 'bale_channel',
				'content'   => sprintf( '<b>کانال بله:</b> تا اطلاع ثانوی و فعالسازی مجدد کانال نابیک در تلگرام، می‌توانید ما را در پیام‌رسان بله دنبال کنید | <a href="%s" target="_blank">کانال نابیک در پیام‌رسان بله</a>', 'https://ble.ir/nabik_net' ),
				'condition' => true,
				'dismiss'   => 6 * MONTH_IN_SECONDS,
			],
			[
				'id'        => 'dokan_shipping',
				'content'   => sprintf( '<b>حمل و نقل دکان:</b> در صورتی که قصد ایجاد یک مارکت‌پلیس حرفه‌ای و قدرتمند را دارید، می‌توانید با استفاده از <a href="%s" target="_blank">اولین و تنها افزونه حمل و نقل برای دکان</a> سیستم حمل و نقل فروشگاه‌تان را مدیریت کنید.', 'https://l.nabik.net/pws-dokan?utm_source=pws' ),
				'condition' => ( $tab == 'shipping' || $page == 'dokan' ) && is_plugin_active( 'dokan-lite/dokan.php' ) && is_plugin_inactive( 'persian-woocommerce-shipping-dokan/pws-dokan.php' ),
				'dismiss'   => 6 * MONTH_IN_SECONDS,
			],
			[
				'id'        => 'nrr_product_reviews',
				'content'   => sprintf( '<b>نظرسنجی خودکار ووکامرس:</b> جهت افزایش تعداد نظرات فروشگاه‌تان، می‌توانید با استفاده از <a href="%s" target="_blank">افزونه نظرسنجی خودکار ندا</a> با ارسال خودکار پیامک، برای هر سفارش از مشتریان خود درخواست ثبت نظر کنید. | کدتخفیف: pws20', 'https://l.nabik.net/neda?utm_source=pws' ),
				'condition' => $page == 'product-reviews' && is_plugin_inactive( 'nabik-review-reminder/nabik-review-reminder.php' ),
				'dismiss'   => 6 * MONTH_IN_SECONDS,
			],
			[
				'id'        => 'post_rate_1405',
				'content'   => sprintf( '<b>تعرفه پستی ۱۴۰۵:</b> تعرفه‌های اداره پست بروزرسانی شد. جهت بهره‌مندی از تعرفه‌های پستی ۱۴۰۵، می‌توانید <a href="%s" target="_blank">نسخه حرفه‌ای افزونه حمل و نقل</a> را نصب و فعال نمایید. ', PWS()->pws_pro_url( 'post_1405' ) ),
				'condition' => ! $has_pws_pro,
				'dismiss'   => MONTH_IN_SECONDS,
			],
			[
				'id'        => 'pws_pro_zone',
				'content'   => '<b>حمل و نقل حرفه‌ای:</b> براساس شهرها مناطق حمل و نقل تعریف کنید، از نرخ ثابت حرفه‌ای بهره ببرید، برای آن‌ها شرط‌های متنوع و مختلف بگذارید و حمل و نقل فروشگاه‌تان را کاملا مدیریت کنید. <a href="' . PWS()->pws_pro_url( 'zone' ) . '" target="_blank">مشاهده امکانات حمل و نقل حرفه‌ای</a>',
				'condition' => $tab == 'shipping' && ! $has_pws_pro,
				'dismiss'   => 6 * MONTH_IN_SECONDS,
			],
			[
				'id'        => 'pws_video',
				'content'   => '<b>آموزش:</b> برای پیکربندی حمل و نقل می توانید از <a href="https://l.nabik.net/pws-video" target="_blank">اینجا</a> فیلم های آموزشی افزونه را مشاهده کنید.',
				'condition' => count( PWS_City::zones() ) == 1,
				'dismiss'   => 6 * MONTH_IN_SECONDS,
			],
			[
				'id'        => 'gateland',
				'content'   => sprintf( '<b>افزونه درگاه پرداخت هوشمند «گیت لند»:</b> یک افزونه رایگان دیگر از نابیک، تجمیع ۴۱ درگاه پرداخت فقط در یک افزونه! همین حالا میتونی به صورت کاملا رایگان تست کنی: <a href="%s" target="_blank"><input type="button" class="button button-primary" value="نصب سریع و رایگان از مخزن وردپرس"></a>', $gateland_install_url ),
				'condition' => $gateland_install_url,
				'dismiss'   => 2 * MONTH_IN_SECONDS,
			],
		];

		$_notices = get_option( 'pws_notices', [] );

		foreach ( $_notices['notices'] ?? [] as $_notice ) {

			$_notice['condition'] = 1;

			$rules = $_notice['rules'];

			if ( isset( $rules['pagenow'] ) && $rules['pagenow'] != $pagenow ) {
				$_notice['condition'] = 0;
			}

			if ( isset( $rules['page'] ) && $rules['page'] != $page ) {
				$_notice['condition'] = 0;
			}

			if ( isset( $rules['tab'] ) && $rules['tab'] != $tab ) {
				$_notice['condition'] = 0;
			}

			if ( isset( $rules['active'] ) && is_plugin_inactive( $rules['active'] ) ) {
				$_notice['condition'] = 0;
			}

			if ( isset( $rules['inactive'] ) && is_plugin_active( $rules['inactive'] ) ) {
				$_notice['condition'] = 0;
			}

			if ( isset( $rules['tapin'] ) && $rules['tapin'] != PWS_Tapin::is_enable() ) {
				$_notice['condition'] = 0;
			}

			unset( $_notice['rules'] );

			array_unshift( $notices, $_notice );
		}

		return $notices;
	}

	public function dismiss_notice() {

		check_ajax_referer( 'pws_dismiss_notice', 'nonce' );

		$this->set_dismiss( $_POST['notice'] );

		die();
	}

	function schedule_update_notice_cron() {
		if ( ! wp_next_scheduled( 'hourly_update_notice' ) ) {
			wp_schedule_event( time(), 'hourly', 'hourly_update_notice' );
		}
	}

	public function update_notice() {

		$notices = wp_remote_get( 'https://wpnotice.ir/pws.json', [ 'timeout' => 5, ] );
		$sign    = wp_remote_get( 'https://wphash.ir/pws.hash', [ 'timeout' => 5, ] );

		if ( is_wp_error( $notices ) || is_wp_error( $sign ) ) {
			die();
		}

		if ( ! is_array( $notices ) || ! is_array( $sign ) ) {
			die();
		}

		$notices = trim( $notices['body'] );
		$sign    = trim( $sign['body'] );

		if ( sha1( $notices ) !== $sign ) {
			die();
		}

		$notices = json_decode( $notices, JSON_OBJECT_AS_ARRAY );

		if ( empty( $notices ) || ! is_array( $notices ) ) {
			die();
		}

		foreach ( $notices['notices'] as &$_notice ) {

			$doc     = new DOMDocument();
			$content = strip_tags( $_notice['content'], '<p><a><b><img><ul><ol><li>' );
			$content = str_replace( [ 'javascript', 'java', 'script' ], '', $content );
			$doc->loadHTML( mb_convert_encoding( $content, 'HTML-ENTITIES', 'UTF-8' ) );

			foreach ( $doc->getElementsByTagName( '*' ) as $element ) {

				$href  = null;
				$src   = null;
				$style = $element->getAttribute( 'style' );

				if ( $element->nodeName == 'a' ) {
					$href = $element->getAttribute( 'href' );
				}

				if ( $element->nodeName == 'img' ) {
					$src = $element->getAttribute( 'src' );
				}

				foreach ( $element->attributes as $attribute ) {
					$element->removeAttribute( $attribute->name );
				}

				if ( $href && filter_var( $href, FILTER_VALIDATE_URL ) ) {
					$element->setAttribute( 'href', $href );
					$element->setAttribute( 'target', '_blank' );
				}

				if ( $src && filter_var( $src, FILTER_VALIDATE_URL ) && strpos( $src, 'https://repo.nabik.net' ) === 0 ) {
					$element->setAttribute( 'src', $src );
				}

				if ( $style ) {
					$element->setAttribute( 'style', $style );
				}
			}

			$_notice['content'] = $doc->saveHTML();
		}

		update_option( 'pws_notices', $notices );

		die();
	}

	public function set_dismiss( string $notice_id ) {

		$notices = wp_list_pluck( $this->notices(), 'dismiss', 'id' );

		if ( isset( $notices[ $notice_id ] ) && $notices[ $notice_id ] ) {
			update_option( 'pws_dismiss_notice_' . $notice_id, time() + intval( $notices[ $notice_id ] ), 'yes' );
			update_option( 'pws_dismiss_notice_all', time() + DAY_IN_SECONDS );
		}
	}

	public function is_dismiss( $notice_id ): bool {
		return intval( get_option( 'pws_dismiss_notice_' . $notice_id ) ) >= time();
	}

	public static function get_plugin_action_url( $plugin ): ?string {

		if ( is_plugin_active( $plugin ) ) {
			return null;
		}

		if ( ! isset( get_plugins()[ $plugin ] ) ) {

			$plugin = strtok( $plugin, '/' );

			return wp_nonce_url(
				add_query_arg(
					[
						'action' => 'install-plugin',
						'plugin' => $plugin,
					],
					admin_url( 'update.php' )
				),
				'install-plugin_' . $plugin
			);
		}

		return wp_nonce_url(
			admin_url( 'plugins.php?action=activate&plugin=' . $plugin ),
			'activate-plugin_' . $plugin
		);
	}

}

new PWS_Notice();