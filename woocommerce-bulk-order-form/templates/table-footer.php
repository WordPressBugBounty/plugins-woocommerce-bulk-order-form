<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
	</tbody>
	<tfoot id="wcbulkordertotaltbl" class="wcbulkordertotaltbl">
		<?php if( ($is_variation === true  || $is_standard === true ) && ($price == 'on')) : ?>
			<tr id="wcbulkordertotals" class="wcbulkordertotals">
				<td class="wc_bof_pricetotalhead" colspan=""><?php echo esc_html( $total_label ); ?> :</td>
				<td class="wc_bof_pricetotal"><span class="wcbulkorderalltotal"></span></td>

			</tr>
		<?php endif; ?>
		<tr id="wcbulkorderactions" class="wcbulkorderactions">
			<td>
				<?php do_action('wc_bof_template_footer_actions',$engine->attr); ?>
			</td>
			<td>
				<button data-formid="<?php echo esc_attr( $formid ); ?>" type="submit" class="wcbofaddtocart button" id="wcbofaddtocart_<?php echo esc_attr( $formid ); ?>"><?php echo esc_html( $cart_label ); ?></button>
			</td>
		</tr>
	</tfoot>
</table> 