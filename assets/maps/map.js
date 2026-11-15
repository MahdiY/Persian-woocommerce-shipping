/**
 * This file includes general methods for all maps.
 * */

/**
 * Return custom [lat,long] of Iran provinces(states)
 * GENERAL METHOD
 * @return array
 * */
function pws_map_get_province_location(province) {
    switch (province) {
        case "استان آذربایجان شرقی":
        case "آذربایجان شرقی":
            return [38.07334959970686, 46.29652594872965];
        case "استان آذربایجان غربی":
        case "آذربایجان غربی":
            return [37.549090717092994, 45.066199010210255];
        case "استان اردبیل":
        case "اردبیل":
            return [38.250977963284896, 48.29705810186877];
        case "استان اصفهان":
        case "اصفهان":
            return [32.64673138417923, 51.667879557967524];
        case "استان البرز":
        case "البرز":
            return [35.81196370276513, 51.007535158359644];
        case "استان ایلام":
        case "ایلام":
            return [33.636365996683466, 46.42491419928686];
        case "استان بوشهر":
        case "بوشهر":
            return [28.93175276389951, 50.86857312232024];
        case "استان تهران":
        case "تهران":
            return [35.6997006457524, 51.33774439566025];
        case "استان چهارمحال و بختیاری":
        case "چهارمحال و بختیاری":
        case "استان چهار محال بختیاری":
            return [32.32563021660313, 50.84949361049016];
        case "استان خراسان جنوبی":
        case "خراسان جنوبی":
            return [32.86310366749149, 59.21695375448638];
        case "استان خراسان رضوی":
        case "خراسان رضوی":
            return [36.29749367352903, 59.60612708710633];
        case "استان خراسان شمالی":
        case "خراسان شمالی":
            return [37.47623251667375, 57.33164422289255];
        case "استان خوزستان":
        case "خوزستان":
            return [31.323059172676807, 48.679357419175574];
        case "استان زنجان":
        case "زنجان":
            return [36.66799998523621, 48.48300964754603];
        case "استان سمنان":
        case "سمنان":
            return [35.58316228924757, 53.38874832538042];
        case "استان سیستان و بلوچستان":
        case "سیستان و بلوچستان":
            return [29.489146181976437, 60.86376908620963];
        case "استان فارس":
        case "فارس":
            return [29.603818535342484, 52.5385086580618];
        case "استان قزوین":
        case "قزوین":
            return [36.27970518245759, 50.00488463587101];
        case "استان قم":
        case "قم":
            return [34.64630675359567, 50.88227991502279];
        case "استان کردستان":
        case "کردستان":
            return [35.3114839356263, 47.002433312163674];
        case "استان کرمان":
        case "کرمان":
            return [30.292465037358852, 57.066016844077694];
        case "استان کرمانشاه":
        case "کرمانشاه":
            return [34.32392220244171, 47.07327816944752];
        case "استان کهگیلویه و بویراحمد":
        case "کهگیلویه و بویراحمد":
            return [30.667254302880878, 51.57938820663321];
        case "استان گلستان":
        case "گلستان":
            return [36.84175133875932, 54.43273154185354];
        case "استان گیلان":
        case "گیلان":
            return [37.27888580875177, 49.58475222497668];
        case "استان لرستان":
        case "لرستان":
            return [33.48489556493388, 48.35352126935422];
        case "استان مازندران":
        case "مازندران":
            return [36.56589812594005, 53.058534799566075];
        case "استان مرکزی":
        case "مرکزی":
            return [34.095494177321996, 49.690812344504934];
        case "استان هرمزگان":
        case "هرمزگان":
            return [27.179653213579527, 56.27686633792305];
        case "استان همدان":
        case "همدان":
            return [34.79871932655388, 48.51427475358636];
        case "استان یزد":
        case "یزد":
            return [31.888301558794836, 54.364528979651965];
        default:
            return null;
    }
}


/**
 * Check if current viewing page is admin area or not!
 * @return bool
 * */
function pws_is_admin() {
    return pws_map_params.is_admin !== '' && typeof pagenow !== 'undefined';
}


function pws_map_distance_between_two_points(user_coords, distance_type) {
    return new Promise(function (resolve, reject) {
        // rest url with distance endpoint
        let rest_url = pws_map_params.rest_url + 'distance';

        let request_payload = {
            user_coords: user_coords,
            type: distance_type,
        };

        // Make the AJAX request to OpenRouteService
        jQuery.ajax({
            url: rest_url,
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(request_payload),
            success: function (response) {
                if (response.success) {
                    resolve(response);
                } else {
                    reject(new Error(response.message));
                }
            },
            error: function (error) {
                console.error('خطا در محاسبه فاصله: ', error);
                reject(error);
            }
        });
    });
}


function pws_map_show_distance(vars) {
    let pws_map_shipping_information = jQuery('.pws-order__map__shipping__information');
    let user_coords = JSON.stringify({"lat": vars.user_lat, "long": vars.user_long});

    if (!vars.show_distance_type || vars.show_distance_type === 'none') {
        return;
    }

    if (!pws_map_shipping_information.length) {
        return;
    }

    if (!vars.user_has_location) {
        return;
    }

    pws_map_distance_between_two_points(user_coords, vars.show_distance_type)
        .then(function (data) {
            pws_map_shipping_information.html('<b>' + data.distance + '</b>')
        })
        .catch(function (error) {
            pws_map_shipping_information.html('');
            console.error('خطا در محاسبه فاصله: ', error);
        });

}


/**
 * Check map placement which passed in params
 * @return bool
 * */
function pws_map_after_checkout() {
    return pws_map_params.checkout_placement === 'after_form';
}


/**
 * Method to check if shipping state has been enabled
 * @return bool
 * */
function pws_checkout_shipping_address_enabled() {
    return jQuery('#ship-to-different-address-checkbox').is(':checked');
}


/**
 * Check if admin can edit and set new point on the map
 * */
function pws_map_admin_editing_enabled() {
    if (!pws_is_admin()) {
        return true;
    }

    return jQuery('#pws-map-admin-edit').is(':checked');
}


function pws_map_is_admin_tools_page() {
    let currentUrl = window.location.href;

    if (currentUrl.includes('?page=pws-tools')) {
        return true;
    }
}


function pws_map_get_order_id_from_url() {
    let url_params = new URLSearchParams(window.location.search);
    let order_id = 0;

    if (url_params.has('post')) {
        order_id = url_params.get('post');
    }

    if (url_params.has('id')) {
        order_id = url_params.get('id');
    }

    return order_id;
}


/*
 * Function to add geolocate control and logic to map
 * TODO: Fix locate control
 */
function pws_map_add_geolocate_control(map_object) {
    return false;
    L.control.locate({
        strings: {
            title: "دریافت مکان فعلی"
        },
        position: "topright",
        locateOptions: {
            enableHighAccuracy: true
        }
    }).addTo(map_object);
}


// Function to add markers to the map
function pws_map_add_marker(map_coords, icon_url, marker_layer_group) {
    var icon = L.icon({
        iconUrl: icon_url,
        iconSize: [18, 32],
        iconAnchor: [16, 32]
    });

    var marker = L.marker(map_coords, {icon: icon});
    // Each layer group can only have one marker
    marker_layer_group.clearLayers();
    marker.addTo(marker_layer_group);
}


/**
 * Set map initial styles and customization
 * */
function pws_map_customize(container) {
    /*Append custom css*/
    var pws_map_css_customization = ``;

    let pws_map_style_tag = jQuery('<style></style>');
    pws_map_style_tag.text(pws_map_css_customization);
    jQuery('head').append(pws_map_style_tag);

    container.css({
        'min-width': container.data('min-width'),
        'min-height': container.data('min-height')
    });
}


function pws_map_call_ajax(url, callback, headers) {
    jQuery.ajax({
        url: url,
        type: 'GET',
        xhrFields: {
            responseType: 'blob'
        },
        beforeSend: function (xhr) {
            headers.forEach(h => {
                xhr.setRequestHeader(h.header, h.value);
            });
        },
        success: function (response) {
            callback(response);
        },
        error: function (xhr, status, error) {
            console.error('AJAX request failed:', status, error);
        }
    });
}


function pws_show_location_data(lat, lng, vars) {
    let pws_order_map_coords = jQuery(".pws-order__map__coords");

    if (!pws_order_map_coords.length || !vars.user_has_location) {
        return;
    }

    pws_order_map_coords.html('<b>' + 'عرض جغرافیایی (lat): ' + lat + '<br><br>' + 'طول جغرافیایی (long): ' + lng + '</b>');
}


function pws_show_location_on_map(map_object, user_input_address, zoom, user_marker_layer) {
    let province_coords = pws_map_get_province_location(user_input_address);

    if (province_coords == null) {
        return;
    }

    map_object.setView(province_coords, zoom);
    jQuery("#pws_map_location").val('');
    user_marker_layer.clearLayers();
}


function pws_map_is_checkout() {
    let body = jQuery('body');
    return body.hasClass('woocommerce-checkout') &&
        !body.hasClass('woocommerce-order-received') &&
        !window.location.search.includes('order-pay')
}


/**
 * Interact with click on map
 * */
function pws_map_on_click(event, map_object, vars, user_marker_layer, store_marker_layer) {
    // If user is admin but the general editing option is not enabled, do nothing!
    if (pws_is_admin() && !pws_map_admin_editing_enabled()) {
        return;
    }

    let clicked_lat_lng = event.latlng;
    let clicked_lat = clicked_lat_lng.lat;
    let clicked_lng = clicked_lat_lng.lng;

    if (clicked_lat < 0 || clicked_lng < 0) {
        jQuery('#pws_map_location').val('');
        user_marker_layer.clearLayers();
        store_marker_layer.clearLayers();
        return;
    }

    let clicked_location = {lat: clicked_lat, long: clicked_lng};

    // Two different situation in editing map location for user marker
    let is_admin_order_editing = pws_is_admin() && pws_map_admin_editing_enabled() && !pws_map_is_admin_tools_page();
    let is_user_editing = !pws_is_admin() && pws_map_is_checkout();

    if (is_admin_order_editing || is_user_editing) {
        // Set the map view to the clicked location for user
        map_object.setView([clicked_lat, clicked_lng], map_object.getZoom());
        pws_map_add_marker([clicked_lat, clicked_lng], vars.user_marker_url, user_marker_layer);
        jQuery('#pws_map_location').val(JSON.stringify(clicked_location));
        // Show lat and long in order editing mode
        pws_show_location_data(clicked_lat, clicked_lng);
        vars.user_lat = clicked_lat;
        vars.user_long = clicked_lng;
        pws_map_show_distance(vars);
    }

    // If admin tools page is active, add store marker
    let is_admin_settings_editing = pws_map_admin_editing_enabled() && pws_map_is_admin_tools_page();

    if (pws_is_admin() && is_admin_settings_editing) {
        pws_map_add_marker([clicked_lat, clicked_lng], vars.store_marker_url, store_marker_layer);
        jQuery('#pws_map\\[store_location\\]').val(JSON.stringify(clicked_location));
    }

    // Draw route if store marker and line drawing are enabled
    if (pws_is_admin() && !is_admin_settings_editing) {
        pws_map_draw_route([vars.store_lat, vars.store_long], [clicked_lat, clicked_lng], vars, map_object);
    }
}


/**
 * Set data global variables
 * */
function pws_map_set_variables(container) {
    // Extract map container ID
    var map_id = container.attr('id') || '';

    // Extract marker-related data attributes from the container
    let user_marker_color = container.data('user-marker-color');
    let store_marker_color = container.data('store-marker-color');

    let user_marker_url = container.data('user-marker-url');
    let store_marker_url = container.data('store-marker-url');

    let is_store_marker_enabled = container.data('store-marker-enable');

    // Extract ORS-related data
    let draw_line_color = container.data('store-draw-line-color');

    // Extract location-related data attributes
    let user_lat = container.data('center-lat');
    let user_long = container.data('center-long');
    let store_lat = container.data('store-lat');
    let store_long = container.data('store-long');

    let center_lat = user_lat;
    let center_long = user_long;
    if (pws_is_admin() && pws_map_is_admin_tools_page()) {
        center_lat = store_lat;
        center_long = store_long;
    }

    // Extract zoom level and other map-specific settings
    let map_zoom = container.data('zoom') || 13;
    let show_distance_type = container.data('show-distance-type');
    let user_has_location = container.data('user-has-location');

    // Neshan map specific data
    let type = container.data('type') || 'neshan';
    let poi = container.data('poi') || false;
    let traffic = container.data('traffic') || false;

    // Return all variables as an object
    return {
        map_id,
        user_marker_color,
        store_marker_color,
        user_marker_url,
        store_marker_url,
        is_store_marker_enabled,
        draw_line_color,
        user_lat,
        user_long,
        store_lat,
        store_long,
        center_lat,
        center_long,
        map_zoom,
        show_distance_type,
        user_has_location,
        type,
        poi,
        traffic
    };
}


function pws_map_geolocation(map_object, vars, marker_layer) {
    // API endpoint for geolocation-db
    var geolocation_url = "https://geolocation-db.com/json/";

    // Make an AJAX request to fetch the user's geolocation data
    jQuery.ajax({
        url: geolocation_url,
        method: 'GET',
        success: function (response) {
            // If the response is a string, parse it as JSON
            if (typeof response === 'string') {
                try {
                    response = JSON.parse(response);
                } catch (error) {
                    console.error('Error parsing JSON response:', error);
                    return;
                }
            }

            // Ensure response contains valid latitude and longitude
            if (typeof response.latitude === 'undefined' || typeof response.longitude === 'undefined') {
                console.error('Geolocation response did not contain valid coordinates.');
                return;
            }

            var lat = response.latitude;
            var lng = response.longitude;

            // Check if lat and lng are valid numbers before proceeding
            if (isNaN(lat) || isNaN(lng)) {
                console.error('Invalid latitude or longitude:', lat, lng);
                return;
            }

            // Create an object to store the lat/lng for setting input values
            var geolocation = {
                lat: lat,
                long: lng
            };

            // Add marker to the map using pws_map_add_marker method
            pws_map_add_marker([lat, lng], vars.user_marker_url, marker_layer);

            // Set the #pws_map_location value with the coordinates in JSON format
            jQuery('#pws_map_location').val(JSON.stringify(geolocation));

            // Optionally, center the map on the user's location
            map_object.setView([lat, lng], vars.map_zoom);

            // Draw route if store marker and line drawing are enabled
            if (pws_is_admin() && vars.is_store_marker_enabled) {
                pws_map_draw_route([vars.store_lat, vars.store_long], [lat, lng], vars, map_object);
            }
        },
        error: function (error) {
            // Handle any errors that occur during the request
            console.error('Error fetching geolocation data:', error);
        }
    });
}


/**
 * Init the store marker on the map
 * */
function pws_map_initialize_store_marker(marker_layer, vars) {
    // Add store marker if enabled
    if (!vars.is_store_marker_enabled && !pws_is_admin()) {
        return;
    }

    pws_map_add_marker([vars.store_lat, vars.store_long], vars.store_marker_url, marker_layer);
}


/**
 * Init the user marker on the map
 * */
function pws_map_initialize_user_marker(marker_layer, vars, map_object) {
    // Add user marker and center the map to user location
    if (!vars.user_has_location) {
        return;
    }

    pws_map_add_marker([vars.user_lat, vars.user_long], vars.user_marker_url, marker_layer);

    jQuery('#pws_map_location').val(JSON.stringify({
        lat: vars.user_lat,
        long: vars.user_long
    }));

    map_object.setView([vars.user_lat, vars.user_long], 15);

    // Draw route if is in admin area and line drawing are enabled
    if (pws_is_admin()) {
        pws_map_draw_route([vars.store_lat, vars.store_long], [vars.user_lat, vars.user_long], vars, map_object);
    }
}


/**
 * Controls over hiding the map
 * If map is hidden but location is required, It'll
 */
function pws_map_view_control(hide) {
    let map_container = jQuery('.pws-map__container');
    let required_location = jQuery('input[name="pws_map_required_location"]');

    if (map_container.length === 0) {
        return;
    }

    if (hide) {

        map_container.hide();

        if (required_location.length > 0) {
            required_location.val('0');
        }

    } else {

        map_container.show();

        if (required_location.length > 0) {
            required_location.val('1');
        }

    }

}


/**
 * Draw route between store and user on map
 * */
function pws_map_draw_route(start_coords, end_coords, vars, map_object, retryCount = 0) {
    // The line drawing is accessible when real distance type is on real
    // It shows the user the example routing of distance + When real distance type is selected ORS token will be accessible
    if (!vars.show_distance_type || vars.show_distance_type !== 'real') {
        return;
    }

    const MAX_RETRIES = 3;

    if (!pws_map_params.ORS_token) {
        console.error('لطفا کلید دسترسی معتبر OpenRouteService برای مسیریابی بین فروشگاه و کاربر را وارد کنید.');
        return;
    }

    // Construct the OpenRouteService URL for driving directions
    var pws_map_ORS_url = `https://api.openrouteservice.org/v2/directions/driving-car?api_key=${pws_map_params.ORS_token}&start=${start_coords[1]},${start_coords[0]}&end=${end_coords[1]},${end_coords[0]}`;

    // Make an AJAX request to the OpenRouteService API
    jQuery.ajax({
        url: pws_map_ORS_url,
        method: 'GET',
        success: function (pws_map_response) {
            if (pws_map_response.features && pws_map_response.features.length > 0) {
                var pws_map_coordinates = pws_map_response.features[0].geometry.coordinates;
                var pws_map_lat_lngs = pws_map_coordinates.map(function (pws_map_coord) {
                    return [pws_map_coord[1], pws_map_coord[0]];
                });

                // Remove the old polyline if it exists in vars
                if (vars.pws_map_polyline) {
                    map_object.removeLayer(vars.pws_map_polyline);
                }

                // Draw the polyline on the map
                vars.pws_map_polyline = L.polyline(pws_map_lat_lngs, {
                    color: vars.draw_line_color,
                    weight: 4
                }).addTo(map_object);
            } else {
                console.error('هیچ داده جغرافیایی از مسیر یافت نشد.');
            }
        },
        error: function (pws_map_error) {
            if (pws_map_error.status === 503 && retryCount < MAX_RETRIES) {
                // Retry the request after a delay (e.g., 2 seconds)
                console.warn(`سرویس در دسترس نیست، تلاش (${retryCount + 1}/${MAX_RETRIES})...`);
                setTimeout(function () {
                    pws_map_draw_route(start_coords, end_coords, vars, map_object, retryCount + 1);
                }, 2000);
            } else {
                console.error('خطا در دریافت اطلاعات مسیر: ', pws_map_error.responseText || pws_map_error.statusText);
            }
        }
    });
}


/**
 * Zoom on provinces
 * */
function pws_map_zoom_on_province(map_object, marker_layer) {
    let billing_state_element = jQuery("#billing_state");
    if (billing_state_element.length) {
        billing_state_element.on('change', function (e) {
            let user_input_address = 'استان ' + jQuery('#billing_state option:selected').text();
            pws_show_location_on_map(map_object, user_input_address, 10, marker_layer);
        });
    }

    let shipping_state_element = jQuery('#shipping_state');
    if (shipping_state_element.length) {
        shipping_state_element.on('change', function (e) {
            let user_input_address = 'استان ' + jQuery('#shipping_state option:selected').text();
            pws_show_location_on_map(map_object, user_input_address, 10, marker_layer);
        });
    }
}


(function ($) {
    $(document).ready(function () {
        /**
         * Toggle show map in billing and shipping forms of woocommerce checkout
         * */
        $("#ship-to-different-address-checkbox").change(function () {
            if (this.checked) {
                $('.woocommerce-billing-fields').find('.pws-map__container').hide();
            } else {
                $('.woocommerce-billing-fields').find('.pws-map__container').show();
            }
        });

        /**
         * Disables the map when virtual products exists in the Cart
         * Using WC()->cart->needs_shipping() method
         */
        if (pws_map_is_checkout() && pws_map_params.needs_shipping !== "1") {
            // Hide the map
            pws_map_view_control(true);
        }

        /**
         * Toggle alert admin about editing the map
         * */
        let pws_map_admin_edit_checkbox = $("#pws-map-admin-edit");
        if (pws_map_admin_edit_checkbox.length) {
            pws_map_admin_edit_checkbox.on('change', function () {
                let pws_map_admin_edit_checkbox_label = $('label[for="' + $(this).attr('id') + '"]');
                if (!this.checked) {
                    pws_map_admin_edit_checkbox_label.html('ویرایش نقشه')
                    pws_map_admin_edit_checkbox_label.css({
                        'color': '#2271b1',
                        'border-color': '#2271b1'
                    });
                    return;
                }
                pws_map_admin_edit_checkbox_label.css({
                    'color': '#b14a22',
                    'border-color': '#b14a22'
                });
                pws_map_admin_edit_checkbox_label.html('در حال ویرایش')
            });
        }

    });


    $(document).on('ajaxComplete', function () {
        /**
         * Responsive mobile map
         * */
        if ($('.pws-map__container').length) {

            if ($(window).width() <= 768) {

                $('.pws-map__container').each(function () {
                    let current_map_style = $(this).attr("style") || "";
                    $(this).attr("style", current_map_style + "; min-width: 0% !important;");
                });

                $(window).trigger('resize');

            }

        }

        /**
         * Remove | from leaflet attribution
         * */
        let leaflet_control_attribution = $('.leaflet-control-attribution');
        if (leaflet_control_attribution.length) {
            leaflet_control_attribution.html(function (_, html) {
                return html.replace(/\s*\|\s*/g, '');
            });
        }

        // Controls over enabled shipping methods
        function pws_map_log_shipping_method(input_enabled_methods) {
            let selected_method = $('input[name="shipping_method[0]"]:checked').val();

            if (selected_method === undefined) {
                selected_method = $('input[name="shipping_method[0]"]').val();
            }

            if (selected_method === undefined) {
                return;
            }

            let enabled_methods = [];

            try {
                enabled_methods = JSON.parse(input_enabled_methods);
            } catch (e) {
                enabled_methods = ['all_shipping_methods'];
                console.log('روش های حمل و نقل نامعتبر هستند. نقشه در تمامی روش های حمل و نقل نمایش داده خواهد شد.');
            }

            // Always show the map if "all_shipping_methods" is included
            if (enabled_methods.includes('all_shipping_methods')) {
                return;
            }

            // Dynamically remove variants from enabled methods
            let base = [];
            enabled_methods.forEach(string => {
                if (string.indexOf(':') === -1) {  // No colon means base method
                    base.push(string);
                }
            });

            enabled_methods = enabled_methods.filter(string => {
                // Keep the method if it is not a variant (doesn't have a colon)
                return !base.some(baseMethod => string.startsWith(baseMethod + ':')) || base.includes(string);
            });


            // Check if the selected method is either a base method or a valid variant of an enabled base method
            if (!enabled_methods.some(baseMethod => {
                // If the selected method doesn't have a colon, it's a base method
                if (selected_method.indexOf(':') === -1) {
                    return baseMethod === selected_method;
                }

                // If the selected method is a variant, check if its base method is in the enabled methods
                let baseMethodSelected = selected_method.split(':')[0];
                return enabled_methods.includes(baseMethodSelected) || enabled_methods.includes(selected_method);

            })) {
                pws_map_view_control(true); // Hide map if not valid
            } else {
                pws_map_view_control(false); // Show map if valid
            }
        }


        // Control the shipping method, only when there's enabled shipping methods element exist
        let pws_map_enabled_shipping_methods = $('input[name="pws_map_enabled_shipping_methods"]');
        let pws_map_shipping_method_field = $('input[name="shipping_method[0]"]');
        if (pws_map_enabled_shipping_methods.length && pws_map_shipping_method_field.length) {
            pws_map_enabled_shipping_methods = pws_map_enabled_shipping_methods.val();
            pws_map_log_shipping_method(pws_map_enabled_shipping_methods);
            $(document).on('change, load', 'input[name="shipping_method[0]"]', function () {
                pws_map_log_shipping_method(pws_map_enabled_shipping_methods);
            });
        }
    });
})(jQuery);