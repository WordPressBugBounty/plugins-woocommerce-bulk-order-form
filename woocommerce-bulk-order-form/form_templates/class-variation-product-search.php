<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WooCommerce_Bulk_Order_Form_Variation_Product_Search' ) ):

class WooCommerce_Bulk_Order_Form_Variation_Product_Search extends WooCommerce_Bulk_Order_Form_Template_Product_Search {

	/**
	 * @var array
	 */
	public $pid_result;

	public function __construct() {
		add_action( 'wc_bof_render_variation_template_product_search', array( $this, 'init_class' ), 1, 2 );
	}

	public function init_class( string &$return, array $arr ): void {
		parent::__construct();
		$this->type = 'variation';
		$return     = $this->render_query( $arr );
	}

	public function render_query( array $arr ): array {
		do_action( 'wc_bof_before_search', $this, $arr );
		$this->set_post_per_page( $arr['settings']['max_items'] );
		$this->pid_result = $this->search_by_all( $arr );
		$this->pid_result = $this->extract_products( $arr );
		do_action('wc_bof_after_search', $this, $arr );
		return $this->pid_result;
	}

	public function search_by_all( array $arr ): array {
		//$products_by_sku       = array();
		//$products_by_id        = array();
		//$products_by_title     = array();
		$prodcuts_by_attribute = array();
		$products_by_sku       = $this->search_by_sku( $arr );
		$products_by_id        = $this->search_by_id( $arr );
		$products_by_title     = $this->search_by_title( $arr );
		$status_enabled        = wc_bof_option( 'enable_search_attributes', false );

		if ( 'on' === $status_enabled ) {
			$prodcuts_by_attribute = $this->search_by_attribute( $arr );
		}

		//$products = array_unique(array_merge($products_by_sku, $products_by_id, $products_by_title));
		$products = array_unique( array_merge( $products_by_sku, $products_by_id, $products_by_title, $prodcuts_by_attribute ) );
		return $products;
	}

	public function search_by_sku( array $arr ): array {
		$products = array();
		$this->_clear_defaults();
		$this->set_post_per_page( $arr['settings']['max_items'] );
		$this->set_search_by_sku( $arr );
		$products = $this->get_products();
		$this->_clear_defaults();
		return array_unique( array_merge( $products ) );
	}

	public function set_search_by_sku( array $arr ): void {
		$this->set_sku_search( $arr['term'] );
	}

	public function search_by_id( array $arr ): array {
		$this->_clear_defaults();
		$this->set_post_per_page( $arr['settings']['max_items'] );
		$search_product_1 = $this->set_search_with_tax_parent( $arr );
		$this->_clear_defaults();
		$this->set_post_per_page( $arr['settings']['max_items'] );
		$search_product_2 = $this->set_search_with_tax( $arr );
		$this->_clear_defaults();
		$products = array_unique( array_merge( $search_product_1, $search_product_2 ) );
		return $products;
	}

	public function set_search_with_tax_parent( array $arr ): array {
		$products = array();

		if ( is_numeric( $arr['term'] ) ) {
			$this->set_post_parent( array( $arr['term'] ) );
			$products = $this->get_products();
			$this->_clear_defaults();
		}
		return $products;
	}

	public function set_search_with_tax( array $arr ): array {
		$products = array();
		if ( is_numeric( $arr['term'] ) ) {
			$products = $this->get_products();
			$this->_clear_defaults();
		}
		return $products;
	}

	public function search_by_title( array $arr ): array {
		$products = array();
		$this->_clear_defaults();
		$this->set_post_per_page( $arr['settings']['max_items'] );
		$this->set_search_by_title_query();
		$products = $this->set_search_by_title( $arr );
		$this->set_search_by_title_query( 'remove' );
		$this->_clear_defaults();
		$products = array_unique( array_merge( $products ) );
		return $products;
	}

	public function set_search_by_title( array $arr ): array {
		$product = array();

		if ( ! is_numeric( $arr['term'] ) ) {
			$this->set_search_query( $arr['term'] );
			$product = $this->get_products();
		}
		return $product;
	}

	public function search_by_attribute( array $arr ): array {
		$products_return = array();
		$this->_clear_defaults();
		// TODO: clear defaults should be replaced with something more precise
		// right now it really clears all, but we need something that can reset stuff
		// while still using arguments like max_items - right now we need to set that
		// for every request
		$this->set_post_per_page( $arr['settings']['max_items'] );
		$attributes = wc_bof_option( 'product_attributes', array() );
		if ( empty( $attributes ) ) {
			$attributes = wc_get_attribute_taxonomies();
		}

		$search_args_old          = $this->get_search_args();
		$search_args              = array();
		$search_args['tax_query'] = array( 'relation' => 'OR' );
		foreach ( $attributes as $tax ) {
			if ( is_object( $tax ) ) {
				$name = wc_attribute_taxonomy_name( $tax->attribute_name );
			} else {
				$name = wc_attribute_taxonomy_name_by_id( $tax );
			}
			$search_args['tax_query'][] = array(
				'field'    => 'name',
				'taxonomy' => $name,
				'terms'    => $arr['term'],
			);
		}
		$search_args_old = array_merge( $search_args_old, $search_args );

		$this->set_search_args( $search_args_old );
		$products = $this->get_products();
		$this->_clear_defaults();
		$products = array_unique( array_merge( $products_return, $products ) );
		return $products;
	}

	public function extract_products( array $arr ): array {
		$number_step     = apply_filters( 'wcbulkorder_number_step', '1' );
		$min_quantity    = apply_filters( 'wcbulkorder_min_quantity', 0 );
		$max_quantity    = apply_filters( 'wcbulkorder_max_quantity', 1000 );
		$suggestions     = array();
		$active_currency = get_woocommerce_currency_symbol();
		$include_hidden  = isset( $arr['settings']['include_hidden'] ) && 'on' === $arr['settings']['include_hidden'];

		foreach ( $this->pid_result as $pid ) {
			global $product;
			$post_type = get_post_type( $pid );
			if ( 'product' !== $post_type ) {
				continue;
			}
			$attribute_html          = '';
			$variation_template_type = '1';
			$product                 = wc_get_product( $pid );
			
			// filter hidden products
			if ( ! $product || ! $product->is_purchasable() || ( ! $product->is_visible() && ! $include_hidden ) ) {
				continue;
			}

			// filter out of stock products
			if ( $arr['settings']['exclude_out_of_stock'] == 'on' && ! $product->is_in_stock() ) {
				continue;
			}

			$product_has_variation = 'no';
			$add_to_cart           = '';
			$product_type          = $product->get_type();

			if ( $product->has_child() ) {
				$product_has_variation = 'yes';
			}
			$price          = floatval( wc_get_price_to_display( $product ) );
			$price_html     = $product->get_price_html();
			$sku            = $product->get_sku();
			$quantity 		= $product->get_max_purchase_quantity();
			$max_quantity   = $quantity === -1 ? $max_quantity : $quantity;
			$title          = $this->get_product_title( $pid );
			$img            = $this->get_product_image( $pid );

			//  if($arr['settings']['variation_attributes'] == 'attributes_value'){
			$variation_template_type = 'attributes_value';
			$att_list                = array();
			$add_to_cart             = $product->add_to_cart_url();
			$product_attributes      = $product->get_attributes();
			$attributes_keys         = array_keys( $product_attributes );

			if ( ! empty( $product_attributes ) ) {
				if ( 'variable' == $product_type ) {
					$get_variations = sizeof( $product->get_children() ) <= apply_filters( 'woocommerce_ajax_variation_threshold', 30, $product );
					$selected_attrs = '';
					$selected_attrs = $product->get_default_attributes();

					ob_start();
					wc_bof_get_template( 'add-to-cart/variable.php', array(
						'product'              => $product,
						'args'                 => $arr,
						'available_variations' => $get_variations ? $product->get_available_variations() : false,
						'attributes'           => $product->get_variation_attributes(),
						'selected_attributes'  => $selected_attrs,
					) );
					$attribute_html .= ob_get_clean();
				}
			}

			if ( empty( $attribute_html ) ) {
				$attribute_html = apply_filters( 'wcbulkorder_no_variations_found', '<span></span>' );
			}
			//}

			$label_price                      = $price;
			$label                            = $this->get_output_title( 'TPS', $arr['settings']['product_display_separator'], $title, $label_price, $sku );
			$suggestion                       = array();
			$suggestion['label']              = html_entity_decode( apply_filters( 'wc_bulk_order_form_label', $label, $price, $title, $sku, $active_currency, $product ) );
			$suggestion['label']              = wp_strip_all_tags( $suggestion['label'] );
			$suggestion['price']              = $price;
			$suggestion['price_html']         = $price_html;
			$suggestion['symbol']             = $active_currency;
			$suggestion['id']                 = $pid;
			$suggestion['imgsrc']             = $img;
			$suggestion['has_variation']      = $product_has_variation;
			$suggestion['attribute_html']     = $attribute_html;
			$suggestion['add_to_cart_url']    = $add_to_cart;
			$suggestion['variation_template'] = $variation_template_type;
			$suggestion['qty_min']            = $min_quantity;
			$suggestion['qty_max']            = $max_quantity;
			$suggestion['qty_step']           = $number_step;
			
			// stock status
			if ( $product->is_on_backorder() ) {
				$suggestion['class'] = 'on-backorder';
			} else {
				$suggestion['class'] = $product->is_in_stock() ? 'in-stock' : 'out-of-stock';
			}
			// visibility
			$suggestion['class'] .= ' '.$product->get_catalog_visibility();
			
			$suggestions[] = apply_filters( 'wc_bulk_order_form_suggestion', $suggestion, $product );
		}

		return apply_filters( 'wc_bulk_order_form_suggestions', $suggestions );
	}

} // end class WooCommerce_Bulk_Order_Form_Variation_Product_Search

endif; // end class_exists()

return new WooCommerce_Bulk_Order_Form_Variation_Product_Search;
