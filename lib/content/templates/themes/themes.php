<?php
/*
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 * @since 1.0
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

/**
 * handling function via wp ajax to show form of this plugin
 *
 * @since 1.0
 *
*/
function stt2extat_form_handler()
{
	if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'heartbeat-nonce' ) ) 
		wp_die( '-1' );
	
	return stt2extat_theme_default();
	wp_die();
}
?>
