<?php
/*
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 * @version 1.0
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

function stt2extat_get_relevant_post( $post_id, $q, $ignore, $nonce )
{
	global $post;
	
	if ( ! wp_verify_nonce( $nonce, 'stt2extat_insert' ) ) 
		wp_die( __( 'Olala... something wrong. Try again.', 'stt2extat' ) );
	
	$item = array();

	$args = array(
		'posts_per_page' => 1,
		'post_type'      => 'any',
		'post_status'    => 'publish',
		'p'              => absint( $post_id ),
		's'              => stt2extat_sanitize( $q ),
		'cache_results'  => false
	);
	
	$query = new WP_Query( $args );
	
	$searchterms = stt2extat_sanitize( $q );
	if ( $query->have_posts() ) :
		while ( $query->have_posts() ) : $query->the_post();
			$term_exist  = stt2extat_search_term_exist_db( $searchterms, $nonce );
            if ( null !== $term_exist )
			{
                $post_id = $term_exist->post_id;
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
			$item['content'] = stt2exat_the_excerpt( $searchterms, absint( $post_id ) );
            $item['title']   = $post->post_title;
			$item['post']    = $post->post_content;
        endwhile;
        wp_reset_postdata();
    else :
		if ( $ignore ) :
			$term_exist  = stt2extat_search_term_exist_db( $searchterms, $nonce );
            if( null !== $term_exist && ! pk_stt2_is_contain_bad_words( $searchterms ) )
			{
				$_post_id = $term_exist->post_id;
                $respon  = '<span class="dashicons dashicons-flag"></span><span>%1$s<a href="%2$s#TB_iframe=true&width=600&height=550" title="%3$s" class="thickbox"><span class="existlink"></span></a></span>';
				$respons = sprintf( $respon,
					__( 'Irrelevant! ( Existed )', 'stt2extat' ),
					get_permalink( $_post_id ),
					get_the_title( $_post_id )
				);
				
				$_post           = get_post( $post_id ); 
			    $item['respons'] = $respons;
			    $item['content'] = stt2exat_the_excerpt( $searchterms, $_post->ID );
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
			$item['content'] = '';
			$item['title']   = '';
			$item['post']    = '';
		endif;
    endif;
    return wp_json_encode( $item );
}

function stt2extat_sanitize( $q ) 
{
    $terms       = null;
    $query_terms = null;
    $q           = str_replace( "'", '', $q );
	$query_array = array();
    $query_array = preg_split( '/[\s,\+\.]+/', $q );
    $query_terms = implode( ' ', $query_array );
    $terms       = htmlspecialchars( urldecode( trim( $query_terms ) ) );
	$terms       = sanitize_text_field( $terms );
    return $terms;
}

function stt2extat_save_search_terms_db( $q, $post_id, $nonce ) 
{
	global $wpdb;
	
	if ( ! wp_verify_nonce( $nonce, 'stt2extat_insert' ) ) 
		wp_die( __( 'Olala... something wrong. Try again.', 'stt2extat' ) );

	$q = stt2extat_sanitize( $q );
	$result = false;
	if ( 3 < strlen( $q  ) ) :
		$q  	 = $wpdb->esc_like( $q );
		$post_id = absint( $post_id );
		$sql     = "
				   INSERT INTO {$wpdb->prefix}stt2_meta ( post_id, meta_value, meta_count )
				   VALUES ( %d, %s, 1 )
				   ON DUPLICATE KEY UPDATE meta_count = meta_count + 1
				   ";
		$sql     = $wpdb->prepare( $sql, $post_id, $q );
		$result  = $wpdb->query( $sql );
	endif;
	return $result;
}

function stt2extat_search_term_exist_db( $term, $nonce )
{
    global $wpdb;
	
	if ( ! wp_verify_nonce( $nonce, 'stt2extat_insert' ) ) 
		wp_die( __( 'Olala... something wrong. Try again.', 'stt2extat' ) );

	$term  = stt2extat_sanitize( $term );
	$term  = $wpdb->esc_like( $term );
	$sql   = "
			 SELECT post_id, meta_value, meta_count
			 FROM {$wpdb->prefix}stt2_meta
			 WHERE meta_value
			 LIKE %s
			 ";
	$sql   = $wpdb->prepare( $sql, $term );
    $exist = $wpdb->get_row( $sql );
	return $exist;
    $wpdb->flush();
    wp_die();
}

function stt2extat_get_searchterms_all_db()
{
    global $wpdb;
	
	if ( ! wp_verify_nonce( $_REQUEST['wpnonce'], 'stt2extat_action' ) ) 
		wp_die( __( 'Olala... something wrong. Try again.', 'stt2extat' ) );
	
	$searchterms = wp_cache_get( 'stt2extat_searchterms_all');
	
	if ( false == $searchterms )
	{
		$id    = absint( $_POST['id'] );
		$sql   = "
				 SELECT meta_value, meta_count
				 FROM {$wpdb->prefix}stt2_meta
				 WHERE post_id = %d
				 ORDER BY meta_count
				 DESC LIMIT 10, 18446744073709551615;
				 ";
		$sql   = $wpdb->prepare( $sql, $id );
		$searchterms = $wpdb->get_results( $sql );
		wp_cache_set( 'stt2extat_searchterms_all', $searchterms, 900 );		
	}
	
	if ( ! empty( $searchterms ) ) :
		$result = implode( "</span><span><a class='ntdelbutton'></a>&nbsp;", array_map(
			function( $obj )
			{
				$output = sprintf( '<i class="termlist" title="%1$s">%2$s</i> (<i class="termcnt" title="%3$s">%4$s</i>)',
					__( 'Increase Number', 'stt2extat' ),
					$obj->meta_value,
					__( 'Decrease Number', 'stt2extat' ),
					$obj->meta_count
			   );
               return $output;
			}, $searchterms
		) );
    
		printf ( '<div class="stplus"><span><a class="ntdelbutton"></a>&nbsp;%s</span></div>',
			$result
		);
    endif;
	wp_die();
}
add_action( 'wp_ajax_stt2extat_get_searchterms_all', 'stt2extat_get_searchterms_all_db' );

function stt2extat_searchterms_count( $post_id )
{
	global $wpdb;
	$count = wp_cache_get( 'stt2extat_searchterms_count' );
	if ( false == $count )
	{
		$sql   = "
				 SELECT COUNT(meta_value)
				 FROM {$wpdb->prefix}stt2_meta
				 WHERE post_id = '%d'
				 ";
		$sql   = $wpdb->prepare( $sql, absint( $post_id ) );
		$count = $wpdb->get_var( $sql );
		$wpdb->flush();
		wp_cache_set( 'stt2extat_searchterms_count', $count, 900 );		
	}
	return (int) $count;
}

function searchterms_tagging2_screen_id()
{
  return apply_filters( 'searchterms_tagging2_screen_id', SEARCHTERMS_TAGGING2_SCREEN_ID );
}
