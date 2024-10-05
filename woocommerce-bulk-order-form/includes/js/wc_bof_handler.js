wcbof_forms = {};

WCBOFHandler.decmultiple = '1';
while ( WCBOFHandler.decmultiple.length <= WCBOFHandler.num_decimals ) {
	WCBOFHandler.decmultiple += '0';
}

var wc_bof_handler = function () {
};

wc_bof_handler.prototype.settings = '';

wc_bof_handler.prototype.form = '';

wc_bof_handler.prototype.formid = '';

wc_bof_handler.prototype.settings_string = '';

wc_bof_handler.prototype.init = function ( form, formid ) {
	this.form   = form;
	this.formid = formid;
	this.grab_settings();
};

wc_bof_handler.prototype.grab_settings = function () {
	var settings         = [];
	this.settings_string = this.form.find( ".form_hidden_fileds :input" ).serialize();
	this.form.find( ".form_hidden_fileds :input" ).each( function ( id, input ) {
		var inpt                      = jQuery( input );
		settings[ inpt.attr( 'id' ) ] = inpt.val();
	} );
	this.settings = settings;
};

wc_bof_handler.prototype.get_setting = function ( key ) {
	if ( this.settings[ key ] === undefined ) {
		return false;
	} else {
		return this.settings[ key ];
	}
};

wc_bof_handler.prototype.add_row = function ( id, elem ) {
	if ( id !== this.formid ) {
		return;
	}

	var template = this.form.find( 'table.wcbulkorderproducttbl' ).find( '#wc_bof_product_removeHidden' ).clone();

	var total_rows = parseInt( this.form.find( '.form_hidden_fileds input#rows' ).val() );
	total_rows     = total_rows + 1;
	this.form.find( '.form_hidden_fileds input#rows' ).val( parseInt( total_rows ) );

	var id_replace = template.attr( 'id' );
	id_replace     = id_replace.replace( "removeHidden", total_rows );

	var data_rowcount_replace = template.attr( 'data-rowcount' );
	data_rowcount_replace     = data_rowcount_replace.replace( "removeHidden", total_rows );

	template.attr( "id", id_replace );
	template.attr( 'data-rowcount', data_rowcount_replace );

	var template_html = template.html();
	template_html     = template_html.replace( /removeHidden/g, total_rows );
	template.html( template_html );

	this.form.find( 'table.wcbulkorderproducttbl tbody tr:last' ).after( template );
	var select = this.form.find( 'table.wcbulkorderproducttbl tbody tr:last .product_name_search_field' );
	select.show();
	wc_bof_init_selectize( select );
};

wc_bof_handler.prototype.trigger_item_add = function ( formid, productID, itemDATA, select ) {
	var selectbox = select;
	if ( this.formid !== formid ) {
		return;
	}

	if ( this.settings.template === 'variation' ) {
		this.add_variation( productID, itemDATA, selectbox );
	} else if ( this.settings.template === 'standard' ) {
		this.add_standard( productID, itemDATA, selectbox );
	} else {
		jQuery( 'body' ).trigger( 'wc_bof_item_selected', [ id, added, select ] );
	}

	selectbox.parent().parent().find( '.add-to-cart-single' ).show();
};

wc_bof_handler.prototype.add_standard = function ( productID, itemDATA, selectbox ) {
	var product_id  = selectbox.parent().parent().find( 'input.product_id' );
	var product_qty = selectbox.parent().parent().find( 'input.product_qty' );
	var amount      = selectbox.parent().parent().find( '.wc_bof_product_price > span.amount' );

	this.set_qty_attributes( itemDATA, product_qty );

	product_qty.attr( 'data-price', itemDATA[ 'price' ] );
	product_qty.attr( 'data-currency', itemDATA[ 'symbol' ] );
	product_id.val( itemDATA[ 'id' ] );
	this.calculate_single_price( product_qty );
	this.calculate_all_price();
};

wc_bof_handler.prototype.add_variation = function ( productID, itemDATA, selectbox ) {
	var product_id  = selectbox.parent().parent().find( 'input.product_id' );
	var product_qty = selectbox.parent().parent().find( 'input.product_qty' );

	this.set_qty_attributes( itemDATA, product_qty );

	product_id.val( itemDATA[ 'id' ] );
	product_qty.attr( 'data-currency', itemDATA[ 'symbol' ] );

	selectbox.parent().parent().find( '.wc_bof_variation_name' ).html( '' );


	if ( itemDATA[ 'has_variation' ] === 'yes' ) {
		var variation = selectbox.parent().parent().find( '.wc_bof_variation_name' );
		var count     = variation.attr( 'data-count' );
		var html      = itemDATA[ 'attribute_html' ];
		html          = html.replace( /REPLACECOUNT/g, count );
		variation.html( html );
		jQuery( variation ).find( '.variations_form' ).wc_variation_form().find( '.variations select:eq(0)' ).change();
	} else {
		selectbox.parent().parent().find( '.wc_bof_variation_name' ).html( '' );
		product_qty.attr( 'data-price', itemDATA[ 'price' ] );
		//variation_calculate_single_price(product_qty);
		this.calculate_single_price( product_qty );
		this.calculate_all_price();
	}
};

wc_bof_handler.prototype.set_qty_attributes = function ( itemDATA, input ) {
	// set min/max/step for qty field
	var qty_field_args = {
		'min'  : 'qty_min',
		'max'  : 'qty_max',
		'step' : 'qty_step',
	}
	for (var key in qty_field_args) {
		if (typeof(itemDATA[qty_field_args[key]]) != "undefined" ) {
			if (itemDATA[qty_field_args[key]] == -1) {
				input.attr( key, '' );
			} else {
				input.attr( key, itemDATA[qty_field_args[key]] );
			}
		}
	}
	this.enforce_qty_attributes( input );
}

wc_bof_handler.prototype.enforce_qty_attributes = function ( input ) {
	if (! jQuery(input).is(':disabled') ) {
		// limit input to min & max attributes
		var max = parseInt(jQuery(input).attr('max'));
		var min = parseInt(jQuery(input).attr('min'));
		if ( jQuery(input).val() > max ) {
			jQuery(input).val(max);
		} else if ( jQuery(input).val() < min ) {
			jQuery(input).val(min);
		}

		// enforce step size
		var step = parseInt(jQuery(input).attr('step'));
		var step_base = parseInt( jQuery(input).val() ) - min;
		if ( step > 1 && step_base != 0 ) {
			var steps = step_base / step;
			if ( Math.round(steps) != steps) {
				jQuery(input).val( ( Math.floor(steps) * step ) + min );
			}
		}
	}
}


wc_bof_handler.prototype.calculate_single_price = function ( qtyf ) {
	var currency    = qtyf.attr( 'data-currency' );
	var price       = qtyf.attr( 'data-price' );
	var qty         = qtyf.val();
	var final_Price = 0;
	var sum         = 0;

	if ( qty !== undefined && qty !== '' ) {
		if ( qty > 0 && price !== undefined ) {
			final_Price = parseFloat( qty ) * parseFloat( price );
			sum         = number_format( final_Price, WCBulkOrder.num_decimals, WCBulkOrder.decimal_sep, WCBulkOrder.thousands_sep );
			//sum = final_Price.toFixed(WCBulkOrder.num_decimals).toString().replace(".", WCBulkOrder.decimal_sep);
		}
	}

	qtyf.attr( 'data-fprice', final_Price );
	final_Price = sprintf( WCBulkOrder.price_format, currency, sum );
	//final_Price = currency + sum;
	if ( price == '' || price == undefined || qty == undefined || qty == '' || qty == 0 ) {
		qtyf.parent().parent().find( '.wc_bof_product_price > span.amount' ).html( '' );
	} else {
		qtyf.parent().parent().find( '.wc_bof_product_price > span.amount' ).html( final_Price );
	}
};

wc_bof_handler.prototype.calculate_all_price = function () {
	var final_Price = 0;
	var currency    = '';
	this.form.find( 'input.product_qty' ).each( function () {
		currency     = jQuery( this ).attr( 'data-currency' );
		var price    = jQuery( this ).attr( 'data-fprice' );
		var quantity = jQuery( this ).val();
		if ( ( price !== undefined ) && ( quantity > 0 ) ) {
			final_Price = parseFloat( final_Price ) + parseFloat( price );
		}
	} );
	//semi_total = final_price.toFixed(WCBulkOrder.num_decimals).toString().replace(".", WCBulkOrder.decimal_sep);
	semi_total  = number_format( final_Price, WCBulkOrder.num_decimals, WCBulkOrder.decimal_sep, WCBulkOrder.thousands_sep );
	final_Price = sprintf( WCBulkOrder.price_format, currency, semi_total );
	//final_Price = currency + semi_total;
	this.form.find( 'span.wcbulkorderalltotal' ).html( final_Price );
};

wc_bof_handler.prototype.calculate_standard_template_price = function ( formid, elem ) {
	if ( this.formid !== formid ) {
		return;
	}
	this.calculate_single_price( elem );
	this.calculate_all_price();
};

wc_bof_handler.prototype.calculate_variation_template_price = function ( formid, elem ) {
	if ( this.formid !== formid ) {
		return;
	}
	this.calculate_single_price( elem );
	this.calculate_all_price();
};

wc_bof_handler.prototype.map_select_variation = function ( formid, clickelem, variation ) {
	if ( this.formid !== formid ) {
		return;
	}
	var count = clickelem.parent().attr( 'data-count' );
	var qty_elem  = this.form.find( 'td#wc_bof_product_qty_' + count + ' .product_qty' );

	qty_elem.attr( 'data-price', variation.display_price );

	if (typeof(variation.max_qty) != "undefined" ) {
		qty_elem.attr( 'max', variation.max_qty );
	}
	if (typeof(variation.min_qty) != "undefined" ) {
		qty_elem.attr( 'min', variation.min_qty );
	}
	if (typeof(variation.step) != "undefined" ) {
		qty_elem.attr( 'step', variation.step );
	}

	var is_in_stock = typeof(variation.is_in_stock) == "undefined" || variation.is_in_stock == true;
	var is_purchasable = typeof(variation.is_purchasable) == "undefined" || variation.is_purchasable == true;
	var stock_notice = qty_elem.closest('tr').find('.woocommerce-variation-availability');
	if ( !is_in_stock || !is_purchasable ) {
		qty_elem.prop('disabled', true);
		qty_elem.val('');
		if (stock_notice.length) {
			stock_notice.html(variation.availability_html).show();
		}
	} else {
		qty_elem.prop('disabled', false);
		if (stock_notice.length) {
			stock_notice.html('').hide();
		}
	}
	this.calculate_variation_template_price( formid, qty_elem );
};

wc_bof_handler.prototype.reset_variations = function ( formid, clickedElem, Event ) {
	if ( this.formid !== formid ) {
		return;
	}
	var count = clickedElem.parent().parent().parent().parent().attr( 'data-count' );
	this.form.find( 'td#wc_bof_product_price_' + count + ' .amount' ).html( '' ).attr( 'data-fprice', '' );
	var qty = this.form.find( 'td#wc_bof_product_qty_' + count + ' .product_qty' );
	qty.val( '' );
	qty.attr( 'data-fprice', '' );
	qty.attr( 'data-price', '' );
	var stock_notice = qty.closest('tr').find('.woocommerce-variation-availability');
	if (stock_notice.length) {
		stock_notice.html('').hide();
	}
	this.calculate_variation_template_price( formid, qty );
};

wc_bof_handler.prototype.single_add_to_cart_handler = function ( formid, clickelem ) {
	if ( this.formid !== formid ) {
		return;
	}
	var elem = clickelem;
	if ( elem.hasClass( 'processing' ) ) {
		return;
	}
	var wcbofh   = this;
	var rowCount = elem.attr( 'data-rowcount' );
	var data     = this.settings_string;

	var inpt_row = elem.closest( 'tr' );

	var formVal = inpt_row.find( ':input' ).serialize();

	data = data + '&' + formVal;
	data = data + '&action=wcbulkorder_product_single_buy_now';
	elem.addClass( 'processing' );
	inpt_row.css( 'opacity', 0.5 ).addClass( 'processing' );

	jQuery.ajax( {
		url: WCBulkOrder.url,
		method: 'post',
		data: data,
	} ).done( function ( res ) {
		wcbofh.form.find( '.backEndResponse' ).hide().html( res.data ).fadeIn( function () {
			setTimeout( function () {
				wcbofh.form.find( '.wcbulkorderform .backEndResponse' ).fadeOut().html( '' );
			}, 10000 );
		} );
		if( typeof(res.data) != "undefined" && res.data.indexOf('error') != -1){
			var scroll_y = jQuery( "article" ).length ? jQuery("article").offset().top : jQuery(".backEndResponse").offset().top - 10;
			jQuery("html, body").animate({ scrollTop: scroll_y }, "fast");
		}

		inpt_row.css( 'opacity', 1 ).removeClass( 'processing' );

		elem.removeClass( 'processing' );

		inpt_row.each( function () {
			wcbofh.clear_row( jQuery( this ) );
		} );

		jQuery( 'body' ).trigger( 'wc_bof_single_added_to_cart', [ elem, inpt_row, res ] );
	} );
};

wc_bof_handler.prototype.add_to_cart_handler = function ( formid, elem ) {

	if ( this.formid !== formid ) {
		return;
	}

	if ( elem.hasClass( 'processing' ) ) {
		return;
	}

	elem.addClass( 'processing' );
	this.form.addClass( 'processing' );
	elem.css( 'opacity', 0.5 );
	var form_data = this.form.serializeArray();
	var wcbofh    = this;
	jQuery.ajax( {
		url: WCBulkOrder.url,
		data: form_data,
		method: 'post',
	} ).done( function ( res ) {
		wcbofh.form.find( '.wcbof_action_btn_wrap' ).remove();

		if ( typeof( WCBulkOrder.auto_redirect ) != "undefined" && WCBulkOrder.auto_redirect === 'on' ) {
			// only redirect if there are no errors
			if( typeof(res.data) != "undefined" && res.data.indexOf('error') == -1 ){
				if ( WCBulkOrder.action_button === 'checkout' ) {
					window.location.href = WCBulkOrder.checkouturl;
				} else {
					window.location.href = WCBulkOrder.carturl;
				}
			}
		}

		wcbofh.form.find( '.backEndResponse' ).hide().html( res.data ).fadeIn( function () {
			setTimeout( function () {
				wcbofh.form.find( '.backEndResponse' ).fadeOut().html( '' );
			}, 10000 );
		} );

		if( typeof(res.data) != "undefined" && res.data.indexOf('error') != -1){
			var scroll_y = jQuery( "article" ).length ? jQuery("article").offset().top : jQuery(".backEndResponse").offset().top - 10;
			jQuery("html, body").animate({ scrollTop: scroll_y }, "fast");
		}

		elem.css( 'opacity', 1 );
		elem.removeClass( 'processing' );
		wcbofh.form.removeClass( 'processing' );

		wcbofh.form.find( 'tr' ).each( function () {
			wcbofh.clear_row( jQuery( this ) );
		} );
		wcbofh.form.find( '.wcbulkorderalltotal' ).text( '' );

		if ( typeof( WCBulkOrder.action_button ) != "undefined" && WCBulkOrder.action_button === 'checkout' ) {
			var html = '<div class="wcbof_action_btn_wrap"><a href="' + WCBulkOrder.checkouturl + '" class="wcbof_action_btn">' + WCBulkOrder.checkouttext + '</a></div>';
			elem.parent().append( html );
		} else {
			var html = '<div class="wcbof_action_btn_wrap"><a href="' + WCBulkOrder.carturl + '" class="wcbof_action_btn">' + WCBulkOrder.carttext + '</a></div>';
			elem.parent().append( html );
		}

		setTimeout( function () {
			wcbofh.form.find( '.wcbof_action_btn_wrap' ).remove();
		}, 10000 );

		jQuery( 'body' ).trigger( 'wc_bof_added_to_cart', [ elem, wcbofh, res ] );
	} );
};

wc_bof_handler.prototype.clear_row = function ( obj ) {
	var wcbofh = this;

	obj.find( 'select.product_name_search_field' ).each( function () {
		$search_field = jQuery( this );
		var count     = $search_field.parent().attr( 'data-count' );
		if ( count === 'removeHidden' ) {
			// do nothing
		} else {
			if ( jQuery( obj ).closest( '.wcbulkorderform' ).hasClass( 'prepopulated' ) ) {
				// clear selectize option
				selectize = $search_field.selectize();
				selectize[ 0 ].selectize.clearOptions();
			} else {
				$search_field.closest( 'tr' ).remove();
				wcbofh.add_row( wcbofh.formid );
			}
		}
	} );
	if ( jQuery( obj ).closest( '.wcbulkorderform' ).hasClass( 'prepopulated' ) ) {
		// clear qty input
		obj.find( '.wc_bof_product_qty input' ).val( '' );
		// clear variation input
		obj.find( '.wc_bof_variation_name .variations_form' ).trigger('reset_variations');
		if ( jQuery( obj ).closest( '.wcbulkorderform' ).hasClass( 'variation' ) ) {
			obj.find( '.wc_bof_variation_name' ).html( '' );
		}

		// clear price
		obj.find( '.wc_bof_product_price .amount' ).text( '' );
		// hide button
		obj.find( '.add-to-cart-single' ).hide();
	}
	this.calculate_all_price();
};