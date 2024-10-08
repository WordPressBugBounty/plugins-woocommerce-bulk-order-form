<?php

/**
 * Check the version of WordPress
 *
 * @link https://wordpress.org/plugins/woocommerce-bulk-order-form/
 * @package Bulk Order Form for WooCommerce
 * @subpackage Bulk Order Form for WooCommerce/core
 * @since 3.0
 */
class WooCommerce_Bulk_Order_Form_Version_Check {
	/**
	 * version
	 *
	 * @var
	 */
	static $version;

	/**
	 * The primary sanity check, automatically disable the plugin on activation if it doesn't meet minimum requirements
	 *
	 * @since  1.0.0
	 */
	public static function activation_check( $version ) {
		self::$version = $version;
		if ( ! self::compatible_version() ) {
			deactivate_plugins( WC_BOF_FILE );
			/* translators: 1. plugin name, 2. WP version */
			wp_die( sprintf( __(  '%1$s requires WordPress %2$s or higher!', 'woocommerce-bulk-order-form' ), WC_BOF_NAME, self::$version ) );
		}
	}

	/**
	 * Check current version against $prefix_version_check
	 *
	 * @since  1.0.0
	 */
	public static function compatible_version() {
		if ( version_compare( $GLOBALS['wp_version'], self::$version, '<' ) ) {
			return false;
		}
		return true;
	}

	/**
	 * The backup sanity check, in case the plugin is activated in a weird way, or the versions change after activation
	 *
	 * @since  1.0.0
	 */
	public function check_version() {
		if ( ! self::compatible_version() ) {
			if ( is_plugin_active( WC_BOF_FILE ) ) {
				deactivate_plugins( WC_BOF_FILE );
				add_action( 'admin_notices', array( $this, 'disabled_notice' ) );
				if ( isset( $_GET['activate'] ) ) {
					unset( $_GET['activate'] );
				}
			}
		}
	}

	/**
	 * Text to display in the notice
	 *
	 * @since  1.0.0
	 */
	public function disabled_notice() {
		/* translators: 1. plugin name, 2. WP version */
		echo '<strong>' . sprintf( esc_html__( '%1$s requires WordPress %2$s or higher!', 'woocommerce-bulk-order-form' ), WC_BOF_NAME, self::$version ) . '</strong>';
	}
}
