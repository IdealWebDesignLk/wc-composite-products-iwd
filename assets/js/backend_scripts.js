( function( $ ) {
"use strict";
	function wccp_getEnhancedSelectFormatString() {
		return {
			'language': {
				errorLoading: function() {
					return wc_enhanced_select_params.i18n_searching;
				},
				inputTooLong: function( args ) {
					var overChars = args.input.length - args.maximum;
					if ( 1 === overChars ) {
						return wc_enhanced_select_params.i18n_input_too_long_1;
					}
					return wc_enhanced_select_params.i18n_input_too_long_n.replace( '%qty%', overChars );
				},
				inputTooShort: function( args ) {
					var remainingChars = args.minimum - args.input.length;
					if ( 1 === remainingChars ) {
						return wc_enhanced_select_params.i18n_input_too_short_1;
					}
					return wc_enhanced_select_params.i18n_input_too_short_n.replace( '%qty%', remainingChars );
				},
				loadingMore: function() {
					return wc_enhanced_select_params.i18n_load_more;
				},
				maximumSelected: function( args ) {
					if ( args.maximum === 1 ) {
						return wc_enhanced_select_params.i18n_selection_too_long_1;
					}
					return wc_enhanced_select_params.i18n_selection_too_long_n.replace( '%qty%', args.maximum );
				},
				noResults: function() {
					return wc_enhanced_select_params.i18n_no_matches;
				},
				searching: function() {
					return wc_enhanced_select_params.i18n_searching;
				}
			}
		};
	}
function wccp_select2() {
	jQuery( ':input.wc-product-search' ).filter( ':not(.enhanced)' ).each( function() {
		var select2_args = {
			allowClear:  jQuery( this ).data( 'allow_clear' ) ? true : false,
			placeholder: jQuery( this ).data( 'placeholder' ),
			minimumInputLength: jQuery( this ).data( 'minimum_input_length' ) ? jQuery( this ).data( 'minimum_input_length' ) : '3',
			escapeMarkup: function( m ) {
				return m;
			},
			ajax: {
				url      : wc_enhanced_select_params.ajax_url,
				dataType : 'json',
				delay    : 250,
				data     : function( params ) {
					return {
						term          : params.term,
						action        : jQuery( this ).data( 'action' ) || 'woocommerce_json_search_products_and_variations',
						security      : wc_enhanced_select_params.search_products_nonce,
						exclude       : jQuery( this ).data( 'exclude' ),
						exclude_type  : jQuery( this ).data( 'exclude_type' ),
						include       : jQuery( this ).data( 'include' ),
						limit         : jQuery( this ).data( 'limit' ),
						display_stock : jQuery( this ).data( 'display_stock' )
					};
				},
				processResults: function( data ) {
					var terms = [];
					if ( data ) {
						jQuery.each( data, function( id, text ) {
							terms.push( { id: id, text: text } );
						});
					}
					return {
						results: terms
					};
				},
				cache: true
			}
		};

		select2_args = jQuery.extend( select2_args, wccp_getEnhancedSelectFormatString() );

		jQuery( this ).selectWoo( select2_args ).addClass( 'enhanced' );
	});
}
	jQuery( document ).ready( function() {
		jQuery('.product_data_tabs .general_tab').addClass('show_if_wccp_composite_product').show();
        jQuery('#general_product_data .pricing').addClass('show_if_wccp_composite_product').show();
        jQuery('.product_data_tabs .general_tab').addClass('show_if_simple').show();
		if( jQuery('.wccp_color_picker ').length > 0 ) {
            jQuery('.wccp_color_picker ').wpColorPicker();
		}
		jQuery( document ).on('click', '.wccp_add_box', function( e ) {
			e.preventDefault();
			jQuery('.wccp_composite_boxes:last .wc-enhanced-select, .wccp_composite_boxes:last .wc-product-search').selectWoo('destroy');
			jQuery('.wccp_composite_boxes:last .wc-product-search').removeClass('enhanced');
			var clone_div=jQuery('.wccp_composite_boxes:last').clone();
			jQuery('.wccp_box_section').append( clone_div );
			jQuery('.wccp_composite_boxes .wc-enhanced-select').selectWoo();
			wccp_select2();
			var i = 0, j = 0;
			jQuery('select.wccp_box_products').each( function() {
				jQuery(this).attr('name', 'wccp_box_products['+i+'][]');
                i++;
            } );
            jQuery('select.wccp_select_categories').each( function() {
                jQuery(this).attr('name', 'wccp_box_categories['+j+'][]');
                j++;
			} );
			var no_of_boxes = jQuery('.wccp_box_serial').length;
			jQuery('.wccp_box_serial:last').html(wccp_composite_steps.boxes_step_label + no_of_boxes);
		} );
		jQuery( document ).on('click', '.remove_variation', function( e ) {
			e.preventDefault();
			var status = confirm(wccp_composite_steps.confirmation_alert);
			if( status ) {
				if( jQuery('.wccp_composite_boxes').length > 1 ) {
					jQuery(this).closest('.wccp_composite_boxes').remove();
				} else {
					alert(wccp_composite_steps.delete_box);
				}
			}
			var i = 0, j = 0;
			jQuery('select.wccp_box_products').each( function() {
				jQuery(this).attr('name', 'wccp_box_products['+i+'][]');
                i++;
            } );
            jQuery('select.wccp_select_categories').each( function() {
                jQuery(this).attr('name', 'wccp_box_categories['+j+'][]');
                j++;
			} );
		} );
	} );
} )( jQuery );