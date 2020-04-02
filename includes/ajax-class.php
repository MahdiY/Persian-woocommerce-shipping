<?php
/**
 * Developer : MahdiY
 * Web Site  : MahdiY.IR
 * E-Mail    : M@hdiY.IR
 */

class PWS_Ajax {

	public static function load_cities_callback() {

		if ( ! isset( $_POST['state_id'] ) ) {
			die();
		}

		$state_id = absint( $_POST['state_id'] );

		if ( ! $state_id ) {
			die();
		}

		$cities = PWS()::cities( $state_id );

		$term_id = 0;

		if ( is_user_logged_in() ) {
			$name    = isset( $_POST['name'] ) && $_POST['name'] == 'billing' ? 'billing' : 'shipping';
			$term_id = get_user_meta( get_current_user_id(), $name . '_city', true );
		}

		$method = isset( $_POST['name'] ) && $_POST['name'] == 'billing' ? 'set_billing_state' : 'set_shipping_state';
		WC()->customer->$method( $state_id );

		foreach ( $cities as $id => $name ) {
			printf( "<option value='%d' %s>%s</option>", $id, selected( $term_id, $id, false ), $name );
		}

		die();
	}

	public static function load_districts_callback() {

		if ( ! isset( $_POST['city_id'] ) ) {
			die();
		}

		$city_id = absint( $_POST['city_id'] );

		if ( ! $city_id ) {
			die();
		}

		$cities = get_terms( array(
			'taxonomy'   => 'state_city',
			'hide_empty' => false,
			'child_of'   => $city_id
		) );

		if ( is_wp_error( $cities ) ) {
			die();
		}

		$term_id = 0;

		if ( is_user_logged_in() ) {
			$name    = isset( $_POST['name'] ) && $_POST['name'] == 'billing' ? 'billing' : 'shipping';
			$term_id = get_user_meta( get_current_user_id(), $name . '_district', true );
		}

		$method = isset( $_POST['name'] ) && $_POST['name'] == 'billing' ? 'set_billing_city' : 'set_shipping_city';
		WC()->customer->$method( $city_id );

		if ( count( $cities ) ) {
			$city = get_term( $city_id, 'state_city' );
			printf( "<option value='%d' %s>%s</option>", $city->term_id, selected( $term_id, $city->term_id, false ), $city->name );
		}

		foreach ( $cities as $city ) {
			printf( "<option value='%d' %s>%s</option>", $city->term_id, selected( $term_id, $city->term_id, false ), str_repeat( "- ", count( get_ancestors( $city->term_id, 'state_city' ) ) - 2 ) . $city->name );
		}

		die();
	}

}