<?php
/**
 * Developer : MahdiY
 * Web Site  : MahdiY.IR
 * E-Mail    : M@hdiY.IR
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( class_exists( 'WC_Custom_Method' ) ) return; // Stop if the class already exists

class WC_Custom_Method extends WC_Shipping_Method {
	
    public function __construct( $instance_id = 0 ) {
		
		$this->id					= 'WC_Custom_Method';
		$this->instance_id 			= absint( $instance_id );
		$this->method_title			= __('پست سفارشی');
		$this->method_description	= __('ارسال کالا با استفاده از پست سفارشی') . PWS()->donate();
		$this->supports				= array(
			'shipping-zones',
			'instance-settings',
		);
		
		$this->init();
    }
	
	public function init() {

		$this->init_form_fields();
		$this->init_settings();

        $this->title				= $this->get_option( 'title', $this->method_title );
        $this->extra_cost			= $this->get_option( 'extra_cost', 0 );
        $this->extra_cost_percent	= $this->get_option( 'extra_cost_percent', 0 );
        $this->source_state			= $this->get_option( 'source_state' );
        $this->insurance_cost		= $this->get_option( 'insurance_cost', 6500 );
        $this->tax_percent			= $this->get_option( 'post_tax_percent', 9 );
        $this->current_weight_unit	= get_option( 'woocommerce_weight_unit' );
        $this->cart_weight			= isset(WC()->cart) ? WC()->cart->get_cart_contents_weight() : 0;

        add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
    }

	public function init_form_fields() {
		
		$states = get_terms(array('taxonomy' => 'state_city', 'hide_empty' => false, 'parent' => 0));
		
        $this->instance_form_fields = array(
            'enabled' => array(
                'title' 		=> 'فعال/غیرفعال',
                'type' 			=> 'checkbox',
                'label' 		=> 'فعال کردن این روش ارسال',
                'default' 		=> 'yes'
            ),
            'title' => array(
                'title' 		=> 'عنوان روش',
                'type' 			=> 'text',
                'default'		=> $this->method_title
            ),
            'extra_cost' => array(
                'title' 		=> 'هزینه های اضافی',
                'type' 			=> 'text',
                'description' 	=> 'هزینه های اضافی علاوه بر نرخ پستی را می توانید وارد نمائید، (مثل: هزینه های بسته بندی و ... ) مبلغ ثابت را به ریال وارد نمائید',
                'default'		=> 0
            ),
            'extra_cost_percent' => array(
                'title' 		=> 'هزینه های اضافی به درصد',
                'type' 			=> 'text',
                'description' 	=> 'هزینه های اضافی علاوه بر نرخ پستی را می توانید به درصد وارد نمائید (مثال: برای 2%، عدد 2 را وارد نمائید)',
                'default'		=> 0
            ),
            'insurance_cost' => array(
                'title' 		=> 'هزینه بیمه',
                'type' 			=> 'text',
                'description' 	=> 'هزینه بیمه را بصورت ثابت وارد کنید، پیشفرض : 6500 ریال',
                'default'		=> 6500
            ),
            'post_tax_percent' => array(
                'title' 		=> 'مالیات پست',
                'type' 			=> 'text',
                'description' 	=> 'درصد مالیات اداره پست، پیشفرض : 9 درصد',
                'default'		=> 9
            ),
            'source_state' => array(
                'title' 		=> 'استان مبدا (فروشنده)',
                'type' 			=> 'select',
                'description' 	=> 'لطفا در این قسمت استانی که محصولات از آنجا ارسال می شوند را انتخاب نمائید',
                'options' 		=> wp_list_pluck($states, 'name', 'term_id')
            ),
            'img_url' => array(
                'title' 		=> 'تصویر روش حمل و نقل',
                'type' 			=> 'text',
                'description' 	=> 'آدرس تصویر مورد نظر برای این روش حمل و نقل را وارد کنید',
                'default'		=> '',
                'css'           => 'direction: ltr;'
            )
        );
	}
	
	public function is_enabled() {
		return 'yes' === $this->get_option( 'enabled', 'no' );
	}
	
    public function calculate_shipping( $package = array() ) {
		
		if(empty($package) || $package['destination']['country'] != 'IR' || !is_numeric($package['destination']['state']))
			return false;
		
        $price = "";
        $terms = PWS()->get_terms_option( $package['destination']['district'] !== 0 ? $package['destination']['district'] : $package['destination']['city'] );

        foreach ( (array) $terms as $term)
            if($term['custom_cost'] != ""){
                $price = $term['custom_cost'];
                break;
            }
			
        if($price !== ""){
            $this->add_rate( array(
                'id'    => $this->get_rate_id(),
                'label' => $this->title,
                'cost'  => $price
            ) );
            return true;
        }

        $shipping_total = 0;

        $weight = $this->cart_weight * ($this->current_weight_unit == 'kg' ? 1000 : 1);

        $rate_price['500']['in'] 		= 32000;
        $rate_price['500']['out'] 		= 38000;

        $rate_price['1000']['in'] 		= 42000;
        $rate_price['1000']['out'] 		= 52000;

        $rate_price['2000']['in'] 		= 60000;
        $rate_price['2000']['out'] 		= 68000;

        $rate_price['9999']['in'] 		= 22500;
        $rate_price['9999']['out'] 		= 25000;

        switch(true){
            case $weight <= 500:
                $weight_indicator = '500';
                break;
            case $weight > 500 && $weight <= 1000:
                $weight_indicator = '1000';
                break;
            case $weight > 1000 && $weight <= 2000:
                $weight_indicator = '2000';
                break;
            case $weight > 2000:
                $weight_indicator = '9999';
                break;
        }

        $checked_state = $this->source_state == $package['destination']['state'] ? 'in' : 'out';
		
        // calculate
        if ( $weight_indicator != '9999' )
            $shipping_total = $rate_price[$weight_indicator][$checked_state];
        elseif ( $weight_indicator == '9999' )
            $shipping_total = $rate_price['2000'][$checked_state] + ( $rate_price[$weight_indicator][$checked_state] * ceil ( ( $weight - 2000) / 1000 ) );

        $shipping_total += $this->insurance_cost;

        $shipping_total += ceil( ( $shipping_total * $this->tax_percent ) / 100 );

        $shipping_total = ceil ( $shipping_total / 1000 ) * 1000;
		
        $shipping_total += ceil ( ( $shipping_total * absint($this->extra_cost_percent)) / 100 );
        $shipping_total += absint($this->extra_cost);

        $this->add_rate( array(
            'id' => $this->get_rate_id(),
            'label' => $this->title,
            'cost' => PWS()->convert_currency($shipping_total)
        ) );
    }
}
