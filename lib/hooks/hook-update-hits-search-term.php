<?php
if ( ! defined( 'ABSPATH' ) ) exit;
function update_meta_count_extat_callback(){
  global $wpdb;
  $table = $wpdb->prefix . "stt2_meta";
  $nonce = $_REQUEST['wpnonce'];
  $term = $_POST['term'];
  $meta_count = $_POST['meta_count'];
  if ( ! wp_verify_nonce( $nonce, 'stt2extat_action' ) ) {
       wp_die( __( '<div id="message" class="error fade"><p><strong>Fail to update!</strong></p></div>' ) );
  }
  if (wp_verify_nonce( $nonce, 'stt2extat_action' ) && check_admin_referer( 'stt2extat_action', 'wpnonce' ) ) {
    $query = "UPDATE $table SET meta_count = %d WHERE meta_value = %s";
    $results = $wpdb->query($wpdb->prepare( $query, $meta_count, $term ) );
    if(false !== $result) 
    echo $term;
    $wpdb->flush();
  }
    wp_die();
}
add_action('wp_ajax_update_meta_count_extat', 'update_meta_count_extat_callback');