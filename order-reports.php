<?php
/*
Order Reports for WooCommerce is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
Order Reports for WooCommerce is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with Order Reports for WooCommerce. If not, see {URI to Plugin License}.
*/

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://finpose.com
 * @since             1.0.0
 * @package           finpose
 *
 * @wordpress-plugin
 * Plugin Name:       Order Reports for WooCommerce
 * Description:       Order Reports for WooCommerce by Payment Method, Order Status and Order Amount filtered by time frame selectors.
 * Version:           1.0.0
 * WC requires at least:  3.0.0
 * WC tested up to:       5.0.0
 * Author:            Finpose
 * Author URI:        https://finpose.com
 * Text Domain:       orfw
 * Domain Path:       /language
 *
 */


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) { die; }


define( 'ORFW_VERSION', '1.0.0' );
define( 'ORFW_DBVERSION', '1.0.0' );
define( 'ORFW_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ORFW_BASE_URL', plugin_dir_url( __FILE__ ) );
define( 'ORFW_ENV', 'production' );
define( 'ORFW_WP_URL', get_site_url() );
define( 'ORFW_WPADMIN_URL', get_admin_url() );


/**
 * Check if WooCommerce is installed & activated
 */
function orfw_is_woocommerce_activated() {
  $blog_plugins = get_option( 'active_plugins', array() );
  $site_plugins = is_multisite() ? (array) maybe_unserialize( get_site_option('active_sitewide_plugins' ) ) : array();

  if ( in_array( 'woocommerce/woocommerce.php', $blog_plugins ) || isset( $site_plugins['woocommerce/woocommerce.php'] ) ) {
      return true;
  } else {
      return false;
  }
}

/**
 * Generate error message if WooCommerce is not active
 */
function orfw_need_woocommerce() {
  $plugin_name = "Order Reports for WooCommerce";
  printf(
    '<div class="notice error"><p><strong>%s</strong></p></div>',
    sprintf(
        esc_html__( '%s requires WooCommerce 3.0 or greater to be installed & activated!', 'orfw' ),
        $plugin_name
    )
  );
}

/**
 * Return error if WooCommerce is not active
 */
if (orfw_is_woocommerce_activated()) {

  /**
   * Activation hook
   */
  function orfw_activate() {
    require_once ORFW_PLUGIN_DIR . 'includes/class-orfw-activator.php';
    orfw_Activator::activate();
  }

  /**
   * Deactivation hook
   */
  function orfw_deactivate() {
    require_once ORFW_PLUGIN_DIR . 'includes/class-orfw-deactivator.php';
    orfw_Deactivator::deactivate();
  }

  /**
   * Register activation/deactivation hooks
   */
  register_activation_hook( __FILE__, 'orfw_activate' );
  register_deactivation_hook( __FILE__, 'orfw_deactivate' );

  /**
   * If version mismatch, upgrade
   */
  if ( ORFW_VERSION != get_option('orfw_version' )) {
    add_action( 'plugin_loaded', 'orfw_activate' );
  }

  /**
   * Handle AJAX requests
   */
  add_action( 'wp_ajax_orfw', 'orfw_ajax_request' );
  function orfw_ajax_request(){
    if(current_user_can( 'view_woocommerce_reports' )) {
      require ORFW_PLUGIN_DIR . 'includes/class-orfw-ajax.php';
      $ajax = new orfw_Ajax();
      // Sanitize every POST data as string, additional sanitation will be applied inside methods when necessary
      $p = array_map('sanitize_text_field', $_POST);
      $ajax->run($p);
      wp_die();
    }
  }



  /**
   * Load Order Reports for WooCommerce
   */
  add_action( 'wp_loaded', function() {
    if(current_user_can( 'view_woocommerce_reports' )) {
      $user = wp_get_current_user();
      $roles = ( array ) $user->roles;
      if ( is_admin() || in_array("shop_manager", $roles)) {
        require ORFW_PLUGIN_DIR . 'includes/class-orfw.php';
        $plugin = new orfw();
        $plugin->run();
      }
    }
  }, 30 );

} else {
  add_action( 'admin_notices', 'orfw_need_woocommerce' );
  return;
}


