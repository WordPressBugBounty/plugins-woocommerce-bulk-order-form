<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<form id="wcbulkorderform_<?php echo esc_attr( $formid ); ?>" data-formid="<?php echo esc_attr( $formid ); ?>" class="wcbulkorderform <?php echo esc_attr( $template ); ?>" method="post" action="" role="search">
	<div class="backEndResponse" > <?php if ( function_exists('wc_print_notices') ) wc_print_notices(); ?> </div>