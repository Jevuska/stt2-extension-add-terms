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
	
	if ( ! wp_verify_nonce( $_REQUEST['wpnonce'], 'stt2extat_action' ) )
		wp_die( wp_json_encode( array( __( 'Security check', 'stt2extat' ) ) ) );

	if ( ! function_exists( 'pk_stt2_admin_menu_hook' ) )
		wp_die( wp_json_encode( array( __( 'Plugin SEO SearchTerms Tagging 2 not active', 'stt2extat' ) ) ) );
	  
    $q     = sanitize_text_field( $_POST['query'] );
    $p     = url_to_postid( esc_url( $q ) );
    $max   = intval( $_POST['max'] );
	
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
	
    if ( check_admin_referer( 'stt2extat_action', 'wpnonce' ) )
	{
        $suggestions = array();
		if ( $query->have_posts() ) : 
			while ( $query->have_posts() ) : $query->the_post();
				$suggestion = array();
				$suggestion['id']      = $post->ID;
				$suggestion['label']   = $post->post_title;
				$suggestion['value']   = get_permalink( $post->ID );
				$suggestion['excerpt'] = wp_trim_words( $post->post_content, $num_words = (int) 35, $more = null );
				$suggestion['content'] = do_shortcode( get_post_field( 'post_content', $post->ID ) );
				$suggestions[]         = $suggestion;
			endwhile;
			wp_reset_postdata();
		endif;
        echo wp_json_encode( $suggestions );
	}
   wp_die();
}

function stt2extat_list_terms_post_callback()
{
	global $wpdb;
	
	if ( ! wp_verify_nonce( $_REQUEST['wpnonce'], 'stt2extat_action' ) ) 
       wp_die( __( 'Security check', 'stt2extat' ) );

    if ( check_admin_referer( 'stt2extat_action', 'wpnonce' ) && isset( $_POST['id'] ) ) :
	
		$post_id     = absint( $_POST['id'] );
		$opt         = get_option( 'pk_stt2_settings' );
		$max         = intval( $opt['max'] );
		$searchterms = wp_cache_get( 'stt2_search_terms_' . $post_id, 'listterms' );
		
		if ( false == $searchterms ) :
			$sql   = "
					 SELECT meta_value, meta_count
					 FROM {$wpdb->prefix}stt2_meta
					 WHERE post_id = %d
					 ORDER BY meta_count
					 DESC LIMIT %d
					 ";
			$sql   = $wpdb->prepare( $sql, $post_id, $max );
			$searchterms = $wpdb->get_results( $sql );
			wp_cache_set( 'stt2_search_terms_' . $max, $searchterms, 'listterms', 300  );
		endif;
		
		if( false == $searchterms ) :
			printf( '<span class="error-message"><span class="dashicons dashicons-tag"></span> %s</span>',
				__( 'Incoming search terms: Empty!', 'stt2extat' )
			);
		else :
			$result = implode( "</span><span><a class='ntdelbutton'></a>&nbsp;", array_map(
				function( $searchterms2 )
				{
				   $respons = sprintf( '<i class="termlist" title="%1$s">%2$s</i> (<i class="termcnt" title="%3$s">%4$s</i>)',
						__( 'Increase Number', 'stt2extat' ),
						$searchterms2->meta_value,
						__( 'Decrease Number', 'stt2extat' ),
						$searchterms2->meta_count
				   );
				   return $respons;
				}, $searchterms
			) );
			
			$more = ( stt2extat_searchterms_count( $post_id ) > $max ) ? '<span class="dashicons dashicons-plus-alt alltag"></span>' : '' ;
			
			printf( '<span><span class="dashicons dashicons-tag"></span> %1$s</span><br><div class="tagchecklist"><span><a class="ntdelbutton"></a>&nbsp;%2$s</span>%3$s</div>',
				__( 'Incoming search terms:', 'stt2extat' ),
				$result,
				$more
			);
		endif;
	endif;
	wp_die();
}

function stt2extat_search_field_callback()
{
	if ( ! wp_verify_nonce( $_REQUEST['wpnonce'], 'stt2extat_action' ) )
		wp_die();

	if ( check_admin_referer( 'stt2extat_action', 'wpnonce' ) )
		return stt2extat_get_template_part( 'content', 'relevant-post-search-field' );
}

function stt2extat_search_relevant_callback()
{
	if ( ! wp_verify_nonce( $_REQUEST['wpnonce'], 'stt2extat_action' ) )
		wp_die( __( 'Security check', 'stt2extat' ) );
	
	if ( ! function_exists( 'pk_stt2_admin_menu_hook' ) )
		wp_die( wp_json_encode( array( 'respons' => 'stt2', 'content' => array( __( 'Plugin SEO SearchTerms Tagging 2 not active', 'stt2extat' ) ) ) ) );
		
    if ( check_admin_referer( 'stt2extat_action', 'wpnonce' ) ) :
		$post_id = absint( $_POST['id'] );
		$q       = sanitize_text_field( $_POST['q'] );
		$q       = urlencode( $q );
		$ignore  = wp_validate_boolean( $_POST['ignore'] );
		$nonce   = wp_create_nonce( 'stt2extat_insert' );
        echo stt2extat_get_relevant_post( $post_id, $q, $ignore, $nonce );
	endif;
	wp_die();
}

function stt2extat_insert_callback()
{
	global $post;

	if ( ! wp_verify_nonce( $_REQUEST['wpnonce'], 'stt2extat_action' ) )
       wp_die( '1' );
	
	if ( ! function_exists( 'pk_stt2_admin_menu_hook' ) )
		wp_die( __( 'Plugin SEO SearchTerms Tagging 2 not active', 'stt2extat' ) );
		
    if ( check_admin_referer( 'stt2extat_action', 'wpnonce' ) && isset( $_POST['terms'] ) && '' != $_POST['terms'] )
	{
		$nonce            = wp_create_nonce( 'stt2extat_insert' );
		$result           = '';
        $list_terms_array = '';
		
		$terms       = esc_textarea( $_POST['terms'] );
		$post_id     = absint( $_POST['postid'] );
		$ignore      = wp_validate_boolean( $_POST['ignore'] );
        $terms_array = explode( ',', $terms );
		
		$exist_term_array = array();
		$badwords_array   = array();
		$irrelevant_array = array();
		$short_term_array = array();
		
        foreach ( $terms_array as $q )
		{
			$q = stt2extat_sanitize( $q );
			
			if ( ! empty( $q ) )
			{
				$term_exist  = stt2extat_search_term_exist_db( $q, $nonce );
				
				$relevant    = stt2extat_get_relevant_post( $post_id, $q, $ignore, $nonce );
				$opt         = json_decode( $relevant, true );
				
				$respons     = $opt['respons'];
				
				if ( null !== $term_exist )
				{
					$post_id     = $term_exist->post_id;
					$meta_count  = $term_exist->meta_count;
				
					stt2extat_save_search_terms_db( $q, $post_id, $nonce );
					
					$list_terms_array .= sprintf( '<div id="message" class="notice notice-warning fade notice is-dismissible"><p style="margin: .5em 0"><kbd>%1$s</kbd> %2$s <kbd class="permalink">%3$s</kbd> <a target="_blank" href="%3$s" title="%4$s"><i class="dashicons dashicons-external"></i></a>. %5$s: <kbd>%6$s</kbd></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">%7$s</span></button></div>',
						$q,
						__( 'already exist in', 'stt2extat' ),
						get_permalink( $post_id ),
						get_the_title( $post_id ),
						__( 'Hits', 'stt2extat' ),
						intval( $meta_count ),
						__( 'Dismiss this notice.', 'stt2extat' )
					);
					
				}
				else if ( ! pk_stt2_is_contain_bad_words( $q ) && 3 < strlen( $q ) && ( ! empty( $respons ) ) )
				{
					
					stt2extat_save_search_terms_db( $q, $post_id, $nonce );
					$exist_term_array[] = $q;
				}
				else if ( pk_stt2_is_contain_bad_words( $q ) )
				{
					$badwords_array[]  = $q;
				} 
				else if ( empty( $respons ) )
				{
					$irrelevant_array[] = $q;
				} 
				else 
				{
					$short_term_array[] = $q;
				}
			}
            unset( $q );
	    }

		if ( array_filter( $exist_term_array ) )
		{
			$result   .= sprintf( '<div id="message" class="updated fade notice is-dismissible"><p><kbd>%1$s</kbd> has been added into <kbd class="permalink">%2$s</kbd> <a target="_blank" href="%2$s" title="%3$s"><i class="dashicons dashicons-external"></i></a></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">%4$s</span></button></div>',
				implode( '</kbd><kbd>', $exist_term_array ),
				get_permalink( $post_id ),
				get_the_title( $post_id ),
				__( 'Dismiss this notice.', 'stt2extat' )
			);
		}
		
		$result .= $list_terms_array;

		if ( array_filter( $badwords_array ) )
		{
			$result .= sprintf( '<div id="message" class="error fade notice is-dismissible"><p><kbd>%1$s</kbd>%2$s</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">%3$s</span></button></div>',
				implode( '</kbd><kbd>', $badwords_array ),
				__( 'contain badword(s), can not be added!.', 'stt2extat' ),
				__( 'Dismiss this notice.', 'stt2extat' )
			);
		}

		if ( array_filter( $irrelevant_array ) )
		{
			$result .= sprintf( '<div id="message" class="error fade notice is-dismissible"><p><kbd>%1$s</kbd> %2$s</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">%3$s</span></button></div>',
				implode( '</kbd><kbd>', $irrelevant_array ),
				__( 'irrelevant, can not be added!.', 'stt2extat' ),
				__( 'Dismiss this notice.', 'stt2extat' )
			);
		}

		if( array_filter( $short_term_array ) )
		{
			$result .= sprintf( '<div id="message" class="error fade notice is-dismissible"><p><kbd>%1$s</kbd> %2$s</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">%3$s</span></button></div>',
				implode( '</kbd><kbd>', $short_term_array ),
				__( 'too short, can not be added!.', 'stt2extat' ),
				__( 'Dismiss this notice.', 'stt2extat' )
			);
		}
		print $result;
	}
	wp_die(); 
}

function stt2extat_delete_callback()
{
	if ( ! wp_verify_nonce( $_REQUEST['wpnonce'], 'stt2extat_action' ) )
	{
		wp_die( sprintf( '<div id="message" class="error"><p>%s</p></div>', 
			__( 'Fail to delete! Try to reload your page.', 'stt2extat' ) 
		) );
    }
	
	if ( check_admin_referer( 'stt2extat_action', 'wpnonce' ) )
	{
		pk_stt2_admin_delete_searchterms();
	}
    wp_die();
}

function stt2extat_update_count_callback()
{
	if ( ! wp_verify_nonce( $_REQUEST['wpnonce'], 'stt2extat_action' ) )
	{
		wp_die( sprintf( '<div id="message" class="error fade"><p><strong>%s</strong></p></div>', 
			__( 'Fail to update! Try to reload your page.', 'stt2extat' ) 
		) );
	}
	
	$term       = sanitize_text_field( $_POST['term'] );
	
	if ( check_admin_referer( 'stt2extat_action', 'wpnonce' ) && isset( $_POST['term'] ) && '' != $term && isset( $_POST['meta_count'] ) )
	{
		global $wpdb;
		
		$count = intval( $_POST['meta_count'] );
		$term  = $wpdb->esc_like( $term );
		$sql   = "
				 UPDATE {$wpdb->prefix}stt2_meta
				 SET meta_count = %d
				 WHERE meta_value LIKE %s
				 ";
		$sql   = $wpdb->prepare( $sql, $count, $term );
		$count = $wpdb->query( $sql );
		if( $count )
			echo $count;
		$wpdb->flush();
	}
	wp_die();
}

function stt2extat_update_settings_callback()
{
	if ( ! wp_verify_nonce( $_REQUEST['wpnonce'], 'stt2extat_action' ) ) 
		wp_die( sprintf( '<div id="message" class="error"><p>%s</p></div>', 
			__( 'Fail to update! Try to reload your page.', 'stt2extat' ) 
		) );

	if ( check_admin_referer( 'stt2extat_action', 'wpnonce' ) && isset( $_POST['maxchar'] ) )
	{
		if ( get_option( 'stt2extat_settings' ) ) :
			$options             = get_option( 'stt2extat_settings' );
			$options['max_char'] = intval( $_POST['maxchar'] );
			update_option( 'stt2extat_settings', $options );
		endif;
	}
    wp_die();
}