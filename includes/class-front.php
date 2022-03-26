<?php
/**
 * Front class for the plugin.
 *
 * @package 
 */

class Front {

    /**
     * Constructor class
     *
     * @access public
     * @return void
     */
    public function __construct() {

        add_shortcode( 'print_order_sample', array( $this, 'wps_render_add_sample_button' ) );

        // do product validation before add to cart
        add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'wps_can_order_sample_product' ), 40, 5 );

        // add our custom data for sample product to cart
        add_filter( 'woocommerce_add_cart_item_data', array( $this, 'wps_add_sample_product_data_to_cart_item_data' ), 99, 3 );

        add_filter( 'woocommerce_add_cart_item', array( $this, 'wps_set_sample_product_price_into_cart' ), 10, 2 );

        add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'wps_update_sample_product_price_from_session' ), 10, 3 );

        add_filter( 'woocommerce_cart_item_name', array( $this, 'wps_set_sample_prefix_to_product_name_into_cart' ), 10, 3 );

        add_filter( 'woocommerce_cart_widget_product_title', array( $this, 'wps_set_sample_prefix_to_product_name_into_cart_widget' ), 10, 2 );

        add_filter( 'woocommerce_cart_item_quantity', array( $this, 'wps_can_order_multiple_quantity_of_product' ), 10, 2 );

        add_action( 'woocommerce_add_order_item_meta', array( $this, 'wps_add_sample_keyword_to_order_item_meta' ), 10, 2 );

        add_action( 'woocommerce_before_calculate_totals', array( $this, 'wps_update_sample_product_price' ), 99, 1 );

        // for free shipping
        add_filter( 'woocommerce_package_rates', array( $this, 'wps_check_free_shipping_filter' ), 0, 1 );
    }

    /**
     * Print "Add Sample" button to product details page.
     *
     * @global WC_Product $product
     *
     * @since 1.0
     * @access private
     */
    private function wps_get_add_sample_form() {
        global $product;

        $is_sample_available = get_post_meta( $product->id, 'wps_cb_enable_sample_product', true );
        if ( $is_sample_available ) {
            ?>
            <form class="cart wc_product_sample" method="post" enctype='multipart/form-data'>
                <div class="wc_product_sample_wrapper">
                    <button type="submit" class="single_add_to_cart_button button alt "><?php _e( 'Add Sample' ); ?></button>
                    <input type="hidden" name="wps_sample_product" id="wps_sample_product" value="1" />
                    <input type="hidden" name="add-to-cart" id="sample_add_to_cart" value="<?php esc_attr_e( intval( $product->id ) ); ?>">
                </div>
            </form>
            <?php
        }
    }

    /**
     * Render the sample button
     * 
     * @since 1.0
     * @return string HTML 
     */
    public function wps_render_add_sample_button() {
        global $product;

        ob_start();

        $this->wps_get_add_sample_form();

        return ob_get_clean();
    }

    /**
     * Check below conditions before adding sample product to the cart
     *  1. If original product is already in cart then do not allow to add sample product to cart.
     *  2. If sample product is already in cart then do not allow to add more than one.
     *
     * @param bool $passed
     * @param int $product_id
     * @param int $quantity
     * @param int $variation_id
     * 
     * @since 1.0
     * @return boolean
     */
    public function wps_can_order_sample_product( $passed, $product_id, $quantity, $variation_id = 0, $variations ) {

        // is it a sample product?
        if ( isset( $_REQUEST[ 'wps_sample_product' ] ) ) {

            global $woocommerce;

            $cart_items  = $woocommerce->cart->get_cart();
            $unique_key  = md5( $product_id . 'wps_sample_product' );

            foreach ( $cart_items as $cart_id_key => $cart_item ) {

                if ( $cart_item[ 'unique_key' ] === $unique_key ) {
                    wc_add_notice( __( 'You already have this sample in your basket.' ), 'error' );
                    return false;
                }

                if ( intval( $cart_item[ 'product_id' ] ) === intval( $product_id ) ) {
                    wc_add_notice( __( 'You cannot add the sample because you already have this product in your basket.' ), 'error' );
                    return false;
                }

            }
        }

        return $passed;
    }

    /**
     * Add custom data for sample product to cart
     *
     * @param array $cart_item_data
     * @param int $product_id
     * @param int $variation_id
     *
     * @since 1.0
     * @return array
     */
    public function wps_add_sample_product_data_to_cart_item_data( $cart_item_data, $product_id, $variation_id ) {

        if ( isset( $_REQUEST[ 'wps_sample_product' ] ) && get_post_meta( $product_id, 'wps_cb_enable_sample_product' ) ) {

            $cart_item_data[ 'wps_sample_product' ]    = true;
            $cart_item_data[ 'unique_key' ]              = md5( $product_id . 'wps_sample_product' );
            
        }

        return $cart_item_data;
    }

    /**
     * Set sample product price
     *
     * @param array $cart_item
     * @param string $cart_item_key
     *
     * @since 1.0
     * @return array
     */
    public function wps_set_sample_product_price_into_cart( $cart_item, $cart_item_key ) {

        if ( isset( $cart_item[ 'wps_sample_product' ] ) && $cart_item[ 'wps_sample_product' ] === true ) {
            $cart_item[ 'data' ]->price = 0;
        }

        return $cart_item;
    }

    /**
     * Filter the cart data from session if its exist and update the price
     *
     * @param WC_Cart $cart_content
     * @param array $value
     * @param string $key
     * 
     * @since 1.0
     * @return WC_Cart object
     */
    public function wps_update_sample_product_price_from_session( $cart_content, $value, $key ) {

        if ( isset( $value[ 'wps_sample_product' ] ) && $value[ 'wps_sample_product' ] ) {
            $cart_content[ 'wps_sample_product' ]  = true;
            $cart_content[ 'unique_key' ]            = $value[ 'unique_key' ];

            $product_id              = $cart_content[ 'product_id' ];
            $sample_product_price    = get_post_meta( $product_id, 'wps_sample_product_price', true ) ? get_post_meta( $product_id, 'wps_sample_product_price', true ) : 0;
            $price                   = $sample_product_price;
            //$cart_content['data']->price = $price;
            $cart_content[ 'data' ]->set_price( $price );
        }

        return $cart_content;
    }

    /**
     * Append the prefix " - (Sample)" to product name in cart
     *
     * @param string $title
     * @param array $values
     * @param string $cart_item_key
     *
     * @since 1.0
     * @return string
     */
    public function wps_set_sample_prefix_to_product_name_into_cart( $title, $values, $cart_item_key ) {

        if ( isset( $values[ 'wps_sample_product' ] ) && $values[ 'wps_sample_product' ] ) {
            $title .= ' ' . wptexturize( '--' ) . ' (Sample)';
        }

        return $title;
    }

    /**
     * Append the prefix " - (Sample)" to product name in cart widget
     *
     * @param string $title
     * @param array $cart_item
     *
     * @since 1.0
     * @return string
     */
    public function wps_set_sample_prefix_to_product_name_into_cart_widget( $title, $cart_item ) {

        if ( is_array( $cart_item ) && isset( $cart_item[ 'wps_sample_product' ] ) ) {
            $title .= ' ' . wptexturize( '--' ) . ' (Sample)';
        }

        return $title;
    }

    /**
     * Do not allow more than one quantity for sample product
     *
     * @param string $product_quantity
     * @param string $cart_item_key
     *
     * @since 1.0
     * @return string
     */
    public function wps_can_order_multiple_quantity_of_product( $product_quantity, $cart_item_key ) {

        global $woocommerce;

        if ( WC()->cart->get_cart_contents_count() > 0 ) {

            $cart_items  = $woocommerce->cart->get_cart();
            $cart_item   = $cart_items[ $cart_item_key ];

            if ( isset( $cart_item[ 'wps_sample_product' ] ) ) {
                $product_quantity = sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key );
            }
        }

        return $product_quantity;
    }

    /**
     * Add our sample product order item meta
     *
     * @param int $item_id
     * @param array $values
     * 
     * @since 1.0
     * @return void
     */
    public function wps_add_sample_keyword_to_order_item_meta( $item_id, $values ) {

        if ( isset( $values[ 'wps_sample_product' ] ) ) {
            woocommerce_add_order_item_meta( $item_id, 'product type', 'Sample' );
        }
    }

    /**
     * Fix: Woocommerce sample product price is not updating in cart
     *
     * @param WC_Cart $cart_object
     * 
     * @since 1.0
     * @return void
     */
    public function wps_update_sample_product_price( $cart_object ) {

        //  necessary for WC 3.0+
        if ( is_admin() && !defined( 'DOING_AJAX' ) ) {
            return;
        }

        if ( !WC()->session->__isset( 'reload_checkout' ) ) {

            foreach ( $cart_object->cart_contents as $cart_item_key => $cart_item ) {

                if ( isset( $cart_item[ 'wps_sample_product' ] ) && $cart_item[ 'wps_sample_product' ] ) {

                    $product_id              = $cart_item[ 'product_id' ];
                    $sample_product_price    = get_post_meta( $product_id, 'wps_sample_product_price', true ) ? get_post_meta( $product_id, 'wps_sample_product_price', true ) : 0;

                    $cart_item[ 'data' ]->set_price( $sample_product_price );

                }

            }

        }
    }

    /**
     * Filter available methods to allow free shipping only for sample products and FREE SHIPPING METHODS RULES
     * 
     * 1. Shipping will be allow free if there are only sample products available into cart.
     * 2. Free shipping option will not appear if there is 1 sample product into the cart.
     * 3. All shipping methods will appear when there are no sample products into the cart.
     *
     * @param array $available_methods
     *
     * @since 1.0
     * @return array
     */
    public function wps_check_free_shipping_filter( $available_methods ) {

        $free_shipping = false;
        if ( WC()->cart->get_cart_contents_count() > 0 ) {
            global $woocommerce;

            foreach ( $woocommerce->cart->get_cart() as $cart_item_key => $cart_item ) {
                if ( isset( $cart_item[ 'wps_sample_product' ] ) && $cart_item[ 'wps_sample_product' ] ) {
                    $free_shipping = true;
                } else {
                    $free_shipping = false;
                    break;
                }
            }
        }

        if ( !$free_shipping ) {
            foreach ( $available_methods as $shipping_id => $shipping ) {
                if ( $shipping->method_id == 'free_shipping' ) {
                    unset( $available_methods[ $shipping_id ] );
                }
            }
        }

        if ( $free_shipping ) {
            foreach ( $available_methods as $key => $method ) {
                if ( strpos( $method->id, 'free_shipping' ) !== false ) {
                    $available_methods           = array();
                    $available_methods[ $key ]   = $method;
                    break;
                }
            }
        }

        return $available_methods;
    }

}

new Front();