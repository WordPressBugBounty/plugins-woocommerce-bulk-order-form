<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WooCommerce_Bulk_Order_Form_Variation_Add_To_Cart_Handler' ) ):

class WooCommerce_Bulk_Order_Form_Variation_Add_To_Cart_Handler {

	public function __construct() {
		add_action( 'wc_bof_variation_add_to_cart', array( $this, 'add_to_cart' ), 10, 2 );
		add_action( 'wc_bof_variation_single_add_to_cart', array( $this, 'single_add_to_cart' ), 10, 2 );
	}

	public function single_add_to_cart( string &$return, array $args ): void {
		$this->add_to_cart( $return, $args );
	}

	public function add_to_cart( string &$return, array $args ): void {
		if ( isset( $args['wcbof_products'] ) ) {
			$success  = 0;
			$products = $args['wcbof_products'];
			unset( $products['removeHidden'] );

			foreach ( $products as $product ) {
				$qty          = $product['product_qty'] ?? 0;
				$product_id   = $product['product_id'];
				$variation_id = $product['variation_id'] ?? '';
				if ( empty( $qty ) || empty( $product_id ) || ( isset( $product['variation_id'] ) && '' === $product['variation_id'] ) ) {
					continue;
				}
				$attributes = $product['attributes'] ?? null;
				$status     = WC()->cart->add_to_cart( $product_id, $qty, $variation_id, $attributes, null );
				if ( $status ) {
					$success++;
				}
			}

			if ( 0 < $success ) {
				$url       = wc_get_cart_url();
				$product_n = _n( 'Your product was successfully added to your cart', 'Your products were successfully added to your cart', $success, 'woocommerce-bulk-order-form' );
				/* translators: 1. URL, 2. product/products */
				$msg  = sprintf( __( '<a class="button wc-forward" href="%1$s">View Cart</a> %2$s.', 'woocommerce-bulk-order-form' ), $url, $product_n );
				$type = 'success';
			} else {
				$msg  = __( "Looks like there was an error. Please try again.", 'woocommerce-bulk-order-form' );
				$type = 'error';
			}
			wc_add_notice( $msg, $type );
		}
	}

} // end class WooCommerce_Bulk_Order_Form_Variation_Add_To_Cart_Handler

endif; // end class_exists()

return new WooCommerce_Bulk_Order_Form_Variation_Add_To_Cart_Handler;
