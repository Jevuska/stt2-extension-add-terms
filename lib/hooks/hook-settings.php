<?php
/*
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 * @version 1.0
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

function stt2extat_update_settings_callback(){
    $nonce = $_REQUEST['wpnonce'];
	$maxchar = $_POST['maxchar'];
    if ( ! wp_verify_nonce( $nonce, 'stt2extat_action' )) {
       wp_die( __( '<div id="message" class="error"><p>Fail to update! Try to reload your page.</p></div>' ) );
    }
	if (wp_verify_nonce( $nonce, 'stt2extat_action' )  && check_admin_referer( 'stt2extat_action', 'wpnonce' ) ) {
    stt2extat_update_settings_searchterms($nonce,$maxchar);
	}
    wp_die();
}
add_action('wp_ajax_stt2extat_update_setting', 'stt2extat_update_settings_callback');