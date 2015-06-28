<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function stt2extat_search_relevant_post_callback(){
    $id = $_POST["id"];
    $queries = trim($_POST["query"]);
    $ignore = $_POST["ignore"];
    $queries = str_replace(",","",$queries);
    $q = urlencode($queries);
    $nonce = $_REQUEST['wpnonce'];
    if (! wp_verify_nonce( $nonce, 'stt2extat_action' ) )
        wp_die( __( 'Security check' ) );
    if (wp_verify_nonce( $nonce, 'stt2extat_action' ) && check_admin_referer( 'stt2extat_action', 'wpnonce' ) )
        echo stt2extat_get_relevant_post($id,$q,$ignore,$nonce);
	wp_die();
}
add_action('wp_ajax_stt2extat_search_relevant_post', 'stt2extat_search_relevant_post_callback');