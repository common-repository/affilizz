( function( tinymce ) {
	tinymce.PluginManager.add( 'affilizz-float', function( editor ) {
		function noop () {}

		// Set this here as wp-tinymce.js may be loaded too early.
		var wp = window.wp;

		if ( ! wp || ! wp.mce || ! wp.mce.views ) {
			return { getView: noop };
		}

		// Check if a node is a view or not.
		function isAffilizzFloat( node ) {
			return typeof( editor.dom.getAttrib( node, 'data-wpview-type' ) ) != 'undefined' && editor.dom.getAttrib( node, 'data-wpview-type' ) == 'affilizz-publication';
		}
		
		editor.once( 'init', function() {
			jQuery( '#affilizz-floating-button' ).appendTo( jQuery(tinymce.activeEditor.contentAreaContainer) ).hide(); 
		} );

		editor.on( 'click keyup', function( event ) {
			var node = editor.selection.getNode();
			if ( isAffilizzFloat( node ) || typeof( tinymce.activeEditor.selection.getSel().anchorNode.offsetTop ) == 'undefined' ) {
				jQuery('#affilizz-floating-button').hide();
			} else {
				jQuery('#affilizz-floating-button')
					.animate( {
						'top': ( tinymce.activeEditor.selection.getSel().anchorNode.offsetTop + 20 ) + 'px',
						'opacity': 1
					}, 100 ).show();
			}
		} );

		editor.wp = editor.wp || {};
		editor.wp.getView = noop;
		editor.wp.setViewCursor = noop;

		return {
			getView: noop
		};
	} );
} )( window.tinymce );