<?php
/**
 * Developer : MahdiY
 * Web Site  : MahdiY.IR
 * E-Mail    : M@hdiY.IR
 */

class PWS_Admin {

	public function __construct() {

		$this->includes();

		add_action( 'admin_menu', [ $this, 'admin_menu' ], 20 );
		add_action( 'admin_head', [ $this, 'admin_head' ], 20 );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts' ], 20 );

		add_filter( 'parent_file', [ $this, 'parent_file' ] );
		add_filter( 'woocommerce_get_sections_shipping', [ $this, 'shipping_section' ], 10, 1 );
		add_filter( 'woocommerce_get_settings_shipping', [ $this, 'shipping_setting' ], 10, 2 );

	}

	public function admin_menu() {

		$capability = apply_filters( 'pws_menu_capability', 'manage_woocommerce' );

		add_menu_page( 'حمل و نقل', 'حمل و نقل', $capability, 'pws-tools', [
			'PWS_Settings_Tools',
			'output',
		], PWS_URL . 'assets/images/pws.png', '55.8' );

		$submenus = [
			10 => [
				'title'      => 'ابزارها',
				'capability' => $capability,
				'slug'       => 'pws-tools',
				'callback'   => [ 'PWS_Settings_Tools', 'output' ],
			],
			20 => [
				'title'      => 'تاپین',
				'capability' => $capability,
				'slug'       => 'pws-tapin',
				'callback'   => [ 'PWS_Settings_Tapin', 'output' ],
			],
			30 => [
				'title'      => 'پیامک',
				'capability' => $capability,
				'slug'       => 'pws-sms',
				'callback'   => [ 'PWS_Settings_SMS', 'output' ],
			],
			40 => [
				'title'      => 'شهرها',
				'capability' => $capability,
				'slug'       => 'edit-tags.php?taxonomy=state_city',
				'callback'   => '',
			],
			50 => [
				'title'      => 'شرط‌ها',
				'capability' => $capability,
				'slug'       => 'admin.php?page=wc-settings&tab=shipping&section=pws_rules',
				'callback'   => '',
			],
		];

		if ( ! defined( 'PWS_PRO_VERSION' ) ) {
			$submenus[60] = [
				'title'      => 'نسخه حرفه‌ای',
				'capability' => $capability,
				'slug'       => 'link-to-pws-pro',
				'callback'   => '',
			];
		}

		$submenus = apply_filters( 'pws_submenu', $submenus );

		foreach ( $submenus as $submenu ) {
			add_submenu_page( 'pws-tools', $submenu['title'], $submenu['title'], $submenu['capability'], $submenu['slug'], $submenu['callback'] );

			add_action( 'admin_init', function () use ( $submenu ) {
				$callback = $submenu['callback'][0] ?? null;
				if ( is_string( $callback ) && class_exists( $callback ) && method_exists( $callback, 'instance' ) ) {
					call_user_func( [ $callback, 'instance' ] );
				}
			}, 5 );
		}

	}

	public function admin_head() {
		?>
		<script type="text/javascript">
            jQuery(document).ready(function ($) {
                $("#toplevel_page_pws-tools a[href$='link-to-pws-pro']")
                    .attr('href', '<?php echo esc_url( PWS()->pws_pro_url( 'menu' ) ); ?>')
                    .attr('target', '_blank');
            });
		</script>
		<?php
	}

	/**
	 * Loads the style in admin page
	 */
	public function admin_scripts( $hook_suffix ) {

		if ( ! in_array( $hook_suffix, [ 'toplevel_page_pws-tools', 'woocommerce_page_wc-orders' ] ) ) {
			return;
		}

		wp_enqueue_style(
			'pws-tools-submenu-css',
			PWS_URL . 'assets/css/admin.css',
			[],
			PWS_VERSION
		);
	}

	public function parent_file( $parent_file ) {

		if ( ! isset( $_GET['taxonomy'] ) || $_GET['taxonomy'] != 'state_city' ) {
			return $parent_file;
		}

		return 'pws-tools';
	}

	public function shipping_section( array $sections ): array {

		$sections['pws_rules'] = 'شرط‌های حمل و نقل';

		return $sections;

	}

	public function shipping_setting( $settings, $current_section ): array {

		if ( $current_section != 'pws_rules' ) {
			return $settings;
		}

		$GLOBALS['hide_save_button'] = true;

		$settings = [];

		$settings[] = [
			'name' => 'شرط‌های حمل و نقل',
			'type' => 'title',
			'desc' => 'ابزار شرط‌های حمل و نقل فقط در <a href="' . PWS()->pws_pro_url( 'pws_rules' ) . '" target="_blank">نسخه حرفه‌ای</a> فعال می‌باشد.',
			'id'   => 'pws_rules_section',
		];

		$settings[] = [
			'type' => 'sectionend',
			'id'   => 'pws_rules_section',
		];

		return $settings;
	}

	public function includes() {
		include 'class-settings.php';
		include 'class-sms.php';
		include 'class-tapin.php';
		include 'class-tools.php';
		include 'class-city.php';
	}

}

new PWS_Admin();
