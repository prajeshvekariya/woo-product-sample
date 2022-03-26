<?php
/**
 * Admin class for the plugin.
 *
 * @package 
 */

class Admin {

    /**
     * Constructor class
     *
     * @access public
     * @return void
     */
    public function __construct() {

        add_action( 'woocommerce_product_options_general_product_data', array( $this, 'wps_register_sample_product_fields_to_general_tab' ) );

        add_action( 'admin_print_scripts-post.php', array( $this, 'wps_register_static_resources' ), 11 );
        
        add_action( 'woocommerce_process_product_meta', array( $this, 'wps_save_sample_product_settings' ), 10, 2 );
    }

    /**
     * Register our field to general tab of create/edit product
     *
     * @since 1.0
     * @return void
     */
    public function wps_register_sample_product_fields_to_general_tab() {

        // checkbox
        woocommerce_wp_checkbox( array(
            'id'             => 'wps_cb_enable_sample_product',
            'label'          => 'Enable Sample',
            'description'    => 'Check if product has a sample product available.',
            'desc_tip'       => true,
            'cbvalue'        => 1
        ) );

        // price
        woocommerce_wp_text_input( array(
            'id'             => 'wps_sample_product_price',
            'label'          => 'Sample Product Price (' . get_woocommerce_currency_symbol() . ')',
            'description'    => 'Set the price for the sample product',
            'desc_tip'       => true,
            'placeholder'    => 'Sample product price'
        ) );
    }

    /**
     * Enqueue required css/js for admin only
     *
     * @since 1.0
     * @return void
     */
    public function wps_register_static_resources() {

        global $post_type;

        if ( $post_type == 'product' ) {

            wp_enqueue_script( 'woo-product-sample-admin', WOO_PRODUCT_SAMPLE_URL . 'assets/js/woo-product-sample-admin.js', array( 'jquery' ), '1.0', TRUE );
        }
    }

    /**
     * save our custom settings for sample product data
     *
     * @param int $post_id
     * @param WP_Post Object $post
     *
     * @since 1.0
     * @return void
     */
    public function wps_save_sample_product_settings( $post_id, $post ) {

        if ( isset( $_POST[ 'wps_cb_enable_sample_product' ] ) ) {
            
            $sample_product_price = sanitize_text_field( $_POST[ 'wps_sample_product_price' ] );
            update_post_meta( $post_id, 'wps_cb_enable_sample_product', sanitize_text_field( $_POST[ 'wps_cb_enable_sample_product' ] ) );
            update_post_meta( $post_id, 'wps_sample_product_price', wc_format_decimal( $sample_product_price ) );

            
        } else {
            // delete sample product settings if product has not sample available
            delete_post_meta( $post_id, 'wps_cb_enable_sample_product' );
            delete_post_meta( $post_id, 'wps_sample_product_price' );
        }
    }
}

new Admin();