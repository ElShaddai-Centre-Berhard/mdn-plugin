<?php
/**
 * Shortcode: [mdn_map]
 * Leaflet.js map with a pin for every published partner that has lat/lng set.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class MDN_Partner_Map {

    public static function init() {
        add_shortcode( 'mdn_map', array( __CLASS__, 'render' ) );
    }

    public static function render() {
        wp_enqueue_style(
            'leaflet-css',
            'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
            array(),
            '1.9.4'
        );
        wp_enqueue_script(
            'leaflet-js',
            'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
            array(),
            '1.9.4',
            true
        );

        $query = new WP_Query( array(
            'post_type'      => 'partner',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'meta_query'     => array(
                array( 'key' => '_mdn_lat', 'compare' => 'EXISTS' ),
                array( 'key' => '_mdn_lng', 'compare' => 'EXISTS' ),
            ),
        ) );

        $markers = array();
        while ( $query->have_posts() ) {
            $query->the_post();
            $id  = get_the_ID();
            $lat = get_post_meta( $id, '_mdn_lat', true );
            $lng = get_post_meta( $id, '_mdn_lng', true );

            if ( $lat && $lng ) {
                $terms    = get_the_terms( $id, 'partner_category' );
                $category = ( ! empty( $terms ) && ! is_wp_error( $terms ) ) ? $terms[0]->name : '';

                $markers[] = array(
                    'lat'      => floatval( $lat ),
                    'lng'      => floatval( $lng ),
                    'title'    => get_the_title(),
                    'url'      => get_permalink(),
                    'thumb'    => get_the_post_thumbnail_url( $id, 'thumbnail' ),
                    'location' => get_post_meta( $id, '_mdn_location', true ),
                    'category' => $category,
                );
            }
        }
        wp_reset_postdata();

        $map_id = 'mdn-map-' . uniqid();

        ob_start();
        ?>
        <div id="<?php echo esc_attr( $map_id ); ?>" class="mdn-map-wrap"></div>

        <script>
        document.addEventListener('DOMContentLoaded', function () {
            var map = L.map('<?php echo esc_js( $map_id ); ?>').setView([3.1390, 101.6869], 6);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(map);

            var markers = <?php echo wp_json_encode( $markers ); ?>;

            markers.forEach(function (m) {
                var popup = '<div class="mdn-map-popup">';
                if (m.thumb) popup += '<img src="' + m.thumb + '" alt="' + m.title + '" />';
                popup += '<strong>' + m.title + '</strong>';
                if (m.category) popup += '<span class="mdn-map-tag">' + m.category + '</span>';
                if (m.location) popup += '<small>' + m.location + '</small>';
                popup += '<a href="' + m.url + '">View profile &rarr;</a>';
                popup += '</div>';

                L.marker([m.lat, m.lng]).addTo(map).bindPopup(popup);
            });

            if (markers.length === 0) {
                document.getElementById('<?php echo esc_js( $map_id ); ?>').innerHTML =
                    '<p class="mdn-map-empty">No partners with map locations yet.</p>';
            }
        });
        </script>
        <?php
        return ob_get_clean();
    }
}
