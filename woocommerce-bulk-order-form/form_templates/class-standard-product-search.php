<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WooCommerce_Bulk_Order_Form_Standard_Product_Search' ) ):

class WooCommerce_Bulk_Order_Form_Standard_Product_Search extends WooCommerce_Bulk_Order_Form_Template_Product_Search {

	/**
	 * @var array
	 */
	private $pid_result;

	public function __construct() {
		add_action( 'wc_bof_render_standard_template_product_search', array( $this, 'init_class' ), 1, 2 );
	}

	public function init_class( string &$return, array $arr ): void {
		parent::__construct();
		$this->type = 'standard';
		$return     = $this->render_query( $arr );
	}

	public function render_query( array $arr ): array {
		do_action( 'wc_bof_before_search', $this, $arr );
		$this->set_post_per_page( $arr['settings']['max_items'] );
		$this->pid_result = $this->search_by_all( $arr );
		$this->pid_result = $this->extract_products( $arr );
		do_action( 'wc_bof_after_search', $this, $arr );
		return $this->pid_result;
	}

	public function search_by_all( array $arr ): array {
		//$search_results = array();
		//$search_types = wc_bof_get_search_types();
		//foreach ( $search_types as $id => $name ) {}
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

		return array_unique( array_merge( $products_by_sku, $products_by_id, $products_by_title, $prodcuts_by_attribute ) );
	}

	public function search_by_sku( array $arr ): array {
		$products = array();
		$this->_clear_defaults();
		// TODO: clear defaults should be replaced with something more precise
		// right now it really clears all, but we need something that can reset stuff
		// while still using arguments like max_items - right now we need to set that
		// for every request
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
		$search_product_1 = $this->set_search_by_id_with_post_in( $arr );
		$this->_clear_defaults();
		$this->set_post_per_page( $arr['settings']['max_items'] );
		$search_product_2 = $this->set_search_by_id_with_post_parent( $arr );
		$this->_clear_defaults();
		return array_unique( array_merge( $search_product_1, $search_product_2 ) );
	}

	public function set_search_by_id_with_post_in( array $arr ): array {
		if ( is_numeric( $arr['term'] ) ) {
			$this->set_includes( array( 0, $arr['term'] ) );
			return $this->get_products();
		}
		return array();
	}

	public function set_search_by_id_with_post_parent( array $arr ): array {
		if ( is_numeric( $arr['term'] ) ) {
			$this->set_post_parent( $arr['term'] );
			return $this->get_products();
		}
		return array();
	}

	public function search_by_title( array $arr ): array {
		$this->_clear_defaults();
		$this->set_post_per_page( $arr['settings']['max_items'] );
		$this->set_search_by_title( $arr );
		$this->set_search_by_title_query();
		$products = $this->get_products();
		$this->set_search_by_title_query( 'remove' );
		return array_unique( array_merge( $products ) );
	}

	public function set_search_by_title( array $arr ): void {
		$this->set_search_query( $arr['term'] );
	}

	public function search_by_attribute( array $arr ): array {
		$products_return = array();
		$this->_clear_defaults();
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
		return array_unique( array_merge( $products_return, $products ) );
	}

	public function extract_products( array $arr ): array {
		$suggestions     = array();
		$active_currency = get_woocommerce_currency_symbol();
		$include_hidden  = isset( $arr['settings']['include_hidden'] ) && 'on' === $arr['settings']['include_hidden'];
		foreach ( $this->pid_result as $pid ) {
			$post_type  = get_post_type( $pid );
			$child_args = array(
				'post_parent' => $pid,
				'post_type'   => 'product_variation',
			);
			$children   = get_children( $child_args );
			$id         = $pid;
			if ( ( 'product' == $post_type ) && ! empty( $children ) ) {
				continue;
			}

			if ( ( 'product' == $post_type ) && empty( $children ) ) {
				$product = wc_get_product( $pid );

				// filter hidden products
				if ( ! $product || ! $product->is_purchasable() || ( ! $product->is_visible() && ! $include_hidden ) ) {
					continue;
				}

				// filter out of stock products
				if ( $arr['settings']['exclude_out_of_stock'] == 'on' && ! $product->is_in_stock() ) {
					continue;
				}

				$price      = floatval( wc_get_price_to_display( $product ) );
				$price_html = $product->get_price_html();
				$sku        = $product->get_sku();
				$title      = $this->get_product_title( $id );
				$img        = $this->get_product_image( $id );

			} elseif ( 'product_variation' == $post_type ) {
				$product = wc_get_product( $pid );
				
				// filter hidden products
				if ( ! $product || ! $product->is_purchasable() || ( ! $product->is_visible() && ! $include_hidden ) ) {
					continue;
				}

				// filter out of stock products
				if ( $arr['settings']['exclude_out_of_stock'] == 'on' && ! $product->is_in_stock() ) {
					continue;
				}

				$parent    = wc_get_product( $pid );
				$parent_id = $product->get_parent_id();

				if ( false === $parent_id || ! $parent ) {
					continue;
				}

				$id           = $pid;
				$price        = floatval( wc_get_price_to_display( $product ) );
				$price_html   = $product->get_price_html();
				$sku          = $product->get_sku();
				$title        = $product->get_title();
				$parent_image = $this->get_product_image( $parent_id, false );
				$img          = $this->get_product_image( $id, false );
				$attributes   = $product->get_variation_attributes();

				// add variation title
				$title .= " - " . wc_get_formatted_variation( $product, true, false );

				if ( ! empty( $img ) ) {
					$img = $img;
				} elseif ( ! empty( $parent_image ) ) {
					$img = $parent_image;
				} else {
					$img = apply_filters( 'woocommerce_placeholder_img_src', '' );
				}
			}

			$label_price              = $price;
			$label                    = $this->get_output_title( 'TPS', $arr['settings']['product_display_separator'], $title, $label_price, $sku );
			$suggestion               = array();
			$suggestion['label']      = html_entity_decode( apply_filters( 'wc_bulk_order_form_label', $label, $price, $title, $sku, $active_currency, $product ) );
			$suggestion['label']      = wp_strip_all_tags( $suggestion['label'] );
			$suggestion['price']      = $price;
			$suggestion['price_html'] = $price_html;
			$suggestion['symbol']     = $active_currency;
			$suggestion['id']         = $id;
			$suggestion['imgsrc']     = $img;
			if ( ! empty( $variation_id ) ) {
				$suggestion['variation_id'] = $variation_id;
			}
			
			// stock status
			if ( $product->is_on_backorder() ) {
				$suggestion['class'] = 'on-backorder';
			} else {
				$suggestion['class'] = $product->is_in_stock() ? 'in-stock' : 'out-of-stock';
			}
			// visibility
			$suggestion['class'] .= ' '.$product->get_catalog_visibility();

			//max quantity
			$qty = $product->get_max_purchase_quantity() === -1 ? apply_filters( 'wcbulkorder_max_quantity', 1000 ) : $product->get_max_purchase_quantity();

			// extract min/max/step from quantity input args
			$qty_input_defaults = array(
				'input_id'     => uniqid( 'quantity_' ),
				'input_name'   => 'quantity',
				'input_value'  => '1',
				'classes'      => apply_filters( 'woocommerce_quantity_input_classes', array( 'input-text', 'qty', 'text' ), $product ),
				'max_value'    => apply_filters( 'woocommerce_quantity_input_max', $qty, $product ),
				'min_value'    => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
				'step'         => apply_filters( 'woocommerce_quantity_input_step', 1, $product ),
				'pattern'      => apply_filters( 'woocommerce_quantity_input_pattern', has_filter( 'woocommerce_stock_amount', 'intval' ) ? '[0-9]*' : '' ),
				'inputmode'    => apply_filters( 'woocommerce_quantity_input_inputmode', has_filter( 'woocommerce_stock_amount', 'intval' ) ? 'numeric' : '' ),
				'product_name' => $product ? $product->get_title() : '',
			);
			$qty_input_args           = apply_filters( 'woocommerce_quantity_input_args', $qty_input_defaults, $product );
			$suggestion['qty_min']    = $qty_input_args['min_value'];
			$suggestion['qty_max']    = $qty_input_args['max_value'];
			$suggestion['qty_step']   = $qty_input_args['step'];

			$suggestions[] = apply_filters( 'wc_bulk_order_form_suggestion', $suggestion, $product );
		}
		return apply_filters( 'wc_bulk_order_form_suggestions', $suggestions );
	}

} // end class WooCommerce_Bulk_Order_Form_Standard_Product_Search

endif; // end class_exists()

return new WooCommerce_Bulk_Order_Form_Standard_Product_Search;
