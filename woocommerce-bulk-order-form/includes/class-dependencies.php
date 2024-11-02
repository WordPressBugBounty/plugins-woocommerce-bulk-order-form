<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_Bulk_Order_Form_Dependencies' ) ):

class WC_Bulk_Order_Form_Dependencies {

	/**
	 * @var array|null
	 */
	private $activated_plugins;

	/**
	 * @var string
	 */
	private $php_min_version         = '7.2';

	/**
	 * @var string
	 */
	private $woocommerce_min_version = '3.0';

	/**
	 * @var
	 */
	private $plugin_name             = WC_BOF_NAME;

	public function __construct() {
		$this->activated_plugins = array_merge( get_option( 'active_plugins', array() ), get_site_option( 'active_sitewide_plugins', array() ) );
	}

	public function check_dependencies(): bool {
		$dependencies_met = true;

		// Check PHP version.
		if ( version_compare( PHP_VERSION, $this->php_min_version, '<' ) ) {
			add_action( 'admin_notices', array( $this, 'notice_php_version_requirement' ) );
			$dependencies_met = false;
		}

		// Check WooCommerce activation.
		if ( ! $this->is_active( 'woocommerce/woocommerce.php' ) ) {
			add_action( 'admin_notices', array( $this, 'notice_woocommerce_requirement' ) );
			$dependencies_met = false;
		}

		// Check WooCommerce version.
		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, $this->woocommerce_min_version, '<' ) ) {
			add_action( 'admin_notices', array( $this, 'notice_woocommerce_version_requirement' ) );
			$dependencies_met = false;
		}

		return $dependencies_met;
	}

	public function notice_php_version_requirement(): void {
		/* translators: 1. Plugin name, 2. PHP version */
		$error         = sprintf( __( '<strong>%1$s</strong> requires PHP %2$s or higher.', 'woocommerce-bulk-order-form' ), $this->plugin_name, $this->php_min_version );
		$how_to_update = __( 'How to update your PHP version', 'woocommerce-bulk-order-form' );
		printf( '<div class="notice notice-error"><p>%s</p><p><a href="%s">%s</a></p></div>', wp_kses_post( $error ), 'http://docs.wpovernight.com/general/how-to-update-your-php-version/', esc_html( $how_to_update ) );
	}

	public function notice_woocommerce_requirement(): void {
		/* translators: 1. Plugin name, 2: Opening anchor tag, 3: Closing anchor tag */
		$error_message = sprintf( __( '<strong>%1$s</strong> requires %2$sWooCommerce%3$s to be installed & activated!', 'woocommerce-bulk-order-form' ), $this->plugin_name, '<a href="https://wordpress.org/plugins/woocommerce/">', '</a>' );
		printf( '<div class="notice notice-error"><p>%s</p></div>', wp_kses_post( $error_message ) );
	}

	public function notice_woocommerce_version_requirement(): void {
		/* translators: 1. Plugin name, 2: WooCommerce version, 3: Opening anchor tag, 4: Closing anchor tag */
		$error_message = sprintf( __( '<strong>%1$s</strong> requires at least version %2$s of WooCommerce to be installed. %3$sGet the latest version here%4$s!', 'woocommerce-bulk-order-form' ), $this->plugin_name, $this->woocommerce_min_version, '<a href="https://wordpress.org/plugins/woocommerce/">', '</a>' );
		printf( '<div class="notice notice-error"><p>%s</p></div>', wp_kses_post( $error_message ) );
	}

	private function is_active( string $plugin_slug ): bool {
		return in_array( $plugin_slug, $this->activated_plugins ) || array_key_exists( $plugin_slug, $this->activated_plugins );
	}

} // end class WC_Bulk_Order_Form_Dependencies

endif; // end class_exists()

return new WC_Bulk_Order_Form_Dependencies();