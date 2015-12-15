/*! stt2extat RES - 2015-10-27
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 *
 * @since 1.1.0
 *
 */
 
( function( $ ) {
    $.fn.stt2extat = function() {
		var el = $( ".stt2extat-container" ),
			ajaxurl = window.stt2extatJs.ajaxurl;
		
		function getReferrer() {
			var referrer, ref;
			
			referrer = {
				init : function() {
					ref = document.referrer,
					ref = ref.replace( /<!--[\s\S]*?(-->|$)/g, "" )
					.replace( /<(script|style)[^>]*>[\s\S]*?(<\/\1>|$)/ig, "" )
					.replace( /<\/?[a-z][\s\S]*?(>|$)/ig, "" )
					.toLowerCase(),
					data = {
						"action": "stt2extat_ref",
						"ref": ref,
						"post_ID": stt2extatJs.post_ID
					};
					
					referrer.update( data );
				},
				update : function( data ) {
					if ( '' == data.ref || ! $.isArray( data.post_ID ) )
						return;
					
					$.post( ajaxurl, data, function( r ) {
						return;
					} );
				}
			}
			referrer.init();
		}
		
		function showTermsList() {
			data = {
				"action": "stt2extat_terms_list",
				"post_ID": parseInt( stt2extatJs.post_ID, 10 ),
				"page": stt2extatJs.single
			};
			
			if ( ! window.stt2extatJs.showList || ! window.stt2extatJs.single ) 
				return;
			
			$.post( ajaxurl, data, function( r ) {
				el.html( r.result );
			}, "json" );
		}
		
		return this.each( function() {
			getReferrer();
			showTermsList();
        } )
	}
}( jQuery ) );