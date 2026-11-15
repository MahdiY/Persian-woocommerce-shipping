jQuery(document).ready(function ($) {

    const tapin_weight = $('#tapin_weight');
    const tapin_box_size = $('#tapin_box_size');
    let pws_button_submit = $("#pws-tapin-submit");
    let pws_button_ship = $("#pws-tapin-ship");

    pws_button_submit.click(function () {
        pws_change_status('pws-packaged');
    });

    pws_button_ship.click(function () {
        pws_change_status('pws-ready-to-ship');
    });

    function pws_change_status(status) {

        // Start
        pws_button_submit.attr('disabled', 'disabled');
        pws_button_ship.attr('disabled', 'disabled');
        $('.pws-tips').html('');

        pws_change_status_ajax(status);
    }

    function pws_change_status_ajax(status) {

        let id = pws_tapin.order_id;

        let data = {
            'action': 'pws_change_order_status',
            '_wpnonce': pws_tapin.nonce,
            'id': id,
            'status': status,
            'weight': tapin_weight.val(),
            'content_type': $('#tapin_content_type').val(),
            'box_size': tapin_box_size.val(),
        };

        $(".pws-tips").html(`
                        <mark class="order-status">
                            <span>...</span>
                        </mark>
                    `);

        $.post(ajaxurl, data).then(function (response) {

            response = JSON.parse(response);

            if (response.success) {

                $(".pws-tips").html(`
                                <mark class="order-status status-processing">
                                    <span>${response.message}</span>
                                </mark>
                            `);

                setTimeout(function () {
                    location.reload();
                }, 3000);

            } else {

                $(".pws-tips").html(`
                                <mark class="order-status status-pws-returned">
                                    <span>خطا در پردازش</span>
                                </mark>
                            `);

                $(".pws-tips").append(`
                                <mark class="order-status status-pws-returned pws-tips"
                                        style="margin-top: 10px; font-size: 11px;">
                                    <span>
                                        ${response.message}
                                    </span>
                                </mark>
                            `);

            }

            pws_button_submit.removeAttr('disabled');
            pws_button_ship.removeAttr('disabled');
        });

    }
});