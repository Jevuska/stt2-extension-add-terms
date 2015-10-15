<?php
/*
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 * @version 1.0
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

add_action( 'wp_ajax_stt2extat_search_relevant_post', 'stt2extat_search_relevant_post_callback' );
function stt2extat_search_relevant_post_callback()
{
	$id      = $_POST['id'];
    $query   = trim( $_POST['query'] );
    $query   = str_replace( ',', '', $query );
    $q       = urlencode( $query );
	$ignore  = $_POST['ignore'];
    $nonce   = $_REQUEST['wpnonce'];
	
    if ( ! wp_verify_nonce( $nonce, 'stt2extat_action' ) )
        wp_die( __( 'Security check', 'stt2extat' ) );
	
    if ( wp_verify_nonce( $nonce, 'stt2extat_action' ) && check_admin_referer( 'stt2extat_action', 'wpnonce' ) )
        echo stt2extat_get_relevant_post( $id, $q, $ignore, $nonce );
	
	wp_die();
}