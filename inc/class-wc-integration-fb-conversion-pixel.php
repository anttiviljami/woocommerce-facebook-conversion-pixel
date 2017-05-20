<?php

class WC_Integration_Facebook_Conversion_Pixel extends WC_Integration {
  /**
   * Constructor, sets up all the actions
   */
  public function __construct() {
    $this->id                 = 'wc-fb-conversion-pixel';
    $this->method_title       = __( 'Facebook', 'wc-fb-conversion-pixel' );
    $this->method_description = __( 'Set up the Facebook conversion pixel and event tracking for WooCommerce.', 'wc-fb-conversion-pixel' );

    // Load the settings.
    $this->init_form_fields();
    $this->init_settings();

    // load our preset tracking id from options
    $this->fbid = $this->get_option( 'fbid' );
    $this->event_viewcontent = 'no' !== $this->get_option( 'fb_event_viewcontent' );
    $this->event_addtocart = 'no' !== $this->get_option( 'fb_event_addtocart' );
    $this->event_checkout = 'no' !== $this->get_option( 'fb_event_checkout' );
    $this->event_purchase = 'no' !== $this->get_option( 'fb_event_purchase' );

    // add WooCommerce settings tab page
    add_action( 'woocommerce_update_options_integration_' .  $this->id, array( &$this, 'process_admin_options' ) );

    // add the tracking pixel to all pages in the frontend
    add_action( 'wp_head', array( &$this, 'fb_tracking_pixel') );

    // add to cart event
    add_action( 'woocommerce_after_add_to_cart_button', array( &$this, 'add_to_cart' ) ); // single product
    add_action( 'woocommerce_pagination', array( &$this, 'loop_add_to_cart' ) ); // loop
  }

  /**
   * User configurable settings
   */
  public function init_form_fields() {
    $this->form_fields = apply_filters( 'wc_integration_fb_conversion_pixel_fields', array(
      'fbid' => array(
        'title' => __( 'Facebook Tracking ID', 'wc-fb-conversion-pixel' ),
        'type' => 'text',
        'description' => __( "The numerical unique ID from your Facebook Pixel Tracking Code. Copied from this line: <code>fbq('init', '<your ID>');</code>", 'wc-fb-conversion-pixel' ),
        'placeholder' => '123456789',
      ),
      'fb_event_viewcontent' => array(
        'title' => __( 'Track ViewContent Events', 'wc-fb-conversion-pixel' ),
        'type' => 'checkbox',
        'label' => __( 'Enable event tracking for when a user views a product page.', 'wc-fb-conversion-pixel' ),
        'default' => 'yes',
      ),
      'fb_event_addtocart' => array(
        'title' => __( 'Track AddToCart Events', 'wc-fb-conversion-pixel' ),
        'type' => 'checkbox',
        'label' => __( 'Enable event tracking for when a user adds a product to their shopping cart.', 'wc-fb-conversion-pixel' ),
        'default' => 'yes',
      ),
      'fb_event_checkout' => array(
        'title' => __( 'Track InitiateCheckout Events', 'wc-fb-conversion-pixel' ),
        'type' => 'checkbox',
        'label' => __( 'Enable event tracking for when a user enters the Checkout page.', 'wc-fb-conversion-pixel' ),
        'default' => 'yes',
      ),
      'fb_event_purchase' => array(
        'title' => __( 'Track Purchase Events', 'wc-fb-conversion-pixel' ),
        'type' => 'checkbox',
        'label' => __( 'Enable event tracking for when a user has succesfully made an order.', 'wc-fb-conversion-pixel' ),
        'default' => 'yes',
      ),
    ));
  }

  /**
   * Event tracking for product page add to cart
   */
  public function add_to_cart() {
    if( $this->event_addtocart ) {
      global $post;
      $product = wc_get_product( $post->ID );
      $params = array();
      $params['content_name'] = $product->get_title();
      $params['content_ids'] = array( $product->id );
      $params['content_type'] = 'product';
      $params['value'] = floatval( $product->get_price() );
      $params['currency'] = get_woocommerce_currency();
      // TODO: variable products
      $this->fb_track_event( 'AddToCart', '.button.alt', $params );
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
document,'script','https://connect.facebook.net/en_US/fbevents.js');

fbq('init', '<?php echo esc_html( $this->fbid ); ?>');
fbq('track', 'PageView');

<?php if( is_singular( 'product' ) && $this->event_viewcontent ) : ?>
<?php
    global $post;
    $product = wc_get_product( $post->ID );
    $params = array();
    $params['content_name'] = $product->get_title();
    $params['content_ids'] = array( $product->get_sku() ? $product->get_sku() : $product->id );
    $params['content_type'] = 'product';
    $params['value'] = floatval( $product->get_price() );
    $params['currency'] = get_woocommerce_currency();
?>
fbq('track', 'ViewContent', <?php echo json_encode( $params ); ?>);
<?php endif; ?>

<?php if( is_order_received_page() && $this->event_purchase ) : ?>
<?php
    global $wp;
    $params = array();

    $order_id = isset( $wp->query_vars['order-received'] ) ? $wp->query_vars['order-received'] : 0;
    if( $order_id ) {
      $params['order_id'] = $order_id;
      $order = new WC_Order( $order_id );
      if( $order->get_items() ) {
        $productids = array();
        foreach ( $order->get_items() as $item ) {
          $product = $order->get_product_from_item( $item );
          $productids[] = $product->get_sku() ? $product->get_sku() : $product->id;
        }
        $params['content_ids'] = $productids;
      }
      $params['content_type'] = 'product';
      $params['value'] = $order->get_total();
      $params['currency'] = get_woocommerce_currency();
    }
?>
fbq('track', 'Purchase', <?php echo json_encode( $params ); ?>);

<?php elseif( is_checkout() && $this->event_checkout ) : ?>
<?php
    // get $cart to params
    $cart = WC()->cart->get_cart();
    $productids = array();
    foreach($cart as $id => $item) {
      $product_id = $item['variation_id'] ? $item['variation_id'] : $item['product_id'];
      $product = new WC_Product( $product_id );
      $productids[] = $product->get_sku() ? $product->get_sku() : $product->id;
    }
    $params = array();
    $params['num_items'] = WC()->cart->cart_contents_count;
    $params['value'] = WC()->cart->total;
    $params['currency'] = get_woocommerce_currency();
    $params['content_ids'] = $productids;
?>
fbq('track', 'InitiateCheckout', <?php echo json_encode( $params ); ?>);
<?php endif; ?>


</script>
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
<?php if( !empty( $params ) ) : ?>
    var params = <?php echo json_encode( $params ); ?>;
<?php else : ?>
    var params = {};
<?php endif; ?>

    fbq('track', '<?php echo esc_js( $name ); ?>', params);
  });
  console.log('Facebook Tracking Enabled for:', $('<?php echo esc_js( $selector ); ?>'));
})(jQuery);
</script>
<?php
  }
}
