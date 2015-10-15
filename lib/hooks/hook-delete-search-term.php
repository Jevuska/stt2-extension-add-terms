<?php
/*
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 * @version 1.0
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

function pk_stt2_admin_delete_searchterms_extat_callback()
{
    $nonce = $_REQUEST['wpnonce'];
    if ( ! wp_verify_nonce( $nonce, 'stt2extat_action' ) )
	{
		$msg  = sprintf( '<div id="message" class="error"><p>%s</p></div>', 
			__( 'Fail to delete! Try to reload your page.', 'stt2extat' ) 
		);
		wp_die( $msg );
    }
	if ( wp_verify_nonce( $nonce, 'stt2extat_action' )  && check_admin_referer( 'stt2extat_action', 'wpnonce' ) )
	{
		pk_stt2_admin_delete_searchterms();
	}
    wp_die();
}
add_action( 'wp_ajax_pk_stt2_admin_delete_searchterms', 'pk_stt2_admin_delete_searchterms_extat_callback' );