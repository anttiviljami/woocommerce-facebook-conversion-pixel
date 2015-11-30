<?php
/**
 * Plugin name: WooCommerce Facebook Conversion Tracking
 * Plugin URI: https://github.com/anttiviljami/wc-fb-conversion-tracking
 * Description: Set up the Facebook conversion pixel and event tracking for WooCommerce
 * Version: 0.1
 * Author: Seravo Oy
 * Author: http://seravo.fi
 * License: GPLv3
 * Text Domain: wc-fb-conversion-tracking
 */

/** Copyright 2015 Seravo Oy
  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 3, as
  published by the Free Software Foundation.
  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  GNU General Public License for more details.
  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( !class_exists('WooCommerce_Facebook_Conversion_Tracking')) :

class WooCommerce_Facebook_Conversion_Tracking {
  public static $instance;

  public static function init() {
    if ( is_null( self::$instance ) ) {
      self::$instance = new WooCommerce_Facebook_Conversion_Tracking();
    }
    return self::$instance;
  }

  private function __construct() {
    add_action( 'plugins_loaded', array( $this, 'load_our_textdomain' ) );
    add_action( 'plugins_loaded', array( $this, 'load_integrations' ) );
  }

  /**
   * Load our plugin textdomain
   */
  public static function load_our_textdomain() {
    load_plugin_textdomain( 'wc-fb-conversion-tracking', false, dirname( plugin_basename(__FILE__) ) . '/lang/' );
  }

  /**
   * Load integration classes
   */
  public static function load_integrations() {
    if ( class_exists( 'WC_Integration' ) ) {
      // load our integration class
      include_once 'inc/class-wc-integration-fb-conversion-tracking.php';

      // add to the WooCommerce settings page
      add_filter( 'woocommerce_integrations', __CLASS__ . '::add_integration' );
    }
  }

  /**
   * Add integration settings pages
   */
  public static function add_integration($integrations) {
    $integrations[] = 'WC_Integration_Facebook_Conversion_Tracking';
    return $integrations;
  }
}

endif;

// init the plugin
$woocommerce_facebook_conversion_tracking = WooCommerce_Facebook_Conversion_Tracking::init();
