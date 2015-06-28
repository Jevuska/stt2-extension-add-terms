(function($) {
    var disnotice = commonL10n.dismiss, 
    idMsg = 'message', 
    cDismissn = 'notice-dismiss', 
    cDismisb = 'error notice is-dismissible', 
    cDismisbUpd = 'notice is-dismissible', 
    cSRT = 'screen-reader-text', 
    cErrMsg = 'error-message', 
    cMore = 'more', 
    cdArrowUp = 'dashicons dashicons-arrow-up', 
    cdTag = 'dashicons dashicons-tag', 
    cdNo = 'dashicons dashicons-no', 
    cBtn = 'button closebtn', 
    cBtnAdd = 'button termadd', 
    cSpin = 'spinner is-active', 
    dvnDismis = $('<div>', {id: idMsg,class: cDismisb}), 
    spSRT = $('<span>', {class: cSRT,text: disnotice}), 
    spRmvIrrlvnt = $('<span>', {class: cdNo,title: stt2extatL10n[2]}), 
    tNotice = $('<p>', {text: stt2extatL10n[12]}), 
    btnDismiss = $('<button>', {type: "button",class: cDismissn,html: spSRT}), 
    btnRmv = $('<button>', {type: "button",class: cBtn,text: stt2extatL10n[14]}), 
    btnAdd = $('<button>', {type: "button",class: cBtnAdd,text: stt2extatL10n[15]}), 
    spMore = $('<span>', {class: cMore,text: stt2extatL10n[13]}), 
    spL105 = $('<span>', {text: stt2extatL10n[5]}), 
    spL107 = $('<span>', {text: stt2extatL10n[7]}), 
    pL108 = $('<p>', {text: stt2extatL10n[8]}), 
    pL109 = $('<p>', {text: stt2extatL10n[9]}), 
    pL1010 = $('<p>', {text: stt2extatL10n[10]}), 
    pL1011 = $('<p>', {text: stt2extatL10n[11]}), 
    iArrowUp = $('<span>', {class: cdArrowUp}), 
    stgText = $('<strong>', {text: stt2extatL10n[6]}), 
    parag = $('<p>', {html: stgText}), 
    spinner = $('<span>', {class: cSpin}), 
    msgSrt = $("<div>", {id: "loading",text: stt2extatL10n[16]}), 
    maxchar = stt2extatL10n[21];
    lclStrg = localStorage['maxchar'];
    msgSuccess = $("<div>", {id: "loading",style: "background-color:#0091cd;color:#fff",text: stt2extatL10n[18]}), 
    success = $("<div>", {id: "loading",style: "background-color:#0091cd;color:#fff",text: stt2extatL10n[22]}), 
	msgLoad = $("<div>", {id: "loading",text: stt2extatL10n[23]}),
    DOM = tinymce.DOM;
	nonce = stt2extatL10n[1];
		
    if (!lclStrg) {
        localStorage['maxchar'] = maxchar;
    }
    
    $.stt2extat = {extatTab: function() {
            var $helpTab = $('.contextual-help-tabs');
            screenMeta = {element: null,toggles: null,init: function() {
                    this.element = $('#fullpost');
                    this.toggles = $('.screen-meta-toggle a');
                    this.toggles.click(this.toggleEvent);
                },toggleEvent: function(e) {
                    var panel = $(this.href.replace(/.+#/, '#'));
                    e.preventDefault();
                    if (!panel.length)
                        return;
                    if (panel.is(':visible'))
                        screenMeta.close(panel, $(this));
                    else
                        screenMeta.open(panel, $(this));
                },open: function(panel, link) {
                    panel.parent().show();
                    panel.slideDown('fast', function() {
                        panel.focus();
                        link.addClass('screen-meta-active').attr('aria-expanded', true);
                    });
                    $(document).trigger('screen:options:open');
                },close: function(panel, link) {
                    panel.slideUp('fast', function() {
                        link.removeClass('screen-meta-active').attr('aria-expanded', false);
                        panel.hide();
                    });
                    $(document).trigger('screen:options:close');
                }};
            screenMeta.init();
            stt2extat_tab('tab-panel-manage');
			
            $helpTab.delegate('a', 'click', function(e) {
                var link = $(this), panel = $(link.attr('href')), tab = panel.attr('id');
                e.preventDefault();
                if (link.is('.active a'))
                    return false;
                $('.contextual-help-tabs .active').removeClass('active');
                link.parent('li').addClass('active');
                $('.help-tab-content').not(panel).removeClass('active').hide();
				panel.html('');
				
                panel.addClass('active').show(function() {
					
                    stt2extat_tab(tab);
                    $("#screen-meta-links").css({"display": "none"});
                    $(".btn-key").attr("data-value", "");
                    $(".btnadd,#keylist,#msgb").html("");
                    $("#prepare-key").hide();
                });
            });
            stt2extat_collapse_sidebar_left();
            $('ol.hint li').hide().filter(':lt(2)').show();
            $('ol.hint').append(spMore).find('.more').click(function(e) {
                e.preventDefault();
                var visible = $(this).siblings(':gt(1)').toggle().is(":visible");
                if (visible) {
                    $(".more").html(iArrowUp);
                } else {
                    $(".more").text(stt2extatL10n[13]);
                }
                return false;
            });
        },insertTerm: function() {
            var t = '';
            $("input#btninsert").one("click", function(e) {
                clearTimeout(t);
                t = setTimeout(function() {
                    stt2extat_insert_searchterm_js();
                    stt2extat_enter_disabled_js(e);
                }, 450);
            });
        },searchPost: function() {
            var max = 10, t = '';
            $("input#title").autocomplete({source: function(request, response) {
                    var query = $("input#title").val(), jqxhr = $.ajax({url: ajaxurl,method: 'POST',dataType: 'json',data: {action: 'stt2extat_search_post','wpnonce': nonce,'max': max,'query': query}}).done(function(data) {
                        response(data);
                    });
                },minLength: 0,select: function(event, ui, e) {
                    var title = ui.item.label, contentui = ui.item.content, link = ui.item.value, content = contentui;
                    $("#id-field").val(ui.item.id);
                    $(this).prop("readonly", true);
                    $("input#btninsert").prop("disabled", false);
                    $('.contextual-help-sidebar').html($(btnDismiss).add($(parag)).add('<p>', {html: ui.item.excerpt}));
                    $("#screen-meta-links").css({"display": "block"});
                    $("#fullpost").html($('<hr>').add($('<h3>', {text: title})).add('<p>', {html: $('<a>', {href: link,target: "_blank",text: link})}).add($('<div>', {html: $('<p>', {html: content.replace(/(\n)+/g, '</p>')})})));
                    stt2extat_close_preview();
                    stt2extat_get_searchterms_post_js();
                },response: function(event, ui) {
                    $("form#stt2-extat #search-panel").append($(dvnDismis));
                    if (ui.content.length === 0) {
                        var resultNotice = $(tNotice).add($(btnDismiss));
                        $("#message").html(resultNotice).fadeIn(400);
                    } else {
                        $("form#stt2-extat #search-panel").find("#message:last").remove();
                    }
                    $(".loader").empty();
                    stt2extat_clickable_dismiss_notice_js();
                }
            }).click('input', function(e) {
                e.preventDefault();
                $(this).autocomplete("search");
                $("#screen-meta-links").hide();
                if ($("#fullpost").is(':visible')) {
                    $("#contextual-help-link").removeClass('screen-meta-active').attr('aria-expanded', false);
                    $("#fullpost").html("").hide();
                }
                stt2extat_clear_input_js();
                return false;
            });
            select_word();
        }};
    function stt2extat_recent_post(elem, max) {
        var data = {'action': 'stt2extat_recent_post','wpnonce': nonce,'max': max,};
        $.post(ajaxurl, data, function(response) {
            elem.val(response);
        });
    }

    function stt2extat_tab(tab) {
		data = {'action': 'stt2extat_tab_content','wpnonce': nonce,'tab': tab,};
			$.post(ajaxurl, data, function(response) {
				$("#" + tab).html(response);
				$.stt2extat.searchPost();
				$.stt2extat.insertTerm();
				if(tab == 'tab-panel-settings'){
					slider_max_char();
				}
			});
    }
    
    function slider_max_char() {
        t = "";
		f = localStorage['maxchar'];
        $("#slider-range-max").slider({
            range: "max",
            min: 4,
            max: 155,
            value: f,
            slide: function(event, ui) {
                $("#maxchar").text(ui.value);
            },
            change: function(event, ui) {
				if(f != ui.value){
					$("#msgbt").html('');
					clearTimeout(t);
					t = setTimeout(function() {
						localStorage['maxchar'] = $("#slider-range-max").slider("value");
						stt2extat_update_settings($("#slider-range-max").slider("value"));
					}	, 450);
				}
            }
        });
    }
    
    function stt2extat_update_settings(newMaxChar) {
        var data = {'action': 'stt2extat_update_setting','wpnonce': nonce,'maxchar': newMaxChar};
        $.post(ajaxurl, data, function(response) {
            $("#msgbt").html($(success).show("fast").delay(2000).fadeOut(100, function() {
                $("#msgbt").html("")
            }));
        });
    }
    
    function stt2extat_get_searchterms_post_js() {
        var id = $("input#id-field").val();
        if (id !== 0) {
            $('#searchtermpost').html("<span class='spinner is-active'></span>");
            var jqxhr = $.ajax({url: ajaxurl,method: "POST",data: {action: "stt2extat_get_search_terms_db_list",id: id,wpnonce: nonce,}}).done(function(data) {
                $('#searchtermpost .spinner').fadeOut(400, function(e) {
                    $('#searchtermpost').html(data).fadeIn(200);
					stt2extat_get_searchterms_all(id);
                    stt2extat_delete_searchterms_post_js();
                    stt2extat_searchterm_hits_js();
                    stt2extat_searchterm_field_js();
                });
            });
        }
    }
	
	function stt2extat_get_searchterms_all(id) {
		
        var data = {'action': 'stt2extat_get_searchterms_all','wpnonce': nonce,'id': id}, tag10 = $('.tagchecklist'),revert = tag10.clone(),gt = $('.alltag');
		
		$('.alltag').toggle(function() {
			$("#msgb").html($(msgLoad).show());
			$.post(ajaxurl, data, function(response) {
				$("#msgb").html("");
				$('.alltag').before(response).removeClass('dashicons-plus-alt').addClass('dashicons-minus');
				                    stt2extat_delete_searchterms_post_js();
                    stt2extat_searchterm_hits_js();
			});
		}, function() {
			$('.stplus').remove();
			$(this).removeClass('dashicons-minus').addClass('dashicons-plus-alt');
		});
    }
	
    function stt2extat_delete_searchterms_post_js() {
        $(".ntdelbutton").each(function() {
            $(this).on("click", function() {
                delete_terms = $(this).parents("span").children("i.termlist").text();
                $(this).parents("span").fadeOut(200).remove();
				$("#message").fadeOut(200).remove();
                jqxhr = $.ajax({url: ajaxurl,method: "POST",data: {action: "pk_stt2_admin_delete_searchterms",delete_terms: $.trim(delete_terms),wpnonce: nonce,}}).done(function(data) {
					$("#message").fadeOut(200).remove();
                    data = $(data);
                    dataFix = data.addClass(cDismisbUpd);
                    dataFinal = dataFix.append($(btnDismiss));
                    $("form#stt2-extat #search-panel").append(dataFinal);
                    listterm = $(".tagchecklist").html();
                    if ($(listterm).length < 1) {
                        emptyST = $('<span>', {class: cdTag}).add($('<span>', {text: stt2extatL10n[3]}));
                        $("#searchtermpost").html($('<span>', {class: cErrMsg,html: emptyST}));
                    }
                    stt2extat_clickable_dismiss_notice_js();
                });
                return false;
            });
        });
    }
	
    function stt2extat_searchterm_hits_js() {
        var t = "";
        $("i.termlist,i.termcnt").off("click");
        $("i.termlist").on("click", function(e) {
            e.preventDefault();
            var term = $(this).text(), termcnt = jQuery($(this).parents("span").children("i.termcnt")), cnt = Number($(this).parents("span").children("i.termcnt").html()), meta_count = cnt + 1;
            termcnt.html(meta_count);
            t = setTimeout(function() {
                stt2extat_searchterm_hits_db_js(term, meta_count, nonce);
                e.preventDefault();
            }, 2000);
            return false;
        });
        $("i.termcnt").on("click", function(e) {
            e.preventDefault();
            var term = $(this).parents("span").children("i.termlist").text(), cnt = Number($(this).text());
            if (cnt < 2) {
                $(this).text(1);
                var meta_count = Number($(this).text());
            } else {
                $(this).text(Number($(this).text()) - 1);
                var meta_count = Number($(this).text());
            }
            t = setTimeout(function() {
                stt2extat_searchterm_hits_db_js(term, meta_count, nonce);
                e.preventDefault();
            }, 2000);
            return false;
        });
        function stt2extat_searchterm_hits_db_js(term, meta_count, nonce) {
            var jqxhr = $.ajax({url: ajaxurl,method: "POST",data: {action: "update_meta_count_extat",term: $.trim(term),meta_count: meta_count,wpnonce: nonce,}}).done(function(data) {
                return true;
            });
        }
    }
    function stt2extat_searchterm_field_js() {
        var field = $('#searchdiv'), jqxhr = $.ajax({url: ajaxurl,beforeSend: function() {
                field.html($('<img>', {src: imgLoader.src,width: 208}));
            },method: "POST",data: {action: "stt2extat_relevant_post_search_field",wpnonce: nonce}}).done(function(data) {
            field.html(data).fadeIn(400);
            stt2extat_searchterm_js();
            stt2extat_notice_ignore_irrelevant_js("gsuggest");
            stt2extat_notice_ignore_irrelevant_js("notmatchfeat");
        })
    }
    function stt2extat_searchterm_js() {
        $("input#wp-link-search").on("keydown", function(e) {
            $('.btnadd,#message,#badterms,#keylist,#msgb').html("");
            $('#message').remove();
            $('#notmatchdata').val("");
            $(".btn-key").attr("data-value", "");
            $("#prepare-key").hide();
            var term = stt2extat_extractLast_js(this.value), code = e.keyCode || e.which;
            if (code == 13) {
                e.preventDefault();
                return false;
            } else {
                if ($('input#gsuggest').is(':checked')) {
                    stt2extat_googlesuggest_js(e);
                } else {
                    $(this).googleSuggest({disabled: true,});
                }
                if (code == 188 && $.trim(term).length > 3) {
                    clearTimeout($(this).data("timeout"));
                    $(this).data("timeout", setTimeout(function() {
                        stt2extat_search_relevant_post_js();
                    }, 1000));
                }
            }
        });
    }
    function stt2extat_notice_ignore_irrelevant_js(g) {
        var s = $('input#' + g), gStore = localStorage[g], gCheck = localStorage[g + 'check'];
        if (gCheck == "check") {
            s.prop("checked", true);
            s.val(1);
        } else {
            s.prop("checked", false);
            s.val("");
        }
        $('input#' + g).change(function() {
            if ($(this).is(':checked')) {
                if (!gStore) {
                    $(".wrap").find('input.' + g).trigger('click');
                    localStorage[g] = "yes";
                }
                localStorage[g + 'check'] = "check";
                $(this).val(1);
            } else {
                localStorage[g + 'check'] = "";
                $(this).val("");
            }
        });
    }
    function stt2extat_googlesuggest_js(e) {
        $("input#wp-link-search").on("keydown", function(event) {
            if (event.keyCode === $.ui.keyCode.TAB && $(this).data("ui-autocomplete").menu.active) {
                event.preventDefault();
            }
        }).googleSuggest({disabled: false,search: function() {
                var term = stt2extat_extractLast_js(this.value);
                if (term.length < 4) {
                    return false;
                }
            },focus: function() {
                return false;
            },select: function(event, ui) {
                var terms = stt2extat_split_js(this.value);
                terms.pop();
                terms.push(ui.item.value);
                terms.push("");
                this.value = terms.join(",");
                clearTimeout($(this).data("timeout"));
                $(this).data("timeout", setTimeout(function() {
                    stt2extat_search_relevant_post_js();
                }, 1000));
                return false;
            }});
    }
    function stt2extat_search_relevant_post_js() {
        var query2 = $("input#wp-link-search").val(), query = $.trim(query2), query = query.replace(/,\s*$/, ''), term1 = query.split(','), term2 = term1.pop();
        if (term2 === "") {
            var term = query;
        } else {
            var term = term2;
        }
        if (term.length > 3) {
            search_relevant(term);
        }
    }
    function search_relevant(term) {
		var ignore = $("#notmatchfeat").val(), id = $("input#id-field").val();
		$('.btnadd').html("");
		$('#wp-link .link-search-wrapper .spinner').addClass("is-active");
		var data = {action: "stt2extat_search_relevant_post",query: term,wpnonce: nonce,id: id,ignore: ignore};
		$.ajax({
				type: "POST",
				url: ajaxurl,
				data: data,
				dataType: "json"
				}).done(function(data) {
					respons = data[0].respons;
					content = data[0].content;
					title = data[0].title;
					post = data[0].post;
					$('#wp-link .link-search-wrapper .spinner').removeClass("is-active");
					if (respons != "" && content != "") {
						if (respons == "badword") {
							$('.btnadd').html($(btnRmv).add($(spRmvIrrlvnt)).add($(spL105)));
							stt2extat_remove_searchterm_js();
						} else {
							$('.btnadd').html($(btnAdd).add($(respons)));
							stt2extat_populate_searchterm_js();
						}
						$('.contextual-help-sidebar').html($(btnDismiss).add($(parag)).add('<span>', {html: content}));
						stt2extat_close_preview();
					} else if (respons != "" && content == "") {
					if (respons == "badword") {
						$('.btnadd').html($(btnRmv).add($(spRmvIrrlvnt)).add($(spL105)));
						stt2extat_remove_searchterm_js();
					} else {
						$('.btnadd').html($(btnAdd).add($(respons)));
						stt2extat_populate_searchterm_js();
					}
					stt2extat_close_preview();
					} else {
					$('.btnadd').html($(btnRmv).add($(spRmvIrrlvnt)).add($(spL107)));
					stt2extat_remove_searchterm_js();
					}
				});
    }
    function stt2extat_close_preview() {
        var hint = $("#thehint").html();
        $('.contextual-help-sidebar .notice-dismiss').on("click", function(e) {
            e.preventDefault();
            $('div.wrap .contextual-help-sidebar').html(hint);
            $('ol.hint li').hide().filter(':lt(2)').show();
            $('.more').remove();
            $('ol.hint').append($(spMore)).find('.more').click(function(e) {
                e.preventDefault();
                var visible = $(this).siblings(':gt(1)').toggle().is(":visible");
                if (visible) {
                    $(".more").html($(iArrowUp));
                } else {
                    $(".more").text(stt2extatL10n[13]);
                }
            });
            return false;
        });
    }
    function stt2extat_remove_searchterm_js() {
        var term = $("input#wp-link-search").val(), term = term.replace(/,\s*$/, ''), query = stt2extat_extractLast_js(term), query = query.replace(/^[,\s]+|[,\s]+$/g, '').replace(/,[,\s]*,/g, ','), query = query.replace(/\s\s+/g, ' '), queryArr = query.split(","), term = $("#notmatchdata").val(), termArr = term.split(","), mergeArr = $.merge(queryArr, termArr), terms = [];
        for (var i = 0; i < mergeArr.length; i++) {
            if ($.trim(mergeArr[i]).length > 3) {
                if (($.inArray($.trim(mergeArr[i]), terms)) == -1) {
                    terms.push($.trim(mergeArr[i]));
                }
            }
        }
        if (query != "" && term != "") {
            $("#notmatchdata").val(terms);
        } else if (query != "") {
            $("#notmatchdata").val(terms);
        } else {
        }
        $(".closebtn").on("click", function(e) {
            e.preventDefault();
            var term2 = $("input#wp-link-search").val(), query2 = term2.replace(/^[,\s]+|[,\s]+$/g, '').replace(/,[,\s]*,/g, ','), query2 = term2.replace(/\s\s+/g, ' '), queryArr2 = query2.split(","), term4 = $("#notmatchdata").val(), termArr4 = term4.split(","), terms3 = [];
            for (var i = 0; i < queryArr2.length; i++) {
                if ($.trim(queryArr2[i]).length > 3) {
                    if (($.inArray($.trim(queryArr2[i]), termArr4)) == -1) {
                        terms3.push($.trim(queryArr2[i]));
                    }
                }
            }
            
            if (terms3.length > 1) {
                $("input#wp-link-search").val(terms3 + ",").focus();
            } else {
                $("input#wp-link-search").val(terms3).focus();
            }
            
            $('.btnadd').html("");
            $("#notmatchdata").val("");
            stt2extat_search_relevant_post_js();
            return false;
        });
    }
    function stt2extat_populate_searchterm_js() {
        $(".termadd").on("click", function(e) {
            e.preventDefault();
            query = $("input#wp-link-search").val(), query = query.replace(/^[,\s]+|[,\s]+$/g, '').replace(/,[,\s]*,/g, ','), query = query.replace(/\s\s+/g, ' '), queryArr = query.split(","), term = $("textarea#insertterms").val(), termArr = term.split(","), mergeArr = $.merge(queryArr, termArr), terms = [], badterms = [];
            
            for (i = 0; i < mergeArr.length; i++) {
                if ($.trim(mergeArr[i]).length > 3) {
                    if (($.inArray($.trim(mergeArr[i]), terms)) == -1 && stt2extat_badwords_js(mergeArr[i]) != 1)
                        terms.push($.trim(mergeArr[i]));
                    
                    if (($.inArray($.trim(mergeArr[i]), badterms)) == -1 && stt2extat_badwords_js(mergeArr[i]) == 1)
                        badterms.push($.trim(mergeArr[i]));
                }
            }
            uBad = badterms.join('</u>, <u>');
            badterm = badterms == "" ? "" : stt2extatL10n[5] + " <u>" + uBad + "</u>";
            if (query != "" && term != "") {
                $("textarea#insertterms").val($.trim(terms));
                $("#badterms").html($.trim(badterm));
            } else if (query != "") {
                $("textarea#insertterms").prop("readonly", false);
                $("textarea#insertterms").val($.trim(terms));
                $("#badterms").html($.trim(badterm));
            } else {
            }
            $("input#wp-link-search").val("").focus();
            $(".btnadd").html("");
            return false;
        });
    }
    function stt2extat_badwords_js(terms) {
        badwords = $("textarea[name=badwords]").val(), badwordsArr = badwords.split(","), compare_terms = terms, total = badwordsArr.length;
        for (var i = 0; i < total; i++) {
            if (compare_terms.indexOf(badwordsArr[i]) > -1) {
                result = 1;
                return result;
                break;
            }
        }
    }
    function stt2extat_insert_searchterm_js() {
        action = "stt2extat_insert_searchterm", postid = $("#id-field").val(), terms = $("textarea#insertterms").val(), ignore = $("#notmatchfeat").val();
        $("form#stt2-extat #message").remove();
        $(".loader").html(spinner).fadeIn(400);
        if (postid.length == "") {
            $(".loader").html(spinner).fadeOut(400, function() {
                $("form#stt2-extat #search-panel").append($('<div>', {id: idMsg,class: cDismisb,html: $(pL108).add($(btnDismiss))})).fadeIn(400);
                stt2extat_clickable_dismiss_notice_js();
            });
            return false;
        } else 
        if ($.trim(terms).length == "") {
            $(".loader").html(spinner).fadeOut(400, function() {
                $("form#stt2-extat #search-panel").append($('<div>', {id: idMsg,class: cDismisb,html: $(btnDismiss).add($(pL109))})).fadeIn(400);
                stt2extat_clickable_dismiss_notice_js();
            });
        } else {
            $.ajax({url: ajaxurl,method: "POST",data: {action: action,terms: terms,postid: postid,ignore: ignore,wpnonce: nonce}}).done(function(data) {
                if (data == "1") {
                    $("form#stt2-extat #search-panel").append($('<div>', {id: idMsg,class: cDismisb,html: $(pL1010).add($(btnDismiss))})).fadeIn(400);
                    stt2extat_clickable_dismiss_notice_js();
                } else {
                    $(".loader").fadeOut(300, function() {
                        $("form#stt2-extat #search-panel").append(data).fadeIn(100);
                        stt2extat_shortlink_js();
                        stt2extat_get_searchterms_post_js();
                        stt2extat_clickable_dismiss_notice_js();
                    });
                }
                $("textarea#insertterms").val("");
                $("#badterms,.loader").html("");
            }).fail(function(data) {
                
                $("form#stt2-extat #search-panel").append($('<div>', {id: idMsg,class: cDismisb,html: $(pL1011).add($(btnDismiss))})).fadeIn(400);
                $("#title,#id-field,textarea#insertterms").val("");
                $("input#btninsert").prop("disabled", true);
                stt2extat_clickable_dismiss_notice_js();
            });
        }
        $.stt2extat.insertTerm();
        return false;
    }
    function stt2extat_clear_input_js() {
        $("input#title").prop("readonly", false);
        $("input#btninsert").prop("disabled", true);
        $("textarea#insertterms").prop("readonly", true);
        $("input#title,#id-field,textarea#insertterms,#notmatchdata").val("");
        $(".loader,#searchdiv,#searchtermpost,.btnadd,#keylist,#msgb").html("");
        $("form#stt2-extat #message").remove();
        $(".btn-key").attr("data-value", "");
        $("#prepare-key").hide();
    }
    function stt2extat_clickable_dismiss_notice_js() {
        $(".notice-dismiss").click(function(e) {
            e.preventDefault();
            $(this).parents("#message").remove();
            return false;
        });
    }
    function stt2extat_enter_disabled_js(e) {
        code = e.keyCode || e.which;
        if (code == 13) {
            e.preventDefault();
            return false;
        }
    }
    function stt2extat_split_js(val) {
        return val.split(/,\s*/);
    }
    function stt2extat_extractLast_js(term) {
        return stt2extat_split_js(term).pop();
    }
    function stt2extat_shortlink_js() {
        kbd = $("kbd.permalink");
        kbd.each(function() {
            var realText = $(this).text();
            $(this).text(realText.substring(0, 30) + "...");
            $(this).toggle(function() {
                $(this).text(realText);
            }, function() {
                $(this).text(realText.substring(0, 30) + "...");
            });
        });
    }
    
    function stt2extat_collapse_sidebar_left() {
        var chb = $("#contextual-help-back"), cht = $(".contextual-help-tabs");
        $("#tog").html(stt2extatL10n[20]);
        $("#tog").on("click", function(e) {
            e.preventDefault();
            
            $(this).parent().next().children(".contextual-help-tabs").toggleClass("hidden");
            if ($(".contextual-help-tabs").is(":visible")) {
                $(this).parent().css({"left": "150px"});
                $(this).removeClass("tab-arrow-left").addClass("tab-arrow-right");
            } else {
                $(this).parent().css({"left": "0px"});
                $(this).removeClass("tab-arrow-right").addClass("tab-arrow-left");
            }
            return false;
        });
    }
    
    function select_word() {
        fullPost = $("#fullpost"), btnKey = $(".btn-key"), keyW = $("#keylist"), keyList = keyW.html();
        keyW.html(keyList);
        $("body").css({"position": "relative"});
        btnKey.off("click");
        
        
        fullPost.on("click", function(e) {
            e.preventDefault;
            $("#msgb").html("");
            
            range = window.getSelection() || document.getSelection() || document.selection.createRange(), word = $.trim(range.toString()), str = word.replace(/[^\w\s]/gi, ''), keyListTxt = keyW.text(), cntText = Number(str.length + keyListTxt.length), maxChars = localStorage['maxchar'];
            msgLng = $("<div>", {id: "loading",text: stt2extatL10n[17].replace('{$maxchar}', maxChars)});
            if (str != '' && ((str.length && cntText) <= maxChars)) {
                
                spanKey = $('<span>', {class: 'key',html: str}), 
                listKey = keyW.html(), 
                result = $(listKey).add($(spanKey)), 
                optionTexts = [];
                keyW.html(result);
                $("#keylist span.key").each(function() {
                    optionTexts.push($(this).text())
                });
                getText = optionTexts.join(' ');
                btnKey.attr("data-value", getText);
                toolbarPos();
            }
            
            if (str != '' && ((str.length && cntText) > maxChars)) {
                $("#msgb").html($(msgLng).show().delay(2000).remove());
            }
            
            click_key();
            return false;
        
        });
        btn_key();
        function toolbarPos() {
            var range = getSelectionRange();
            
            if (range.getBoundingClientRect) {
                var boxKey = $("#prepare-key");
                boundary = range.getBoundingClientRect();
                boundaryMiddle = (boundary.left + boundary.right) / 2;
                windowWidth = window.innerWidth;
                adminBarHeight = 32;
                boxKey.show();
                toolbarKey = getEl();
                
                if (boxKey.is(":visible")) {
                    toolbarWidth = toolbarKey.offsetWidth;
                    toolbarHalf = toolbarWidth / 2;
                    posLeft = boundaryMiddle - toolbarHalf;
                    upperMargin = parseInt(DOM.getStyle(document.body, 'margin-top', true), 10);
                    margin = parseInt(DOM.getStyle(toolbarKey, 'margin-bottom', true), 10) + upperMargin;
                    
                    if (boundary.top < toolbarKey.offsetHeight + adminBarHeight) {
                        className = ' key-arrow-up';
                        posTop = boundary.bottom + window.pageYOffset - adminBarHeight + margin;
                    } else {
                        className = ' key-arrow-down';
                        posTop = boundary.top + window.pageYOffset - toolbarKey.offsetHeight - adminBarHeight - margin;
                    }
                    
                    setTimeout(function() {
                        boxKey.css({'left': posLeft,'top': posTop,'z-index': 1});
                        if (className == ' key-arrow-up') {
                            boxKey.removeClass('key-arrow-down');
                        } else {
                            boxKey.removeClass('key-arrow-up');
                        }
                        boxKey.addClass('key-inline key-inline-active' + className);
                    }, 100);
                }
            } else {
                alert(stt2extatL10n[19]);
            }
        
        }
        
        function getSelectionRange() {
            var sel;
            if (window.getSelection) {
                sel = window.getSelection();
                if (sel.rangeCount) {
                    return sel.getRangeAt(0);
                }
            } else if (document.selection) {
                return document.selection.createRange();
            }
            return null;
        }
        
        function getEl() {
            var ev = document.getElementById("prepare-key");
            return ev;
        }
        
        function click_key() {
            $(".key").click(function(e) {
                e.preventDefault();
                if ($(".key").length == 1)
                    $("#prepare-key").hide();
                $(this).remove();
                
                optionTexts = [];
                $("#keylist span.key").each(function() {
                    optionTexts.push($(this).text())
                });
                getText = optionTexts.join(' ');
                btnKey.attr("data-value", getText);
                return false;
            });
        }
        
        function btn_key() {
            
            btnKey.on("click", function(e) {
                e.preventDefault;
                var btnData = btnKey.attr("data-value"), prepareKey = $.trim(btnData), prepareKey = prepareKey.replace(/\s\s+/g, ' '), term = $("#wp-link-search").val(), maxChars = localStorage['maxchar'];
                msgLng = $("<div>", {id: "loading",text: stt2extatL10n[17].replace('{$maxchar}', maxChars)});
                if (prepareKey.length < 4) {
                    $("#msgb").html($(msgSrt).show().delay(2000).fadeOut(100, function() {
                        $("#msgb").html("")
                    }));
                } else if (prepareKey.length > maxChars) {
                    $("#msgb").html($(msgLng).show().delay(2000).fadeOut(100, function() {
                        $("#msgb").html("")
                    }));
                } else {
                    if (term != "") {
                        $("#wp-link-search").val(term + prepareKey + ',');
                    } else {
                        $("#wp-link-search").val(prepareKey + ',');
                    }
                    stt2extat_search_relevant_post_js();
                    $(this).attr("data-value", "");
                    $(".btnadd,#keylist,#msgb").html("");
                    $("#prepare-key").hide();
                    $("#msgb").html($(msgSuccess).show().delay(2000).fadeOut(100, function() {
                        $("#msgb").html("")
                    }));
                    clearSelection();
                }
                return false;
            });
        }
        function clearSelection() {
            if (document.selection && document.selection.empty) {
                document.selection.empty();
            } else if (window.getSelection) {
                var sel = window.getSelection();
                sel.removeAllRanges();
            }
        }
    }
}(jQuery));
