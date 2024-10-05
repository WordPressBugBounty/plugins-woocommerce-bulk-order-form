<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WooCommerce_Bulk_Order_Form_ShortCode_Handler' ) ):

class WooCommerce_Bulk_Order_Form_ShortCode_Handler {

	public function __construct() {
		add_shortcode( 'wcbulkorder', array( $this, 'render_bulk_order' ) );
	}

	/**
	 * @param string|array $att
	 * @param string $content
	 *
	 * @return string
	 */
	public function render_bulk_order( $att, string $content = '' ): string {
		wp_enqueue_script( 'wc-add-to-cart-variation' );
		$render = '';
		$dbs    = $this->get_db_settings();

		$default_atts = apply_filters( 'wc_bof_shortcode_atts', array(
			'template'                    => $dbs['template_type'],
			'rows'                        => $dbs['no_of_rows'],
			'price'                       => $dbs['show_price'],
			'price_label'                 => $dbs['price_label'],
			'product_label'               => $dbs['product_label'],
			'quantity_label'              => $dbs['quantity_label'],
			'variation_label'             => $dbs['variation_label'],
			'add_rows'                    => $dbs['add_rows'],
			'category'                    => $dbs['category'],
			'excluded'                    => $dbs['excluded'],
			'included'                    => $dbs['included'],
			'search_by'                   => $dbs['search_by'],
			'max_items'                   => $dbs['max_items'],
			'product_display'             => $dbs['product_display'],
			'variation_display'           => $dbs['variation_display'],
			'product_display_separator'   => ' - ',
			'variation_display_separator' => ' - ',
			'product_attributes'          => $dbs['product_attributes'],
			'single_addtocart'            => $dbs['single_addtocart'],
			'single_addtocart_label'      => $dbs['single_addtocart_label'],
			'cart_label'                  => $dbs['cart_label'],
			'total_label'                 => $dbs['total_label'],
			'include_hidden'              => $dbs['include_hidden'],
			'exclude_out_of_stock'        => $dbs['exclude_out_of_stock'],
			'term'                        => 'Ship',
		) );

		$atts = shortcode_atts( $default_atts, $att, 'wcbulkorder' );

		$atts['total_columns']  = 5;
		$atts['active_columns'] = 0;

		$template_list = wc_bof_template_types();
		$class_called  = false;


		if ( ! is_array( $atts['category'] ) ) {
			if ( ! empty( $atts['category'] ) ) {
				$atts['category'] = explode( ',', $atts['category'] );
			}
		}

		if ( ! is_array( $atts['excluded'] ) ) {
			if ( ! empty( $atts['excluded'] ) ) {
				$atts['excluded'] = explode( ',', $atts['excluded'] );
			}
		}

		if ( ! is_array( $atts['included'] ) ) {
			if ( ! empty( $atts['included'] ) ) {
				$atts['included'] = explode( ',', $atts['included'] );
			}
		}


		///do_action_ref_array('wc_bof_render_'.$atts['template'].'_template_product_search',array(&$return,$atts));

		do_action( 'wc_bof_before_shortcode_render', $att, $content );

		if ( isset( $template_list[ $atts['template'] ] ) ) {
			if ( isset( $template_list[ $atts['template'] ]['callback'] ) ) {
				$class = $template_list[ $atts['template'] ]['callback'];
				if ( class_exists( $class ) ) {
					$class        = new $class( $atts, $atts['template'] );
					$render       = $class->render();
					$class_called = true;
				}
			}
		}

		if ( ! $class_called ) {
			do_action_ref_array( 'wc_bof_render_' . $atts['template'] . '_template', array(
				&$render,
				&$atts,
				&$content,
			) );
		}

		do_action( 'wc_bof_after_shortcode_render', $att, $content );

		return $render;
	}

	public function get_db_settings(): array {
		$db_settings = array();

		$db_settings['add_rows']                 = wc_bof_option( 'add_rows' );
		$db_settings['show_price']               = wc_bof_option( 'show_price' );
		$db_settings['template_type']            = wc_bof_option( 'template_type', 'standard' );
		$db_settings['no_of_rows']               = wc_bof_option( 'no_of_rows', 10 );
		$db_settings['max_items']                = wc_bof_option( 'max_items', -1 );
		$db_settings['price_label']              = wc_bof_option( 'price_label', __( 'Price', 'woocommerce-bulk-order-form' ) );
		$db_settings['product_label']            = wc_bof_option( 'product_label', __( 'Product', 'woocommerce-bulk-order-form' ) );
		$db_settings['quantity_label']           = wc_bof_option( 'quantity_label', __( 'Qty', 'woocommerce-bulk-order-form' ) );
		$db_settings['variation_label']          = wc_bof_option( 'variation_label', __( 'Variation', 'woocommerce-bulk-order-form' ) );
		$db_settings['category']                 = wc_bof_option( 'category', '' );
		$db_settings['excluded']                 = wc_bof_option( 'excluded', '' );
		$db_settings['included']                 = wc_bof_option( 'included', '' );
		$db_settings['search_by']                = wc_bof_option( 'search_by', 'all' );
		$db_settings['product_display']          = wc_bof_option( 'result_format', 'TPS' );
		$db_settings['variation_display']        = wc_bof_option( 'result_variation_format', 'TPS' );
		$db_settings['product_attributes']       = wc_bof_option( 'product_attributes', array() );
		$db_settings['attribute_display_format'] = wc_bof_option( 'attribute_display_format', 'value' ); // deprecated?
		$db_settings['single_addtocart']         = wc_bof_option( 'single_addtocart', false );
		$db_settings['single_addtocart_label']   = wc_bof_option( 'single_addtocart_label', __( 'Add to cart', 'woocommerce-bulk-order-form' ) );
		$db_settings['cart_label']               = wc_bof_option( 'cart_label', __( 'Add to cart', 'woocommerce-bulk-order-form' ) );
		$db_settings['total_label']              = wc_bof_option( 'total_label', __( 'Total', 'woocommerce-bulk-order-form' ) );
		$db_settings['include_hidden']           = wc_bof_option( 'include_hidden', false );
		$db_settings['exclude_out_of_stock']     = wc_bof_option( 'exclude_out_of_stock', false );

		return apply_filters( 'wc_bof_shortcode_settings', $db_settings );
	}

} // end class WooCommerce_Bulk_Order_Form_ShortCode_Handler

endif; // end class_exists()
