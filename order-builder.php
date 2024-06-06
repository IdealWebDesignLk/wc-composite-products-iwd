<?php
/*
* Plugin Name: Multi Step Order Builder IWD
* Plugin URI: https://idealwebdesign.lk/
* Description: WooCommerce multi step order builder plugin.
* Version: 1.0.6
* Author: Ideal Web Design
* Author URI: https://idealwebdesign.lk/
* Support: https://idealwebdesign.lk/
* License: GPL-2.0+
* Text Domain: wc-cp
* Domain Path: /languages/
*/
if ( !defined('ABSPATH') ) {
	wp_die();
}
if ( !defined('WP_CP_URL') ) {
	define('WP_CP_URL', plugin_dir_url(__FILE__));
}
if ( !defined('WP_CP_DIR') ) {
	define('WP_CP_DIR', plugin_dir_path(__FILE__));
}
if ( !class_exists('WCCP_Composite_Products') ) {
	class WCCP_Composite_Products {
		public function __construct() {
			if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
				require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
			}
			if ( in_array('woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option('active_plugins')) ) || is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) {
				self::wccp_init_plugin_files();
				add_action('plugins_loaded', array(__CLASS__, 'wccp_composite_product_type'));
			} else {
				add_action('admin_notices', array(__CLASS__, 'wccp_admin_notice'));
			}
		}
		public static function wccp_init_plugin_files() {
			if ( function_exists( 'load_plugin_textdomain' ) ) {
				load_plugin_textdomain('wc-cp', false, dirname(plugin_basename(__FILE__)) . '/languages/');
			}
			require_once WP_CP_DIR . 'include/wccp-common.php';
			if ( is_admin() ) {
				require_once WP_CP_DIR . 'include/wccp-admin.php';
			} else {
				require_once WP_CP_DIR . 'include/wccp-frontend.php';
				require_once WP_CP_DIR . 'include/wccp-cart.php';
			}
		}
		public static function wccp_composite_product_type() {
			require_once WP_CP_DIR . 'include/wccp-product-options.php';
		}
		public static function wccp_admin_notice() {
			global $pagenow;
	        if ( 'plugins.php' === $pagenow ) {
	            $class = esc_attr( 'notice notice-error is-dismissible' );
	            $message = esc_html__('WooCommerce multi step order builder plugin needs WooCommerce to be installed and active.', 'wc-cp');
	            printf('<div class="%1$s"><p>%2$s</p></div>', $class, $message);
		    }
		}
	}
	new WCCP_Composite_Products();
}