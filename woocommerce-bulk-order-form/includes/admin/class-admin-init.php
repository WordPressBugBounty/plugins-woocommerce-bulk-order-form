<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WooCommerce_Bulk_Order_Form_Admin' ) ):

class WooCommerce_Bulk_Order_Form_Admin {

	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ), 99 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_links' ), 10, 2 );
		add_filter( 'plugin_action_links_' . WC_BOF_FILE, array( $this, 'plugin_action_links' ) );
		add_filter( 'woocommerce_screen_ids', array( $this, 'set_wc_screen_ids' ), 99 );
	}

	public function set_wc_screen_ids( array $screens ): array {
		$screens[] = 'woocommerce_page_woocommerce-bulk-order-form-settings';
		return $screens;
	}

	public function enqueue_styles( string $hook ): void {
		// Load script only on Bulk order settings page.
		if ( 'woocommerce_page_woocommerce-bulk-order-form-settings' === $hook ) {
			wp_register_style( WC_BOF_SLUG . '_backend_style', WC_BOF_CSS . 'backend.css', array(), WC_BOF_V );
			wp_enqueue_style( WC_BOF_SLUG . '_backend_style' );
		}
	}

	public function enqueue_scripts( string $hook ): void {
		// Load script only on Bulk order settings page.
		if ( 'woocommerce_page_woocommerce-bulk-order-form-settings' === $hook ) {
			wp_register_script( WC_BOF_SLUG . '_backend_script', WC_BOF_JS . 'backend.js', array( 'jquery' ), WC_BOF_V, false );
			wp_enqueue_script( WC_BOF_SLUG . '_backend_script' );

			wp_localize_script(
				WC_BOF_SLUG . '_backend_script',
				'wc_bof_admin',
				array(
					'pro_feature' => sprintf(
						/* translators: pro extension link */
						__( 'This feature is only available in %s', 'woocommerce-bulk-order-form' ),
						'<a href="https://wpovernight.com/downloads/woocommerce-bulk-order-form">Bulk Order Form for WooCommerce Pro</a>'
					),
				)
			);
		}
	}

	public function plugin_action_links( array $action ): array {
		$settings_url = admin_url( 'admin.php?page=' . WC_BOF_SLUG . '-settings' );
		$actions[]    = sprintf( '<a href="%s">%s</a>', $settings_url, __( 'Settings', 'woocommerce-bulk-order-form' ) );

		return array_merge( $actions, $action );
	}

	public function plugin_row_links( array $plugin_meta, string $plugin_file ): array {
		if ( WC_BOF_FILE === $plugin_file ) {
			$plugin_meta[] = sprintf( '<a href="%s">%s</a>', 'https://docs.wpovernight.com/category/bulk-order-form/', __( 'FAQ', 'woocommerce-bulk-order-form' ) );
			$plugin_meta[] = sprintf( '<a href="%s">%s</a>', 'https://wpovernight.com/contact/', __( 'Support', 'woocommerce-bulk-order-form' ) );
		}

		return $plugin_meta;
	}

} // end class WooCommerce_Bulk_Order_Form_Admin

endif; // end class_exists()
