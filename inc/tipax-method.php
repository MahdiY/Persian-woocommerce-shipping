<?php
/**
 * Developer : MahdiY
 * Web Site  : MahdiY.IR
 * E-Mail    : M@hdiY.IR
 */

if( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

if( class_exists( 'WC_Tipax_Method' ) ) {
    return;
} // Stop if the class already exists

class WC_Tipax_Method extends WC_Shipping_Method {

    public function __construct( $instance_id = 0 ) {

        $this->id = 'WC_Tipax_Method';
        $this->instance_id = absint( $instance_id );
        $this->method_title = __( 'تیپاکس' );
        $this->method_description = __( 'ارسال کالا با استفاده از تیپاکس' ) . PWS()->donate();
        $this->supports = [ 'shipping-zones',
                            'instance-settings', ];

        $this->init();
    }

    public function init() {

        $this->init_form_fields();
        $this->init_settings();

        $this->enabled = $this->get_option( 'enabled' );
        $this->title = $this->get_option( 'title', $this->method_title );
        $this->extra_cost = $this->get_option( 'extra_cost' );
        $this->extra_cost_percent = $this->get_option( 'extra_cost_percent' );

        add_action( 'woocommerce_update_options_shipping_' . $this->id, [ $this, 'process_admin_options' ] );
    }

    public function init_form_fields() {
        $this->instance_form_fields = [ 'enabled'            => [ 'title'   => 'فعال/غیرفعال',
                                                                  'type'    => 'checkbox',
                                                                  'label'   => 'فعال کردن این روش ارسال',
                                                                  'default' => 'yes' ],
                                        'title'              => [ 'title'   => 'عنوان روش',
                                                                  'type'    => 'text',
                                                                  'default' => $this->method_title ],
                                        'extra_cost'         => [ 'title'       => 'هزینه های اضافی',
                                                                  'type'        => 'text',
                                                                  'description' => 'هزینه های اضافی علاوه بر نرخ پستی را می توانید وارد نمائید، (مثل: هزینه های بسته بندی و ... ) مبلغ ثابت را به ' . get_woocommerce_currency_symbol() . ' وارد نمائید',
                                                                  'default'     => 0 ],
                                        'extra_cost_percent' => [ 'title'       => 'هزینه های اضافی به درصد',
                                                                  'type'        => 'text',
                                                                  'description' => 'هزینه های اضافی علاوه بر نرخ پستی را می توانید به درصد وارد نمائید (مثال: برای 2%، عدد 2 را وارد نمائید)',
                                                                  'default'     => 0 ],
                                        'img_url'            => [ 'title'       => 'تصویر روش حمل و نقل',
                                                                  'type'        => 'text',
                                                                  'description' => 'آدرس تصویر مورد نظر برای این روش حمل و نقل را وارد کنید',
                                                                  'default'     => '',
                                                                  'css'         => 'direction: ltr;' ] ];
    }

    public function is_available( $package = [] ) {
        $terms = PWS()->get_terms_option( $package['destination']['district'] !== 0 ? $package['destination']['district'] : $package['destination']['city'] );
        $is_available = true;

        if( $terms === false || is_wp_error( $terms ) ) {
            $is_available = false;
        } else {
            foreach( (array) $terms as $term )
                if( $term['tipax_on'] == 0 ) {
                    $is_available = false;
                }
        }

        return apply_filters( 'woocommerce_is_available_shipping_' . $this->id, $is_available, $package );
    }

    public function calculate_shipping( $package = [] ) {

        if( empty( $package ) || $package['destination']['country'] != 'IR' || !is_numeric( $package['destination']['state'] ) ) {
            return false;
        }

        $price = "";
        $terms = PWS()->get_terms_option( $package['destination']['district'] !== 0 ? $package['destination']['district'] : $package['destination']['city'] );

        foreach( $terms as $term )
            if( $term['tipax_cost'] != "" ) {
                $price = $term['tipax_cost'];
                break;
            }

        if( !is_numeric( $price ) ) {
            return false;
        }

        $price += ceil( ( $price * absint( $this->extra_cost_percent ) ) / 100 );
        $price += absint( $this->extra_cost );

        $rate = apply_filters( 'pws_add_rate', [ 'id'    => $this->get_rate_id(),
                                                 'label' => $this->title,
                                                 'cost'  => $price ], $package, $this );

        $this->add_rate( $rate );
    }
}
