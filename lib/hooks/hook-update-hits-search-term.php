<?php
/*
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 * @version 1.0
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

function update_meta_count_extat_callback()
{
	global $wpdb;
  
	$table      = $wpdb->prefix . 'stt2_meta';
	$nonce      = $_REQUEST['wpnonce'];
	$term       = $_POST['term'];
	$meta_count = $_POST['meta_count'];
  
	if ( ! wp_verify_nonce( $nonce, 'stt2extat_action' ) )
	{
		wp_die( sprintf( '<div id="message" class="error fade"><p><strong>%s</strong></p></div>', 
			__( 'Fail to update! Try to reload your page.', 'stt2extat' ) 
		) );
	}
  
	if ( wp_verify_nonce( $nonce, 'stt2extat_action' ) && check_admin_referer( 'stt2extat_action', 'wpnonce' ) )
	{
		$sql = "UPDATE $table SET meta_count = %d WHERE meta_value = %s";
		$results = $wpdb->query( $wpdb->prepare( $sql, 
			$meta_count, 
			$term 
		) );
		
		if( false !== $results )
			echo $term;
	
		$wpdb->flush();
	}
	wp_die();
}
add_action( 'wp_ajax_update_meta_count_extat', 'update_meta_count_extat_callback' );