jQuery(function ($) {

    function pws_selectWoo(element) {
        let select2_args = {
            placeholder: element.attr('data-placeholder') || element.attr('placeholder') || '',
            width: '100%'
        };

        element.selectWoo(select2_args);
    }

    function pws_state_changed(type, state_id) {

        let city_element = $('select#' + type + '_city');

        city_element.html('<option value="0">در حال بارگزاری لیست شهرها...</option>');

        let data = {
            'action': 'mahdiy_load_cities',
            'state_id': state_id,
            'type': type
        };

        $.post(pws_settings.ajax_url, data, function (response) {
            city_element.html(response);
        });

        pws_selectWoo(city_element);
    }

    $(document.body)
        .off('change.pwsState', "select[id$='_state']")
        .on('change.pwsState', "select[id$='_state']", function () {
            let stateId = $(this).val();

            if (!stateId || stateId === '0') {
                return;
            }

            let type = $(this).attr('id').indexOf('billing') !== -1 ? 'billing' : 'shipping';
            pws_state_changed(type, stateId);
        });

    $("select[id$='_city']").on('select2:select', function (e) {
        $('body').trigger('update_checkout');
    });

    pws_settings.types.forEach(type => {
        pws_selectWoo($('select#' + type + '_state'));
        pws_selectWoo($('select#' + type + '_city'));
    });

    let is_cod = pws_settings.is_cod;

    $('form.checkout').on('change', 'input[name^="payment_method"]', function () {
        if (($(this).val() === 'cod') !== is_cod) {
            $('body').trigger('update_checkout');
            is_cod = ($(this).val() === 'cod');
        }
    });

});
