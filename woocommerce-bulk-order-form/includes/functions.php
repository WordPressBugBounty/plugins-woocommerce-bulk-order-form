<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $wc_bof_db_settins_values;
$wc_bof_db_settins_values = array();
add_action( 'wc_bof_loaded', 'wc_bof_get_settings_from_db', 1 );

if ( ! function_exists( 'wc_bof_option' ) ) {
	/**
	 * @param string $key
	 * @param bool|array $default
	 *
	 * @return array|false|mixed
	 */
	function wc_bof_option( string $key = '', $default = false ) {
		global $wc_bof_db_settins_values;
		if ( '' === $key ) {
			return $wc_bof_db_settins_values;
		}
		if ( ! empty( $wc_bof_db_settins_values[ WC_BOF_DB . $key ] ) ) {
			return $wc_bof_db_settins_values[ WC_BOF_DB . $key ];
		}

		return $default;
	}
}

if ( ! function_exists( 'wc_bof_get_settings_from_db' ) ) {
	/**
	 * Retrives All Plugin Options From DB
	 */
	function wc_bof_get_settings_from_db(): void {
		global $wc_bof_db_settins_values;
		$section = array();
		$section = apply_filters( 'wc_bof_settings_section', $section );
		$values  = array();
		foreach ( $section as $settings ) {
			foreach ( $settings as $set ) {
				$db_val = get_option( WC_BOF_DB . $set['id'] );
				if ( is_array( $db_val ) ) {
					unset( $db_val['section_id'] );
					$values = array_merge( $db_val, $values );
				}
			}
		}
		$wc_bof_db_settins_values = $values;
	}
}

if ( ! function_exists( 'wc_bof_current_screen' ) ) {
	function wc_bof_current_screen(): string {
		$screen = get_current_screen();
		return $screen->id;
	}
}

if ( ! function_exists( 'wc_bof_get_screen_ids' ) ) {
	/**
	 * Returns Predefined Screen IDS
	 *
	 * @return array
	 */
	function wc_bof_get_screen_ids(): array {
		$screen_ids   = array();
		$screen_ids[] = 'woocommerce_page_woocommerce-bulk-order-form-settings';
		return $screen_ids;
	}
}

if ( ! function_exists( 'wc_bof_do_settings_sections' ) ) {
	/**
	 * Prints out all settings sections added to a particular settings page
	 *
	 * Part of the Settings API. Use this in a settings page callback function
	 * to output all the sections and fields that were added to that $page with
	 * add_settings_section() and add_settings_field()
	 *
	 * @param string $page The slug name of the page whose settings sections you want to output
	 *
	 * @global       $wp_settings_fields Storage array of settings fields and info about their pages/sections
	 * @since 2.7.0
	 *
	 * @global       $wp_settings_sections Storage array of all settings sections added to admin pages
	 */
	function wc_bof_do_settings_sections( string $page ): void {
		global $wp_settings_sections, $wp_settings_fields;
		if ( ! isset( $wp_settings_sections[ $page ] ) )
			return;
		$section_count = count( $wp_settings_sections[ $page ] );
		if ( $section_count > 1 ) {
			echo '<ul class="subsubsub wc_bof_settings_submenu">';
			foreach ( (array) $wp_settings_sections[ $page ] as $section ) {
				echo '<li> <a href="#' . esc_attr( $section['id'] ) . '">' . esc_html( $section['title'] ) . '</a> | </li>';
			}
			echo '</ul> <br/>';
		}

		foreach ( (array) $wp_settings_sections[ $page ] as $section ) {
			if ( $section_count > 1 ) {
				echo '<div id="settings_' . esc_attr( $section['id'] ) . '" class="hidden wc_bof_settings_content">';
			}
			if ( $section['title'] ) {
				echo "<h3>" . esc_html( $section['title'] ) . "</h3>\n";
			}
			if ( $section['callback'] ) {
				call_user_func( $section['callback'], $section );
			}
			if ( ! isset( $wp_settings_fields ) || ! isset( $wp_settings_fields[ $page ] ) || ! isset( $wp_settings_fields[ $page ][ $section['id'] ] ) ) {
				continue;
			}
			echo '<table class="form-table">';
			do_settings_fields( $page, $section['id'] );
			echo '</table>';
			if ( $section_count > 1 ) {
				echo '</div>';
			}
		}
	}
}

if ( ! function_exists( 'wc_bof_template_types' ) ) {
	/*
	 * Returns Available Bulk Order Form Template Views
	 */
	function wc_bof_template_types(): array {
		$templates              = array();
		$templates['standard']  = array(
			'name'     => __( 'Standard template', 'woocommerce-bulk-order-form' ),
			'callback' => 'WooCommerce_Bulk_Order_Form_Template_UI',
		);
		$templates['variation'] = array(
			'name'     => __( 'Variation template', 'woocommerce-bulk-order-form' ),
			'callback' => 'WooCommerce_Bulk_Order_Form_Template_UI',
		);

		return apply_filters( 'wc_bof_templates', $templates );
	}
}

if ( ! function_exists( 'wc_bof_active_template' ) ) {
	/**
	 * @param string $name
	 *
	 * @return array|bool|mixed|string
	 */
	function wc_bof_active_template( string $name = '' ) {
		//$templates = !empty(wc_bof_option('template_type')) ? wc_bof_option('template_type') : 'standard' ;
		$templates = wc_bof_option( 'template_type' );
		if ( ! empty( $template ) ) {
			$template = wc_bof_option( 'template_type' );
		} else {
			$template = 'standard';
		}
		if ( ! empty( $name ) ) {
			if ( $name === $templates ) {
				return true;
			}
			return false;
		}
		return $template;
	}
}

if ( ! function_exists( 'wc_bof_template_select_box' ) ) {
	function wc_bof_template_select_box(): array {
		$list   = wc_bof_template_types();
		$return = array();
		foreach ( $list as $LK => $LV ) {
			$return[ $LK ] = $LV;
		}

		return $return;
	}
}

if ( ! function_exists( 'wc_bof_get_template' ) ) {
	function wc_bof_get_template( string $name, array $args = array(), string $dfpath = '' ): void {
		if ( empty( $dfpath ) ) {
			$dfpath = WC_BOF_PATH . '/templates/';
		}
		wc_get_template( $name, $args, 'woocommerce/wcbulkorder', $dfpath );
	}
}

if ( ! function_exists( 'wc_bof_get_search_types' ) ) {
	function wc_bof_get_search_types(): array {
		$types               = array();
		$types['all']        = __( 'All', 'woocommerce-bulk-order-form' );
		$types['sku']        = __( 'Product SKU', 'woocommerce-bulk-order-form' );
		$types['id']         = __( 'Product ID', 'woocommerce-bulk-order-form' );
		$types['title']      = __( 'Product title', 'woocommerce-bulk-order-form' );
		$types['attributes'] = __( 'Product attributes', 'woocommerce-bulk-order-form' );

		return apply_filters( 'wc_bof_search_types', $types );
	}
}

if ( ! function_exists( 'wc_bof_get_title_templates' ) ) {
	function wc_bof_get_title_templates(): array {
		$title        = array();
		$title['STP'] = __( '[sku] - [title] - [price]', 'woocommerce-bulk-order-form' );
		$title['TPS'] = __( '[title] - [price] - [sku]', 'woocommerce-bulk-order-form' );
		$title['TP']  = __( '[title] - [price]', 'woocommerce-bulk-order-form' );
		$title['TS']  = __( '[title] - [sku]', 'woocommerce-bulk-order-form' );
		$title['T']   = __( '[title]', 'woocommerce-bulk-order-form' );

		return apply_filters( 'wc_bof_product_display_templates_tags', $title );
	}
}

if ( ! function_exists( 'wc_bof_settings_products_json' ) ) {
	/**
	 * @param mixed $ids
	 *
	 * @return array
	 */
	function wc_bof_settings_products_json( $ids ): array {
		$json_ids = array();
		if ( ! empty( $ids ) ) {
			if ( ! is_array( $ids ) ) {
				$ids = explode( ',', $ids );
			}

			foreach ( $ids as $product_id ) {
				$product = wc_get_product( $product_id );
				if ( ! $product ) {
					continue;
				}
				$json_ids[ $product_id ] = wp_kses_post( $product->get_formatted_name() );
			}
		}
		return $json_ids;
	}
}

if ( ! function_exists( 'wc_bof_settings_get_categories' ) ) {
	function wc_bof_settings_get_categories( string $tax = 'product_cat', array $terms = array() ): array {
		if ( empty( $terms ) ) {
			$args                           = array();
			$args['hide_empty']             = false;
			$args['number']                 = 0;
			$args['pad_counts']             = true;
			$args['update_term_meta_cache'] = false;
			$terms                          = get_terms( $tax, $args );
			$output                         = array();
		}
		foreach ( $terms as $term ) {
			$output[ $term->term_id ] = $term->name . ' (' . $term->count . ') ';
		}

		return $output;
	}
}

if ( ! function_exists( 'wc_bof_settings_get_product_attributes' ) ) {
	function wc_bof_settings_get_product_attributes( bool $selected = false ): array {
		$output = array();
		if ( $selected ) {
			$output = wc_bof_option( 'product_attributes', array() );
		} else {
			$attribute_taxonomies = wc_get_attribute_taxonomies();

			foreach ( $attribute_taxonomies as $term ) {
				$output[ $term->attribute_id ] = $term->attribute_label;
			}
		}

		return $output;
	}
}

if ( ! function_exists( 'wc_bof_settings_page_link' ) ) {
	function wc_bof_settings_page_link( string $tab = '', string $section = '' ): ?string {
		$settings_url = admin_url( 'admin.php?page=' . WC_BOF_SLUG . '-settings' );
		if ( ! empty( $tab ) ) {
			$settings_url .= '&tab=' . $tab;
		}
		if ( ! empty( $section ) ) {
			$settings_url .= '#' . $section;
		}
		return $settings_url;
	}
}


if ( ! function_exists( 'wc_bof_is_wc_v' ) ) {
	function wc_bof_is_wc_v( string $compare = '>=', string $version = '' ): bool {
		if ( defined( 'WOOCOMMERCE_VERSION' ) && ( empty( $version ) || version_compare( WOOCOMMERCE_VERSION, $version, $compare ) ) ) {
			return true;
		} else {
			return false;
		}
	}
}

if ( ! function_exists( 'wc_bof_get_product' ) ) {
	/**
	 * @param mixed $product_id
	 *
	 * @return false|WC_Product|null
	 */
	function wc_bof_get_product( $product_id ) {
		wc_deprecated_function( 'wc_bof_get_product', '3.5.8', 'wc_get_product' );
		return wc_get_product( $product_id );
	}
}
