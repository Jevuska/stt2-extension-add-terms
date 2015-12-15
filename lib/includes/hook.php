<?php
/*
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 * @since 1.0
 *
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

/** 
 * hook action and filter template
 *
 * @since 1.0
 *
 * relocated hooks from
 * template-functions.php
 * metabox-form.php
 *
 * @since 1.1.0
 *
*/

add_action( 'init', 'stt2extat_load' );
add_action( 'stt2extat_create_admin_page', array( 'STT2EXTAT_Admin', 'init' ) );
add_action( 'widgets_init', 'stt2extat_widgets_init' );

add_action( 'parse_request', 'stt2extat_parse_request' );
add_filter( 'search_link', 'stt2extat_filter_search_link', 1, 2 );
add_filter( 'wp_search_stopwords', 'stt2extat_search_stopwords', 10, 2 );
add_filter( 'wp_title', 'stt2extat_filter_search_page_title', 10, 2 );
add_filter( 'get_the_excerpt', 'stt2exat_the_excerpt', 5, 3 );

add_action( 'stt2extat_edited_action', 'stt2extat_edited_action_callback', 10, 1 );
add_action( 'stt2extat_hint', 'stt2extat_hint_callback' );
add_action( 'stt2extat_thickbox', 'stt2extat_thickbox_callback' );
add_action( 'stt2extat_checkbox_google', 'stt2extat_checkbox_google_callback' );
add_action( 'stt2extat_checkbox_irrelevant', 'stt2extat_checkbox_irrelevant_callback' );
add_action( 'stt2extat_screenmeta', 'stt2extat_screenmeta_callback' );
add_action( 'stt2extat_metabox_content', 'stt2extat_metabox_content_callback' );
add_filter( 'stt2extat_footer', 'stt2extat_footer_callback' );

add_action( 'stt2extat_no_robots', 'stt2extat_no_robots' );
add_filter( 'stt2extat_data_args', 'stt2extat_data_args' );
add_filter( 'stt2extat_ignore_relevant', '__return_false' );

add_action( 'login_footer', 'stt2extat_clear_localstorage_on_logout' );

add_action( 'stt2extat_section_manual', 'stt2extat_post_wo_terms_button' );

#add_filter( 'stt2extat_is_single', 'is_single' );
add_filter( 'stt2extat_allow_localhost', '__return_true' );
#add_filter( 'stt2extat_term_count', 'stt2extat_count_posts', 1, 2 );
#add_action( 'wp_head', 'stt2extat_add_meta_origin_referer' );
#add_filter( 'stt2extat_allow_localhost', '__return_true' );
#remove_filter( 'stt2extat_ignore_relevant', '__return_false' );

if ( ! isset( $_SERVER['HTTP_X_MOZ'] ) || $_SERVER['HTTP_X_MOZ'] != 'prefetch' ) :
	add_action( 'stt2extat_update_post_meta', 'stt2extat_update_postmeta', 10, 8 );
	add_action( 'stt2extat_nopriv_update_post_meta', 'stt2extat_update_postmeta', 10, 8 );
endif;

add_action( 'wp_ajax_stt2extat_post_wo_terms', 'stt2extat_post_wo_terms_ajax' );
add_action( 'wp_ajax_stt2extat_template_content', 'stt2extat_template_content_ajax' );
add_action( 'wp_ajax_stt2extat_delete_term', 'stt2extat_delete_term_ajax', 10, 1 );
add_action( 'wp_ajax_stt2extat_search_post', 'stt2extat_search_post_ajax' );
add_action( 'wp_ajax_stt2extat_terms_list_post', 'stt2extat_terms_list_post_ajax' );
add_action( 'wp_ajax_stt2extat_list_all_terms', 'stt2extat_list_all_terms_ajax' );
add_action( 'wp_ajax_stt2extat_search_field', 'stt2extat_search_field_ajax' );
add_action( 'wp_ajax_stt2extat_search_relevant', 'stt2extat_search_relevant_ajax' );
add_action( 'wp_ajax_stt2extat_insert', 'stt2extat_insert_ajax' );
add_action( 'wp_ajax_stt2extat_update_count', 'stt2extat_update_count_ajax' );