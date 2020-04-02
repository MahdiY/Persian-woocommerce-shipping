<?php defined( 'ABSPATH' ) || exit; ?>
<div class="content factor" style="height: 100%; overflow: hidden;">
    <div class="ticket-image"></div>
    <table style="height: 100%; min-height: 400px">
        <tr>
            <th width="40%">شماره سفارش: <?php echo $order->get_order_number(); ?></th>
            <th width="60%">فرستنده (<?php echo PW()->get_options( 'wooi_store_state' ); ?>
                ، <?php echo PW()->get_options( 'wooi_store_city' ); ?>)
            </th>
        </tr>
        <tr>
            <td rowspan="4">
				<?php

				$tapin_method = $order->get_meta( 'tapin_method' );

				if ( is_array( $tapin_method ) ) {

					$types = array(
						0 => 'سفارشی',
						1 => 'پیشتاز',
					);

					$shipping_method = $types[ $tapin_method['post_type'] ];
				}

				?>
                <table id="data">
                    <tr <?php echo wooi_show_item( ! is_array( $tapin_method ) ); ?>>
                        <td><b>شیوه ارسال</b></td>
                        <td><?php echo $order->get_shipping_method(); ?></td>
                    </tr>
                    <tr>
                        <td><b>روش پرداخت</b></td>
                        <td>
							<?php echo $order->get_payment_method() == 'cod' ? 'پرداخت هنگام دریافت' : 'پرداخت آنلاین'; ?>
                        </td>
                    </tr>
                    <tr <?php wooi_show_item( $order->get_payment_method() == 'cod' ); ?>>
                        <td colspan="2">
                            <b>
                                مبلغ قابل پرداخت از گیرنده دریافت شود.
                            </b>
                        </td>
                    </tr>
                    <tr <?php wooi_show_item( $order->get_payment_method() == 'cod' ); ?>>
                        <td><b>مبلغ قابل پرداخت</b></td>
                        <td><?php echo wooi_price( $order->get_total() ); ?><?php echo get_woocommerce_currency_symbol(); ?></td>
                    </tr>
                </table>
                <br>
                <div <?php echo wooi_show_item( is_array( $tapin_method ) ); ?>>
                    <table style="height: 55mm; width: 100%; margin: 0 auto; font-size: 10px;">
                        <tr style="height: 18mm;text-align: center;">
                            <td colspan="2">
                                <img src="<?php echo plugin_dir_url( PWS_FILE ); ?>assets/images/logo.png" alt=""
                                     style="height: 10mm;"><br>
                                خدمات تجارت الکترونیک<br>
                                نوع ارسال: <?php echo $shipping_method; ?>
                            </td>
                        </tr>
                        <tr style="height: 4mm;">
                            <td>مبدا
                                ◀ <?php echo PW()->get_options( 'wooi_store_state' ); ?> <?php echo PW()->get_options( 'wooi_store_city' ); ?></td>
                            <td>مقصد
                                ◀ <?php echo $order->get_shipping_state(); ?> <?php echo $order->get_shipping_city(); ?></td>
                        </tr>
                        <tr style="height: 4mm;">
                            <td>خدمات ◀ <?php echo number_format( intval( PWS_Tapin::shop()->total_price ) ); ?> ریال
                            </td>
                            <td>وزن ◀ <?php echo number_format( intval( PWS_Tapin::shop()->total_price ) ); ?> گرم
                            </td>
                        </tr>
                        <tr style="height: 4mm;">
                            <td>تاریخ ◀ <?php echo date_i18n( 'Y-m-d', $order->get_meta( 'tapin_send_time' ) ); ?></td>
                            <td>زمان ◀ <?php echo date_i18n( 'H:i:s', $order->get_meta( 'tapin_send_time' ) ); ?></td>
                        </tr>
                        <tr style="height: 4mm;">
                            <td>کرایه پستی
                                ◀ <?php echo number_format( intval( $order->get_meta( 'tapin_send_price' ) ) ); ?>
                                ریال
                            </td>
                            <td>مالیات ارزش افزوده پستی
                                ◀ <?php echo number_format( intval( $order->get_meta( 'tapin_send_price_tax' ) ) ); ?>
                                ریال
                            </td>
                        </tr>
                        <tr style="height: 8mm;">
                            <td colspan="2" class="post_barcode" style="text-align: center;padding-top: 2px;">
                            <span>
							    <?php echo $order->get_meta( 'post_barcode' ); ?>
                            </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
            <td>
                <p>
                    <b>فرستنده: </b><?php echo PW()->get_options( 'wooi_store_name' ); ?>&emsp;&emsp;
                    <b>وب سایت: </b><?php echo trim( site_url(), '/' ); ?>
                </p>
                <p><b>نشانی: </b><?php echo PW()->get_options( 'wooi_store_state' ); ?>
                    | <?php echo PW()->get_options( 'wooi_store_city' ); ?>
                    | <?php echo PW()->get_options( 'wooi_store_address' ); ?></p>
                <p>
                    <b>کد پستی: </b><?php echo wooi_fa( PW()->get_options( 'wooi_store_postcode' ) ); ?>&emsp;
                    <b>تلفن: </b><?php echo wooi_fa( PW()->get_options( 'wooi_store_phone' ) ); ?>
                </p>

            </td>
        </tr>
        <tr>
            <th width="50%">گیرنده (<?php echo wooi_state_city_name( $order->get_shipping_state() ); ?>
                ، <?php echo wooi_state_city_name( $order->get_shipping_city() ); ?>)
            </th>
        </tr>
        <tr>
            <td>
                <p>
                    <b>گیرنده: </b><?php echo $order->get_shipping_first_name() . " " . $order->get_shipping_last_name(); ?>
                </p>
                <p><b>نشانی: </b><?php echo wooi_state_city_name( $order->get_shipping_state() ); ?>
                    | <?php echo wooi_state_city_name( $order->get_shipping_city() ); ?>
                    | <?php echo $order->get_shipping_address_1(); ?>
					<?php echo $order->get_shipping_address_2(); ?>
                </p>
                <p>
                    <b>کد پستی: </b><?php echo $order->get_shipping_postcode(); ?>
                    <b>تلفن: </b><?php echo $order->get_billing_phone(); ?>
                </p>

            </td>
        </tr>
        <tr>
            <td>
                <div style="text-align: center;">
                    <img src="<?php echo $logo; ?>" alt="<?php echo PW()->get_options( 'wooi_store_name' ); ?>"
                         style="height: 50px">
                    <span <?php echo wooi_show_item( is_array( $tapin_method ) ); ?>>
                        <img src="<?php echo plugin_dir_url( PWS_FILE ); ?>assets/images/tapin.jpg"
                             alt="<?php echo PW()->get_options( 'wooi_store_name' ); ?>"
                             style="height: 50px">
                    </span>
                </div>
            </td>
        </tr>

    </table>
    <div class="ticket-image"></div>
</div>