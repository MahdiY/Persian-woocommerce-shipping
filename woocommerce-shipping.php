<?php
/*
Plugin Name: Persian Woocommerce Shipping
Plugin URI: http://MahdiY.ir
Description: Powerful shipping plugin for woocommerce
Version: 0.8.8
Author: MahdiY
Author URI: http://MahdiY.ir
*/

if( !defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

include( "inc/state_city.php" );
include( "inc/taxonomy-shipping.php" );
include( "inc/woocommerce-shipping.php" );

define( 'PWS_VERSION', '0.8.8' );

function PWS() {
    return Persian_Woocommerce_Shipping::instance();
}

$GLOBALS['PWS'] = PWS();

register_activation_hook( __FILE__, [ 'Persian_Woocommerce_Shipping', 'install' ] );

?>