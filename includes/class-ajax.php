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

		$name = isset( $_POST['name'] ) && $_POST['name'] == 'billing' ? 'billing' : 'shipping';

		if ( is_user_logged_in() ) {
			$term_id = get_user_meta( get_current_user_id(), $name . '_city', true );
		} else {
			$term_id = WC()->customer->{"get_{$name}_city"}();
		}

		if ( intval( $term_id ) == 0 ) {
			$term_id = apply_filters( 'pws_default_city', 0, $name, $state_id );
		}

		WC()->customer->{"set_{$name}_state"}( $state_id );

		printf( "<option value='0'>لطفا شهر خود را انتخاب نمایید </option>" );

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

		$cities = get_terms( [
			'taxonomy'   => 'state_city',
			'hide_empty' => false,
			'child_of'   => $city_id
		] );

		if ( is_wp_error( $cities ) ) {
			die();
		}

		$name = isset( $_POST['name'] ) && $_POST['name'] == 'billing' ? 'billing' : 'shipping';

		$term_id = 0;

		if ( is_user_logged_in() ) {
			$term_id = get_user_meta( get_current_user_id(), $name . '_district', true );
		}

		if ( intval( $term_id ) == 0 ) {
			$term_id = apply_filters( 'pws_default_district', 0, $name, $city_id );
		}

		WC()->customer->{"set_{$name}_city"}( $city_id );

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