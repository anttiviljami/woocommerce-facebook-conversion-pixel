<?php
/**
 * Plugin name: WooCommerce Facebook Pixel Events
 * Plugin URI: https://github.com/Seravo/woocommerce-facebook-pixel-events
 * Description: Set up the Facebook conversion pixel and event tracking for WooCommerce
 * Version: 0.1
 * Author: Seravo Oy
 * Author: http://seravo.fi
 * License: GPLv3
 * Text Domain: wc-fb-pixel-events
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

if (!class_exists('WooCommerce_Facebook_Pixel_Events')) {
  class WooCommerce_Facebook_Pixel_Events {
    public static $instance;

    public static function init() {
      if ( is_null( self::$instance ) ) {
        self::$instance = new WooCommerce_Facebook_Pixel_Events();
      }
      return self::$instance;
    }

    private function __construct() {
    }

    /**
     * Load our textdomain
     */
    function load_our_textdomain() {
      load_plugin_textdomain( 'wc-fb-pixel-events', false, dirname( plugin_basename(__FILE__) ) . '/lang/' );
    }
  }
}

// init the plugin
$woocommerce_facebook_pixel_events = WooCommerce_Facebook_Pixel_Events::init();
