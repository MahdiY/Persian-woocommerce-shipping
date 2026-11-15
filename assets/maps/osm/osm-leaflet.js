(function ($) {
    $(document).ready(function () {
        // Find map containers
        var pws_map_containers = $('.pws-map__container');
        if (pws_map_containers.length === 0) {
            return false;
        }

        pws_map_containers.each(function (pws_map_index, pws_map_element) {
            var pws_map_container = $(pws_map_element);

            pws_map_customize(pws_map_container);
            const pws_map_vars = pws_map_set_variables(pws_map_container);

            // Create the Leaflet map
            var pws_map_object = L.map(pws_map_vars.map_id, {
                center: [pws_map_vars.center_lat, pws_map_vars.center_long],
                zoom: pws_map_vars.map_zoom,
            });

            // Add OSM Tile Layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: 'OpenStreetMap &copy;'
            }).addTo(pws_map_object);

            // Handle map clicks (e.g., setting a marker)
            pws_map_object.on('click', function (event) {
                pws_map_on_click(event, pws_map_object, pws_map_vars, pws_map_user_marker_layer, pws_map_store_marker_layer)
            });

            /**
             * Handle markers
             * */
            var pws_map_user_marker_layer = L.layerGroup().addTo(pws_map_object);
            var pws_map_store_marker_layer = L.layerGroup().addTo(pws_map_object);

            pws_map_initialize_store_marker(pws_map_store_marker_layer, pws_map_vars);
            pws_map_initialize_user_marker(pws_map_user_marker_layer, pws_map_vars, pws_map_object);


            if (pws_is_admin()) {
                // Handle showing distance between user and store
                pws_map_show_distance(pws_map_vars);
                pws_show_location_data(pws_map_vars.user_lat, pws_map_vars.user_long, pws_map_vars);

                // Fix incomplete map
                setTimeout(function () {
                    pws_map_object.invalidateSize()
                }, 100);
            }


            if (!pws_is_admin()) {
                // Handle Geolocation if enabled
                pws_map_add_geolocate_control(pws_map_object);
            }

            $(document).ajaxComplete(function (event, xhr, settings) {
                // Zoom on provinces
                pws_map_zoom_on_province(pws_map_object, pws_map_user_marker_layer);
            });

        });

    });

})(jQuery);
