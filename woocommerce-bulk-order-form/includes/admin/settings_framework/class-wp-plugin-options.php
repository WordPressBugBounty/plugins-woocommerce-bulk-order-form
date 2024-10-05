<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WooCommerce_Bulk_Order_Form_Admin_Settings_Options' ) ):

class WooCommerce_Bulk_Order_Form_Admin_Settings_Options {

	public function __construct() {
		add_filter( 'wc_bof_settings_pages', array( $this, 'settings_pages' ) );
		add_filter( 'wc_bof_settings_section', array( $this, 'settings_section' ) );
		add_filter( 'wc_bof_settings_fields', array( $this, 'settings_fields' ) );
	}

	public function settings_pages( array $page ): array {
		$page[] = array(
			'id'    => 'general',
			'slug'  => 'general',
			'title' => __( 'Settings', 'woocommerce-bulk-order-form' ),
		);
		$page[] = array(
			'id'    => 'addons',
			'slug'  => 'addons',
			'title' => __( 'Extensions', 'woocommerce-bulk-order-form' ),
		);
		return $page;
	}

	public function settings_section( array $section ): array {
		$section['general'][] = array(
			'id'    => 'general',
			'title' => __( 'General settings', 'woocommerce-bulk-order-form' ),
		);
		$section['general'][] = array(
			'id'    => 'products',
			'title' => __( 'Products', 'woocommerce-bulk-order-form' ),
		);
		$section['general'][] = array(
			'id'                => 'template_label',
			'title'             => __( 'Text translations', 'woocommerce-bulk-order-form' ),
			'sanitize_callback' => array( $this, 'template_label_validate' ),
		);
		//$section['addons'][] = array( 'id'=>'welcome', 'title'=> __('Extension settings','woocommerce-bulk-order-form'));  

		$addonSettings = array(
			'addon_sample' => array(
				'id'    => 'welcome',
				'title' => __( 'No extensions activated or installed.', 'woocommerce-bulk-order-form' ),
			),
		);
		$addonSettings = apply_filters( 'wc_bof_addon_sections', $addonSettings );

		if ( 1 < count( $addonSettings ) ) {
			unset( $addonSettings['addon_sample'] );
		}

		$section['addons'] = $addonSettings;
		return $section;
	}
	
	public function template_label_validate( array $options ): array {
		return array_map( 'sanitize_text_field', $options );
	}

	public function settings_fields( array $fields ): array {
		$templates = wc_bof_template_types();
		$tpl_List  = array();
		foreach ( $templates as $ky => $template ) {
			$tpl_List[ $ky ] = $template['name'];
		}
		$included_products_array = array();
		$included_products_json  = '';
		$included_products       = wc_bof_option( 'included' );
		$excluded_products_array = array();
		$excluded_products       = wc_bof_option( 'excluded' );
		$excluded_products_json  = '';

		if ( ! empty( $included_products ) ) {
			$included_products_array = wc_bof_settings_products_json( $included_products );
			$included_products_json  = json_encode( $included_products_array );
		} else {
			$included_products = '';
		}

		if ( ! empty( $excluded_products ) ) {
			$excluded_products_array = wc_bof_settings_products_json( $excluded_products );
			$excluded_products_json  = json_encode( $excluded_products_array );
		} else {
			$excluded_products = '';
		}

		$fields['general']['general'][] = array(
			'id'      => WC_BOF_DB . 'template_type',
			'type'    => 'select',
			'label'   => __( 'Order form template', 'woocommerce-bulk-order-form' ),
			'desc'    => __( 'Select which template you want to use.', 'woocommerce-bulk-order-form' ),
			'options' => $tpl_List,
			'default' => 'standard',
			'attr'    => array( 'class' => 'wc-enhanced-select wcbof-settings-width-fix', ),
		);

		$fields['general']['general'][] = array(
			'id'        => WC_BOF_DB . 'no_of_rows',
			'type'      => 'text',
			'text_type' => 'number',
			'default'   => '10',
			'label'     => __( 'Number of rows', 'woocommerce-bulk-order-form' ),
			'desc'      => __( 'Number of rows to display on the bulk order form', 'woocommerce-bulk-order-form' ),
			//  'attr'    => array(  'class' => ' wcbof-settings-width-fix', )
		);

		$fields['general']['general'][] = array(
			'id'        => WC_BOF_DB . 'max_items',
			'type'      => 'text',
			'text_type' => 'number',
			'default'   => '0',
			'label'     => __( 'Maximum items', 'woocommerce-bulk-order-form' ),
			'desc'      => __( 'Maximum items to display in a search', 'woocommerce-bulk-order-form' ),
		);

		$fields['general']['general'][] = array(
			'id'         => WC_BOF_DB . 'include_hidden',
			'type'       => 'checkbox',
			'default'    => false,
			'label'      => __( 'Include hidden items', 'woocommerce-bulk-order-form' ),
		);

		$fields['general']['general'][] = array(
			'id'         => WC_BOF_DB . 'exclude_out_of_stock',
			'type'       => 'checkbox',
			'default'    => true,
			'label'      => __( 'Exclude out of stock items', 'woocommerce-bulk-order-form' ),
		);

		$fields['general']['general'][] = array(
			'id'         => WC_BOF_DB . 'single_addtocart',
			'pro_option' => true,
			'type'       => 'checkbox',
			'default'    => true,
			'label'      => __( '"Add to cart" button on each row', 'woocommerce-bulk-order-form' ),
			'desc'       => __( 'Show "Add to cart" button on each row', 'woocommerce-bulk-order-form' ),
		);

		$fields['general']['general'][] = array(
			'id'         => WC_BOF_DB . 'add_rows',
			'pro_option' => true,
			'type'       => 'checkbox',
			'default'    => true,
			'label'      => __( '"Add row" button', 'woocommerce-bulk-order-form' ),
			'desc'       => __( 'Display "Add row" button?','woocommerce-bulk-order-form' ),
		);
		// register string for translation
		__('Add Rows (+)', 'woocommerce-bulk-order-form' );
		__('Add row', 'woocommerce-bulk-order-form' );

		$fields['general']['general'][] = array(
			'id'         => WC_BOF_DB . 'show_image',
			'pro_option' => true,
			'type'       => 'checkbox',
			'default'    => true,
			'label'      => __( 'Show product image', 'woocommerce-bulk-order-form' ),
			'desc'       => __( 'Display product image in autocomplete search & prepopulated templates?', 'woocommerce-bulk-order-form' ),
		);

		$fields['general']['general'][] = array(
			'id'      => WC_BOF_DB . 'show_price',
			'type'    => 'checkbox',
			'default' => true,
			'label'   => __( 'Show price', 'woocommerce-bulk-order-form' ),
			'desc'    => __( 'Display price on bulk order form?', 'woocommerce-bulk-order-form' ),
		);

		$fields['general']['general'][] = array(
			'id'         => WC_BOF_DB . 'action_button',
			'pro_option' => true,
			'type'       => 'radio',
			'label'      => __( 'After adding to cart, link to:', 'woocommerce-bulk-order-form' ),
			'default'    => 'cart',
			'options'    => array(
				'cart'     => __( 'Cart', 'woocommerce-bulk-order-form' ),
				'checkout' => __( 'Checkout', 'woocommerce-bulk-order-form' ),
			),
		);

		$fields['general']['general'][] = array(
			'id'         => WC_BOF_DB . 'auto_redirect',
			'pro_option' => true,
			'type'       => 'checkbox',
			'default'    => false,
			'label'      => __( 'Automatically redirect', 'woocommerce-bulk-order-form' ),
			'desc'       => __( 'Redirect to cart or checkout automatically after successfully adding products to the cart', 'woocommerce-bulk-order-form' ),
		);

		$fields['general']['general'][] = array(
			'id'         => WC_BOF_DB . 'image_width',
			'pro_option' => true,
			'type'       => 'text',
			'text_type'  => 'number',
			'label'      => __( 'Product image width & height', 'woocommerce-bulk-order-form' ),
			'default'    => '50',
		);

		$fields['general']['general'][] = array(
			'id'         => WC_BOF_DB . 'image_height',
			'pro_option' => true,
			'type'       => 'text',
			'text_type'  => 'number',
			'label'      => '',
			'default'    => '50',
		);

		$fields['general']['template_label'][] = array(
			'id'      => WC_BOF_DB . 'price_label',
			'type'    => 'text',
			'default' => __( 'Price', 'woocommerce-bulk-order-form' ),
			'label'   => __( 'Price column', 'woocommerce-bulk-order-form' ),
			'attr'    => array( 'class' => ' wcbof-settings-width-fix', ),
		);

		$fields['general']['template_label'][] = array(
			'id'      => WC_BOF_DB . 'product_label',
			'type'    => 'text',
			'default' => __( 'Product', 'woocommerce-bulk-order-form' ),
			'label'   => __( 'Product column', 'woocommerce-bulk-order-form' ),
			'attr'    => array( 'class' => ' wcbof-settings-width-fix', ),
		);

		$fields['general']['template_label'][] = array(
			'id'      => WC_BOF_DB . 'quantity_label',
			'type'    => 'text',
			'default' => __( 'Qty', 'woocommerce-bulk-order-form' ),
			'label'   => __( 'Quantity column', 'woocommerce-bulk-order-form' ),
			'attr'    => array( 'class' => ' wcbof-settings-width-fix', ),
		);

		$fields['general']['template_label'][] = array(
			'id'      => WC_BOF_DB . 'variation_label',
			'type'    => 'text',
			'default' => __( 'Variation', 'woocommerce-bulk-order-form' ),
			'label'   => __( 'Variation column', 'woocommerce-bulk-order-form' ),
			'attr'    => array( 'class' => ' wcbof-settings-width-fix', ),
		);

		$fields['general']['template_label'][] = array(
			'id'      => WC_BOF_DB . 'total_label',
			'type'    => 'text',
			'default' => __( 'Total', 'woocommerce-bulk-order-form' ),
			'label'   => __( 'Order form total', 'woocommerce-bulk-order-form' ),
			'attr'    => array( 'class' => ' wcbof-settings-width-fix', ),
		);

		$fields['general']['template_label'][] = array(
			'id'      => WC_BOF_DB . 'single_addtocart_label',
			'type'    => 'text',
			'default' => __( 'Add to cart', 'woocommerce-bulk-order-form' ),
			'label'   => __( '"Add to cart" button on single rows', 'woocommerce-bulk-order-form' ),
			'attr'    => array( 'class' => ' wcbof-settings-width-fix', ),
		);

		$fields['general']['template_label'][] = array(
			'id'      => WC_BOF_DB . 'cart_label',
			'type'    => 'text',
			'default' => __( 'Cart', 'woocommerce-bulk-order-form' ),
			'label'   => __( 'Cart button', 'woocommerce-bulk-order-form' ),
			'attr'    => array( 'class' => ' wcbof-settings-width-fix', ),
		);

		$fields['general']['template_label'][] = array(
			'id'         => WC_BOF_DB . 'checkout_label',
			'pro_option' => true,
			'type'       => 'text',
			'default'    => __( 'Checkout', 'woocommerce-bulk-order-form' ),
			'label'      => __( 'Checkout button', 'woocommerce-bulk-order-form' ),
			'attr'       => array( 'class' => ' wcbof-settings-width-fix', ),
		);

		$fields['general']['products'][] = array(
			'id'         => WC_BOF_DB . 'category',
			'pro_option' => true,
			'type'       => 'select',
			'options'    => wc_bof_settings_get_categories(),
			'label'      => __( 'Product category', 'woocommerce-bulk-order-form' ),
			'desc'       => __( 'Select a category to list only products from that category', 'woocommerce-bulk-order-form' ),
			'attr'       => array(
				'multiple' => "multiple",
				'class'    => 'wc-enhanced-select  wcbof-settings-width-fix',
			),
		);

		$fields['general']['products'][] = array(
			'id'         => WC_BOF_DB . 'excluded',
			'pro_option' => true,
			'type'       => 'select',
			'label'      => __( 'Excluded products', 'woocommerce-bulk-order-form' ),
			'desc'       => __( 'Search & select product by name.', 'woocommerce-bulk-order-form' ),
			'value'      => $excluded_products,
			'options'    => $excluded_products_array,
			'attr'       => array(
				'data-action'   => "woocommerce_json_search_products",
				'data-multiple' => "true",
				'multiple'      => "multiple",
				'class'         => 'wc-product-search  wcbof-settings-width-fix',
				'data-selected' => $excluded_products_json,
			),
		);

		$fields['general']['products'][] = array(
			'id'         => WC_BOF_DB . 'included',
			'pro_option' => true,
			'type'       => 'select',
			'label'      => __( 'Included products', 'woocommerce-bulk-order-form' ),
			'desc'       => __( 'Search & select product by name.', 'woocommerce-bulk-order-form' ),
			'value'      => $included_products,
			'options'    => $included_products_array,
			'attr'       => array(
				'data-action'   => "woocommerce_json_search_products",
				'data-multiple' => "true",
				'class'         => 'wc-product-search  wcbof-settings-width-fix',
				'multiple'      => "multiple",
				"data-selected" => $included_products_json,
			),

		);

		$fields['general']['products'][] = array(
			'id'         => WC_BOF_DB . 'search_by',
			'pro_option' => true,
			'type'       => 'select',
			'default'    => 'all',
			'label'      => __( 'Search by', 'woocommerce-bulk-order-form' ),
			'desc'       => __( 'Search products based on selected criteria.', 'woocommerce-bulk-order-form' ),
			'options'    => wc_bof_get_search_types(),
			'attr'       => array( 'class' => 'wc-enhanced-select  wcbof-settings-width-fix', ),
		);

		$fields['general']['products'][] = array(
			'id'    => WC_BOF_DB . 'enable_search_attributes',
			'type'  => 'checkbox',
			'label' => __( 'Product attributes', 'woocommerce-bulk-order-form' ),
			'desc'  => __( 'This option is used only when "Search by" is set to "All".', 'woocommerce-bulk-order-form' ) . '<br/> <span style="font-weight: bold; color: red; font-size: 10px; display: block;">' . __( 'Note: if attribute search is enabled when "Search by" is set to "All", the search process may become slow based on how many attributes you have. So, please select only the attributes you need to search in the field below.', 'woocommerce-bulk-order-form' ) . '</span>',
			'attr'  => array(
				'data-label'     => __( 'Attributes will be searched', 'woocommerce-bulk-order-form' ),
				'data-ulabel'    => __( 'Attributes will not be searched', 'woocommerce-bulk-order-form' ),
				'data-separator' => '||',
			),
		);

		$fields['general']['products'][] = array(
			'id'      => WC_BOF_DB . 'product_attributes',
			'type'    => 'select',
			'options' => wc_bof_settings_get_product_attributes(),
			'label'   => __( 'Product attributes', 'woocommerce-bulk-order-form' ),
			//'desc' => __('Enter Category ID by <code>,</code> to list only products from that category','woocommerce-bulk-order-form'),
			'attr'    => array(
				'multiple' => "multiple",
				'class'    => 'wc-enhanced-select  wcbof-settings-width-fix',
			),
		);

		$fields['general']['products'][] = array(
			'id'         => WC_BOF_DB . 'result_format',
			'pro_option' => true,
			'type'       => 'select',
			'default'    => 'TPS',
			'label'      => __( 'Product result format', 'woocommerce-bulk-order-form' ),
			'desc'       => __( 'Choose your product search results format', 'woocommerce-bulk-order-form' ),
			'options'    => wc_bof_get_title_templates(),
			'attr'       => array( 'class' => 'wc-enhanced-select  wcbof-settings-width-fix', ),
		);

		$fields['general']['products'][] = array(
			'id'         => WC_BOF_DB . 'result_variation_format',
			'pro_option' => true,
			'type'       => 'select',
			'default'    => 'TPS',
			'label'      => __( 'Variation product result format', 'woocommerce-bulk-order-form' ),
			'desc'       => __( 'Choose your variation product search results format', 'woocommerce-bulk-order-form' ),
			'options'    => wc_bof_get_title_templates(),
			'attr'       => array( 'class' => 'wc-enhanced-select  wcbof-settings-width-fix', ),
		);

		// $fields['general']['products'][] = array(
		// 	'id'      => WC_BOF_DB . 'attribute_display_format',
		// 	'type'    => 'radio',
		// 	'label'   => __( 'Product attribute format', 'woocommerce-bulk-order-form' ),
		// 	'default' => 'value',
		// 	'desc'    => __( 'Display the attribute title or just the attribute value. Eg: <code>Color:Red</code> | <code>Red</code>', 'woocommerce-bulk-order-form' ),
		// 	'options' => array(
		// 		'value'            => __( 'Attribute value only (recommended)', 'woocommerce-bulk-order-form' ),
		// 		'attributes_value' => __( 'Attribute title And value', 'woocommerce-bulk-order-form' ),
		// 	),
		// );
		return $fields;
	}
} // end class WooCommerce_Bulk_Order_Form_Admin_Settings_Options

endif; // end class_exists()

return new WooCommerce_Bulk_Order_Form_Admin_Settings_Options;