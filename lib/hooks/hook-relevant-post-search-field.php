<?php
/*
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 * @version 1.0
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

function stt2extat_relevant_post_search_field_callback() {
  $nonce = $_REQUEST['wpnonce'];
  if (! wp_verify_nonce( $nonce, 'stt2extat_action' ) )
	wp_die();
  if (wp_verify_nonce( $nonce, 'stt2extat_action' ) && check_admin_referer( 'stt2extat_action', 'wpnonce' ) )
	return stt2extat_get_template_part('content', 'relevant-post-search-field');
}
add_action('wp_ajax_stt2extat_relevant_post_search_field','stt2extat_relevant_post_search_field_callback');