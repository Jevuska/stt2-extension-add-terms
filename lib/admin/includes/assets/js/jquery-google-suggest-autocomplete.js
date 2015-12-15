/**@license
This file uses Google Suggest for jQuery plugin by Haochi Chen ( http://ihaochi.com )
 ** add functions as needs (split and extractLast)
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 *
 * @since 1.0
 *
 */
(function($) {
    $.fn.googleSuggest = function(opts) {
        opts = $.extend({service: 'web',secure: false}, opts);
        
        var services = {
            youtube: {client: 'youtube',ds: 'yt'},
            books: {client: 'books',ds: 'bo'},
            products: {client: 'products-cc',ds: 'sh'},
            news: {client: 'news-cc',ds: 'n'},
            images: {client: 'img',ds: 'i'},
            web: {client: 'hp',ds: ''},
            recipes: {client: 'hp',ds: 'r'}
        }, service = services[opts.service], span = $('<span>');

        //add function
        split = function(str) {
            return str.split(/,\s*/);
        };
        
        extractLast = function( str ) {
            return split(str).pop();
        };
        
        opts.source = function( request, response ) {
            var api = 'http' + (opts.secure ? 's' : '') + '://clients1.google.com/complete/search', 
            jqxhr = $.ajax({
                url: api,
                dataType: 'jsonp',
                data: {
                    q: extractLast( request.term ),
                    nolabels: 't',
                    client: service.client,
                    ds: service.ds
                }
            })
            .done( function( data ) {
                response( $.map(data[1], function( item ) {
                    return { value: span.html( item[0] ).text() };
                }));
            });
        };
        return this.each( function() {
            $( this ).autocomplete( opts );
        } );
    }
} ( jQuery ) );
