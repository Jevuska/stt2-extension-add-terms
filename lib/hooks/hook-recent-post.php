<?php
if ( ! defined( 'ABSPATH' ) ) exit;
function stt2extat_recent_post_callback(){
$nonce = $_REQUEST['wpnonce'];
$max = $_REQUEST['max'];
$q = $_POST["query"];
$p = url_to_postid($q);

$nonce = $_REQUEST['wpnonce'];

    if ( ! wp_verify_nonce( $nonce, 'stt2extat_action' ))
		wp_die();
	
	if (wp_verify_nonce( $nonce, 'stt2extat_action' )  && check_admin_referer( 'stt2extat_action', 'wpnonce' ) ) 
		echo stt2extat_recent_post_wp($max,$q,$p,$nonce);
		wp_die();
}
add_action('wp_ajax_stt2extat_recent_post', 'stt2extat_recent_post_callback');