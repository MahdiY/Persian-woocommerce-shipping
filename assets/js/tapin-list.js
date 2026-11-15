jQuery(document).ready(function ($) {

    let pws_IDs = [];
    let pws_button_submit = $("#pws-tapin-submit");
    let pws_button_ship = $("#pws-tapin-ship");

    pws_button_submit.click(function () {
        pws_change_status('pws-packaged');
    });

    pws_button_ship.click(function () {
        pws_change_status('pws-ready-to-ship');
    });

    function pws_change_status(status) {

        pws_IDs = [];

        $('.check-column input[name="' + pws_tapin.order_field + '[]"]:checked').each(function () {
            pws_IDs.push($(this).val());
        });

        if (pws_IDs.length === 0) {
            alert('سفارشی جهت پردازش انتخاب نشده است.');
            return false;
        }

        // Start
        pws_button_submit.attr('disabled', 'disabled');
        pws_button_ship.attr('disabled', 'disabled');
        $('.pws-tips').remove();

        pws_change_status_ajax(status);
    }

    function pws_change_status_ajax(status) {

        let id = pws_IDs.shift();

        if (id == undefined) {
            // End
            pws_button_submit.removeAttr('disabled');
            pws_button_ship.removeAttr('disabled');
            return true;
        }

        let data = {
            'action': 'pws_change_order_status',
            '_wpnonce': pws_tapin.nonce,
            'status': status,
            'id': id
        };

        $(pws_tapin.status_field + id + " td.order_status").html(`
                        <mark class="order-status">
                            <span>...</span>
                        </mark>
                    `);

        $.post(ajaxurl, data).then(function (response) {

            response = JSON.parse(response);

            if (response.success) {

                $(pws_tapin.status_field + id + " td.order_status").html(`
                                <mark class="order-status status-processing">
                                    <span>${response.message}</span>
                                </mark>
                            `);

            } else {

                $(pws_tapin.status_field + id + " td.order_status").html(`
                                <mark class="order-status status-pws-returned">
                                    <span>خطا در پردازش</span>
                                </mark>
                            `);

                $(pws_tapin.status_field + id + " td.column-order_number").append(`
                                <mark class="order-status status-pws-returned pws-tips"
                                        style="margin-top: 10px; font-size: 11px;">
                                    <span>
                                        ${response.message}
                                    </span>
                                </mark>
                            `);

            }

            pws_change_status_ajax(status);
        });

    }
});