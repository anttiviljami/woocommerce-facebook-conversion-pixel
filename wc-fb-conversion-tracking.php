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

if (!class_exists('WooCommerce_Facebook_Conversion_Tracking')) {
  class WooCommerce_Facebook_Conversion_Tracking {
    public static $instance;

    private $fbid = false; // this is wehere we store the tracking id

    public static function init() {
      if ( is_null( self::$instance ) ) {
        self::$instance = new WooCommerce_Facebook_Conversion_Tracking();
      }
      return self::$instance;
    }

    private function __construct() {
      // load our preset tracking id from options
      $this->fbid = get_option( 'wc_settings_fbid', false );

      // add WooCommerce settings tab page
      add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_tab' ), 50 );
      add_action( 'woocommerce_settings_tabs_fb_tracking', array( $this, 'settings_tab' ) );
      add_action( 'woocommerce_update_options_fb_tracking', array( $this, 'update_settings' ) );

      // add the tracking pixel to all pages in the frontend
      add_action( 'wp_head', array( $this, 'fb_tracking_pixel') );
    }

    /**
     * Load our plugin textdomain
     */
    public static function load_our_textdomain() {
      load_plugin_textdomain( 'wc-fb-conversion-tracking', false, dirname( plugin_basename(__FILE__) ) . '/lang/' );
    }

    /**
     * Add a WooCommerce Settings tab for plugin settings
     */
    public static function add_settings_tab( $settings_tabs ) {
      $settings_tabs['fb_tracking'] = __( 'Facebook', 'wc-fb-conversion-tracking' );
      return $settings_tabs;
    }

    /**
     * User configurable settings
     */
    public static function get_settings() {
      return apply_filters( 'wc_settings_tab_fb_tracking', array(
        'section_title' => array(
          'name'     => __( 'Facebook Conversion Pixel', 'wc-fb-conversion-tracking' ),
          'type'     => 'title',
          'desc'     => '',
          'id'       => 'wc_settings_fb_tracking_section_title'
        ),
        'title' => array(
          'name' => __( 'Facebook Pixel ID', 'wc-fb-conversion-tracking' ),
          'type' => 'text',
          'desc' => __( "The numerical unique ID from your Facebook Pixel Tracking Code. Copied from this line: <code>fbq('init', '&lt;your ID&gt;');</code>", 'wc-fb-conversion-tracking' ),
          'placeholder' => '123456789',
          'id'   => 'wc_settings_fbid'
        ),
        'section_end' => array(
          'type' => 'sectionend',
          'id' => 'wc_settings_fb_tracking_section_end'
        )
      ) );
    }

    /**
     * Render our Settings tab
     */
    public static function settings_tab() {
      woocommerce_admin_fields( self::get_settings() );
    }

    /**
     * Save options from our settings tab
     */
    public static function update_settings() {
      woocommerce_update_options( self::get_settings() );
    }

    /**
     * Print the tracking pixel to wp_head
     */
    public function fb_tracking_pixel() {
      // only show the pixel if a tracking ID is defined
      if( !$this->fbid ) {
        return;
      }
?>
<!-- Facebook Pixel Code -->
<script>
!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
document,'script','//connect.facebook.net/en_US/fbevents.js');

fbq('init', '<?php echo esc_html( $this->fbid ); ?>');
fbq('track', "PageView");</script>
  <noscript><img height="1" width="1" style="display:none"
  src="https://www.facebook.com/tr?id=<?php echo esc_html( $this->fbid ); ?>&ev=PageView&noscript=1"
  /></noscript>
  <!-- End Facebook Pixel Code -->
<?php
    }
  }
}

// init the plugin
$woocommerce_facebook_conversion_tracking = WooCommerce_Facebook_Conversion_Tracking::init();

