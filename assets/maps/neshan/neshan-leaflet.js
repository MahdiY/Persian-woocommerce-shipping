(function ($) {
    $(document).ready(function () {
        /**
         * Set the api keys
         * */
        const PWS_MAP_NESHAN_API_KEY = atob(pws_map_params.api_key);
        /**
         * Take out the map containers and loop on them to create multiple maps
         * */
        var pws_map_containers = $('.pws-map__container');

        if (pws_map_containers.length === 0) {
            return false;
        }

        pws_map_containers.each(function (index, element) {
            var pws_map_container = $(element);
            pws_map_customize(pws_map_container);
            const pws_map_vars = pws_map_set_variables(pws_map_container);

            /**
             * Create map instance with Leaflet
             * */
            var pws_map_object = new L.Map(pws_map_vars.map_id, {
                key: PWS_MAP_NESHAN_API_KEY,
                maptype: pws_map_vars.type,
                poi: pws_map_vars.poi,
                traffic: pws_map_vars.traffic,
                center: [pws_map_vars.user_lat, pws_map_vars.user_long],
                zoom: pws_map_vars.map_zoom,
            });

            /**
             * Add click event to get lat/long of clicked point and place a new user marker
             */
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

}(jQuery));
