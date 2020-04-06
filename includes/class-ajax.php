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

		$type = isset( $_POST['type'] ) && $_POST['type'] == 'billing' ? 'billing' : 'shipping';

		$term_id = WC()->checkout()->get_value( $type . '_city' );

		if ( intval( $term_id ) == 0 ) {
			$term_id = apply_filters( 'pws_default_city', 0, $type, $state_id );
		}

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

		$type = isset( $_POST['type'] ) && $_POST['type'] == 'billing' ? 'billing' : 'shipping';

		$term_id = WC()->session->get( $type . '_district', 0 );

		if ( intval( $term_id ) == 0 ) {
			$term_id = apply_filters( 'pws_default_district', $city_id, $type, $city_id );
		}

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