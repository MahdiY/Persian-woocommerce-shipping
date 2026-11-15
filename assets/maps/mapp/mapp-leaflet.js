(function ($) {
    $(document).ready(function () {

        /**
         * Set the api keys
         * */
        const PWS_MAP_MAPP_API_KEY = atob(pws_map_params.api_key);

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


            L.TileLayer.WMSHeader = L.TileLayer.WMS.extend({
                initialize: function (url, options, headers) {
                    L.TileLayer.WMS.prototype.initialize.call(this, url, options);
                    this.headers = headers;
                },
                createTile: function (coords, done) {
                    const url = this.getTileUrl(coords);
                    const img = document.createElement('img');
                    pws_map_call_ajax(
                        url,
                        function (response) {
                            img.src = URL.createObjectURL(response);
                            done(null, img);
                        },
                        this.headers
                    );
                    return img;
                }
            });

            L.TileLayer.wmsHeader = function (url, options, headers) {
                return new L.TileLayer.WMSHeader(url, options, headers);
            };

            /**
             * Create map instance with Leaflet
             * */
            var pws_map_object = L.map(pws_map_vars.map_id, {
                minZoom: 1,
                maxZoom: 20,
                crs: L.CRS.EPSG3857,
                center: [pws_map_vars.user_lat, pws_map_vars.user_long],
                zoom: pws_map_vars.map_zoom,
            });

            L.TileLayer.wmsHeader(
                "https://map.ir/shiveh",
                {
                    attribution: 'map.ir &copy;',
                    layers: "Shiveh:Shiveh",
                    format: "image/png",
                    minZoom: 1,
                    maxZoom: 20,
                    tileSize: 128
                },
                [
                    {
                        header: "x-api-key",
                        value: PWS_MAP_MAPP_API_KEY
                    }
                ]
            ).addTo(pws_map_object);

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
