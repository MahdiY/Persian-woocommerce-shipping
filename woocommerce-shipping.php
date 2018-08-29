<?php
/*
Plugin Name: Persian Woocommerce Shipping
Plugin URI: http://MahdiY.ir
Description: Powerful shipping plugin for woocommerce
Version: 0.9.1
Author: MahdiY
Author URI: http://MahdiY.ir
WC requires at least: 3.0.0
WC tested up to: 3.2.2
*/

if( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

include( "inc/state_city.php" );
include( "inc/taxonomy-shipping.php" );
include( "inc/woocommerce-shipping.php" );

define( 'PWS_VERSION', '0.9.1' );

function PWS() {
    return Persian_Woocommerce_Shipping::instance();
}

$GLOBALS['PWS'] = PWS();

register_activation_hook( __FILE__, array( 'Persian_Woocommerce_Shipping', 'install' ) );
