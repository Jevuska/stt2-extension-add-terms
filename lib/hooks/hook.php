<?php
/*
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 * @version 1.0
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

add_action( 'plugins_loaded', 'stt2extat_loaded' );
add_action( 'wp_ajax_stt2extat_search_post', 'stt2extat_search_post_callback' );
add_action( 'wp_ajax_stt2extat_list_terms_post', 'stt2extat_list_terms_post_callback' );
add_action( 'wp_ajax_stt2extat_search_field', 'stt2extat_search_field_callback' );
add_action( 'wp_ajax_stt2extat_search_relevant', 'stt2extat_search_relevant_callback' );
add_action( 'wp_ajax_stt2extat_insert', 'stt2extat_insert_callback' );
add_action( 'wp_ajax_stt2extat_delete', 'stt2extat_delete_callback' );
add_action( 'wp_ajax_stt2extat_update_count', 'stt2extat_update_count_callback' );
add_action( 'wp_ajax_stt2extat_update_settings', 'stt2extat_update_settings_callback');
?>