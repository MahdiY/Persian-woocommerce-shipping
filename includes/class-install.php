<?php
/**
 * Developer : MahdiY
 * Web Site  : MahdiY.IR
 * E-Mail    : M@hdiY.IR
 */

defined( 'ABSPATH' ) || exit;

class PWS_Install {

	public function __construct() {
		add_action( 'admin_init', [ $this, 'activated_plugin' ], 50 );
		add_action( 'admin_notices', [ $this, 'admin_notices' ], 5 );
		add_action( 'wp_ajax_pws_install_cities', [ $this, 'install_cities' ] );
	}

	public function activated_plugin() {

		if ( ! file_exists( PWS_DIR . '/.activated' ) ) {
			return;
		}

		$installed_version = get_option( PWS_Version::VERSION_KEY );

		if ( empty( $installed_version ) ) {
			update_option( PWS_Version::VERSION_KEY, PWS_VERSION, 'yes' );
		}

		update_option( PWS_Version::VERSION_KEY, PWS_VERSION );

		unlink( PWS_DIR . '/.activated' );
	}

	public function admin_notices() {

		if ( get_option( 'sabira_set_iran_cities', 0 ) || get_option( 'pws_install_cities', 0 ) ) {
			return;
		}

		?>
		<script type="text/javascript">
            jQuery(document).ready(function ($) {

                $.ajax({
                    url: "<?php echo admin_url( 'admin-ajax.php' ) ?>",
                    type: 'post',
                    data: {
                        action: 'pws_install_cities'
                    }
                });

            });
		</script>
		<?php
	}

	public function install_cities(): bool {
		global $wp_filter;

		if ( 'yes' === get_transient( 'pws_installing_cities' ) ) {
			die( 'pws_installing_cities' );
		}

		set_transient( 'pws_installing_cities', 'yes', MINUTE_IN_SECONDS * 10 );

		unset( $wp_filter['delete_state_city'] );
		unset( $wp_filter['edited_state_city'] );
		unset( $wp_filter['created_state_city'] );

		if ( function_exists( 'set_time_limit' ) ) {
			set_time_limit( 0 );
		}

		require_once( PWS_DIR . '/data/state_city.php' );

		foreach ( PWS_get_states() as $key => $state ) {

			$term = wp_insert_term( $state, 'state_city', [
				'slug'        => $key,
				'description' => "استان $state",
			] );

			if ( is_wp_error( $term ) ) {

				if ( $term->get_error_code() == 'term_exists' ) {
					$term_id = $term->get_error_data( 'term_exists' );
				} else {
					die( 'false' );
				}

			} else {
				$term_id = $term['term_id'];
			}

			$installed_cities = get_terms( [
				'taxonomy'               => 'state_city',
				'hide_empty'             => false,
				'parent'                 => $term_id,
				'update_term_meta_cache' => false,
			] );
			$installed_cities = wp_list_pluck( $installed_cities, 'name' );

			foreach ( array_diff( PWS_get_state_city( $key ), $installed_cities ) as $city ) {

				$term = wp_insert_term( $city, 'state_city', [
					'parent'      => $term_id,
					'slug'        => $city,
					'description' => "$state - $city",
				] );

				if ( is_wp_error( $term ) && $term->get_error_code() != 'term_exists' ) {
					die( 'false' );
				}
			}

		}

		update_option( 'pws_install_cities', 1 );

		PWS_City::flush_cache();

		delete_transient( 'pws_installing_cities' );

		die( 'true' );
	}
}

new PWS_Install();
