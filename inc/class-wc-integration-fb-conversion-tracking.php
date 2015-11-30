<?php

class WC_Integration_Facebook_Conversion_Tracking extends WC_Integration {
  /**
   * Constructor, sets up all the actions
   */
  public function __construct() {
    $this->id                 = 'wc-fb-conversion-tracking';
    $this->method_title       = __( 'Facebook', 'wc-fb-conversion-tracking' );
    $this->method_description = __( 'Set up the Facebook conversion pixel and event tracking for WooCommerce.', 'wc-fb-conversion-tracking' );

    // Load the settings.
    $this->init_form_fields();
    $this->init_settings();

    // load our preset tracking id from options
    $this->fbid = $this->get_option( 'fbid', false );
    $this->event_addtocart = $this->get_option( 'fb_event_addtocart', true );

    // add WooCommerce settings tab page
    add_action( 'woocommerce_update_options_integration_' .  $this->id, array( &$this, 'process_admin_options' ) );

    // add the tracking pixel to all pages in the frontend
    add_action( 'wp_head', array( &$this, 'fb_tracking_pixel') );

    // add to cart event
    add_action( 'woocommerce_after_add_to_cart_button', array( &$this, 'add_to_cart' ) );
    add_action( 'woocommerce_pagination', array( &$this, 'loop_add_to_cart' ) );

  }

  /**
   * User configurable settings
   */
  public function init_form_fields() {
    $this->form_fields = apply_filters( 'wc_integration_fb_tracking_fields', array(
      'fbid' => array(
        'title' => __( 'Facebook Tracking ID', 'wc-fb-conversion-tracking' ),
        'type' => 'text',
        'description' => __( "The numerical unique ID from your Facebook Pixel Tracking Code. Copied from this line: <code>fbq('init', '<your ID>');</code>", 'wc-fb-conversion-tracking' ),
        'placeholder' => '123456789',
      ),
      'fb_event_addtocart' => array(
        'title' => __( 'Track AddToCart Events', 'wc-fb-conversion-tracking' ),
        'type' => 'checkbox',
        'label' => __( 'Enable event tracking for when a user adds a product to their shopping cart.', 'wc-fb-conversion-tracking' ),
        'default' => 'yes',
      ),
    ));
  }

  /**
   * Event tracking for product page add to cart
   */
  public function add_to_cart() {
    if( $this->event_addtocart ) {
      $this->fb_track_event( 'AddToCart', '.button.alt' );
    }
  }

  /**
   * Event tracking for loop add to cart
   */
  public function loop_add_to_cart() {
    if( $this->event_addtocart ) {
      $this->fb_track_event( 'AddToCart', '.button.add_to_cart_button' );
    }
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

  /**
   * Output inline javascript to bind an fbq event to the $.click() event of a selector
   */
  public function fb_track_event( $name, $selector, $params = array() ) {
    if( !$this->fbid ) {
      return;
    }
?>
<script>
(function($) {
  $('<?php echo esc_js( $selector ); ?>').click(function() {
    fbq('track', '<?php echo esc_js( $name ); ?>');
  });
  console.log('Facebook Tracking Enabled for:', $('<?php echo esc_js( $selector ); ?>'));
})(jQuery);
</script>
<?php
  }
}

