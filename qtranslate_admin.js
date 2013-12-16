
var qtrans_current_lang = '';
var qtrans_full_title = '';
var qtrans_full_excerpt = '';
var qtrans_full_content = '';

// this should be inside jquery block, but did not find how to insert it there
function qtrans_tiymce_onchange_callback(e) {
	if (tinyMCE.activeEditor.isDirty()) {
		qtrans_full_content = qtrans_integrate(qtrans_current_lang,tinyMCE.activeEditor.getContent(),qtrans_full_content);
    }
}

(function($){
	$(document).ready(function(){
		if ( $('#mlwp-editors').length ) {
			var $editors_cont = $('#mlwp-editors').remove().prependTo( $('#post-body-content') ).css( { 'visibility': 'hidden' } ).show();
			
			// See if the user's using the classic colorscheme or not
			if ( $('#colors-css').length && $('#colors-css').attr('href').match(/colors-classic/i) ) {
				$('#mlwp-editors').addClass('colors-classic');
			};

			$(window).load(function(){
				init_js_tabs( $editors_cont );

				$( "#poststuff" ).css( { 'visibility': 'visible' } );		
				$editors_cont.css( { 'visibility': 'visible' } );
			});

			function init_js_tabs( parent ) {
				if( $(".js-tab", parent).length ) {
					var tabs = [];

					$(".js-tab").each(function(){
						tabs.push({
							id:    $(this).attr("id"),
							title: $(this).attr("title")
						});

						$(this).attr("title", "");
					});

					var nav = $("> h2", parent);
					nav.addClass("nav-tab-wrapper").addClass("js-tabs-nav");
					//nav.append("<span>&nbsp;&nbsp;</span>");
					//for(i in tabs) {
					for(i=0; i<tabs.length; i++) {
						//el = '<a href="#' + tabs[i].id + '" class="button button-' + ( $('#' + tabs[i].id).hasClass('mlwp-deflang') ? 'primary' : 'secondary' ) + '">' + tabs[i].title + '</a>';
						el = '<a href="#' + tabs[i].id + '" class="nav-tab' + ( $('#' + tabs[i].id).hasClass('mlwp-deflang') ? ' active' : '' ) + '">' + tabs[i].title + '</a>';
						nav.append(el);
					}
					nav.append('<div class="clear" height=20></div>');

					$(".js-tabs-nav a", parent).click(function() {
						var th = $(this);
						$(".js-tab").hide();
						$( th.attr("href") ).show();

						//th.addClass("button-primary").siblings().removeClass("button-primary");
						th.addClass("nav-tab-active").siblings().removeClass("nav-tab-active");

						// hide 
						$(this).blur();

						var href = th.attr("href"); 
						if(href.indexOf("#mlwp_tab_lang_") != -1 ) {
							var new_lang = href.substring(15);
							if ( new_lang != '' ) {
								change_lang(new_lang);
							}
							else {
							}
						}

						return false;
					})
					//$(".js-tabs-nav a.button-primary", parent).click();
					$(".js-tabs-nav a.active", parent).click();
				}
			}

			function change_lang( lang ) {
				if ( qtrans_current_lang == '' ) {
					qtrans_full_title = $('#title').val();
					qtrans_full_excerpt = $('#excerpt').val();
					qtrans_full_content = $('#content').val();
				}

				if( lang != qtrans_current_lang ) {
					$('#title').val(qtrans_use(lang,qtrans_full_title));
					$('#excerpt').val(qtrans_use(lang,qtrans_full_excerpt));
					set_editor_content(qtrans_use(lang,qtrans_full_content));

					qtrans_current_lang = lang;
				}
			}

			$( "#title" ).change(function() {
				qtrans_full_title = qtrans_integrate(qtrans_current_lang,$(this).val(),qtrans_full_title);
			});

			$( "#excerpt" ).change(function() {
				qtrans_full_excerpt = qtrans_integrate(qtrans_current_lang,$(this).val(),qtrans_full_excerpt);
			});

			// this should be only change() 
			// but it seems that the is no way to detect when media editor 
			// calls qt.insertContent (which calls canvas.value=<bla>)
			$( "#content" ).blur(function() {
				qtrans_full_content = qtrans_integrate(qtrans_current_lang,$(this).val(),qtrans_full_content);
			});

			function get_editor_content(){
				if (jQuery("#wp-content-wrap").hasClass("tmce-active")){
					return tinyMCE.activeEditor.getContent();
				}else{
					return jQuery('#content').val();
				}
			}

			function set_editor_content(content){
				if (jQuery("#wp-content-wrap").hasClass("tmce-active")){
					tinyMCE.activeEditor.setContent(content);
				}else{
					jQuery('#content').val(content);
				}
			}

			// this does the magic that sets the input fields content to the translated content
			// todo category, tag, link cat., editor
			$( "#post" ).submit(function() {
				$.blockUI();
				$editors_cont.css( { 'visibility': 'hidden' } );
				$( "#poststuff" ).css( { 'visibility': 'hidden' } );

				$( "#title" ).val(qtrans_full_title);
				$( "#excerpt" ).val(qtrans_full_excerpt);
				set_editor_content(qtrans_full_content);
			});

			// Set-up word counting
/*
			// TODO fix this, doesn't work
			$('textarea.wp-editor-area', $editors_cont).each(function(){
				var th = $(this), last = false, parent_id = th.parents('.js-tab').attr('id');
				$(document).triggerHandler('wpcountwords', [ th.val(), undefined, parent_id ]);

				th.keyup( function(e) {
					var k = e.keyCode || e.charCode;

					if ( k == last )
						return true;

					if ( 13 == k || 8 == last || 46 == last )
						$(document).triggerHandler('wpcountwords', [ th.val(), undefined, parent_id ]);

					last = k;
					return true;
				});
			})

			$('#content').unbind('keyup');
			$(document).unbind( 'wpcountwords' );
			$(document).bind( 'wpcountwords', function(e, txt, type, parent) {
				wpWordCount.wc(txt, type, parent);
			});

			wpWordCount.block = {};

			// Override WP's wordcounting function
			wpWordCount.wc = function(tx, type, parent) {
				var parent = typeof parent == 'undefined' ? 'postdivrich' : parent;
				var t = this, w = $( '.word-count', $('#' + parent) ), tc = 0;

				if ( type === undefined )
					type = wordCountL10n.type;
				if ( type !== 'w' && type !== 'c' )
					type = 'w';

				if ( typeof t.block[ parent ] != 'undefined' && t.block[ parent ] )
					return;

				t.block[ parent ] = 1;

				setTimeout( function() {
					if ( tx ) {
						tx = tx.replace( t.settings.strip, ' ' ).replace( /&nbsp;|&#160;/gi, ' ' );
						tx = tx.replace( t.settings.clean, '' );
						tx.replace( t.settings[type], function(){tc++;} );
					}
					w.html(tc.toString());

					setTimeout( function() { t.block[ parent ] = 0; }, 2000 );
				}, 1 );
			}
*/

		};
	})

})(jQuery)