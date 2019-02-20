<?php
/*
Plugin Name: افزونه حمل و نقل ووکامرس
Plugin URI: http://MahdiY.ir
Description: افزونه قدرتمند حمل و نقل ووکامرس با قابلیت ارسال از طریق پست پیشتاز، سفارشی، پیک موتوری و تیپاکس
Version: 1.1.2
Author: MahdiY
Author URI: http://MahdiY.ir
WC requires at least: 3.0.0
WC tested up to: 3.5.5
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

include( "inc/state_city.php" );
include( "inc/taxonomy-shipping.php" );
include( "inc/woocommerce-shipping.php" );

define( 'PWS_VERSION', '1.1.2' );

function PWS() {
	return Persian_Woocommerce_Shipping::instance();
}

$GLOBALS['PWS'] = PWS();

register_activation_hook( __FILE__, array( 'Persian_Woocommerce_Shipping', 'install' ) );
