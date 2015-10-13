<?php
/*
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 * @version 1.0
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

function stt2extat_insert_searchterm_callback() {
    global $post;
    $terms = $_POST["terms"];
    $ID = $_POST["postid"];
    $ignore = $_POST["ignore"];
    $nonce = $_REQUEST['wpnonce'];

    if ( ! wp_verify_nonce( $nonce, 'stt2extat_action' ) ) {
       wp_die( __( '1' ) );
    }

    if (wp_verify_nonce( $nonce, 'stt2extat_action' )  && check_admin_referer( 'stt2extat_action', 'wpnonce' ) ) {
        $arr_searchterms = explode(',',$terms);
        $listtermsArr1 = "";$listtermsArr2 = array();$listtermsArr3 = array();$listtermsArr4 = array();$listtermsArr5 = array();

        foreach ($arr_searchterms as $term){
		         if( !empty($term) ){
                  $searchterms = stt2extat_searchterms($term);
                  $termexist =  stt2extat_search_term_exist_db($searchterms,$nonce);
                  $post_id = $termexist->post_id;
                  $meta_value = $termexist->meta_value;
                  $meta_count = $termexist->meta_count;
                  $relevant = stt2extat_get_relevant_post($ID,$searchterms,$ignore,$nonce);
				  $opt = json_decode($relevant,true);
                  $respons = $opt[0]['respons'];
				  if (!empty($meta_value)) {
				       stt2extat_save_search_terms_db( $meta_value, $post_id, $nonce );
                       $listtermsArr1 .= '<div id="message" class="notice notice-warning fade notice is-dismissible"><p style="margin: .5em 0"><kbd>'.$searchterms.'</kbd> already exist in <kbd class="permalink">'.get_permalink($post_id).'</kbd> <a target="_blank" href="'.get_permalink($post_id).'" title="'.get_the_title($post_id).'"><i class="dashicons dashicons-external"></i></a>. Search Count: <kbd>'.$meta_count.'</kbd></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
			            }else if (!pk_stt2_is_contain_bad_words( $searchterms ) && strlen($searchterms) > 3 && (!empty($respons)) ) {
				               stt2extat_save_search_terms_db( $searchterms, $ID, $nonce );
				               $listtermsArr2[] = $searchterms;
			            }else if(pk_stt2_is_contain_bad_words( $searchterms )){
                       $listtermsArr3[] = $searchterms;
			            }else if(empty($respons)){
                       $listtermsArr4[] = $searchterms;
			            } else {
                       $listtermsArr5[] = $searchterms;
                    }
		          }
            unset($term);
	    }

        $searchtermlist = implode("</kbd><kbd>",$listtermsArr2);
        $badwordstermlist = implode("</kbd><kbd>",$listtermsArr3);
        $irrelevantermlist = implode("</kbd><kbd>",$listtermsArr4);
        $shortermlist = implode("</kbd><kbd>",$listtermsArr5);
        $permalink = get_permalink($ID);
        $thetitle = get_the_title($ID);

if(!empty($searchtermlist)){
      $result = '<div id="message" class="updated fade notice is-dismissible"><p><kbd>'.$searchtermlist.'</kbd> has been added into <kbd class="permalink">'.$permalink.'</kbd> <a target="_blank" href="'.$permalink.'" title="'.$thetitle.'"><i class="dashicons dashicons-external"></i></a></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
}
$result .= $listtermsArr1;

if(!empty($badwordstermlist)){
      $result .= '<div id="message" class="error fade notice is-dismissible"><p><kbd>'.$badwordstermlist.'</kbd> contain badword(s), can not be added!.</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
}

if(!empty($irrelevantermlist)){
      $result .= '<div id="message" class="error fade notice is-dismissible"><p><kbd>'.$irrelevantermlist.'</kbd> irrelevant, can not be added!.</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
}

if(!empty($shortermlist)){
      $result .= '<div id="message" class="error fade notice is-dismissible"><p><kbd>'.$shortermlist.'</kbd> too short, can not be added!.</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
}
  echo $result;
  wp_die(); 
}
}
add_action('wp_ajax_stt2extat_insert_searchterm', 'stt2extat_insert_searchterm_callback');