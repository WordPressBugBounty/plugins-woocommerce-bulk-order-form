jQuery( function ( $ ) {
	if ( $( '.wc_bof_settings_submenu' ).length ) {
		var id = window.location.hash;
		$( '.wc_bof_settings_submenu a' ).removeClass( 'current' );
		$( '.wc_bof_settings_submenu a[href="' + id + '" ]' ).addClass( 'current' );
		if ( id === '' ) {
			$( '.wc_bof_settings_submenu a:first' ).addClass( 'current' );
			id = $( '.wc_bof_settings_submenu a:first' ).attr( 'href' );
		}
		http_reffer = $( 'input[name=_wp_http_referer]' ).val();
		settings_showHash( id );
	}

	$( '.wrap.wc_bof_settings :checkbox' ).each( function () {
		var datalabel  = $( this ).attr( 'data-label' );
		var separator  = $( this ).attr( 'data-separator' );
		var dataulabel = $( this ).attr( 'data-ulabel' );

		$( this ).labelauty( {
			label: true,
			separator: separator,
			checked_label: datalabel,
			unchecked_label: dataulabel,

		} );
	} );

	$( '.wrap.wc_bof_settings :radio' ).each( function () {
		$( this ).labelauty( {
			label: false,
		} );
	} );

	$( '.wc_bof_settings_submenu a' ).on('click', function () {
		var id = $( this ).attr( 'href' );
		$( '.wc_bof_settings_submenu a' ).removeClass( 'current' );
		$( this ).addClass( 'current' );
		settings_showHash( id );
		$( 'input[name=_wp_http_referer]' ).val( http_reffer + id )
	} );

	/* Hide Irrelevant Settings */
	$( '#wc_bof_general_wc_bof_template_type' ).on( 'change', function () {
		$( 'table.form-table tr' ).show();
		var chosen_template = $( '#wc_bof_general_wc_bof_template_type' ).val();
		if ( chosen_template === 'prepopulated' ) {
			$( '#wc_bof_general_wc_bof_no_of_rows' ).parents( 'tr' ).hide();
			$( '#wc_bof_general_wc_bof_add_rows' ).parents( 'tr' ).hide();
			$( '#wc_bof_general_wc_bof_single_addtocart' ).parents( 'tr' ).hide();
		}
	} ).trigger('change');

	function settings_showHash ( id ) {
		$( 'div.wc_bof_settings_content' ).hide();
		id = id.replace( '#', '#settings_' );
		$( id ).show();
	}
} );
