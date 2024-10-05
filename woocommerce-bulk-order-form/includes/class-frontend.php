<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WooCommerce_Bulk_Order_Form_Functions' ) ):

class WooCommerce_Bulk_Order_Form_Functions {

	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	public function enqueue_styles(): void {
		if ( ! $this->can_load_assets() ) {
			return;
		}

		wp_enqueue_style( WC_BOF_NAME . 'frontend_style', WC_BOF_CSS . 'frontend.css', array(), WC_BOF_V );
		wp_register_style( WC_BOF_NAME . 'selectize', WC_BOF_CSS . 'selectize.css', array(), WC_BOF_V );
		wp_enqueue_style( WC_BOF_NAME . 'selectize' );

		$parent_theme = wp_get_theme( get_template() );
		if ( $parent_theme ) {
			if ( 'Flatsome' === $parent_theme->get( 'Name' ) ) {
				wp_enqueue_style( WC_BOF_NAME . 'frontend_style_flatsome', WC_BOF_CSS . 'frontend-flatsome.css', array(), WC_BOF_V );
			}
		}

	}

	public function enqueue_scripts(): void {
		if ( ! $this->can_load_assets() ) {
			return;
		}
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_script( WC_BOF_NAME . 'frontend_script', WC_BOF_JS . 'frontend.js', array(
			'jquery',
			'wc-add-to-cart-variation',
			WC_BOF_NAME . 'selectize',
			WC_BOF_NAME . 'form_handler',
		), WC_BOF_V, true );

		wp_register_script( WC_BOF_NAME . 'sprintf', WC_BOF_JS . 'wcbof-sprintf.js', null, WC_BOF_V, true );

		wp_register_script( WC_BOF_NAME . 'form_handler', WC_BOF_JS . 'wc_bof_handler.js', array( 'jquery' ), WC_BOF_V, true );
		wp_register_script( WC_BOF_NAME . 'selectize', WC_BOF_JS . 'selectize' . $suffix . '.js', array( 'jquery' ), WC_BOF_V, true );

		$localize_arr = apply_filters( 'wc_bof_localize_script_vars', array(
			'url'                       => admin_url( 'admin-ajax.php' ),
			'noproductsfound'           => __( 'No products found', 'woocommerce-bulk-order-form' ),
			'selectaproduct'            => __( 'Please select a product', 'woocommerce-bulk-order-form' ),
			'enterquantity'             => __( 'Enter quantity', 'woocommerce-bulk-order-form' ),
			'variation_noproductsfound' => __( 'No variations', 'woocommerce-bulk-order-form' ),
			'decimal_sep'               => wc_get_price_decimal_separator(),
			'thousands_sep'             => wc_get_price_thousand_separator(),
			'num_decimals'              => wc_get_price_decimals(),
			'Delay'                     => 500,
			'minLength'                 => 3,
			'checkouttext'              => __( 'Go to checkout', 'woocommerce-bulk-order-form' ),
			'carttext'                  => __( 'View cart', 'woocommerce-bulk-order-form' ),
			'checkouturl'               => wc_get_checkout_url(),
			'carturl'                   => wc_get_cart_url(),
			'price_format'              => get_woocommerce_price_format(),
		) );

		wp_localize_script( WC_BOF_NAME . 'frontend_script', 'WCBulkOrder', $localize_arr );
		wp_localize_script( WC_BOF_NAME . 'form_handler', 'WCBOFHandler', $localize_arr );

		wp_enqueue_script( WC_BOF_NAME . 'sprintf' );
		wp_enqueue_script( WC_BOF_NAME . 'selectize' );
		wp_enqueue_script( WC_BOF_NAME . 'form_handler' );
		wp_enqueue_script( WC_BOF_NAME . 'frontend_script' );
	}

	public function can_load_assets(): bool {
		global $post;
		$shop_page_id = wc_get_page_id( 'shop' );

		if ( function_exists( 'is_shop' ) && is_shop() && $shop_page_id ) {
			$_post = get_post( $shop_page_id );
		} else {
			$_post = $post;
		}

		$has_shortcode = is_a( $_post, 'WP_Post' ) && has_shortcode( $_post->post_content, 'wcbulkorder' );

		return apply_filters( 'wc_bof_load_assets', $has_shortcode, $_post );
	}

} // end class WooCommerce_Bulk_Order_Form_Functions

endif; // end class_exists()
