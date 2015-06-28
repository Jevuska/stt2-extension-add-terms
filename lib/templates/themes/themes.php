<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function stt2extat_form_handler(){
     $nonce = $_REQUEST['stt2extat_nonce'];
     if ( ! wp_verify_nonce( $nonce, 'stt2extat_action')) 
	  wp_die( __( 'Olala... please try again.' ) );
     return stt2extat_theme_default($nonce);
	 wp_die();
}
?>
