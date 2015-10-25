<?php
/*
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 * @version 1.0
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

function stt2extat_form_handler()
{
	if ( ! wp_verify_nonce( $_POST['stt2extat_nonce'], 'stt2extat_action' ) ) 
		wp_die( __( 'Olala... something wrong. Try again.', 'stt2extat' ) );
	
	return stt2extat_theme_default();
	wp_die();
}
?>
