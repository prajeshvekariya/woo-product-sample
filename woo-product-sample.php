<?php

/**
 * Plugin Name: Woo Product Sample
 * Plugin URI: https://prajeshvekariya.com
 * Description: Display Sample button on front-end where product sample is enabled
 * Version: 1.0
 * Runtime: 5.6+
 * Author: Prajesh Vekariya
 * Text Domain: wc-product-sample
 * Domain Path: i18n
 * Author URI: https://prajeshvekariya.com
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WOO_PRODUCT_SAMPLE_PATH', plugin_dir_path( __FILE__ ) );
define( 'WOO_PRODUCT_SAMPLE_URL', plugins_url( '/', __FILE__ ) );

Class WooProductSample {

	/**
     * Constructor class
     *
     * @access public
     * @return void
     */
	public function __construct() {
		add_action( 'woocommerce_init', array( $this, 'load_class_files' ) );
	}

	public function load_class_files() {
		require_once WOO_PRODUCT_SAMPLE_PATH . 'includes/class-admin.php';
		require_once WOO_PRODUCT_SAMPLE_PATH . 'includes/class-front.php';
	}

}

new WooProductSample();