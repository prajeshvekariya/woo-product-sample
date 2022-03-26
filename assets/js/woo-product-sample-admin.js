jQuery( document ).ready( function() {

	// price input wrapper
	var $sample_product_price_wrapper = jQuery( '.wps_sample_product_price_field' );
	
	jQuery( document ).on( 'click', '#wps_cb_enable_sample_product', function() {
		toggle_sample_product_price_field();
	} );

	/**
	 * Toggle sample product price by checkbox value
	 * 
	 * @returns void
	 */
	function toggle_sample_product_price_field() {
		if ( true === jQuery( '#wps_cb_enable_sample_product' ).prop( 'checked' ) ) {
			$sample_product_price_wrapper.show();
		} else {
			$sample_product_price_wrapper.hide();
		}
	}
	
	toggle_sample_product_price_field();
} );