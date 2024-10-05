<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WooCommerce_Bulk_Order_Form_Template_Product_Search' ) ):

	abstract class WooCommerce_Bulk_Order_Form_Template_Product_Search {

		/**
		 * @var string
		 */
		public $type = ''; # Type of the current template

		/**
		 * @var array
		 */
		public static $founded_post = array();

		/**
		 * @var array
		 */
		private static $post_args = array();

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
		 * @param string|array $ids
		 *
		 * @return void
		 */
		public function set_post_parent( $ids = array() ): void {
			self::$post_args['post_parent'] = $ids;
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

		public function get_products(): array {
			$search_args = $this->get_search_args();
			$search_args = apply_filters( 'wc_bof_product_search_args', $search_args, $this->type );
			$posts       = get_posts( $search_args );

			foreach ( $posts as $key => $post ) {
				$product      = wc_get_product( $post );
				$product_type = method_exists( $product, 'get_type' ) ? $product->get_type() : $product->product_type;

				if ( 'external' === $product_type ) {
					unset( $posts[ $key ] );
				}
			}

			$max_results = ( isset( $search_args['posts_per_page'] ) && intval( $search_args['posts_per_page'] ) > 0 ) ? intval( $search_args['posts_per_page'] ) : false;
			if ( $posts && $max_results && count( $posts ) > $max_results ) {
				$posts = array_slice( $posts, 0, $max_results );
			}

			self::$founded_post = apply_filters( 'wc_bof_product_search_results', $posts, $search_args );

			return self::$founded_post;
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