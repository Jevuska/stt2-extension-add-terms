<?php
/*
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 * @version 1.0
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

function stt2extat_get_relevant_post( $id, $q, $ignore, $nonce )
{
	global $post;
	
	$searchterms = stt2extat_searchterms( $q );
	
	$args = array(
		'posts_per_page' => 1,
		'post_type'      => 'any',
		'post_status'    => 'publish',
		'p'              => $id,
		's'              => $q,
		'cache_results'  => false
	);
	$query = new WP_Query( $args );

	if ( ! wp_verify_nonce( $nonce, 'stt2extat_action' ) ) 
		wp_die( __( 'Olala... something wrong. Try again.', 'stt2extat' ) );
	
	if ( $query->have_posts() ) :
		while ( $query->have_posts() ) : $query->the_post();
			$results = stt2extat_search_term_exist_db( $searchterms, $nonce );
			
			$item    = array();
            if ( $results )
			{
                $post_id = $results->post_id;
                $respon  = '<span class="dashicons dashicons-flag"></span>%1$s<span><a href="%2$s" title="%3$s"><span class="existlink"></span></a></span>';
				$respons = sprintf( $respon,
					__( 'Relevant! ( Existed )', 'stt2extat' ),
					get_permalink( $post_id ),
					get_the_title( $post_id )
				);
            } 
			else if ( ! pk_stt2_is_contain_bad_words( $searchterms ) && 3 < strlen( $searchterms ) )
			{
               $respon  = '<span class="dashicons dashicons-yes"></span><span>%1$s</span>';
			   $respons = sprintf( $respon, 
				__( 'Relevant!', 'stt2extat' )
				);
            } 
			else 
			{
                $respons = 'badword';
            }
			
			$item['respons'] = $respons;
			$item['content'] = stt2exat_the_excerpt( $searchterms, $id );
            $item['title']   = $post->post_title;
			$item['post']    = $post->post_content;
			$result[]        = $item;
			
        endwhile;
        wp_reset_postdata();
    else :
		$item = array();
		if ( isset( $ignore ) && 1 == $ignore ) :
			$results = stt2extat_search_term_exist_db( $searchterms, $nonce );
		   
            if( $results && ! pk_stt2_is_contain_bad_words( $searchterms ) )
			{
				
                $post_id = $results->post_id;
                $respon  = '<span class="dashicons dashicons-flag"></span><span>%1$s<a href="%2$s" title="%3$s"><span class="existlink"></span></a></span>';
				$respons = sprintf( $respon,
					__( 'Irrelevant! ( Existed )', 'stt2extat' ),
					get_permalink( $post_id ),
					get_the_title( $post_id )
				);
				
				$_post           = get_post( $post_id ); 
			    $item['respons'] = $respons;
			    $item['content'] = stt2exat_the_excerpt( $searchterms, $postid );
                $item['title']   = $_post->post_title;
			    $item['post']    = $_post->post_content;
				
            }
			else if( ! pk_stt2_is_contain_bad_words( $searchterms ) && 3 < strlen( $searchterms ) )
			{
                $respons = '<span class="dashicons dashicons-no"></span><span>%1$s</span>';
				$respons = sprintf( $respons,
					__( 'Irrelevant!', 'stt2extat' )
				);
				$item['respons'] = $respons;
				$item['content'] = '';
				$item['title']   = '';
				$item['post']    = '';
				
            }
			else 
			{
                $respons         = 'badword';
				$item['respons'] = $respons;
				$item['content'] = '';
				$item['title']   = '';
				$item['post']    = '';
            }
			
           else:
		    $item['respons'] = '';
		   endif;
			$result[] = $item;
    endif;
	
    $response = json_encode( $result );
    return $response;
}

function stt2extat_recent_post_wp( $max, $q, $p, $nonce )
{
	$args = array(
		'numberposts' => $max,
		'post_type'   => 'any',
		'post_status' => 'publish',
	);
			  
	$recent_posts = wp_get_recent_posts( $args );
	$suggestions  = array();
	foreach( $recent_posts as $recent ) :
			$suggestion            = array();
			$suggestion['id']      = $recent['ID'];
			$suggestion['label']   = $recent['post_title'];
			$suggestion['value']   = get_permalink( $recent['ID'] );
			$suggestion['excerpt'] = wp_trim_words( $recent['post_content'], $num_words = 35, $more = null );
			$suggestion['content'] = $recent['post_content'];
			$suggestions[]         = $suggestion;
	endforeach;
	
	$response = json_encode( $suggestions );
    return $response;
	wp_die();
}

function stt2extat_searchterms( $query ) 
{
    $terms       = null;
    $query_terms = null;
    $query       = str_replace( "'", '', $query );
	$query_array = array();
    $query_array = preg_split( '/[\s,\+\.]+/', $query );
    $query_terms = implode( ' ', $query_array );
    $terms       = htmlspecialchars( urldecode( trim( $query_terms ) ) );
    return $terms;
}

function stt2extat_save_search_terms_db( $meta_value, $ID, $nonce ) 
{
    global $wpdb;
	
    if ( ! wp_verify_nonce( $nonce, 'stt2extat_action' ) ) 
		wp_die( __( 'Olala... something wrong. Try again.', 'stt2extat' ) );
	
	if ( 3 < strlen( $meta_value ) )
		$sql = $wpdb->query( $wpdb->prepare( "INSERT INTO " . $wpdb->prefix . "stt2_meta ( `post_id`,`meta_value`,`meta_count` ) VALUES ( %s, %s, 1 ) ON DUPLICATE KEY UPDATE `meta_count` = `meta_count` + 1", $ID, $meta_value ) );
	return $sql;
}

function stt2extat_get_search_terms_db( $max, $id, $nonce )
{
    global $wpdb;
	
    if ( ! wp_verify_nonce( $nonce, 'stt2extat_action' ) ) 
		wp_die( __( 'Olala... something wrong. Try again.', 'stt2extat' ) );
	
	$result = wp_cache_get( 'stt2_search_terms_' . $max );
	
	if ( false == $result ) :
		$result = $wpdb->get_results( "SELECT `meta_value`,`meta_count` FROM `" . $wpdb->prefix . "stt2_meta` WHERE `post_id` = $id ORDER BY `meta_count` DESC LIMIT ".$max.";" );
		wp_cache_set( 'stt2_search_terms_' . $max, $result, 900 );
	endif;
	
	return $result;
}

function stt2extat_search_term_exist_db( $term, $nonce )
{
    global $wpdb;
	
    $table   = $wpdb->prefix . 'stt2_meta'; 
	if ( ! wp_verify_nonce( $nonce, 'stt2extat_action' ) ) 
		wp_die( __( 'Olala... something wrong. Try again.', 'stt2extat' ) );
	
	$sql     = "SELECT post_id, meta_value, meta_count FROM $table WHERE meta_value = %s";
    $results = $wpdb->get_row(
		$wpdb->prepare( $sql, 
			$term
		)
	);
	
    return $results;
    $wpdb->flush();
    wp_die();
}

function stt2extat_get_searchterms_all_db()
{
    global $wpdb;
	$id    = $_POST['id'];
	$nonce = $_REQUEST['wpnonce'];

    if ( ! wp_verify_nonce( $nonce, 'stt2extat_action' ) ) 
		wp_die( __( 'Olala... something wrong. Try again.', 'stt2extat' ) );
	
	$searchterms = wp_cache_get( 'stt2extat_searchterms_all');
	if ( false == $searchterms )
	{
		$searchterms = $wpdb->get_results( "SELECT `meta_value`,`meta_count` FROM `" . $wpdb->prefix . "stt2_meta` WHERE `post_id` = $id ORDER BY `meta_count` DESC LIMIT 10, 18446744073709551615;" );
		wp_cache_set( 'stt2extat_searchterms_all', $searchterms, 900 );		
	}
	
	if( ! empty( $searchterms ) ) :
		$result = implode( "</span><span><a class='ntdelbutton'></a>&nbsp;", array_map(
			function( $searchterms2 )
			{
               $respons = "<i class='termlist' title='" . __( 'Increase Number', 'stt2extat' ) . "'>" . $searchterms2->meta_value . "</i> (<i class='termcnt' title='" . __( 'Decrease Number', 'stt2extat' ) . "'>" . $searchterms2->meta_count . "</i>)";
               return $respons;
			}, $searchterms
		) );
    
		printf( '<div class="stplus"><span><a class="ntdelbutton"></a>&nbsp;%s</span></div>',
			$result
		);
    endif;
	wp_die();
}
add_action( 'wp_ajax_stt2extat_get_searchterms_all', 'stt2extat_get_searchterms_all_db' );

function stt2extat_searchterms_count( $id )
{
	global $wpdb;
	$table   = $wpdb->prefix . 'stt2_meta'; 
	$results = wp_cache_get( 'stt2extat_searchterms_count' );
	
	if ( false == $results )
	{
		$sql = "SELECT COUNT(meta_value) FROM $table WHERE post_id = '%d'";
		$results = $wpdb->get_var( $wpdb->prepare( $sql, $id ) );
		$wpdb->flush();
		wp_cache_set( 'stt2extat_searchterms_count', $results, 900 );		
	}
	 return $results;
}

function stt2extat_search_terms_list( $id, $nonce )
{
    if ( ! wp_verify_nonce( $nonce, 'stt2extat_action' ) ) 
		wp_die( __( 'Olala... something wrong. Try again.', 'stt2extat' ) );
	
	$options         = get_option( 'pk_stt2_settings' );
	$searchterms     = stt2extat_get_search_terms_db( $options['max'], $id, $nonce );	
	$moresearchterms = stt2extat_searchterms_count( $id ) > $options['max'] ? '<span class="dashicons dashicons-plus-alt alltag"></span>' : '' ;
	
	if( ! empty( $searchterms ) ) :
		$result = implode( "</span><span><a class='ntdelbutton'></a>&nbsp;", array_map(
			function( $searchterms2 )
			{
               $respons = "<i class='termlist' title='" . __( 'Increase Number', 'stt2extat' ) . "'>" . $searchterms2->meta_value . "</i> (<i class='termcnt' title='" . __( 'Decrease Number', 'stt2extat' ) . "'>" . $searchterms2->meta_count . "</i>)";
               return $respons;
			}, $searchterms
		) );
		
        printf( '<span><span class="dashicons dashicons-tag"></span> %1$s</span><br><div class="tagchecklist"><span><a class="ntdelbutton"></a>&nbsp;%2$s</span>%3$s</div>',
			__( 'Incoming search terms:', 'stt2extat' ),
			$result,
			$moresearchterms
		);
    else:
    	printf( '<span class="error-message"><span class="dashicons dashicons-tag"></span> %1$s</span>',
			__( 'Incoming search terms: Empty!', 'stt2extat' )
		);
    endif;
}

function stt2extat_update_settings_searchterms( $nonce, $maxchar )
{
	$options                       = get_option( 'stt2extat_settings' );
	$options['stt2extat_max_char'] = $maxchar;
	
    if ( ! wp_verify_nonce( $nonce, 'stt2extat_action' ) ) 
		wp_die( __( 'Olala... something wrong. Try again.', 'stt2extat' ) );
	
    update_option( 'stt2extat_settings', $options );
}

function searchterms_tagging2_screen_id()
{
  return apply_filters( 'searchterms_tagging2_screen_id', SEARCHTERMS_TAGGING2_SCREEN_ID );
}
