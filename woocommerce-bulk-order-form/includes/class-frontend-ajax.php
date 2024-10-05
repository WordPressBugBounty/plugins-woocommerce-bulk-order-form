<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WooCommerce_Bulk_Order_Form_Ajax_FrontEnd' ) ):

class WooCommerce_Bulk_Order_Form_Ajax_FrontEnd {

	public function __construct() {
		add_action( 'wp_ajax_wcbulkorder_product_search', array( $this, 'product_search' ) );
		add_action( 'wp_ajax_nopriv_wcbulkorder_product_search', array( $this, 'product_search' ) );

		add_action( 'wp', array( $this, 'add_to_cart_process' ) );
		add_action( 'wp_ajax_wcbulkorder_product_buy_now', array( $this, 'ajax_add_to_cart_process' ) );
		add_action( 'wp_ajax_nopriv_wcbulkorder_product_buy_now', array( $this, 'ajax_add_to_cart_process' ) );

		add_action( 'wp_ajax_wcbulkorder_product_single_buy_now', array( $this, 'ajax_single_add_to_cart_process' ) );
		add_action( 'wp_ajax_nopriv_wcbulkorder_product_single_buy_now', array( $this, 'ajax_single_add_to_cart_process' ) );
	}

	public function product_search(): void {
		$request = stripslashes_deep( $_REQUEST );

		if ( isset( $request['wcbulkorder'] ) && is_array( $request['wcbulkorder'] ) ) {
			$data         = $this->sanitize_array_data( $request['wcbulkorder'] );
			$data['term'] = isset( $request['term'] ) ? esc_attr( $request['term'] ) : '';
		}

		if ( ! empty( $data['settings']['category'] ) ) {
			$data['settings']['category'] = explode( ',', $data['settings']['category'] );
		}
		if ( ! empty( $data['settings']['excluded'] ) ) {
			$data['settings']['excluded'] = explode( ',', $data['settings']['excluded'] );
		}
		if ( ! empty( $data['settings']['included'] ) ) {
			$data['settings']['included'] = explode( ',', $data['settings']['included'] );
		}

		$return = '';
		if ( ! empty( $data['settings']['template'] ) && array_key_exists( $data['settings']['template'], wc_bof_template_types() ) ) {
			do_action_ref_array( 'wc_bof_render_' . $data['settings']['template'] . '_template_product_search', array( &$return, $data ) );
		}

		wp_send_json( $return );
		wp_die();
	}

	public function ajax_add_to_cart_process(): void {
		$this->add_to_cart_process();
		ob_start();
		wc_print_notices();
		$output = ob_get_clean();
		wp_send_json_success( $output );
		wp_die();
	}

	public function add_to_cart_process(): void {
		$return  = '';
		$request = stripslashes_deep( $_REQUEST );

		if ( isset( $request['wcbulkorder'] ) && is_array( $request['wcbulkorder'] ) ) {
			$data = apply_filters( 'wc_bof_add_to_cart_data', $this->sanitize_array_data( $request['wcbulkorder'] ) );
			if ( ! empty( $data['settings']['template'] ) && array_key_exists( $data['settings']['template'], wc_bof_template_types() ) ) {
				do_action_ref_array( 'wc_bof_' . $data['settings']['template'] . '_add_to_cart', array( &$return, &$data ) );
			}
		}
	}

	public function ajax_single_add_to_cart_process(): void {
		$this->single_add_to_cart_process();
		ob_start();
		wc_print_notices();
		$output = ob_get_clean();
		wp_send_json_success( $output );
		wp_die();
	}

	public function single_add_to_cart_process(): void {
		$return  = '';
		$request = stripslashes_deep( $_REQUEST );

		if ( isset( $request['wcbulkorder'] ) && is_array( $request['wcbulkorder'] ) ) {
			$data = $this->sanitize_array_data( $request['wcbulkorder'] );
			if ( ! empty( $data['settings']['template'] ) && array_key_exists( $data['settings']['template'], wc_bof_template_types() ) ) {
				do_action_ref_array( 'wc_bof_' . $data['settings']['template'] . '_single_add_to_cart', array( &$return, &$data ) );
			}
		}
	}

	public function sanitize_array_data( array $input ): array {
		$new_input = array();
		foreach ( $input as $key => $val ) {
			if ( is_array( $val ) ) {
				$new_input[ $key ] = $this->sanitize_array_data( $val );
			} else {
				$new_input[ $key ] = wp_kses_post( $val );
			}
		}
		return $new_input;
	}

} // end class WooCommerce_Bulk_Order_Form_Ajax_FrontEnd

endif; // end class_exists()