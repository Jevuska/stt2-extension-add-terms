<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function stt2extat_get_relevant_post($id,$q,$ignore,$nonce){
global $post;
$searchterms = stt2extat_searchterms($q);
$args = array(
              'posts_per_page' => 1,
              'post_type'  => 'any',
              'post_status'  => 'publish',
              'p'  => $id,
              's'  => $q,
              'cache_results'  => false
              );
$query = new WP_Query( $args );

if (!wp_verify_nonce( $nonce, 'stt2extat_action' ) ) 
	wp_die(__('Olala... something wrong. Try again.'));
	
 if ( $query->have_posts() ) :
            while ( $query->have_posts() ) : $query->the_post();
            $results = stt2extat_search_term_exist_db($searchterms,$nonce);
			$item = array();
            if($results){
                $postid = $results->post_id;
                $respons = '<span class="dashicons dashicons-flag"></span><span>Relevant! (Existed)<a href="%1$s" title="%2$s"><span class="existlink"></span></a></span>';
				$respons = sprintf($respons,get_permalink($postid),get_the_title($postid));
            } else if(!pk_stt2_is_contain_bad_words( $searchterms ) && strlen($searchterms) > 3){
               $respons = '<span class="dashicons dashicons-yes"></span><span>%1$s</span>';
			   $respons = sprintf($respons,'Relevant!');
            } else {
                $respons = 'badword';
            }
			$excerpt = stt2exat_the_excerpt($searchterms,$id);
			$item ['respons']= $respons;
			$item ['content']= $excerpt;
            $item ['title']= $post->post_title;
			$item ['post']= $post->post_content;
			$result[] = $item;
        endwhile;
        wp_reset_postdata();
    else:
          $item = array();
          if ( isset($ignore) && $ignore == 1) :
           $results = stt2extat_search_term_exist_db($searchterms,$nonce);
		  
            if($results) {
                $postid = $results->post_id;
                $respons = '<span class="dashicons dashicons-flag"></span><span>%1$s<a href="%2$s" title="%3$s"><span class="existlink"></span></a></span>';
				$respons = sprintf($respons,'Irrelevant! (Existed)',get_permalink($postid),get_the_title($postid));
				$postpostid = get_post($postid); 
                $title = $post_7->post_title;
		        $excerpt = stt2exat_the_excerpt($searchterms,$postid);
			    $item ['respons']= $respons;
			    $item ['content']= $excerpt;
                $item ['title']= $postpostid->post_title;
			    $item ['post']= $postpostid->post_content;
            } else if(!pk_stt2_is_contain_bad_words( $searchterms ) && strlen($searchterms) > 3){
                $respons = '<span class="dashicons dashicons-no"></span><span>%1$s</span>';
				$respons = sprintf($respons,'Irrelevant!');
				$item ['respons']= $respons;
				$item ['content']= "";
				$item ['title']= "";
				$item ['post'] ="";
            } else {
                $respons = "badword";
				$item ['respons']= $respons;
				$item ['content']= "";
				$item ['title']= "";
				$item ['post'] ="";
            }
			
           else:
		    $item ['respons']= "";
		   endif;
			$result[] = $item;
    endif;
        $response = json_encode($result);
        return $response;
}

function stt2extat_recent_post_wp($max,$q,$p,$nonce){


$args = array(
              'numberposts' => $max,
			  'post_type' => 'any',
			  'post_status' => 'publish',
              );

			  
$recent_posts = wp_get_recent_posts( $args );
   $suggestions = array();
	foreach( $recent_posts as $recent ){
			$suggestion = array();
			$suggestion ['id']= $recent["ID"];
			$suggestion ['label']= $recent["post_title"];
			$suggestion ['value']= get_permalink($recent["ID"]);
			$suggestion ['excerpt']= wp_trim_words($recent["post_content"], $num_words = 35, $more = null );
			$suggestion ['content']= $recent["post_content"];
			$suggestions[] = $suggestion;
	}
	$response = json_encode($suggestions);
    return $response;
	wp_die();
}

function stt2extat_searchterms($query) {
    $terms       = null;
    $query_array = array();
    $query_terms = null;
    $query = str_replace("'", '', $query);
    $query = str_replace('"', '', $query);
    $query_array = preg_split('/[\s,\+\.]+/',$query);
    $query_terms = implode(' ', $query_array);
    $terms = htmlspecialchars(urldecode(trim($query_terms)));
    return $terms;
}

function stt2extat_save_search_terms_db( $meta_value,$ID,$nonce) {
    global $post,$wpdb;
	
    if (!wp_verify_nonce( $nonce, 'stt2extat_action' ) ) 
	wp_die(__('Olala... something wrong. Try again.'));
	
	if ( strlen($meta_value) > 3 )
		$success = $wpdb->query( $wpdb->prepare( "INSERT INTO ".$wpdb->prefix."stt2_meta ( `post_id`,`meta_value`,`meta_count` ) VALUES ( %s, %s, 1 ) ON DUPLICATE KEY UPDATE `meta_count` = `meta_count` + 1", $ID, $meta_value ) );
	return $success;
}

function stt2extat_get_search_terms_db($max,$id,$nonce){
    global $wpdb;
	
    if (!wp_verify_nonce( $nonce, 'stt2extat_action' ) ) 
	wp_die(__('Olala... something wrong. Try again.'));
	
	$result = wp_cache_get( 'stt2_search_terms_'.$max );
	if ( false == $result ) {
		$result = $wpdb->get_results( "SELECT `meta_value`,`meta_count` FROM `".$wpdb->prefix."stt2_meta` WHERE `post_id` = $id ORDER BY `meta_count` DESC LIMIT ".$max.";" );
		wp_cache_set( 'stt2_search_terms_'.$max, $result, 900 );		
	}	
	return $result;
}

function stt2extat_search_term_exist_db($term,$nonce){
    global $wpdb;
    $table = $wpdb->prefix . "stt2_meta"; 
	if (!wp_verify_nonce( $nonce, 'stt2extat_action' ) ) 
	wp_die(__('Olala... something wrong. Try again.'));
	
    $results = $wpdb->get_row( $wpdb->prepare( 
                                              " SELECT post_id,meta_value,meta_count FROM $table
                                                WHERE meta_value = '%s'
                                              ", 
                                                $term
                                              ) 
                              );
    return $results;
    $wpdb->flush();
    wp_die();
}

function stt2extat_get_searchterms_all_db(){
    global $wpdb;
	$id = $_POST['id'];
	$nonce = $_REQUEST['wpnonce'];

    if (!wp_verify_nonce( $nonce, 'stt2extat_action' ) ) 
	wp_die(__('Olala... something wrong. Try again.'));
	
	$searchterms = wp_cache_get( 'stt2extat_searchterms_all');
	if ( false == $searchterms ) {
		$searchterms = $wpdb->get_results( "SELECT `meta_value`,`meta_count` FROM `".$wpdb->prefix."stt2_meta` WHERE `post_id` = $id ORDER BY `meta_count` DESC LIMIT 10, 18446744073709551615;" );
		wp_cache_set( 'stt2extat_searchterms_all', $searchterms, 900 );		
	}	
	
	if(!empty( $searchterms )) :
	  $result = implode("</span><span><a class='ntdelbutton'></a>&nbsp;", array_map(function($searchterms2) {
               $respons = "<i class='termlist' title='Increase Number'>".$searchterms2->meta_value."</i> (<i class='termcnt' title='Decrease Number'>".$searchterms2->meta_count."</i>)";
               return $respons;
              }, $searchterms));
        printf('<div class="stplus"><span><a class="ntdelbutton"></a>&nbsp;%1$s</span></div>',$result, 'stt2extat');
    endif;
	wp_die();
}
add_action('wp_ajax_stt2extat_get_searchterms_all', 'stt2extat_get_searchterms_all_db');

function stt2extat_searchterms_count($id){
	global $wpdb;
	$table = $wpdb->prefix . "stt2_meta"; 
	$results = wp_cache_get( 'stt2extat_searchterms_count');
	if ( false == $searchterms ) {
		    $results = $wpdb->get_var( $wpdb->prepare( 
                                              " SELECT COUNT(meta_value) FROM $table
                                                WHERE post_id = '%d'
                                              ", 
                                                $id
                                              ) 
                              );
    $wpdb->flush();
		wp_cache_set( 'stt2extat_searchterms_count', $results, 900 );		
	}
	 return $results;
}

function stt2extat_search_terms_list($id,$nonce){
    if (!wp_verify_nonce( $nonce, 'stt2extat_action' ) ) 
	wp_die(__('Olala... something wrong. Try again.'));
	$options = get_option('pk_stt2_settings');
	$searchterms = stt2extat_get_search_terms_db( $options['max'],$id,$nonce);	
	$moresearchterms = stt2extat_searchterms_count($id) > $options['max'] ? '<span class="dashicons dashicons-plus-alt alltag"></span>' : '' ;	
	if(!empty( $searchterms )) :
	  $result = implode("</span><span><a class='ntdelbutton'></a>&nbsp;", array_map(function($searchterms2) {
               $respons = "<i class='termlist' title='Increase Number'>".$searchterms2->meta_value."</i> (<i class='termcnt' title='Decrease Number'>".$searchterms2->meta_count."</i>)";
               return $respons;
              }, $searchterms));
        printf('<span><span class="dashicons dashicons-tag"></span> %1$s</span><br><div class="tagchecklist"><span><a class="ntdelbutton"></a>&nbsp;%2$s</span>%3$s</div>',esc_html__('Incoming search terms:'),$result,$moresearchterms, 'stt2extat');
    else:
    	printf('<span class="error-message"><span class="dashicons dashicons-tag"></span> %1$s</span>',esc_html__('Incoming search terms: Empty!'), 'stt2extat');
    endif;
}

function stt2extat_update_settings_searchterms($nonce,$maxchar){
	$my_options = get_option('stt2extat_settings');
	$my_options['stt2extat_max_char'] = $maxchar;
    if (!wp_verify_nonce( $nonce, 'stt2extat_action' ) ) 
	wp_die(__('Olala... something wrong. Try again.'));
    update_option ('stt2extat_settings',$my_options);
}

function searchterms_tagging2_screen_id() {
  return apply_filters( 'searchterms_tagging2_screen_id',SEARCHTERMS_TAGGING2_SCREEN_ID  );
}
