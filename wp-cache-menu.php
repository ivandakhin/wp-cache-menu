<?php
/**
 * Plugin Name:       WP Auto Posting
 * Plugin URI:        https://wp-cache-menu.com
 * Description:       Caches WordPress menus to improve performance.
 * Version:           1.0.0
 * Author:            Ivan Dakhin
 * Author URI:        https://github.com/ivandakhin/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-cache-menu
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Menu_Cache {
    private static $cache_time = WEEK_IN_SECONDS;

    public static function get_cached_menu( $menu_name ) {
        $cache_key = 'menu_cache_' . sanitize_key( $menu_name );
        $menu_html = get_transient( $cache_key );

        if ( false === $menu_html ) {
            $menu_html = wp_nav_menu( [
                'theme_location' => $menu_name,
                'container'      => false,
                'echo'           => false,
            ] );

            if ( ! empty( $menu_html ) ) {
                set_transient( $cache_key, $menu_html, self::$cache_time );
            }
        }

        return $menu_html;
    }

    public static function purge_menu_cache() {
        global $wpdb;
        $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_menu_cache_%'" );
    }

    public static function filter_wp_nav_menu( $nav_menu, $args ) {
        return self::get_cached_menu( $args->theme_location );
    }
}

// Hook into menu update to clear cache.
add_action( 'wp_update_nav_menu', [ 'WP_Menu_Cache', 'purge_menu_cache' ] );

// Automatically cache and serve menus.
add_filter( 'wp_nav_menu', [ 'WP_Menu_Cache', 'filter_wp_nav_menu' ], 10, 2 );
