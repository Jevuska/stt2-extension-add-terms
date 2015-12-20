/*! stt2extat - 2015-10-27http://www.jevuska.com/2015/06/28/injeksi-manual-keyword-add-onsextension-plugin-seo-searchterms-tagging-2/
 * Thanks to Charlie MERLAND for Custom AJAX List Table
 * https://github.com/CaerCam/Custom-AJAX-List-Table-Example
 *
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 *
 * @since 1.0
 *
 * Fixes function related to compatibility
 *
 * @since 1.1.0
 *
 */
 
( function( $ ) {
    $.fn.stt2extat = function() {
		
		var comma = tagsBoxL10n.tagDelimiter,
			nonce = "object" == typeof heartbeatSettings ? heartbeatSettings.nonce : "",
			idBox = $( "#stt2extat-manual" ),
			idTable = $( "#stt2extat-table-stats" ),
			idForm = "stt2extat-form",
			idMsg = "message",
			cDismissn = "notice-dismiss",
            cDismisb = "error notice is-dismissible",
            cDismisbUpd = "notice is-dismissible",
            cSRT = "screen-reader-text",
            cErrMsg = "error-message",
            cMore = "readmore",
            cdArrowUp = "dashicons dashicons-arrow-up",
            cdTag = "dashicons dashicons-tag",
            cdNo = "dashicons dashicons-no",
            cBtn = "button closebtn",
            cBtnAdd = "button termadd",
            cSpin = "spinner is-active",
            dvnDismis = $( "<div>", {
                "id": idMsg,
                "class": cDismisb
            } ),
            spSRT = $( "<span>", {
                "class": cSRT,
                "text": commonL10n.dismiss
            } ),
            spRmvIrrlvnt = $( "<span>", {
                "class": cdNo,
                "title": stt2extatL10n[2]
            } ),
            btnDismiss = $( "<button>", {
                "type": "button",
                "class": cDismissn,
                "html": spSRT
            } ),
            btnRmv = $( "<button>", {
                "type": "button",
                "class": cBtn,
                "text": stt2extatL10n[14]
            } ),
            btnAdd = $( "<button>", {
                "type": "button",
                "class": cBtnAdd,
                "text": stt2extatL10n[15]
            } ),
            spMore = $( "<li>", {
                "class": cMore,
                "text": stt2extatL10n[13][1],
				"title": stt2extatL10n[13][0]
            } ),
            spL105 = $( "<span>", {
                "text": stt2extatL10n[5]
            } ),
            spL107 = $( "<span>", {
                "text": stt2extatL10n[7]
            } ),
            pL108 = $( "<p>", {
                "text": stt2extatL10n[8]
            } ),
            pL109 = $( "<p>", {
                "text": stt2extatL10n[9]
            } ),
            pL1010 = $( "<p>", {
                "text": wpAjax.noPerm
            } ),
            pL1011 = $( "<p>", {
                "text": stt2extatL10n[11]
            } ),
			pL1012 = $( "<p>", {
                "text": stt2extatL10n[12]
            } ),
			pL1025 = $( "<p>", {
                "text": stt2extatL10n[25]
            } ),
			pL1026 = $( "<p>", {
                "text": stt2extatL10n[26]
            } ),
			pL1027 = $( "<p>", {
                "text": wpAjax.broken
            } ),
			pL104 = $( "<p>", {
                "text": stt2extatL10n[4]
            } ),
            iArrowUp = $( "<span>", {
                "class": cdArrowUp
            } ),
            stgText = $( "<strong>", {
                "text": stt2extatL10n[6]
            } ),
            parag = $( "<p>", {
                "html": stgText
            } ),
            spinner = $( "<span>", {
                "class": cSpin
            } ),
            msgSrt = $( "<div>", {
                "id": "loading",
                "text": stt2extatL10n[16]
            } ),
			emptyST = $( "<span>", {
				"class": cdTag
			} ).add( $( "<span>", {
				"text": stt2extatL10n[3]
			} ) );
			emptyList = $( "<span>", {
				"class": cErrMsg,
				"html": emptyST
			} ),
			maxchar = stt2extatL10n[21],
            lclStrgMaxChar = localStorage[ "maxchar" ],
			searchexcerpt = stt2extatL10n[24],
            lclStrgSearchExcerpt = localStorage[ "searchexcerpt" ],
            msgSuccess = $( "<div>", {
                "id": "loading",
                "style": "background-color:#0091cd;color:#fff",
                "text": stt2extatL10n[18]
            } ),
            success = $( "<div>", {
                "id": "loading",
                "style": "background-color:#0091cd;color:#fff",
                "text": stt2extatL10n[22]
            } ),
            msgLoad = $( "<div>", {
                "id": "loading",
                "text": stt2extatL10n[23]
            } ),
			textarea = document.createElement( "textarea" ),
			stopwords = $( "textarea#stopwords" ).text(),
			thehint = $( ".hint-box" ).clone();
			
		if ( lclStrgMaxChar )
			localStorage.removeItem( "maxchar" );
		
		if ( lclStrgSearchExcerpt )
			localStorage.removeItem( "searchexcerpt" );
		
		imgLoader  = new Image();
		imgLoader.src = thickboxL10n.loadingAnimation;
		
		function loadForm() {
			var data;
			$( idBox ).html( $( "<div>", {
				"id" : idForm,
				"html" : $( "<img>", {
					"src" : imgLoader.src,
					"width" : 208
				} )
			} ) );
			data = {
				"action" : "stt2extat_action",
				"_wpnonce" : nonce
			};
			$.post( ajaxurl, data, function( r ) {
				noticeAuthForm( r );
				$( "#" + idForm ).html( r );
				prepareForm();
			} );
		}
		
		function loadTable() {
			var list, deleteTerm;
			list = {
				init: function() {
					var timer;
					var delay = 500;
					$( ".tablenav-pages a, .manage-column.sortable a, .manage-column.sorted a" ).on( "click", function( e ) {
						e.preventDefault();
						var query = this.search.substring( 1 );
						
						var data = {
							paged: list.__query( query, "paged" ) || "1",
							order: list.__query( query, "order" ) || "asc",
							orderby: list.__query( query, "orderby" ) || "id"
						};
						
						list.update( data );
					});
					$( "input[name=paged]" ).on( "keyup", function( e ) {
						if ( 13 == e.which )
							e.preventDefault();
						var data = {
							paged: parseInt( $( "input[name=paged]" ).val(), 10 ) || "1",
							order: $( "input[name=order]" ).val() || "asc",
							orderby: $( "input[name=orderby]" ).val() || "id"
						};
						window.clearTimeout( timer );
						timer = window.setTimeout( function() {
							list.update( data );
						}, delay);
					} );
				},
				update: function( data ) {
					data = $.extend ( {
						"action" : "stt2extat_ajax_table",
						"table" : list_args,
						"_wpnonce" : $( "#stats input#_wpnonce" ).val()
					}, data );
					$.post( ajaxurl, data, function( response ) {
						var response = $.parseJSON( response );
						
						if ( response.rows.length )
							$( "#the-list" ).html( response.rows );
						
						if ( $(response.column_headers).length ) {
							var thtf = $( "thead tr, tfoot tr" );
							if ( "asc" == response.order )
								thtf.html(
									response.column_headers.replace( /admin-ajax.php/g, "options-general.php" ).replace( /order=asc/g, "order=desc&amp;page=stt2extat" )
								);
							else
								thtf.html(
									response.column_headers.replace( /admin-ajax.php/g, "options-general.php" ).replace( /order=desc/g, "order=asc&page=stt2extat" )
								);
						}
						
						if ( response.pagination.bottom.length ) {
							var nTop = $( ".tablenav.top .tablenav-pages" ),	
								pTop = $( response.pagination.top ).html().replace( /admin-ajax.php/g, "options-general.php" );
							
							if ( "id" != response.orderby )
								nTop.html(
									pTop.replace(/paged=/g, "page=stt2extat&amp;orderby=" + response.orderby + "&amp;order="+ response.order + "&amp;paged=" )
								);
							else
								nTop.html( pTop );
						}
						
						if ( response.pagination.top.length ) {
							var nBot = $( ".tablenav.bottom .tablenav-pages" ),
								pBot = $( response.pagination.bottom ).html().replace( /admin-ajax.php/g, "options-general.php" );
							if ( "id" != response.orderby )
								nBot.html(
									pBot.replace(/paged=/g, "page=stt2extat&amp;orderby=" + response.orderby + "&amp;order="+ response.order + "&amp;paged=" )
								);
							else
								nBot.html( pBot );
						}
						list.init();
					} );
				},
				__query: function( query, variable ) {
					var vars = query.split( "&" );
					for ( var i = 0; i < vars.length; i++ ) {
						var pair = vars[ i ].split( "=" );
						if ( pair[0] == variable )
							return pair[1];
					}
					return false;
				},
			}
			list.init();
			
			deleteTerm = {
				init : function () {
					$( "#the-list" ).on( "click", ".delete-term", function( e ) {
						e.preventDefault;
						$( "#message" ).remove();
						var t = $( this ), tr = t.parents( "tr" ), r = true, data;
						if ( "undefined" != showNotice )
							r = showNotice.warn();
						if ( r ) {
							data = t.attr( "href" ).replace( /[^?]*\?/, "" ).replace(/action=delete/, "action=stt2extat_delete_term" );
							$.post( ajaxurl, data, function( r ) {
								$( "#ajax-response" ).empty().append( r );
								clickableDismissNotice();
								tr.fadeOut( "normal", function() {
									tr.remove();
								} );
								tr.children().css( "backgroundColor", "" );
							} );
							tr.children().css( "backgroundColor", "#f33" );
						}
						return false;
					} );
				}
			}
			deleteTerm.init();
		}
		
		function check_relevant_terms()
		{
			var el = $( ".check_relevant_terms" );
			el.change( function() {
				$( "#message" ).remove();
				if ( $( this ).is( ":checked" ) )
					val = 1;
				else
					val = 0;
				
				var data = {
					"action": "stt2extat_check_relevant_terms",
					"val": val,
					"_wpnonce": nonce
				}
				$.post( ajaxurl, data, function( r ) {
					$( "#message" ).remove();
					if ( 0 == r || 1 == r ) {
						el.val( r );
					} else {
						$( "#delete .inside" ).prepend( r );
						clickableDismissNotice();
					}
					return false;
				} )
			} )
		}
		
		function migrate_stt2_terms() {
			$( "#migrate_stt2_terms" ).click( function( e ) {
				var data;
				e.preventDefault();
				$( "#message" ).remove();
				data = {
					"action" : "stt2extat_migrate_stt2_terms",
					"_wpnonce" : nonce
				};
				$( this ).siblings( ".spinner" ).addClass( "is-active" );
				$.post( ajaxurl, data, function( r ) {
					$( ".spinner" ).removeClass( "is-active" );
					if (  -1 != r.indexOf( "page=stt2extat" ) ) {
						window.location.replace( r );
						return false;
					}
					$( "#delete .inside" ).prepend( r );
					clickableDismissNotice();
				} );
				return false;
			} )
		}
		
		function deleteAllTerms() {
			$( "#delete_all_searchterms" ).click( function( e ) {
				var r, data;
				e.preventDefault();
				$( "#message" ).remove();
				r = true;
				if ( "undefined" != showNotice )
					r = showNotice.warn();
				if ( r ) {
					data = {
						"action" : "stt2extat_delete_all_terms",
						"_wpnonce" : nonce
					};
					$( this ).siblings( ".spinner" ).addClass( "is-active" );
					$.post( ajaxurl, data, function( r ) {
						$( ".spinner" ).removeClass( "is-active" );
						if (  -1 != r.indexOf( "page=stt2extat" ) ) {
							window.location.replace( r );
							return false;
						}
						$( "#delete .inside" ).prepend( r );
						clickableDismissNotice();
					} );
				}
				return false;
			} )
		}
		
		function uaField() {
			var btnRemove = $( "<span>",{ "class": "tagchecklist main-ua", "html": $( "<span>",{ "html": $( "<a>", {
				"class" : "ntdelbutton",
				"text": "X"
			} ) } ) } );
			
			$( ".useragent" ).click( function( e ) {
				e.preventDefault();
				$( "span.main-ua" ).remove();
				$( this ).parents( "label" ).append( $( btnRemove ).on( "click", function( e ) {
					$( this ).parents( "label" ).remove();
					return false;
				} ) );
				return false;
			} ).on( "dblclick", function( e ) {
				e.preventDefault();
				$( "span.main-ua" ).remove();
				return false;
			} );
			uaAdditionalField( btnRemove );
			uaToggle();
		}
		
		function uaAdditionalField( btnRemove ) {
			$( "#ua-bottom .more" ).on( "click", function( e ) {
				e.preventDefault();
				
				var input, n = $( "input.useragent" ).length / 2,
					uaTopK = $( "#ua-top-k" ).clone(),
					uaTopV = $( "#ua-top-v" ).clone();
				uaTopK = $( uaTopK ).prop( "readonly", false ).attr( { "class":"useragent regular-text", "id":"useragent-" + ( n + 1 ), "name" : "stt2extat_settings[useragent][k][]" } ).val( "" );
				
				uaTopV = $( uaTopV ).prop( "readonly", false ).attr( { "class":"useragent medium-text","name" : "stt2extat_settings[useragent][v][]" } ).val( "" );
				
				input = $( "<label>", {
					"for" : "useragent-" + ( n + 1 ),
					"html": $( uaTopK ).add( $( uaTopV ) )
				} );
				
				$( "#ua-list label" ).filter( function( index ) {
					return index > 5;
				} ).show();
				$( "#ua-additional" ).append( input );
				$( ".useragent" ).click( function( e ){
					e.preventDefault();
					$( this ).parents( "label" ).append( $( btnRemove ).on( "click", function( e ) {
						e.preventDefault();
						$( this ).parents( "label" ).remove();
					} ) );
				} ).on( "dblclick", function( e ) {
					e.preventDefault();
					$( "span.main-ua" ).remove();
				} );
				return false;
			} );
		}
		
		function uaToggle() {
			$( "#ua-list label" ).hide().filter( function( index ) {
				return index < 6;
			} ).show();
			
			$( "#stt2extat .dashicons-menu" ).click( function( e ) {
				e.preventDefault();
				$( "#ua-list label" ).filter( function( index ) {
					return index > 5;
				} ).toggle().not( ":visible" );
				return false;
			} )
		}
		
        function prepareForm() {
			var manage_box = $( "#stt2extat-manage" ),
				hints,
				loadManageField;
				
			loadManageField = {
				element: null,
				toggles: null,
				init : function() {
					this.element = $( "#fullpost" );
					this.toggles = $( "#fullpost-toggle a" );
					this.toggles.click( this.toggleEvent );
						
					var data = {
						"action": "stt2extat_template_form",
						"_wpnonce" : nonce
					};
					loadManageField.prepare( data );
				},
				prepare : function( data ) {
					$.post( ajaxurl, data, function ( r ) {
						noticeAuthForm( r );
						manage_box.html( r );
						searchPost();
						insertTerm();
						return false;
					} );
					loadManageField.ready();
				},
				ready : function() {
					$( "#fullpost-toggle" ).css( {
						"display": "none"
					} );
					$( ".btn-key").attr( "data-value", "" );
					$( ".btnadd, #keylist, #msgb" ).html( "" );
					$( "#prepare-key" ).hide();
					$( "#thehint" ).html( thehint );
					$( "#thehint ol.hint" ).addClass( "hints" );
					hints = $( "#thehint" ).html();
					$( "#stt2extat-excerpt" ).html( hints );
					$( "ol.hints li" ).hide().filter( ":lt(2)" ).show();
					appendMore( "ol.hints" );
				},
				toggleEvent: function ( e ) {
					var a = $( this.href.replace( /.+#/, "#" ) );
					e.preventDefault();
						
					if ( ! a.length )
						return;
						
					if ( a.is( ":visible") )
						loadManageField.close( a, $( this ) );
					else
						loadManageField.open( a, $( this ) )
				},
				open: function ( a, b ) {
					a.parent().show();
					a.slideDown( "fast", function () {
						a.focus();
						b.addClass( "fullpost-active" ).attr( "aria-expanded", true )
					} );
				},
				close: function ( a, b ) {
					a.slideUp( "fast", function () {
						b.removeClass( "fullpost-active" ).attr( "aria-expanded", false );
						a.hide()
					} );
				}
			}
			loadManageField.init();
		}
		
		function searchPost() {
			var el = $( "#stt2extat-manual input#title" ), q, data, text, content, link, fp = $( "#fullpost" ), fpL = $( "#fullpost-toggle" );
			el.autocomplete( {
				minLength: 1,
				delay: 450,
				source: function ( event, ui ) {
					var q = sanitizeText( el.val() ),
						data = {
							"action": "stt2extat_search_post",
							"_wpnonce" : nonce,
							"query": q
						};
						
					if ( "" == q ) {
						el.removeClass( "ui-autocomplete-loading" );
						return false;
					}
					
					$.post( ajaxurl, data, function ( r ) {
						$( "#message" ).remove();
						if ( "-1" == r )
							noticeSearchPanel( pL1010 );
						else if ( "-2" == r )
							noticeSearchPanel( pL1027 );
						else
							ui( r );
						el.removeClass( "ui-autocomplete-loading" );
						return false;
					}, "json" )
				},
				select: function ( event, ui ) {
						text = ui.item.label,
						content = ui.item.content,
						link = ui.item.value;
					$( "#id-field" ).val( ui.item.id );
					$( "input#btninsert" ).prop( "disabled", false );
					$( "#stt2extat-excerpt" ).html( $( btnDismiss ).add( $( parag ) ).add( "<p>", {
						"html": ui.item.excerpt
					} ) );
					fpL.css( {
						"display": "block"
                    } );
					fp.html( $( "<hr>" ).add($( "<h3>", {
						"text": text
					} ) ).add( $( "<p>", {
						"html": $( "<a>", {
							"href": link,
							"target": "_blank",
							"text": link,
							"class": "post-permalink"
							} )
					} ) ).add( $( "<div>", {
						"html": $( "<p>", {
							"html": content.replace( /(\n)+/g, "</p>" )
						} )
					} ) ) );
					el.removeClass( "ui-autocomplete-loading" );
					closePreview();
					getTermsListPost();
				},
				response: function ( event, ui ) {
					$( "#message" ).remove();
					if ( 0 == ui.content.length )
						noticeSearchPanel( pL1012 );
					$( ".loader" ).empty();
					clickableDismissNotice()
				}
			} ).click( "input", function ( e ) {
				e.preventDefault();
				el.autocomplete( "search" );
				el.removeClass( "ui-autocomplete-loading" );
				fpL.hide();
				if ( fp.is( ":visible" ) ) {
					fpL.removeClass( "fullpost-active" ).attr( "aria-expanded", false );
					fp.html( "" ).hide()
				}
				clearInput();
				$( "#stt2extat-excerpt .notice-dismiss" ).click();
				return false
			} );
			termSelection( fp );
		}
		
		function insertTerm() {
			var t = "";
			$( "input#btninsert" ).one( "click", function ( e ) {
				e.preventDefault();
				$( ".loader" ).html( "" );
				clearTimeout( t );
				t = setTimeout( function () {
					insertTermIntoPostMeta();
					disableEnterKey( e );
				}, 450 );
				return false;
			} )
		}
		
		function insertTermIntoPostMeta() {
            var postid = $( "#id-field" ).val(),
				ignore = $( "#notmatch" ).val(),
                terms = $( "textarea#insertterms" ).val().replace( /\r\n|\r|\n/g, comma ),
				arrayTerms = array_unique_noempty( queryToArray( terms ) ),
				result = filterArray( arrayTerms ),
				terms = result.terms,
				data;
				
			var terms = terms.join( comma );
            $( "#search-panel #message" ).remove();
            $( ".loader" ).html( spinner.fadeIn( 400 ) );
            if ( 0 == postid.length ) {
                spinner.fadeOut( 400, function () {
					noticeSearchPanel( pL108 );
                } );
            } else if ( 0 == $.trim( terms ).length ) {
                spinner.fadeOut( 400, function () {
					noticeSearchPanel( pL109 );
                } );
            } else {
				data = {
					"action": "stt2extat_insert",
					"terms": terms,
					"postid": postid,
					"ignore": ignore,
					"_wpnonce" : nonce
				};
				$.post( ajaxurl, data, function ( r ) {
					if ( "1" == r || "-1" == r ) {
						noticeSearchPanel( pL1010 );
                    } else if ( "2" == r ) {
						noticeSearchPanel( pL1025 );
                    } else {
                        $( ".loader" ).html( spinner.fadeOut( 400, function () {
                            $( "#search-panel" ).append( r ).fadeIn(100);
                            shortlink();
                            getTermsListPost();
                            clickableDismissNotice()
                        } ) );
                    }
                    $( "textarea#insertterms" ).val( "" ).removeAttr( "style" ).attr( "aria-expanded", "false" );
                    $( "#badterms,.loader" ).html( "" );
				} )
            }
            insertTerm();
            return false
        }
		
        function getTermsListPost() {
			
			var post_ID = $( "input#id-field" ).val(),
				termList;
				
			termList = {
				init : function() {
					if ( "" != post_ID ) {
						$( "#searchtermpost" ).html( spinner );
						data = {
							"action": "stt2extat_terms_list_post",
							"post_ID": post_ID,
							"_wpnonce": nonce,
						};
						termList.postData( data );
					}
				},
				postData : function( data ) {
					$.post( ajaxurl, data, function ( r ) {
						noticeAuthForm( r );
						$( "#searchtermpost .spinner" ).fadeOut( 400, function () {
							$( "#searchtermpost" ).html( r ).fadeIn( 200 );
							listAllTerms( post_ID );
							hitsCount( post_ID );
							getSearchTermField();
							resizeTextArea()
						} )
					} );
					
					termList.deleteTerms();
				},
				deleteTerms : function() {
					$( "#searchtermpost" ).on( "click", ".ntdelbutton", function( e ) {
						e.preventDefault();
						immediatePropStopped( e, $( this ) );
						e.stopImmediatePropagation();
					} )
				}
			}
			termList.init();
        }

        function listAllTerms( post_ID ) {
			var data = {
				"action": "stt2extat_list_all_terms",
				"_wpnonce" : nonce,
				"post_ID": post_ID
			};
			
			$( ".alltag" ).toggle( function () {
                $( "#msgb" ).html( $( msgLoad ).show() );
                $.post( ajaxurl, data, function ( r ) {
					noticeAuthForm( r );
                    $( "#msgb" ).html( "" );
					if ( "" == r ) {
						$( ".alltag" ).remove();
						return false;
					}
                    $( ".alltag" ).before( r ).removeClass( "dashicons-plus-alt" ).addClass( "dashicons-minus" );
                    hitsCount( post_ID )
                } )
            }, function () {
                $( ".stplus" ).remove();
                $( this ).removeClass( "dashicons-minus" ).addClass( "dashicons-plus-alt" )
            } )
        }
		
		function immediatePropStopped( event, t ) {
			if ( event.isImmediatePropagationStopped() ) {
			}else{
				var span = t.parent( "span" ),
					term_id = t.siblings( "i.termlist" ).attr( "data-id" ),
					post_id = $( "input#id-field" ).val();
				$( "#message" ).remove();
				data = {
					"action": "stt2extat_delete_term",
					"term_ID": term_id,
					"post_ID": post_id,
					"_wpnonce" : nonce
				};
				span.css( "text-decoration", "line-through" );
				$.post( ajaxurl, data, function( r ) {
					span.fadeOut( "normal", function() { span.remove() } );
					$( "#message" ).remove();
					$( "#search-panel" ).append( $( r ) );
					clickableDismissNotice();
					$( ".wp-list-table input[value='" + term_id + "']" ).parents( "tr" ).fadeOut( "normal", function() {
						$( this ).remove();
					} );
					var list = $( ".tagchecklist" ).text();
					if ( 1 > list.length ) {
						$( "#searchtermpost" ).html( emptyList );
						$( ".alltag" ).remove();
					};
				} );
				return false;
			}
		}
		
        function hitsCount( post_ID ) {
            var t = "";
            $( "i.termlist,i.termcnt" ).off( "click" );
            $( "i.termlist" ).on( "click", function ( e ) {
                e.preventDefault();
                var term_id = $( this ).attr( "data-id" ),
					termcnt = jQuery( $( this ).parents( "span" ).children( "i.termcnt" ) ),
                    cnt = Number( $( this ).parents( "span" ).children( "i.termcnt" ).html() ),
                    hits = cnt + 1;
                termcnt.html( hits );
                t = setTimeout( function ( e ) {
                    updateHitsCount( term_id, post_ID, hits );
                }, 2000 );
                return false
            } );
			
            $( "i.termcnt" ).on( "click", function ( e ) {
                e.preventDefault();
                var term_id = $( this ).siblings( "i" ).attr( "data-id" ),
					cnt     = Number( $( this ).text() );
					
				if ( 1 == cnt ) {
					$( "#message" ).remove();
					return false;
				} else if ( 2 == cnt ) {
                    $( this ).text(1);
                    var hits = Number( $( this ).text() )
                } else {
                    $( this ).text( Number( $( this ).text() ) - 1 );
                    var hits = Number( $( this ).text() )
                }
                t = setTimeout( function ( e ) {
                    updateHitsCount( term_id, post_ID, hits );
                    e.preventDefault()
                }, 2000 );
				$( "#message" ).remove();
                return false
            } );

            function updateHitsCount( term_id, post_ID, hits ) {
				var data = {
					"action": "stt2extat_update_count",
					"term_ID": term_id,
					"post_ID": post_ID,
					"count": hits,
					"_wpnonce" : nonce
				};
				$.post( ajaxurl, data, function ( r ) {
					$( "#message" ).remove();
					if ( "-1" == r ) {
						msg = pL1010;
						noticeSearchPanel( msg );
						getTermsListPost();
                    }
					return false;
				} )
            }
        }

        function getSearchTermField() {
            var b = $( "#searchdiv" ),
                jqxhr = $.ajax({
                    url: ajaxurl,
                    beforeSend: function () {
                        b.html( $( "<img>", {
                            "src": imgLoader.src,
                            "width": 208
                        } ) )
                    },
                    method: "POST",
                    data: {
                        "action": "stt2extat_search_field",
                        "_wpnonce" : nonce
                    }
                } ).done( function ( r ) {
					noticeAuthForm( r );
                    b.html( r ).fadeIn( 400 );
                    populateTerm();
                    ignoreIrrelevantNotice( "gsuggest" );
                    ignoreIrrelevantNotice( "notmatch" )
                } )
        }

        function populateTerm() {
            $( "input#wp-link-search" ).on( "keydown", function ( e ) {
                $( ".btnadd, #message, #badterms, #keylist, #msgb" ).html( "" );
                $( "#message" ).remove();
                $( "#notmatchdata" ).val("");
                $( ".btn-key" ).attr( "data-value", "" );
                $( "#prepare-key" ).hide();
                var a = extractLastTerm( this.value ),
                    code = e.keyCode || e.which;
                if ( 13 == code ) {
                    e.preventDefault();
                    return false
                } else {
                    if ( $( "input#gsuggest" ).is( ":checked" ) ) {
                        googleSuggestAutocomplete()
                    } else {
                        $( this ).googleSuggest({
                            disabled: true
                        } )
                    }
                    if ( 188 == code && 3 < $.trim( a ).length ) {
                        clearTimeout( $( this ).data( "timeout" ) );
                        $( this ).data( "timeout", setTimeout( function () {
                            prepareTerms()
                        }, 1000 ) )
                    }
                }
            } )
        }

        function ignoreIrrelevantNotice( g ) {
            var s = $( "input#" + g ),
                gStore = localStorage[ g ],
                gCheck = localStorage[ g + "check" ];
            if ( "check" == gCheck ) {
                s.prop( "checked", true );
                s.val(1)
            } else {
                s.prop( "checked", false );
                s.val( "" )
            }
            $( "input#" + g ).change( function () {
                if ( $( this ).is( ":checked" ) ) {
                    if ( ! gStore ) {
                        $( ".wrap" ).find( "input." + g ).trigger( "click" );
                        localStorage[ g ] = "yes"
                    }
                    localStorage[ g + "check" ] = "check";
                    $( this ).val(1)
                } else {
                    localStorage[ g + "check" ] = "";
                    $( this ).val( "" )
                }
            } )
        }
		
		
		
        function googleSuggestAutocomplete() {
            $( "input#wp-link-search" ).on( "keydown", function ( a ) {
                if ( a.keyCode == $.ui.keyCode.TAB && $( this ).data( "ui-autocomplete" ).menu.active) {
                    a.preventDefault()
                }
            } ).googleSuggest( {
                disabled: false,
                search: function () {
                    var a = extractLastTerm( this.value );
                    if ( 4 > a.length )
                        return false;
                },
                focus: function () {
                    return false
                },
                select: function ( a, b ) {
                    var c = splitTerms( this.value );
                    c.pop();
                    c.push( b.item.value );
                    c.push( "" );
                    this.value = c.join( "," );
                    clearTimeout( $( this ).data( "timeout" ) );
                    $( this ).data( "timeout", setTimeout( function () {
                        prepareTerms()
                    }, 1000 ) );
                    return false
                }
            } )
        }

        function prepareTerms() {
            var a = $( "input#wp-link-search" ).val(),
                query = $.trim( a ),
                query = query.replace( /,\s*$/, "" ),
                terms = query.split( "," ),
                term  = terms.pop();
				
            if ( "" == term )
                var term = query;
			
            if ( 3 < term.length )
                searchRelevantPost( term );
        }
		
        function searchRelevantPost( b ) {
			
            var notice, href, c = $( "#notmatch" ).val(),
                id = $( "input#id-field" ).val(),
				data = {
					"action": "stt2extat_search_relevant",
					"s": sanitizeText( b ),
					"_wpnonce" : nonce,
					"post_ID": id,
					"ignore": c
				};
			$( "#message" ).remove();
            $( ".btnadd" ).html( "" );
            $( "#wp-link .link-search-wrapper .spinner" ).addClass( "is-active" );
			
            $.post( ajaxurl, data, function ( r ) {
				$( ".spinner" ).removeClass( "is-active" );
				
				if ( r == undefined || r == null || 0 == r.length ) {
					noticeSearchPanel( pL1026 );
					return false;
				}
				
				notice = $( "<p>", {
					"text": r.id.errors.notice
				} );
				
				href = $( "<a>", {
					"href": r.data.link,
					"text": r.data.title,
				} );
				
				switch ( r.what ) { 
					case 'nonce': 
						c = false;
						noticeSearchPanel( pL1010 );
						break;
						
					case 'disallow':
					case 'stopwords': 
						c = false;
						noticeSearchPanel( notice );
						break;
						
					case 'exist':
					case 'existirrelevant': 
						noticeSearchPanel( $( notice ).add( $( href ) ) );
						break;
						
					case 'relevant': 
						$( ".btnadd" ).html( $( btnAdd ) );
						$( "#stt2extat-excerpt" ).html(
							$( btnDismiss ).add( $( parag ) ).add( "<span>", { "html": r.data.excerpt } )
						);
						closePreview();
						break;
						
					case 'irrelevant': 
						noticeSearchPanel( notice );
						break;
						
					default:
				}
				
				if ( c || 'relevant' == r.what ) {
					 $( ".btnadd" ).html( $( btnAdd ) );
                     insertTermsIntoTextarea();
				} else {
					$( ".btnadd" ).html( $( btnRmv ) );
					removeTerm();
				}
				
            }, "json" );
			return false;
        }

        function closePreview() {
			var hints = $( "#thehint" ).html();
			$( "#stt2extat-excerpt .notice-dismiss" ).on( "click", function ( e ) {
				e.preventDefault();
				$( "#stt2extat-excerpt" ).html( hints );
				$( "ol.hints li" ).hide().filter( ":lt(2)" ).show();
				$( "ol.hints li.readmore" ).remove();
				appendMore( "ol.hints" );
				return false
			} )
		}
		
        function removeTerm() {
            var b = $( "input#wp-link-search" ).val(),
                b = b.replace( /,\s*$/, "" ),
                query = extractLastTerm( b ),
                query = query.replace( /^[,\s]+|[,\s]+$/g, "" ).replace( /,[,\s]*,/g, "," ),
                query = query.replace( /\s\s+/g, " "),
                queryArr = query.split( "," ),
                b = $( "#notmatchdata" ).val(),
                termArr = b.split( "," ),
                mergeArr = $.merge( queryArr, termArr ),
                terms = [];
            for ( var i = 0; i < mergeArr.length; i++ ) {
                if ( $.trim( 3 < mergeArr[i] ).length ) {
                    if ( -1 == ( $.inArray( $.trim( mergeArr[i]), terms ) ) ) {
                        terms.push( $.trim( mergeArr[i] ) )
                    }
                }
            }
            if ( "" != query && "" != b ) {
                $( "#notmatchdata" ).val( terms )
            } else if ( "" != query ) {
                $( "#notmatchdata" ).val( terms )
            } else {}
            $( ".closebtn" ).on( "click", function ( e ) {
                e.preventDefault();
                var a = $( "input#wp-link-search" ).val(),
                    f = a.replace( /^[,\s]+|[,\s]+$/g, "" ).replace( /,[,\s]*,/g, "," ),
                    f = a.replace( /\s\s+/g, " " ),
                    g = f.split( "," ),
                    h = $( "#notmatchdata" ).val(),
                    j = h.split( "," ),
                    k = [];
                for ( var i = 0; i < g.length; i++ ) {
                    if ( 3 < $.trim( g[i] ).length) {
                        if ( -1 == ( $.inArray( $.trim( g[i] ), j ) ) ) {
                            k.push( $.trim( g[i] ) )
                        }
                    }
                }
                if ( 1 < k.length )
                    $( "input#wp-link-search" ).val( k + "," ).focus();
                else
                    $( "input#wp-link-search" ).val( k ).focus();
				
                $( ".btnadd" ).html( "" );
                $( "#notmatchdata" ).val( "" );
                prepareTerms();
                return false
            } )
        }

        function insertTermsIntoTextarea() {
			var script = ( ".link-search-wrapper" );
            $( ".termadd" ).on( "click", function ( e ) {
				e.preventDefault();
				var query = $( "input#wp-link-search" ).val(),
					queryArr = array_unique_noempty( queryToArray( query ) ),
					qTextarea = $( "textarea#insertterms" ).val(),
					qTextareaArr = array_unique_noempty( queryToArray( qTextarea ) ),
					mergeArr = $.merge( queryArr, qTextareaArr );
					
				var result = filterArray( mergeArr ),
					terms = result.terms,
					badterms = result.badterms,
					shortLongterms = result.shortLongterms;
                
				uBad = badterms.join( "</u>, <u>" );
				badterm = ( "" == badterms ) ? "" : stt2extatL10n[5] + " <u>" + uBad + "</u>";
				
				uShortLongterm = shortLongterms.join( "</u>, <u>" );
				shortLongterm = ( "" == shortLongterms ) ? "" : " <u>" + uShortLongterm + "</u>";
				
                if ( "" != query && "" != qTextarea ) {
					$( "textarea#insertterms" ).val( $.trim( terms ) );
					$( "#badterms" ).html( $.trim( badterm + shortLongterm ) )
				} else if ( "" != query ) {
					$( "textarea#insertterms" ).val( $.trim( terms ) );
					$( "#badterms" ).html( $.trim( badterm + shortLongterm ) )
				} else {
					return false;
				}
				
				$( "input#wp-link-search" ).val( "" ).focus();
				$( ".btnadd" ).html( "" );
				resizeTextArea();
				return false;
			} );
		}
		
        function resizeTextArea() {
			var textarea = $( "textarea#insertterms, #stopwords" ),
				eloffsetHeight,
				offset,
				resizeTextarea,
				el,
				terms;
				
            $.each( $( textarea ), function () {
				
				if ( "true" == $( this ).attr( "aria-expanded" ) )
					return;
				
				elOffsetHeight = this.offsetHeight;
                offset = elOffsetHeight - this.clientHeight;
                resizeTextarea = function ( el ) {
                    $( el ).css( "height", "auto" ).css( "height", el.scrollHeight + offset );
                };
				
				$( this ).on( "keyup input focus", function( e ) {
					el = $( e.target );
					resizeTextarea( this );
					if ( elOffsetHeight == this.offsetHeight ) {
						$( el ).attr( "aria-expanded", "false" );
						$( ".collapse-textarea" ).remove();
					} else {
						$( el ).attr( "aria-expanded", "true" );
					}
				} ).on( "click", function( e ) {
					terms = $( this ).val();
					e.preventDefault();
					$( this ).val( terms.replace( /, /g, "\n" ).replace( /,/g, "\n" ) );
					if ( "true" == $( this ).attr( "aria-expanded" ) ) {
						if ( $( this ).parents( "label" ).siblings( "#textarea-bottom" ).is( ":empty" ) ) {
							$( "#textarea-bottom" ).html(
								$( "<button>",{ "class":"button button-secondary button-small collapse-textarea","title": "Collapse" } ).on( "click", function( e ) {
									e.preventDefault();
									$( textarea ).removeAttr( "style" ).attr( "aria-expanded", "false" );
									( $( this ) ).remove();
									return false;
							} ) );
						}
					}
					return false;
				} );
            } );
        }
		
		function termSelection( fp ) {
			var fp = fp,
				btnKey = $( ".btn-key" );
			
			selectTerm = {
				init : function()
				{
					var keyW = $( "#keylist" ),
						ls = localStorage[ "maxchar" ];
						keyList = keyW.html();
						
					if ( ! ls )
						localStorage.setItem( "maxchar", stt2extatL10n[21] );
					
					keyW.html( keyList );
					$( "body" ).css( {
						"position": "relative"
					} );
					btnKey.off( "click" );
					fp.on( "click", function ( e ) {
						e.preventDefault;
						$( "#msgb" ).html( "" );
						
						var range = window.getSelection() || document.getSelection() || document.selection.createRange(),
							word = $.trim( range.toString() ),
							str = sanitizeText( word ),
							keyListTxt = keyW.text(),
							cntText = Number( str.length + keyListTxt.length ),
							maxChars = localStorage[ "maxchar" ];
							
						msgLng = $( "<div>", {
							"id": "loading",
							"text": stt2extatL10n[17].replace( "{$maxchar}", maxChars )
						} );
						if ( "" != str && ( ( str.length && cntText ) <= maxChars ) ) {
							spanKey = $( "<span>", {
									"class": "key",
									"html": str
								} ),
								listKey = keyW.html(),
								result = $( listKey ).add( $( spanKey ) ),
								optionTexts = [];
							keyW.html( result );
							$( "#keylist span.key" ).each( function () {
								optionTexts.push( $( this ).text() )
							} );
							getText = optionTexts.join( " " );
							btnKey.attr( "data-value", getText );
							selectTerm.toolbarPos()
						}
						if ( "" != str && ( ( str.length && cntText ) > maxChars ) ) {
							$( "#msgb" ).html( $( msgLng ).show().delay( 2000 ).remove() )
						}
						selectTerm.clickKey();
						return false
					} );
					selectTerm.buttonKey();
				},
				toolbarPos : function() {
					var range = selectTerm.getSelectionRange();
				
					if ( range.getBoundingClientRect ) {
						var b = $( "#prepare-key" );
						boundary = range.getBoundingClientRect();
						boundaryMiddle = ( boundary.left + boundary.right ) / 2;
						windowWidth = window.innerWidth;
						adminBarHeight = 32;
						b.show();
						toolbarKey = selectTerm.getEl();
						if ( b.is( ":visible" ) ) {
							toolbarWidth = toolbarKey.offsetWidth;
							toolbarHalf = toolbarWidth / 2;
							posLeft = boundaryMiddle - toolbarHalf;
							upperMargin = parseInt( $( document.body ).css( "margin-bottom" ), 10 );
							margin = parseInt( $( toolbarKey ).css( "margin-bottom" ), 10 ) + upperMargin;
							if ( boundary.top < toolbarKey.offsetHeight + adminBarHeight ) {
								className = " key-arrow-up";
								posTop = boundary.bottom + window.pageYOffset - adminBarHeight + margin
							} else {
								className = " key-arrow-down";
								posTop = boundary.top + window.pageYOffset - toolbarKey.offsetHeight - adminBarHeight - margin
							}
							setTimeout( function () {
								b.css( {
									"left": posLeft,
									"top": posTop,
									"z-index": 1
								} );
								if ( " key-arrow-up" == className )
									b.removeClass( "key-arrow-down" );
								else
									b.removeClass( "key-arrow-up" );
								b.addClass( "key-inline key-inline-active" + className )
							}, 100 )
						}
					} else {
						alert( stt2extatL10n[19] )
					}
				},
				getSelectionRange : function() {
					var gS;
					if ( window.getSelection ) {
						gS = window.getSelection();
						if ( gS.rangeCount )
							return gS.getRangeAt(0);
					} else if ( document.selection ) {
						return document.selection.createRange()
					}
					return null
				},
				getEl : function() {
					 var pk = document.getElementById( "prepare-key" );
					 return pk
				},
				clickKey : function() {
					$( ".key" ).click( function ( e ) {
						var optionTexts, getText;
						
						e.preventDefault();
						if ( 1 == $( ".key" ).length )
							$( "#prepare-key" ).hide();
						$( this ).remove();
						optionTexts = [];
						$( "#keylist span.key" ).each( function () {
							optionTexts.push( $( this ).text() )
						} );
						getText = optionTexts.join( " " );
						btnKey.attr( "data-value", sanitizeText( getText ) );
						return false
					} )
				},
				buttonKey : function() {
					btnKey.on( "click", function ( e ) {
						var value = btnKey.attr( "data-value" ),
							prepareKey = $.trim( value ).replace( /\s\s+/g, " " ),
							term = $( "#wp-link-search" ).val(),
							maxChars = localStorage["maxchar"];
							
						e.preventDefault;
						msgLng = $( "<div>", {
							"id": "loading",
							"text": stt2extatL10n[17].replace( "{$maxchar}", maxChars )
						} );
						
						if ( 4 > prepareKey.length ) {
							$( "#msgb" ).html( $( msgSrt ).show().delay( 2000 ).fadeOut( 100, function () {
								$( "#msgb" ).html( "" )
							} ) )
						} else if ( prepareKey.length > maxChars ) {
							$( "#msgb" ).html( $( msgLng ).show().delay( 2000 ).fadeOut( 100, function () {
								$( "#msgb" ).html( "" )
							} ) )
						} else {
							if ( "" != term ) {
								$( "#wp-link-search" ).val( term + prepareKey + "," )
							} else {
								$( "#wp-link-search" ).val( prepareKey + "," )
							}
							prepareTerms();
							$( this ).attr( "data-value", "" );
							$( ".btnadd, #keylist, #msgb" ).html( "" );
							$( "#prepare-key").hide();
							$( "#msgb").html( $( msgSuccess ).show().delay( 2000 ).fadeOut(100, function () {
								$( "#msgb" ).html( "" )
							} ) );
							selectTerm.clearSelection()
						}
						return false
					} )
				},
				clearSelection : function() {
					if ( document.selection && document.selection.empty ) {
						document.selection.empty()
					} else if ( window.getSelection ) {
						var gS = window.getSelection();
						gS.removeAllRanges()
					}
				}
			};
			selectTerm.init();
        }
		
		function appendMore( t ) {
			$( t ).children( "li:nth-child(2)" ).after( spMore.show() );
			$( t ).click( function ( e ) {
				e.preventDefault();
				var a = $( t ).children( ":gt(1)" ).toggle().not( ":visible" );
				if ( a )
					$( t ).children( ".readmore" ).text( stt2extatL10n[13][1] );
				return false;
			} )
		}
		
		function noticeSearchPanel( msg ) {
			$( "#search-panel" ).append( $( "<div>", {
				"id": idMsg,
				"class": cDismisb,
				"html": $( msg ).add( $( btnDismiss ) )
			} ) ).fadeIn( 400 );
			clickableDismissNotice();
		}
		
		function noticeAuthForm( a ) {
			if ( "-1" == a ) {
				$( idBox ).html( $( "<div>", {
					"id": idMsg,
					"class": cDismisb,
					"html": $( pL1010 ).add( $( btnDismiss ) )
				} ) ).fadeIn( 400 );
				clickableDismissNotice();
				return false;
			}
		}
		
        function clearInput() {
            $( "input#title" ).prop( "readonly", false );
            $( "input#btninsert" ).prop( "disabled", true );
            $( "input#title, #id-field, textarea#insertterms, #notmatchdata" ).val( "" );
            $( ".loader,#searchdiv, #searchtermpost,.btnadd, #keylist, #msgb, #badterms" ).html( "" );
            $( "#stt2extat #message" ).remove();
            $( ".btn-key" ).attr( "data-value", "" );
            $( "#prepare-key" ).hide()
        }

        function clickableDismissNotice() {
            $( ".notice-dismiss" ).click( function ( e ) {
                e.preventDefault();
                $( this ).parents( "#message" ).remove();
                return false
            } )
        }
		
		function disableEnterSubmitButton() {
			$( "#stt2extat-main-form" ).on( "keyup keypress", function( e ) {
				var target = $( e.target );
				if ( $( target ).is( "textarea" ) )
					return;
				disableEnterKey( e );
			} );
		}
		
        function shortlink() {
            kbd = $( "kbd.permalink" );
            kbd.each( function () {
                var a = $( this ).text();
                $( this ).text( a.substring( 0, 30 ) + "..." ).toggle( function () {
                    $( this ).text( a )
                }, function () {
                    $( this ).text( a.substring( 0, 30 ) + "..." )
                } )
            } )
        }
		
		function postBoxScreenOption() {
			var value;
			$( "input:not(:checked).hide-postbox-tog" ).each( function() {
				value = $( this ).val();
				$( "#" + value + ".postbox" ).hide();
			} )
		}
		
		function disableDragMetaBox()
		{
			$( ".meta-box-sortables" ).sortable( {
				cancel: ".disable-drag"
			} );
			$( "#stats, #delete, #support, #features" ).addClass( "disable-drag" );
		}
		
		function submitGeneralForm()
		{
			$( "#submitdiv" ).on( "click", ".settings-save", function() {
				$( this ).siblings( ".spinner" ).addClass( "is-active" );
			} )
		}
		
		function disableEnterKey( e ) {
            code = e.keyCode || e.which;
            if ( 13 == code ) {
                e.preventDefault();
                return false
            }
        }
		
		function showPostWOTerms()
		{
			$( "#stt2extat-wo-terms" ).on( "click", function( e ) {
				e.preventDefault();
				$( ".ui-menu" ).hide();
				var visible = $( ".stt2extat-wo-terms-list" ).toggle().is( ":visible" );
				
				if ( visible ) {
					
					var data = {
						"action" : "stt2extat_post_wo_terms",
						"_wpnonce" : nonce
					};
					$.post( ajaxurl, data, function( r ) {
						noticeAuthForm( r );
						$( "#stt2extat-wo-terms span" ).text( r.count );
						$( ".stt2extat-wo-terms-list" ).html( r.result );
						$( ".stt2extat-wo-terms-list li a" ).on( "click", function() {
							$( ".stt2extat-wo-terms-list" ).hide();
							$( "#stt2extat-manual input#title" ).val( $( this ).data( "href" ) ).autocomplete( "search" );
							$( "html, body" ).animate( { scrollTop: $( "#manual" ).offset().top }, "slow" );
						} )
					}, "json" );
				}
				
				return false;
			} );
		}
		
		function filterArray( array ) {
			var result = [],
				terms = [],
				badterms = [],
				shortLongterms = [];
			for ( i = 0; i < array.length; i++ ) {
				if ( -1 == ( $.inArray( array[i], terms ) )
					&& false == inStopwords( array[i] )
					&& 3 < array[i].length
					&& stt2extatL10n[21] > array[i].length )
						terms.push( array[i] );
				
				if ( -1 == ( $.inArray( array[i], badterms ) )
					&& inStopwords( array[i] ) )
						badterms.push( array[i] );
				
				if ( ( -1 == ( $.inArray( array[i], shortLongterms ) )
					&& ( 4 > array[i].length || stt2extatL10n[21] < array[i].length )
					&& false == inStopwords( array[i] ) ) )
						shortLongterms.push( array[i] );
			}
			
			result["terms"] = terms;
			result["badterms"] = badterms;
			result["shortLongterms"] = shortLongterms;
			return result;
		}
		
		function inStopwords( q ) {
			var q = q || false;
			
			if ( ! $.isArray( stopwords ) )
				stopwords = queryToArray( stopwords );
			else
				stopwords = sanitizeArray( stopwords );
			
            for ( var i = 0; i < stopwords.length; i++ ) {
                if ( -1 != q.indexOf( stopwords[i] ) ) {
					return true;
                }
            }
			return false;
        }
		
		function sanitizeArray( array ) {
			return array_unique_noempty( queryToArray( array.join( comma ) ) );
		}
		
		function queryToArray( q ) {
			var q = sanitizeText( q );
			return $.map( q.split( comma ), $.trim )
		}
		
		function sanitizeText( q ) {
			var _text = stripTags( q );
			
			try {
				textarea.innerHTML = _text;
				_text = stripTags( textarea.value );
			} catch ( er ) {}
			
			return _text;
		}
		
		function stripTags( string ) {
			var string = string || "";
			
			return string
				.replace( /<!--[\s\S]*?(-->|$)/g, "" )
				.replace( /<(script|style)[^>]*>[\s\S]*?(<\/\1>|$)/ig, "" )
				.replace( /<\/?[a-z][\s\S]*?(>|$)/ig, "" )
				.replace( /[\r\n\t ]|\xC2\xA0|&nbsp;/g, " " )
				.replace( /^[,\s]+|[,\s]+$/g, "" ).replace( /,[,\s]*,/g, "," )
				.replace( /\s+/g, " " )
				.toLowerCase();
		}
		
		function splitTerms( q ) {
            return q.split( /,\s*/ )
        }
		
        function extractLastTerm( q ) {
            return splitTerms( q ).pop()
        }
		
		return this.each( function() {
			disableEnterSubmitButton();
			postBoxScreenOption();
            postboxes.add_postbox_toggles( pagenow );
			disableDragMetaBox();
			submitGeneralForm();
			resizeTextArea();
			loadForm();
			loadTable();
			deleteAllTerms();
			uaField();
			check_relevant_terms();
			migrate_stt2_terms();
			showPostWOTerms()
        } )
    }
} ( jQuery ) );