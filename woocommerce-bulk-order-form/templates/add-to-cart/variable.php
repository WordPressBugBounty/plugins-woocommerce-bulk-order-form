<?php
/**
 * @author  WooThemes
 * @package WC Bulk Order Form/Templates
 * @version 2.5.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
global $product;
$attribute_keys = array_keys( $attributes );

if ( isset( $args['settings'] ) ) {
	$args = $args['settings'];
}
?>

<div class="variations_form cart" method="post" enctype='multipart/form-data' data-formid="<?php echo esc_attr( $args['formid'] ); ?>" data-product_id="<?php echo absint( $product->get_id() ); ?>" data-product_variations="<?php echo wp_json_encode( $available_variations ); ?>">
	<input type="hidden" name="wcbulkorder[wcbof_products][REPLACECOUNT][variation_id]" value="" class="variation_id" />
	<?php if ( empty( $available_variations ) && false !== $available_variations ) : ?>
		<p class="stock out-of-stock">
			<?php esc_html_e( 'This product is currently out of stock and unavailable.', 'woocommerce-bulk-order-form' ); ?>
		</p>
	<?php else : ?>
		<div class="variations"> 
			<?php foreach ( $attributes as $attribute_name => $options ) : ?>
				<div class="value" data-formid="<?php echo esc_attr( $args['formid'] ); ?>">
					<?php
						$attribute_key = 'attribute_' . sanitize_title( $attribute_name );
						$selected      = isset( $_REQUEST[ $attribute_key ] ) ? sanitize_text_field( wp_unslash( $_REQUEST[ $attribute_key ] ) ) : $product->get_variation_default_attribute( $attribute_name ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
						
						wc_dropdown_variation_attribute_options( array( 
							'show_option_none' => wc_attribute_label( $attribute_name ),
							'options' => $options,
							'attribute' => $attribute_name, 
							'product' => $product, 
							'name'  => 'wcbulkorder[wcbof_products][REPLACECOUNT][attributes][' . $attribute_name . ']',
							'id' => sanitize_title( $attribute_name.'_' . absint( $product->get_id() ) ),
							'selected' => $selected )
						);
						
						echo end( $attribute_keys ) === $attribute_name ? wp_kses( apply_filters( 'woocommerce_reset_variations_link', '<a class="reset_variations" href="#">' . __( 'Clear', 'woocommerce-bulk-order-form' ) . '</a>' ), WC_BOF_ALLOWED_HTML ) : '';
					?>
				</div> 
			<?php endforeach;?> 
			<div class="woocommerce-variation-availability" style="display:none;"></div>
		</div>
		
	<?php endif; ?>
</div>
