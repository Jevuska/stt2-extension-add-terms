<?php
/*
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 * @version 1.0
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

function stt2extat_search_post_callback()
{
    global $post;
	
    $q     = $_POST['query'];
    $p     = url_to_postid( $q );
    $nonce = $_REQUEST['wpnonce'];
    $max   = $_REQUEST['max'];
	
	if ( is_numeric( $p ) && ( int ) $p > 0 ) :
		$args = array(
			'posts_per_page' => $max,
			'post_type'      => 'any',
			'post_status'    => 'publish',
			'p'              => $p,
			'cache_results'  => false
		);
	else:
	$args = array(
		'posts_per_page' => $max,
		'post_type'      => 'any',
		'post_status'    => 'publish',
		's'              => $q,
		'cache_results'  => false
	);
	endif;

    $query = new WP_Query( $args );

    if ( ! wp_verify_nonce( $nonce, 'stt2extat_action' ) )
		wp_die( __( 'Security check', 'stt2extat' ) );
	
    if ( wp_verify_nonce( $nonce, 'stt2extat_action' ) && check_admin_referer( 'stt2extat_action', 'wpnonce' ) )
	{
        $suggestions = array();
		if ( $query->have_posts() ) : 
			while ( $query->have_posts() ) : $query->the_post();
				$suggestion = array();
				$suggestion['id']      = $post->ID;
				$suggestion['label']   = $post->post_title;
				$suggestion['value']   = get_permalink( $post->ID );
				$suggestion['excerpt'] = wp_trim_words( $post->post_content, $num_words = 35, $more = null );
				$suggestion['content'] = do_shortcode( get_post_field( 'post_content', $post->ID ) );
				$suggestions[]         = $suggestion;
			endwhile;
			wp_reset_postdata();
		endif;
        $response = json_encode( $suggestions );
        echo $response;
	}
   wp_die();
}
add_action( 'wp_ajax_stt2extat_search_post', 'stt2extat_search_post_callback' );