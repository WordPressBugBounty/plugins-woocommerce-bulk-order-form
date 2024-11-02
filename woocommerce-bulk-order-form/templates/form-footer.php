<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
	<?php echo wp_kses( $reqfields, WC_BOF_ALLOWED_HTML );  ?>
</form>
