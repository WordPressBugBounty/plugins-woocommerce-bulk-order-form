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

	// disabled pro features
	$( "input.wcbulkorder-disabled" ).attr( 'disabled', true );

	$( '.extensions .more' ).hide();
	$( '.extensions > li' ).on('click', function () {
		$( this ).toggleClass( 'expanded' );
		$( this ).find( '.more' ).slideToggle();
	} );
	$( '.wc_bof_settings .select2-container--disabled' ).on('click', function () {
		show_bof_pro_feature( $( this ).closest( 'td' ) );
	} );
	$( '.wc_bof_settings td' ).on( 'click', function () {
		if ( $( this ).find( 'input' ).prop( 'disabled' ) ) {
			show_bof_pro_feature( $( this ).closest( 'td' ) );
		}
	} );
	$( '.wc_bof_settings label' ).on( 'click', function () {
		if ( $( '#' + $( this ).attr( 'for' ) ).prop( 'disabled' ) ) {
			show_bof_pro_feature( $( this ).closest( 'td' ) );
		}
	} );

	// add overlay to detect clicks on disabled features
	$( '.wc_bof_settings :input' ).each( function () {
		if ( $( this ).prop( 'disabled' ) ) {
			$( this ).closest( 'td' ).append( '<div style="position:absolute; left:0; right:0; top:0; bottom:0; background-color:white; -moz-opacity: 0; opacity:0;filter: alpha(opacity=0);" class="hidden-input"></div>' );
		}
	} );

	$( '.hidden-input' ).on( 'click', function() {
		show_bof_pro_feature( $( this ).closest( 'td' ) );
	} );

	function show_bof_pro_feature ( $row ) {
		if ( $row.find( '.pro-feature' ).length < 1 ) {
			$row.append( '<div class="pro-feature" style="display:none;">' + wc_bof_admin.pro_feature + '</div>' );
		}
		$row_ad = $row.find( '.pro-feature' );
		$( '.pro-feature' ).not( $row_ad ).hide( 'slow' );
		$row_ad.show( 'slow' );
	}
} );
