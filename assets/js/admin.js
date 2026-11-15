(function ($) {
    $(document).ready(function () {

        /**
         * Copy map direction links to clipboard
         * */
        let pws_order_map_share_links_container = $('.pws-order__map__share__links__container');
        if (pws_order_map_share_links_container.length) {
            pws_order_map_share_links_container.children('div').on('click', function () {
                let link_element = $(this);
                let url_to_copy = link_element.find('.url').text();

                navigator.clipboard.writeText(url_to_copy).then(function () {
                    // Success: Add the "copied" class for visual feedback
                    let custom_alert = $('.pws-order__map__share__links__custom__alert');
                    link_element.addClass('copied');
                    custom_alert.removeClass('hide').addClass('show');

                    setTimeout(function () {
                        link_element.removeClass('copied');
                        custom_alert.removeClass('show').addClass('hide');
                    }, 3000);

                }).catch(function (error) {
                    console.error('خطا در کپی متن: ', error);
                });
            });
        }


        /**
         * Control map settings behaviour, show or hide form inputs on certain selections.
         *  @since 4.0.4
         * */
        if ($('.toplevel_page_pws-tools').length) {
            let map_service_provider = $("select[name*=pws_map\\[provider\\]]");
            let map_store_calculate_distance = $("select[name*=pws_map\\[store_calculate_distance\\]]");

            /**
             * Hide all specific inputs and only enable General options.
             * Static selectors are presented here to be more clarified
             *
             * @return void
             * */
            function pws_map_hide_neshan_related_fields() {
                $(".pws-map__info-neshan").hide();
                $("[id*=pws_map\\[neshan_api_key\\]]").closest('tr').hide();
                $("[id*=pws_map\\[neshan_type\\]]").closest('tr').hide();
            }

            /**
             * Show all neshan related fields
             *
             * @return void
             * */
            function pws_map_show_neshan_related_fields() {
                $(".pws-map__info-neshan").show();
                $("[id*=pws_map\\[neshan_api_key\\]]").closest('tr').show();
                $("[id*=pws_map\\[neshan_type\\]]").closest('tr').show();
            }


            /**
             * Hide all fields related to Map.ir configuration
             *
             * @return void
             * */
            function pws_map_hide_mapp_related_fields() {
                $(".pws-map__info-mapp").hide();
                $("[id*=pws_map\\[mapp_api_key\\]]").closest('tr').hide();
            }

            /**
             * Show all fields related to Map.ir configuration
             *
             * @return void
             * */
            function pws_map_show_mapp_related_fields() {
                $(".pws-map__info-mapp").show();
                $("[id*=pws_map\\[mapp_api_key\\]]").closest('tr').show();
            }


            /**
             * Show related elements to OSM configuration
             *
             * @return void
             * */
            function pws_map_show_OSM_related_fields() {
                $(".pws-map__info-OSM").show();
            }


            /**
             * Hide related elements to OSM configuration
             *
             * @return void
             * */
            function pws_map_hide_OSM_related_fields() {
                $(".pws-map__info-OSM").hide();
            }

            /**
             * Show specific fields based on provider
             * */
            map_service_provider.on('change', function () {
                let map_selected_provider = $(this).val();
                switch (map_selected_provider) {
                    case 'neshan': // Neshan.ir
                        pws_map_hide_mapp_related_fields();
                        pws_map_hide_OSM_related_fields();
                        pws_map_show_neshan_related_fields();
                        break;
                    case 'mapp': // Map.ir
                        pws_map_hide_neshan_related_fields();
                        pws_map_hide_OSM_related_fields();
                        pws_map_show_mapp_related_fields();
                        break;
                    case 'OSM': //OpenStreetMap.com
                        pws_map_hide_neshan_related_fields();
                        pws_map_hide_mapp_related_fields();
                        pws_map_show_OSM_related_fields();
                        break;
                    default:
                        pws_map_hide_neshan_related_fields();
                        pws_map_hide_mapp_related_fields();
                        break;
                }

            });
            map_service_provider.trigger('change');

            /**
             * Show open route service token if pws_map[store_calculate_distance] has 'real' value
             * In this case it will need the direction api to calculate distance
             */
            map_store_calculate_distance.on('change', function () {
                if ($(this).val() !== 'real') {
                    $("[id*=pws_map\\[ORS_token\\]]").closest('tr').hide();
                } else {
                    $("[id*=pws_map\\[ORS_token\\]]").closest('tr').show();
                }
            });
            map_store_calculate_distance.trigger('change');

            // Set pretty selection with select2 on enabled shipping methods
            let pws_map_shipping_methods = $("[id=pws_map\\[shipping_methods\\]]");

            if (pws_map_shipping_methods.length) {
                pws_map_shipping_methods.selectWoo();
            }

        }/*End Code execution in tools section*/

    });/* End document.ready */
})(jQuery); /* End jQuery noConflict */