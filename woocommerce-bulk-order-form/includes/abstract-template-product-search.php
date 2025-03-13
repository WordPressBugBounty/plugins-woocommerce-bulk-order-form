<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WooCommerce_Bulk_Order_Form_Template_Product_Search' ) ):

	abstract class WooCommerce_Bulk_Order_Form_Template_Product_Search {

		/**
		 * Type of the current template.
		 *
		 * @var string
		 */
		public string $type = '';

		public static array $founded_post = array();

		private static array $post_args = array();

		public function __construct() {
			$this->set_default_args();
		}

		public function _clear_defaults(): void {
			self::$post_args = array();
			$this->set_default_args();
		}

		public function set_default_args( array $posttypes = array( 'product', 'product_variation' ) ): void {
			self::$post_args['post_type']              = $posttypes;
			self::$post_args['nopaging']               = false; // setting to true will ignore any 'posts_per_page' or 'numberposts' arguments
			self::$post_args['suppress_filters']       = false;
			self::$post_args['update_post_term_cache'] = false;
			self::$post_args['update_post_meta_cache'] = false;
			self::$post_args['cache_results']          = false;
			self::$post_args['no_found_rows']          = true;
			self::$post_args['fields']                 = 'ids';
		}

		public function set_search_args( array $args ): bool {
			if ( ! empty( $args ) ) {
				self::$post_args = $args;
				return true;
			}

			return false;
		}

		public function set_search_by_title_query( string $type = 'add' ) {
			if ( 'add' === $type ) {
				add_filter( 'posts_search', array( $this, 'search_by_title_init' ), 10, 2 );
			} else {
				remove_filter( 'posts_search', array( $this, 'search_by_title_init' ), 10 );
			}
		}

		public function set_category( array $cats = array(), string $field = 'id' ): void {
			$terms = array();

			foreach ( $cats as $value ) {
				if ( is_numeric( $value ) && get_term_by( 'id', $value, 'product_cat' ) ) {
					$terms[] = $value;
				} else {
					$term = get_term_by( 'name', $value, 'product_cat' );

					if ( empty( $term ) ) {
						$term = get_term_by( 'slug', $value, 'product_cat' );
					}

					if ( ! empty( $term ) ) {
						$terms[] = $term->term_id;
					}
				}
			}

			self::$post_args['tax_query'][] = array(
				'taxonomy' => 'product_cat',
				'field'    => $field,
				'terms'    => $terms,
			);
		}

		/**
		 * @param string|array $ids
		 *
		 * @return void
		 */
		public function set_excludes( $ids = array() ): void {
			self::$post_args['post__not_in'] = $ids;
		}

		/**
		 * @param string|array $ids
		 *
		 * @return void
		 */
		public function set_includes( $ids = array() ): void {
			self::$post_args['post__in'] = $ids;
		}

		/**
		 * @param string|int $num
		 *
		 * @return void
		 */
		public function set_post_per_page( $num ): void {
			$num                               = intval( $num );
			self::$post_args['posts_per_page'] = $num;
			if ( 1 > $num ) {
				self::$post_args['nopaging'] = true;
			}
		}

		/**
		 * @param int $id
		 *
		 * @return void
		 */
		public function set_post_parent( int $id ): void {
			self::$post_args['post_parent'] = $id;
		}

		public function set_meta_query( array $query = array() ): void {
			self::$post_args['meta_query'][] = $query;
		}

		/**
		 * @param mixed $term
		 *
		 * @return void
		 */
		public function set_sku_search( $term ): void {
			$args = array(
				'key'     => '_sku',
				'value'   => $term,
				'compare' => 'LIKE'
			);
			$this->set_meta_query( apply_filters( 'wc_bof_search_sku_query_args', $args, $term ) );
		}

		/**
		 * Set global unique id search.
		 *
		 * @param mixed $term
		 *
		 * @return void
		 */
		public function set_global_unique_id_search( $term ): void {
			$args = array(
				'key'     => '_global_unique_id',
				'value'   => $term,
				'compare' => 'LIKE'
			);
			$this->set_meta_query( apply_filters( 'wc_bof_search_global_unique_id_query_args', $args, $term ) );
		}

		public function set_search_query( string $s = '' ): void {
			self::$post_args['s'] = $s;
		}

		public function set_orderby( string $order_by ): void {
			self::$post_args['orderby'] = $order_by;
		}

		public function set_order( string $order ): void {
			self::$post_args['order'] = $order;
		}

		/**
		 * @param string $key
		 * @param string $separator
		 * @param string $name
		 * @param float|int $price
		 * @param string $sku
		 *
		 * @return string
		 */
		public function get_output_title( string $key = 'TPS', string $separator = ' - ', string $name = '', $price = null, string $sku = '' ): string {
			$return = array();

			$format_rules = array(
				'STP' => array( 'sku', 'name', 'price' ),
				'TPS' => array( 'name', 'price', 'sku' ),
				'TP'  => array( 'name', 'price' ),
				'TS'  => array( 'name', 'sku' ),
				'T'   => array( 'name' ),
			);

			if ( isset( $format_rules[ $key ] ) ) {
				foreach ( $format_rules[ $key ] as $param ) {
					if ( ! empty( $$param ) ) {
						if ( 'price' === $param ) {
							$return[] = wc_price( $$param );
						} else {
							$return[] = $$param;
						}
					}
				}
			}

			return implode( $separator, $return );
		}

		/*
		 * Returns all search args
		 */
		public function get_search_args(): array {
			return self::$post_args;
		}

		/**
		 * Get all products by prepared query.
		 *
		 * @param bool $extract_children
		 *
		 * @return array
		 */
		public function get_products( bool $extract_children = false ): array {
			$search_args    = $this->get_search_args();
			$search_args    = apply_filters( 'wc_bof_product_search_args', $search_args, $this->type );
			$posts          = get_posts( $search_args );
			$children_posts = array();
			$parent_posts   = array();

			foreach ( $posts as $key => $post ) {
				$product = wc_get_product( $post );

				if ( $this->is_external_product( $product ) ) {
					unset( $posts[ $key ] );
				}

				// Get variable products
				if ( $extract_children && 'variable' === $product->get_type() ) {
					$children = $product->get_children();

					if ( ! empty( $children ) ) {
						foreach ( $children as $child ) {
							$variation_product = wc_get_product( $child );

							if ( ! $this->is_external_product( $variation_product ) ) {
								$children_posts[] = $variation_product->get_id();
							}
						}
					}
				}

				// Get parent product from variation
				if ( 'variation' === $product->get_type() ) {
					$parent_product = wc_get_product( $product->get_parent_id() );

					if ( ! $this->is_external_product( $parent_product ) ) {
						$parent_posts[] = $parent_product->get_id();
					}
				}
			}

			// Merge variation products
			$posts = array_merge( $posts, array_diff( $children_posts, $posts ) );

			// Merge variation parent products
			$posts = array_merge( $posts, array_diff( $parent_posts, $posts ) );

			$max_results = ( isset( $search_args['posts_per_page'] ) && intval( $search_args['posts_per_page'] ) > 0 ) ? intval( $search_args['posts_per_page'] ) : false;
			if ( $posts && $max_results && count( $posts ) > $max_results ) {
				$posts = array_slice( $posts, 0, $max_results );
			}

			self::$founded_post = apply_filters( 'wc_bof_product_search_results', $posts, $search_args );

			return self::$founded_post;
		}

		/**
		 * Check whether a product is an external product.
		 *
		 * @param \WC_Product $product
		 *
		 * @return bool
		 */
		private function is_external_product( \WC_Product $product ): bool {
			$product_type = method_exists( $product, 'get_type' ) ? $product->get_type() : $product->product_type;

			return 'external' === $product_type;
		}

		public function search_by_title_init( string $search, WP_Query $wp_query ): string {
			if ( ! empty( $search ) && ! empty( $wp_query->query_vars['search_terms'] ) ) {
				global $wpdb;
				$q      = $wp_query->query_vars;
				$n      = ! empty( $q['exact'] ) ? '' : '%';
				$search = array();

				foreach ( ( array ) $q['search_terms'] as $term ) {
					$search[] = $wpdb->prepare( "$wpdb->posts.post_title LIKE %s", $n . $wpdb->esc_like( $term ) . $n );
				}

				if ( ! is_user_logged_in() ) {
					$search[] = "$wpdb->posts.post_password = ''";
				}

				$search = ' AND ' . implode( ' AND ', $search );
			}

			return $search;
		}

		/**
		 * @param $id
		 *
		 * @return string
		 */
		public function get_product_title( $id ) {
			$title = get_the_title( $id );
			return html_entity_decode( $title, ENT_COMPAT, 'UTF-8' );
		}

		/**
		 * @param int|WP_Post $id
		 * @param bool $forceFilter
		 *
		 * @return false|mixed|string|null
		 */
		public function get_product_image( $id, bool $forceFilter = true ) {
			if ( wc_bof_option( 'show_image' ) ) {
				$settings = get_option( 'wc_bof_general', array() );
				if ( ! empty( $settings['wc_bof_image_width'] ) && ! empty( $settings['wc_bof_image_height'] ) ) {
					$size = array( intval( $settings['wc_bof_image_width'] ), intval( $settings['wc_bof_image_height'] ) );
				} else {
					$size = 'shop_thumbnail';
				}

				if ( has_post_thumbnail( $id ) ) {
					$img = get_the_post_thumbnail_url( $id, $size );
				} elseif ( ( $parent_id = wp_get_post_parent_id( $id ) ) && has_post_thumbnail( $parent_id ) ) {
					$img = get_the_post_thumbnail_url( $parent_id, $size );
				} elseif ( function_exists( 'wc_placeholder_img_src' ) ) {
					$img = wc_placeholder_img_src( $size );
				} else {
					$img = apply_filters( 'woocommerce_placeholder_img_src', '' );
				}
			} else {
				return '';
			}

			return $img;
		}

	} // end class WooCommerce_Bulk_Order_Form_Template_Product_Search

endif; // end class_exists()