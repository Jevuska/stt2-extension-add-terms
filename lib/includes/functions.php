<?php
/*
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 * @version 1.1
 */

if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

/**
 * update terms via update_post_meta
 * 
 * @since 1.1
 *
 */
function stt2extat_update_postmeta( $q, $post_id = null, $old_term = null, $last_id = 0, $data = array(), $current_id = null, $hits = null, $post_modified = null )
{
	$q = stt2extat_filter_text( $q );
	
	if ( isset( $q['error'] ) )
		return false;
	
	if ( '' == $q )
		return ( int ) 6;
	
	$meta_key   = '_stt2extat';
	
	if ( is_admin() && 'stt2extat_nopriv_update_post_meta' != current_action() ) :
		if ( ! current_user_can( 'manage_options' ) )
			wp_die( __( 'Cheatin&#8217; uh?' ), 403 );
			
		if ( 'wp_ajax_stt2extat_insert' == current_action() ) :
			if (  ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'heartbeat-nonce' ) ) :
				wp_die( '1' );
			endif;
		else :
		
		endif;
	endif;
	
	if ( null == $post_id )
		$post_id = get_the_ID();
	
	if ( 0 == $post_id || ! is_string( get_post_status( ( int ) $post_id ) ) )
		return false;
	
	if ( 4 > mb_strlen( $q ) )
		return ( int ) 12;
	
	if ( 70 < mb_strlen( $q ) )
		return ( int ) 13;
	
	$prev_value = stt2extat_get_post_terms( $post_id );
	$prev_value = ( '' != $prev_value && is_array( $prev_value ) ) ? $prev_value : array();
	$meta_value = array();
	
	if ( isset( $data[ $q ] ) && is_object( $data[ $q ] ) ) :
		
		if ( null != $old_term )
			return ( int ) 7;
		
		$post_id    = $data[ $q ]->post_id;
		$prev_value = stt2extat_get_post_terms( $post_id );
		$meta_value = $prev_value;
		
		if ( isset( $prev_value[ $q ] ) && null != $hits && null != $post_modified )
			return true;
		
		if ( isset( $prev_value[ $q ] ) ) :
			
			// interval time between hit
			$last_hours = apply_filters( 'stt2extat_interval_hit_time', strtotime( '-1 hours' ) );
			
			if ( isset( $prev_value[ $q ][2] ) && $prev_value[ $q ][2] > $last_hours )
				return false;
			
			$meta_value[ $q ] = array(
				( int ) $data[ $q ]->term_id,
				( int ) $data[ $q ]->count + 1,
				( int ) current_time( 'timestamp' )
			);
			
		endif;
		
	else :
		
		if ( null != $old_term ) :
			
			$meta_value = $prev_value;
			
			if ( isset( $prev_value[ $old_term ] ) && ! empty( $prev_value[ $old_term ] ) ) :
				$meta_value[ $q ] = array_map( 'absint', $meta_value[ $old_term ] );
				unset( $meta_value[ $old_term ] );
			endif;
			
		else :
			
			if ( null == $hits )
				$hits = ( int ) 1;
			
			if ( null == $post_modified )
				$post_modified = ( int ) current_time( 'timestamp' );
			
			$meta_value = array(
				$q => array(
					( int ) $last_id,
					( int ) $hits,
					( int ) stt2extat_sanitize_strtotime( $post_modified )
				)
			);
			
			$meta_value = $prev_value + $meta_value;
		
		endif;
		
	endif;
	
	if ( ! array_filter( $meta_value ) )
		return ( int ) 5;
	
	update_post_meta( absint( $post_id ), $meta_key, $meta_value, $prev_value );
	
	return true;
}

/**
 * manual insert terms into wp table database post_meta
 * @deprecated see stt2extat_insert_searchterm_callback
 * 
 * @since 1.0.0
 *
 * sanitize $_POST and $_REQUEST and other variable
 *
 * @since 1.0.3
 *
 * change shortcut syntax array
 *
 * @since 1.0.4
 *
 */
function stt2extat_insert_ajax()
{
	global $post, $stt2extat_settings, $stt2extat_data;
	
	if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'heartbeat-nonce' ) )
		wp_die( '1' );
	
	if ( check_admin_referer( 'heartbeat-nonce', '_wpnonce' ) && isset( $_POST['terms'] ) && '' != $_POST['terms'] ) :
		
		if( ! session_id() && ! headers_sent() )
			session_start();
		
		$post_id     = absint( $_POST['postid'] );
		$ignore      = wp_validate_boolean( $_POST['ignore'] );
        $terms_array = array_map( 'trim', explode( ',', $_POST['terms'] ) );
		
		$data		 = $stt2extat_data->terms;
		$last_id     = $stt2extat_data->last_id;
		
		$i = 0;
		$result      = array();
        foreach ( $terms_array as $query ) :
		
			if ( '' == $query )
				continue;
			
			$q = stt2extat_filter_text( $query );
			
			if ( ! is_array( $q ) && ! empty( $q ) ) :
			
				$relevant = stt2extat_get_relevant_post( $post_id, $q, $ignore, true );
				
				if ( isset( $data[ $q ] ) ) :
					$post_id_exist      = $data[ $q ]->post_id;
					$meta_count         = $data[ $q ]->count;
					$result['exists'][] = array(
						$q,
						__( 'already exist in', 'stt2extat' ),
						get_permalink( $post_id_exist ),
						get_the_title( $post_id_exist ),
						__( 'Hits', 'stt2extat' ),
						intval( $meta_count )
					);
				
				elseif ( ! stt2extat_in_stopwords( $q ) && 3 < mb_strlen( $q ) && $stt2extat_settings['max_char'] >= mb_strlen( $q ) && $relevant ) :
					$i++;
					$id = $last_id + $i;
					stt2extat_update_postmeta( $q, $post_id, '', $id, $data, null );
					$result['new'][] = array(
						$q,
						get_permalink( $post_id ),
						get_the_title( $post_id )
					);
				
				elseif ( stt2extat_in_stopwords( $q ) ) :
					$result['stopwords'][]  = $q;
				
				elseif ( $stt2extat_settings['max_char'] < mb_strlen( $q ) ) :
					$result['long_term'][] = $q;
				
				elseif ( 4 > mb_strlen( $q ) ) :
					$result['short_term'][] = $q;
				
				elseif ( ! $relevant ) :
					$result['irrelevant'][] = $q;
					
				endif;
				
			else :
			
				if ( stt2extat_in_stopwords( $q['error'] ) ) :
					$result['stopwords'][]  = $q['error'];
				else :
					$result['error'][] = esc_attr( $q['error'] );
				endif;
				
			endif;
			
	    endforeach;
		
		$button_dissmiss = sprintf( '<button type="button" class="notice-dismiss"><span class="screen-reader-text">%1$s</span></button>',
			__( 'Dismiss this notice.', 'stt2extat' )
		);
		
		$print = array();
		if ( isset( $result['exists'] ) ) :
			$exist_msg_html = '<div id="message" class="notice notice-warning fade notice is-dismissible"><p style="margin: .5em 0"><kbd>%1$s</kbd> %2$s <kbd class="permalink">%3$s</kbd> <a target="_blank" href="%4$s" title="%5$s"><i class="dashicons dashicons-external"></i></a>. %6$s: <kbd>%7$s</kbd></p>%8$s</div>';
			
			$unique = array();
			foreach( $result['exists'] as $k )
				$unique[] = sprintf( $exist_msg_html,
					esc_attr( $k[0] ),
					esc_attr( $k[1] ),
					urldecode( $k[2] ),
					esc_url( $k[2] ),
					esc_attr( $k[3] ),
					esc_attr( $k[4] ),
					absint( $k[5] ),
					$button_dissmiss
				);
				
			$print[] = implode( '', array_unique( $unique ) );
			
		endif;
		
		if ( isset( $result['new'] ) ) :
			$new_msg_html  = '<div id="message" class="updated fade notice is-dismissible"><p><kbd>%1$s</kbd> %2$s <kbd class="permalink">%3$s</kbd> <a target="_blank" href="%4$s" title="%5$s"><i class="dashicons dashicons-external"></i></a></p>%6$s</div>';
			
			$unique = array();
			foreach( $result['new'] as $k ) :
				$unique['term'][]  = $k[0];
				$unique['link'][]  = $k[1];
				$unique['title'][] = $k[2];
			endforeach;
			
			$print[] = sprintf( $new_msg_html,
				implode( '</kbd><kbd>', array_unique( $unique['term'] ) ),
				__( 'added into', 'stt2extat' ),
				urldecode( $unique['link'][0] ),
				esc_url( $unique['link'][0] ),
				esc_attr( $unique['title'][0] ),
				$button_dissmiss
			);
			
		endif;
		
		$error_msg_html = '<div id="message" class="error fade notice is-dismissible"><p><kbd>%1$s</kbd> %2$s</p>%3$s</div>';
		
		if ( isset( $result['error'] ) )
			$print[] = sprintf( $error_msg_html,
				implode( '</kbd><kbd>', $result['error'] ),
				__( 'this term not allowed.', 'stt2extat' ),
				$button_dissmiss
			);
		
		if ( isset( $result['stopwords'] ) )
			$print[] = sprintf( $error_msg_html,
				implode( '</kbd><kbd>', $result['stopwords'] ),
				__( 'include in filter word(s), can not be added!.', 'stt2extat' ),
				$button_dissmiss
			);
		
		if ( isset( $result['irrelevant'] ) )
			$print[] = sprintf( $error_msg_html,
				implode( '</kbd><kbd>', $result['irrelevant'] ),
				__( 'irrelevant, can not be added!.', 'stt2extat' ),
				$button_dissmiss
			);
		
		if( isset( $result['long_term'] ) )
			$print[] = sprintf( $error_msg_html,
				implode( '</kbd><kbd>', $result['long_term'] ),
				__( 'too long, can not be added!.', 'stt2extat' ),
				$button_dissmiss
			);
		
		if( isset( $result['short_term'] ) )
			$print[] = sprintf( $error_msg_html,
				implode( '</kbd><kbd>', $result['short_term'] ),
				__( 'too short, can not be added!.', 'stt2extat' ),
				$button_dissmiss
			);
			
		if ( array_filter( $print ) )
			echo join( '', $print );
		
	endif;
	
	wp_die(); 
}

/**
 * on search page 
 * $q is term query by referrer, not search query
 *
 * @since 1.1
 *
*/
function stt2extat_get_the_id_relevant_post( $post_ids, $q, $ignore )
{
	$id = null;
	foreach ( $post_ids as $post_id ) :
	
		$relevant = stt2extat_get_relevant_post(
			$post_id,
			sanitize_text_field( $q ),
			wp_validate_boolean( $ignore ),
			true
		);
		
		if( ! $relevant )
			continue;
			
		$id = absint( $post_id );
		break;
			
	endforeach;
	return $id;
}

/**
 * get relevant post
 * return in json with array keys - respons, content, title and post
 *
 * @since 1.0
 *
*/
function stt2extat_get_relevant_post( $post_id, $q, $ignore, $insert = false )
{
	global $stt2extat_query;
	
	$args = array(
		'posts_per_page'         => 1,
		'post_type'              => stt2extat_post_type(),
		'post_status'            => 'publish',
		'p'                      => absint( $post_id ),
		's'                      => sanitize_text_field( $q ),
		'cache_results'          => false,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false
	);
	
	$stt2extat_query = new WP_Query( $args );
	
	if ( '' == $args['s'] )
		return false;
	
	if ( $insert )
	{
		if ( isset( $stt2extat_query->post->ID ) || $ignore )
			return true;
		return false;
	}
	return $stt2extat_query;
}

/**
 * get all post id on current page
 *
 * @since 1.1
 *
*/
function stt2extat_post_ids_query()
{
	global $wp_query;
	
	$post_id = array();
	if ( $wp_query->is_main_query() ) :
	
		if ( ! isset( $wp_query->posts ) )
			return array();
		
		foreach ( $wp_query->posts as $item => $post ) :
			$post_id[] = ( int ) $post->ID;
		endforeach;
		
	endif;
	
	return $post_id;
}

/**
 * filter unwanted words
 * used by search and terms
 *
 * @since 1.1
 *
*/
function stt2extat_in_stopwords( $term = null, $as_array = false )
{
	global $stt2extat_settings, $stt2extat_sanitize;
	
	$stopwords = $stt2extat_settings['stopwords'];
	$stopwords = ( ! isset( $stopwords ) || '' == $stopwords ) ? $stt2extat_sanitize->data['stopwords']['normal'] : $stopwords;
	
	if ( $as_array )
		return $stopwords;
	
	$func = function ( $a, $b  )
	{
		$b = str_ireplace( $a, '***', $b );
		
		if( false !== strpos( $b, '***' ) )
			return true;
		
		return false;
	};
	
	$func = apply_filters( 'stt2extat_stopwords_filter_method', $func, $stopwords, $term );
	
	return call_user_func( $func, $stopwords, $term );
}

/**
 * show error notice when update terms
 *
 * @since 1.1
 *
*/
function stt2extat_error_msg( $page = null )
{
	$n = false;
	
	if ( null == $page )
		return $n;
	
	if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], $_GET['page'] .'_'. $_GET['post_ID'] . '_' . $_GET['term_ID'] . '_' . $_GET['term_name'] . '_edit' ) ) :
	
		$n = ( int ) 9;
		
	elseif ( false == $n ) :
	
		$meta_key  = '_' . sanitize_key( $page );
		$post_id   = absint( $_GET['post_ID'] );
		$post_meta = stt2extat_get_post_terms( $post_id );
		$name      = sanitize_text_field( $_GET['term_name'] );
		
		if ( empty( $post_meta ) || ! isset( $post_meta[ $name ] ) )
			$n = ( int ) 10;
		
	endif;
	
	return $n;
}

/**
 * shortcode [stt2extat]
 * to show terms list on post content area, or widget text
 *
 * @since 1.1
 *
*/
function stt2extat_shortcode( $atts = null, $result = '' )
{
	if ( ! is_single() )
		return;
	
	$setting = shortcode_atts(
		stt2extat_default_setting( 'shortcode' ),
		$atts,
		'stt2extat'
	);
	
	$list    = stt2extat_terms_list( $setting );
	
	if ( ! empty ( $list ) )
		$result  = wp_sprintf( '<div id="stt2extat-shortcode">%s</div>', $list );
	
	return $result;
}

/**
 * Filter post content to show terms list
 *
 * @since 1.1
 *
*/
function stt2extat_filter_the_content( $content, $yn = 'y' )
{
	global $stt2extat_settings;
	
	if ( is_single() ) :
	
		$list = stt2extat_terms_list();
		
		if ( $yn == $stt2extat_settings['auto'] )
			$list = '';
			
		$list    = wp_sprintf( '<div class="stt2extat-container" role="note" aria-expanded=false>%s</div>', $list );
		$content = $content . '' . $list;
		
	endif;
	
	return $content;
}

/**
 * show terms list via wp_ajax
 *
 * @since 1.1
 *
*/
function stt2extat_terms_list_ajax()
{
	if ( ! isset( $_POST['post_ID'], $_POST['page'] ) && ! is_array( $_POST['post_ID'] ) && ! $_POST['page'] )
		die();
	
	$list = stt2extat_terms_list();
	wp_send_json( array( 'result' => $list ) );
}

/**
 * process term list by argument
 *
 * @since 1.1
 *
*/
function stt2extat_terms_list( $args = array(), $widget = false )
{
	global $stt2extat_settings;
	
	$default = array(
		'text_header',
		'html_heading',
		'number',
		'display',
		'count',
		'convert'
	);
	
	$set = wp_array_slice_assoc( $args, $default );
	
	if ( is_object( $widget ) && wp_validate_boolean( $widget->is_widget ) ) :
		if ( isset( $args['tax_query'] ) || isset( $args['p'] ) ) :
			$query = new STT2EXTAT_Query( $args );
			$data  = $query->terms;
		else :
			$data  = stt2extat_data_query( $args );
		endif;
		
	else :
		$set  = wp_parse_args( $set, $stt2extat_settings );
		
		if ( ! array_filter( $set ) )
			return;
		
		$post_id  = ( isset( $_POST['post_ID'] ) && defined( 'DOING_AJAX' ) && DOING_AJAX ) ? absint( $_POST['post_ID'] ) : get_the_ID();
		
		$data = stt2extat_get_terms( array(
			'post_id' => $post_id
		) );
		
		uasort( $data, '_usort_terms_by_count' );
		
		$data = array_reverse( $data, true );
		
	endif;
	
	if ( array_filter( $data ) ) :
		$result = stt2extat_data_format( $data, $set, $widget );
		return $result;
	endif;
}

/**
 * html format of term list
 *
 * @since 1.1
 *
*/
function stt2extat_data_format( $data, $set, $widget = false )
{
	$html = '';
	if ( '' != $set['text_header'] )
		$html .= "<" . $set['html_heading'] . ">" . $set['text_header'] . "</" . $set['html_heading'] . ">";
	
	return call_user_func_array( '_stt2extat_data_format',
		array(
			$data,
			$html,
			$set['count'],
			$set['number'],
			$set['display'],
			$set['convert'],
			$widget
		)
	);
}

/**
 * generate html format of term list
 *
 * @since 1.1
 *
*/
function _stt2extat_data_format( $data, $html, $count, $number, $display, $convert, $widget )
{
	$li        = 'li';
	$display   = sanitize_key( $display );
	$sep = $tooltips = $sup = '';
	$output    = '%1$s<%2$s><li>%3$s</li></%2$s>';
	$link_html = '<a href="%1$s" %2$s>%3$s</a>';
	$list      = array();
	$cloud     = false;
	
	if ( isset( $widget->cloud ) && false != wp_validate_boolean( $widget->cloud ) )
		$cloud = true;
	
	if ( 'span' == $display ) :
		$li     = 'span';
		$sep    = ' &bull; ';
		$sep    = esc_html( apply_filters( 'stt2extat_sep_terms_list', $sep ) );
		$output = '%1$s<div class="list-inline"><%2$s>%3$s</%2$s></div>';
	endif;
	
	foreach ( $data as $k => $v ) :
		
		$name       = esc_html( $v->name );
		$text_link  = stt2extat_convert_case( $name );
		
		$term_count = ( object ) array(
			'number' => $v->count,
			'text'   => _n_noop( '%s hit', '%s hits' )
		);
		
		$term_count = apply_filters( 'stt2extat_term_count', $term_count, $name );
		
		switch ( $count )
		{
			case 'tooltips' :
				$hit      = $term_count->text;
				$tooltips = sprintf( translate_nooped_plural( $hit, absint( $term_count->number ) ), number_format_i18n( absint( $term_count->number ) ) );
				$tooltips = 'title="' . esc_attr( $tooltips ) . '"';
				break;
				
			case 'sup' :
				$sup = sprintf( ' <sup><i>( %1$s )</i></sup>', absint( $term_count->number ) );
				break;
				
			default :
				break;
		}
		
		if ( $cloud ) :
			$link_html = '%1$s';
			$text_link = '#';
		endif;
		
		switch ( $convert )
		{
			case 'post' :
				$text_link = wp_sprintf( $link_html,
					get_permalink( absint( $v->post_id ) ),
					$tooltips,
					$text_link
				);
				break;
			
			case 'search' :
				$text_link = wp_sprintf( $link_html,
					get_search_link( esc_html( $v->name ) ),
					$tooltips,
					$text_link
				);
				break;
			
			default :
				$text_link = $text_link;
				break;
		}
		
		if ( $cloud ) :
			$v->link    = $text_link;
			$v->count   = $term_count->number;
			$list[ $k ] = $v;
		else :
			$list[] = array(
				'text_link'  => $text_link . $sup,
				'count' => $term_count->number
			);
		endif;
			
	endforeach;
	
	if ( $cloud )
		return $list;
	
	if ( has_filter( 'stt2extat_term_count', 'stt2extat_count_posts' ) )
		uasort( $list, '_usort_terms_by_count' );
	
	if ( array_filter( $list ) && 0 < $number ) :
		$list = array_column( array_slice( $list, 0, $number ), 'text_link' );
		$list = array_map( 'trim', $list );
		$list = implode( "</$li>$sep<$li>", $list );
	endif;
	
	return wp_sprintf( $output, $html, $display, $list );
}

/**
 * get terms by term_id, name, slug, post_id, count, date
 * 
 * @since 1.1
 *
 */
function stt2extat_get_terms( $args = array() )
{
	global $stt2extat_data;
	
	$list = wp_list_filter( $stt2extat_data->terms, $args, 'and' );
	
	return $list;
}

/**
 * delete terms on database
 * @deprecated see stt2extat_insert_searchterm_callback
 * 
 * @since 1.0.0
 *
 * sanitize $_POST and $_REQUEST
 *
 * @since 1.0.3
 *
 */

function stt2extat_delete_term_ajax( $opt = null )
{
	if ( 'wp_ajax_stt2extat_delete_term' == current_action() ) :
		
		$nonce    = false;
		$meta_key = '_stt2extat';
		$location = 'options-general.php?page=' . ( str_replace( '_', '', $meta_key ) );
		
		if ( $referrer = wp_get_referer() ) :
			if ( false !== strpos( $referrer, 'options-general.php' ) )
				$location = $referrer;
		endif;
		
		if( isset( $_REQUEST['page'], $_REQUEST['post_ID'], $_REQUEST['term_ID'], $_REQUEST['term_name']  ) )
			$nonce = sanitize_text_field( $_REQUEST['page'] .'_'. $_REQUEST['post_ID'] . '_' . $_REQUEST['term_ID'] . '_' . $_REQUEST['term_name'] . '_delete' );
		
		if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'],  $nonce ) ) :
			$location = add_query_arg( array( 'error' => true, 'message' => 9 ), $location );
			
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) :
				$nonce = 'heartbeat-nonce';
				if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'],  $nonce ) ) :
					do_action( 'stt2extat_notice', $code = 9, $error = true, $add_setting_error = false );
					wp_die();
				endif;
			else :
				wp_redirect( $location );
				exit;
			endif;
		endif;
		
		if ( 'delete_all' == $opt ) :
			$location = add_query_arg( array( 'error' => false, 'message' => 6 ), $location );
			
			if ( delete_post_meta_by_key( $meta_key ) ) :
				$location = add_query_arg( array( 'error' => false, 'message' => 8 ), $location );
				wp_die( $location );
			endif;
			
			do_action( 'stt2extat_notice', $code = 6, $error = true, $add_setting_error = false );
			wp_die();
			exit;
			
		endif;
		
		if ( ! isset( $_REQUEST['term_ID'] ) )
			wp_die();
		
		$id         = absint( $_REQUEST['term_ID'] );
		$post_id    = absint( $_REQUEST['post_ID'] );
		$prev_value = stt2extat_get_post_terms( $post_id );
		$prev_value = ( '' != $prev_value && is_array( $prev_value ) ) ? $prev_value : array();
		
		$exist      = false;
		$meta_value = array();
		foreach ( $prev_value as $k => $v ) :
			if( isset( $v[0] ) && $id == $v[0] ) :
				$exist = true;
				continue;
			endif;
			$meta_value[ $k ] = $v;
		endforeach;
		
		if ( ! $prev_value || ! $exist ) :
			$location = add_query_arg( array( 'error' => true, 'message' => 11 ), $location );
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) :
				do_action( 'stt2extat_notice', $code = 11, $error = true, $add_setting_error = false );
				wp_die();
			else :
				wp_redirect( $location );
				exit;
			endif;
		endif;
		
		if ( $prev_value === $meta_value ) :
		
			$location = add_query_arg( array( 'error' => true, 'message' => 5 ), $location );
			
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) :
				do_action( 'stt2extat_notice', $code = 5, $error = true, $add_setting_error = false );
				wp_die();
			else :
				wp_redirect( $location );
				exit;
			endif;
		
		endif;
		
		if ( ! array_filter( $meta_value ) )
			$update = delete_post_meta( $post_id, $meta_key );
		else
			$update = update_post_meta( $post_id, $meta_key, $meta_value, $prev_value );
		
		if ( $update ) :
			$location = add_query_arg( array( 'error' => false, 'message' => 2 ), $location );
			
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) :
				do_action( 'stt2extat_notice', $code = 2, $error = false, $add_setting_error = false );
				wp_die();
			endif;
			
			wp_redirect( $location );
			exit;
			
		endif;
	endif;
}

/**
 * edit and delete terms
 *
 * @since 1.1
 *
*/
function stt2extat_edited_action_callback( $get = null )
{
	if ( ! isset( $_GET['page'] ) || 'stt2extat_edited_action' != current_action() || ! is_admin()  )
		return;
	
	$page = 'stt2extat';
	
	if ( sanitize_key( $_GET['page'] ) != $page )
		return;
	 
	switch ( $get )
	{
		case 'edit' :
			$location = 'options-general.php?page=' . $page;
			$ret = stt2extat_error_msg( $page );
			if ( false != $ret ) :
				$location = add_query_arg( array( 'error' => true, 'message' => $ret ), $location );
				wp_redirect( $location );
				exit;
			endif;
			break;
		
		case 'delete' :
			do_action( 'wp_ajax_stt2extat_delete_term' );
			break;
		
		default :
			return;
			break;
	}
}

/**
 *
 * add hook functions into this new single file - hook-functions.php
 * deprecated function stt2extat_recent_post on unused file hook-recent-post.php
 *
 * @since 1.0.3
 *
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

/**
 * search by query and select one relevant posts
 *
 * @since 1.0.0
 *
 * sanitize $_POST and $_REQUEST and other variable
 *
 * @since 1.0.3
 *
 */
function stt2extat_search_post_ajax()
{
	if ( ! isset( $_REQUEST['_wpnonce'], $_POST['query'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'heartbeat-nonce' ) )
		wp_die( '-1' );
	
	$q         = sanitize_text_field( $_POST['query'] );
	$p         = url_to_postid( esc_url( $_POST['query'] ) );
	$max       = get_option( 'posts_per_page' );
	
	if ( is_numeric( $p ) && ( int ) $p > 0 ) :
		$args = array(
			'p'              => $p,
			'posts_per_page' => 1,
		);
	
	elseif ( is_numeric( $q ) && ( int ) $q > 0 ) :
		$args = array(
			'p'              => absint( $q ),
			'posts_per_page' => 1,
		);
	
	else :
		$args = array(
			's'              => $q
		);
	
	endif;
	
	$args    = apply_filters( 'stt2extat_args_search_post_query', $args );
	
	$default = array(
		'posts_per_page' => $max,
		'post_type'      => stt2extat_post_type(),
		'post_status'    => 'publish',
		'no_found_rows'  => true
	);
	
	$args  = wp_parse_args( $args, $default );
	
	$query = new WP_Query( $args );
	
	$respons = array();
	if ( $query->have_posts() ) : 
		while ( $query->have_posts() ) : $query->the_post();
			$item            = array();
			$item['id']      = get_the_ID();
			$item['label']   = get_the_title();
			$item['value']   = urldecode( get_permalink() );
			$item['excerpt'] = wp_trim_words( get_the_content(), (int) 35, null );
			
			$content = get_post_field( 'post_content',  get_the_ID() );
			if( is_wp_error( $content ) )
				$content = $content->get_error_message();
			$item['content'] = wp_strip_all_tags( $content );
			$respons[]         = $item;
		endwhile;
		wp_reset_postdata();
	endif;
	wp_send_json( $respons );
}

/**
 * get terms list of selected post
 * @deprecated see stt2extat_get_search_terms_db_callback
 *
 * @since 1.0.0
 *
 * sanitize $_POST and $_REQUEST and other variable
 *
 * @since 1.0.3
 *
 */
function stt2extat_terms_list_post_ajax()
{
	global $stt2extat_settings;
	
	if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'heartbeat-nonce' ) ) 
       wp_die( '-1' );
   
	$post_id  = absint( $_POST['post_ID'] );
	$postmeta = stt2extat_get_post_terms( $post_id );
		
	if( ! empty( $postmeta ) ) :
			
		uasort( $postmeta, 'stt2extat_sort_by_hits' );
		
		$max   = ( isset( $stt2extat_settings['number'] ) ) ? $stt2extat_settings['number'] : 5;
		
		$more  = ( count( $postmeta ) > $max ) ? '<span class="dashicons dashicons-plus-alt alltag"></span>' : '' ;
		
		$slice = array_slice( $postmeta, 0, $max, true );
		$data  = array_keys( $slice );
		
		$result = implode( '</span><span><a class="ntdelbutton"></a>&nbsp;',
			array_map( 'stt2extat_terms_list_post', $data, $slice )
		);
		
		printf ( '<span><span class="dashicons dashicons-tag"></span> %1$s</span><br /><div class="tagchecklist"><span><a class="ntdelbutton"></a>&nbsp;%2$s</span>%3$s</div>',
			__( 'Terms of post:', 'stt2extat' ),
			$result,
			$more
		);
	
	else :
		printf ( '<span class="attention"><span class="dashicons dashicons-tag"></span> %s</span>',
			__( 'Terms of post: Empty!', 'stt2extat' )
		);
	
	endif;
	wp_die();
}

/**
 * get all terms of post
 *
 * @since 1.0
 *
 * sanitize variable for database
 *
 * @since 1.0.3
 *
*/
function stt2extat_list_all_terms_ajax()
{
    global $stt2extat_settings;
	
	if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'heartbeat-nonce' ) || ! isset ( $_POST['post_ID'] ) )
		wp_die( '-1' );
	
	$post_id  = absint( $_POST['post_ID'] );
	$postmeta = stt2extat_get_post_terms( $post_id );
	
	if ( ! empty( $postmeta ) ) :
		uasort( $postmeta, 'stt2extat_sort_by_hits' );
		
		$max    = ( isset( $stt2extat_settings['number'] ) ) ? absint( $stt2extat_settings['number'] ): ( int ) 5;
		$slice  = array_slice( $postmeta, ( int ) $max, 1000, true );
		$data   = array_keys( $slice );
		
		$result = implode( '</span><span><a class="ntdelbutton"></a>&nbsp;',
			array_map( 'stt2extat_terms_list_post', $data, $slice )
		);
		
		if ( '' != $result )
			printf ( '<div class="stplus"><span><a class="ntdelbutton"></a>&nbsp;%s</span></div>',
			$result
		);
    endif;
	
	wp_die();
}

/**
 * edit and delete terms
 *
 * @since 1.1
 *
*/
function stt2extat_terms_list_post( $data, $slice )
{
	$output = sprintf( '<i class="termlist" data-id="%1$s" title="%2$s">%3$s</i> (<i class="termcnt" title="%4$s">%5$s</i>)',
		absint( $slice[0] ),
		__( 'Increase Number', 'stt2extat' ),
		esc_html( $data ),
		__( 'Decrease Number', 'stt2extat' ),
		absint( $slice[1] )
	);
	
	return $output;
}

/**
 * sort terms by post modified
 *
 * @since 1.1
 *
*/
function _usort_terms_by_post_modified( $a, $b )
{
	if ( $a->post_modified > $b->post_modified )
		return 1;
	elseif ( $a->post_modified < $b->post_modifiedd )
		return -1;
	else
		return 0;
}

/**
 * sort terms by count
 *
 * @since 1.1
 *
*/
function _usort_terms_by_count( $a, $b )
{
	if( is_object( $a ) )
		return ( $a->count ) - ( $b->count );
	
	return ( $b['count'] ) - ( $a['count'] );
}

/**
 * sort terms by hits
 *
 * @since 1.1
 *
*/
function stt2extat_sort_by_hits( $a, $b )
{
	return $b[1] - $a[1];
}

/**
 * populate terms of relevant posts
 * @deprecated see stt2extat_search_relevant_post_callback
 * 
 * @since 1.0.0
 *
 * sanitize $_POST and $_REQUEST and other variable
 *
 * @since 1.0.3
 *
 * patch get_the_excerpt
 *
 * @since 1.0.9
 *
 */
function stt2extat_search_relevant_ajax()
{
	$response = array(
		   'what'   => 'nonce',
		   'action' => 'search_relevant',
		   'id'     => new WP_Error( 'notice', 'error' ),
		   'data'   => __( 'You do not have permission to do that.', 'stt2extat' )
		);
		
	if ( ! isset( $_REQUEST['_wpnonce'], $_POST['post_ID'], $_POST['s'], $_POST['ignore'] )
		|| ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'heartbeat-nonce' )
	)
		wp_send_json( $response );
	
	global $stt2extat_settings, $stt2extat_data;
	
	$q       = sanitize_text_field( $_POST['s'] );
	$post_id = absint( $_POST['post_ID'] );
	
	if ( '' == $q ) :
		$response['what'] = 'disallow';
		$response['id']   = new WP_Error( 'notice', 'Terms Disallow' );
		wp_send_json( $response );
	endif;
	
	$ignore  = wp_validate_boolean( $_POST['ignore'] );
	
	$query   = stt2extat_get_relevant_post( $post_id, $q, $ignore );
	$post    = $query->post;
	$excerpt = get_the_excerpt();
	$excerpt = apply_filters( 'get_the_excerpt', $excerpt, $q, $post_id );
	$data    = $stt2extat_data->terms;
	
	if ( stt2extat_in_stopwords( $q ) ) :
		$post = get_post( $post_id );
		
		$what = 'stopwords';
		$msg  = __( 'include in filter word(s), can not be added!.', 'stt2extat' );
		$id   = new WP_Error( 'notice', $q . ' ' . $msg );
		
	elseif ( isset( $post->ID ) ) :
	
		if ( isset( $data[ $q ] ) ) :
			$what    = 'exist';
			$post_id = $data[ $q ]->post_id;
			$id      = new WP_Error( 'notice', 'Terms Exists ( Relevant )' );
			
		elseif ( 3 < mb_strlen( $q ) && $stt2extat_settings['max_char'] >= mb_strlen( $q ) ) :
			$what  = 'relevant';
			$id    = new WP_Error( 'notice', 'Terms Relevant' );
		else:
			$what  = 'disallow';
			$id    = new WP_Error( 'notice', 'Terms Disallow' );
		endif;
		
	else :
	
		$post = get_post( $post_id );
		
		if( isset( $data[ $q ] ) ) :
			$what    = 'existirrelevant';
			$post_id = $data[ $q ]->post_id;
			$id      = new WP_Error( 'notice', 'Terms Exists ( Irrelevant )' );
			
		elseif( 3 < mb_strlen( $q ) && $stt2extat_settings['max_char'] >= mb_strlen( $q ) ) :
			$what = 'irrelevant';
			$id   = new WP_Error( 'notice', 'Terms Irrelevant' );
			
		else :
			$what = 'disallow';
			$id   = new WP_Error( 'notice', 'Terms Disallow' );
			
		endif;
	
	endif;
	
	$data = array(
		'title'   => sanitize_text_field( $q ),
		'link'    => get_permalink( $post_id ),
		'excerpt' => $excerpt,
		'content' => wp_strip_all_tags( $post->post_content )
	);
	
	$response = array(
	   'what'   => sanitize_key( $what ),
	   'action' => 'search_relevant',
	   'id'     => $id,
	   'data'   => $data
	);
	
	return wp_send_json( $response );
}

/**
 * manual increase and decrease terms hits
 * @deprecated see update_meta_count_extat_callback
 * 
 * @since 1.0.0
 *
 * sanitize $_POST and $_REQUEST
 *
 * @since 1.0.3
 *
 */
function stt2extat_update_count_ajax()
{
	if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'heartbeat-nonce' ) || ! check_admin_referer( 'heartbeat-nonce', '_wpnonce' ) )
		wp_die( '-1' );
	
	if ( isset( $_POST['term_ID'], $_POST['post_ID'], $_POST['count'] ) && '' != $_POST['post_ID'] ) :

		$meta_key      = '_stt2extat';
		$term_id       = absint( $_POST['term_ID'] );
		$post_id       = absint( $_POST['post_ID'] );
		$count         = intval( $_POST['count'] );
		$post_modified = (int) current_time( 'timestamp' );
		
		$prev_value = stt2extat_get_post_terms( $post_id );
		
		$meta_value = array();
		if( ! empty( $prev_value ) ) :
			foreach ( $prev_value as $k => $v ) :
				if ( $term_id == $v[0] )
					$meta_value[ $k ] = array( $v[0], $count, $post_modified );
				else
					$meta_value[ $k ] = $v;
			endforeach;
		endif;
		
		if ( ! array_filter( $meta_value ) )
			wp_die();
		
		$update = update_post_meta( $post_id, $meta_key, $meta_value, $prev_value );
		if ( false != wp_validate_boolean( $update ) )
			wp_die();
	endif;
	wp_die();
}

/**
 * Toggle ajax button to show post without terms
 *
 * @since 1.1
 *
*/
function stt2extat_post_wo_terms_button()
{
	$data = stt2extat_post_wo_terms();
	if ( 0 < absint( $data->number ) )
		printf( '<div id="stt2extat-wo-terms" class="button-link"><strong>%1$s</strong> <span title="%2$s">%3$s</span></div><ol class="stt2extat-wo-terms-list hide-if-js"><div class="spinner is-active"></div></ol>', __( 'Posts Without Terms', 'stt2extat' ), esc_attr__( 'Show', 'stt2extat' ), absint( $data->number ) );
}

/**
 * wp ajax to show post without terms
 *
 * @since 1.1
 *
*/
function stt2extat_post_wo_terms_ajax()
{
	if (  ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'heartbeat-nonce' ) )
		wp_die( '-1' );
	
	$data = stt2extat_post_wo_terms();
	
	if ( 0 < $data->number ) :
		$post = array();
		for ( $i = 0; $i < $data->number ; $i++ ) :
			$post[] = sprintf( '<a data-href="%1$s" href="#title">%2$s</a>',
				get_permalink( $data->ids[ $i ] ),
				get_the_title( $data->ids[ $i ] )
			);
		endfor;
		$html = sprintf( '<li>%1$s</li>', implode( '</li><li>', $post ) );
	else :
		$html = '';
	endif;
	
	$result = array(
		'result' => $html,
		'count'  => absint( $data->number )
	);
	wp_send_json( $result );
}

/**
 * wp query to show post without terms
 *
 * @since 1.1
 *
*/
function stt2extat_post_wo_terms()
{
	$args = array(
		'post_status'            => 'publish',
		'post_type'              => stt2extat_post_type(),
		'cache_result'           => false,
		'fields'                 => 'ids',
		'posts_per_page'         => -1,
		'update_post_term_cache' => false,
		'update_post_meta_cache' => false,
		'meta_query' => array(
			'relation' => 'OR',
			array(
				'key'     => '_stt2extat',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key'     => '_stt2extat',
				'value'   => ' ',
				'compare' => '=',
			),
		),
	);
	
	$ids = get_posts( $args );
	
	$result = ( object ) array(
		'ids' => $ids,
		'number' => count( $ids )
	);
	
	return $result;
}

/**
 * wp query to show each post count
 *
 * @since 1.1
 *
*/
function stt2extat_count_posts( $term_count, $name )
{
	$args = array(
		's'                      => sanitize_text_field( $name ),
		'post_status'            => 'publish',
		'post_type'              => stt2extat_post_type(),
		'cache_result'           => false,
		'fields'                 => 'ids',
		'posts_per_page'         => -1,
		'update_post_term_cache' => false,
		'update_post_meta_cache' => false
	);
	$query = get_posts( $args );
	$count = count( $query );
	wp_reset_postdata();
	
	$term_count = ( object ) array(
		'number' => $count,
		'text'   => _n_noop( '%s topic', '%s topics' )
	);
	
	return $term_count;
}

/**
 * html meta head to ignore referrer
 *
 * @since 1.1
 *
*/
function stt2extat_add_meta_origin_referer()
{
	echo '<meta name="referrer" content="origin">';
}

/**
 * parse url of referrer
 *
 * @since 1.1
 *
*/
function stt2extat_parse_url( $url, $www = false )
{
	static $pattern = '';
	
	$url       = wp_unslash( $url );
	$url       = esc_url( $url );
	$parse_url = parse_url( $url );
	$parse_url = array_map( 'strtolower', $parse_url );
	$host      = ( isset( $parse_url['host'] ) ) ? $parse_url['host'] : false;
	
	if ( empty( $pattern ) )
		$pattern = apply_filters( 'stt2extat_domain_regexp', '/(?P<host>(?!-)([a-z0-9-_]+\.)*[a-z0-9-]+\.[a-z\.]{2,6})$/i' );
	
	if ( preg_match( $pattern, $host, $matches ) ) :
		$host = $matches['host'];
			if ( ! $www && 'www.' == substr( $host, 0, 4 ) )
				$host = substr( $host, 4 );
		
		$host = array(
			'host' => $host
		);
		return wp_parse_args( $host, $parse_url );
	endif;
	
	return '';
}

/**
 * clear local storage when logout
 *
 * @since 1.1
 *
*/
function stt2extat_clear_localstorage_on_logout()
{
	if ( false !== strpos( strtolower( $_SERVER['REQUEST_URI'] ), 'wp-login.php?loggedout' ) ) :
		echo "<script type='text/javascript'>\n";
		echo "var index;\n";
		echo "var lsarray = [ 'maxchar', 'searchexcerpt', 'gsuggest', 'gsuggestcheck', 'notmatch', 'notmatchcheck' ];\n";
		echo "for ( index = 0; index < lsarray.length; index++ ) {\n";
		echo "localStorage.removeItem( lsarray[index] );";
		echo "}";
		echo "</script>\n";
	endif;
}

/**
 * sanitize terms with sanitize_title_with_dashes
 * remove dash to get real terms
 *
 * @since 1.1.0
 *
*/
function stt2extat_filter_text( $rawkey = null )
{
	$terms = stt2extat_remove_char( $rawkey );
	$terms = stt2extat_build_terms( $terms );
	if (  0 !== mb_strlen( $terms ) ) :
		$rawkey = $terms;
	elseif (  0 !== mb_strlen( $rawkey ) ) :
		$rawkey = array( 'error' => $rawkey );
	endif;
	return $rawkey;
}

/**
 * remove unwanted characters
 *
 * @since 1.1
 *
*/
function stt2extat_remove_char( $q = null )
{
	$q = sanitize_title_with_dashes( urldecode( $q ), '', 'save' );
	$q = wp_strip_all_tags( $q );
	$q = preg_replace( '/&#?[a-z0-9]+;/i','', $q );
	$q = preg_replace( '/[^%A-Za-z0-9 _-]/', ' ', $q );
	$q = preg_replace( '/&.+?;/', '', $q );
	$q = preg_replace( '/_+/', ' ', $q );
	$q = preg_replace( '/\s+/', ' ', $q );
	$q = preg_replace( '|-+|', ' ', $q );
	$q = htmlspecialchars( urldecode( trim( $q ) ) );
	return $q;
}

/**
 * validate strtotime format
 *
 * @since 1.1
 *
*/
function stt2extat_sanitize_strtotime( $unix_time )
{
	$unix_time = absint( $unix_time );
	
	$d = gmdate( 'd', $unix_time );
	$m = gmdate( 'm', $unix_time );
	$Y = gmdate( 'Y', $unix_time );
	
	if ( false == checkdate( $m, $d, $Y ) )
		return false;
	
	return $unix_time;
}

/**
 * build insert terms
 *
 * @since 1.1
 *
*/
function stt2extat_build_terms( $terms = null )
{
	if ( null == $terms )
		return '';
	
	$terms   = explode( ' ', $terms );
	$checked = array();
	
	foreach ( $terms as $term ) :
		if ( preg_match( '/^".+"$/', $term ) )
			$term = trim( $term, "\"'" );
		else
			$term = trim( $term, "\"' " );
		
		if ( ! $term || ( 1 === strlen( $term ) && preg_match( '/^[a-z]$/i', $term ) ) )
			continue;
		
		if ( stt2extat_in_stopwords( $term ) )
			continue;
		
		$checked[] = $term;
	endforeach;
	
	if ( array_filter( $checked ) ) :
		$checked = array_unique( array_map( 'trim', $checked ) );
		return implode( ' ', $checked );
	endif;
	
	return '';
}

/**
 * build query with nonce
 *
 * @since 1.1
 *
*/
function stt2extat_build_query_nonce( $data, $action = null, $nonce = false )
{
	if ( null != $action )
		$data = wp_parse_args(
			array(
				'action' => sanitize_key( $action )
			),
			$data
		);
	
	$build_query = '?' . build_query( $data );
	
	if ( $nonce )
		$build_query = wp_nonce_url(
			$build_query,
			implode( '_', array_values( $data ) )
		);
	
	return $build_query;
}

/**
 * convert case of terms
 *
 * @since 1.1
 *
*/
function stt2extat_convert_case( $name )
{
	if ( function_exists( 'mb_convert_case' ) )
		$name = mb_convert_case( $name, MB_CASE_TITLE, 'UTF-8' );
	else
		$name = ucwords( $name );
	
	return $name;
}

/**
 * filter the title of search page
 *
 * @since 1.1
 *
 * filter `document_title_parts`
 *
 * @since 1.1.6
 * 
*/
function stt2extat_search_page_title( $title )
{
	if ( is_search() ) :
		$search = get_search_query();
		$search = stt2extat_convert_case( $search );
		$title['title'] = $search;
	endif;
	
	return $title;
}

/**
 * filter the title separator of search page
 *
 * @since 1.1
 *
 * filter `document_title_separator`
 *
 * @since 1.1.6
 * 
*/
function stt2extat_search_page_title_separator( $sep )
{
	if ( is_search() )
		$sep = '&#8212;'; // em-dash HTML number
		
	return $sep;
}

/**
 * filter get_search_link
 *
 * @since 1.1
 *
*/
function stt2extat_filter_search_link( $link, $search )
{
	$query = stt2extat_filter_text( $search );
	
	if ( empty( $query ) || isset( $query['error'] ) )
		return home_url();
	
	$slug  = stt2extat_create_slug( $query );
    $link  = stt2extat_create_link( $slug );
	
	return $link;
}

/**
 * create slug of terms
 *
 * @since 1.1
 *
*/
function stt2extat_create_slug( $q = null, $format = '+' )
{
	$q      = urlencode( trim( $q ) );
	$format = apply_filters( 'stt2extat_sep_search_link', $format );
	
	switch( $format )
	{
		case '-' :
			$q = preg_replace( '/\++/', '-', $q );
			break;
		case '_' :
			$q = preg_replace( '/\++/', '_', $q );
			break;
		default :
			$q = preg_replace( '/\++/', $format, $q );
			break;
	}
	
	$q = wp_specialchars_decode( $q );
	
	return apply_filters( 'stt2extat_slug', $q );
}

/**
 * create search link of terms
 *
 * @since 1.1
 *
*/
function stt2extat_create_link( $slug = null )
{
	global $wp_rewrite;

	if ( null == $slug )
		return false;
	
	$permastruct = $wp_rewrite->get_search_permastruct();
	
	if ( ! $permastruct ) :
	
		$link = home_url( '?s=' . $slug );
		
	else :
		
		$link = str_replace( '%search-term%', $slug, $permastruct );
		$link = str_replace( '%search_term%', $slug, $link );
		$link = str_replace( '%search%', $slug, $link );
		$link = home_url( $link, 'search' );
		
	endif;
	
	return $link;
}

/**
 * parse request search query
 *
 * @since 1.1
 *
*/
function stt2extat_parse_request()
{
	global $wp, $wp_query, $stt2extat_settings;
	
	if ( ! is_admin() && isset( $wp->query_vars['s'] ) ) :
		$s = stt2extat_build_terms( stt2extat_remove_char( $wp->query_vars['s'] ) );
		if ( 3 < mb_strlen( $s ) && $stt2extat_settings['max_char'] > mb_strlen( $s ) ) :
			$wp->extra_query_vars['rel_canonical'] = get_search_link( $s );
			$wp->set_query_var( 's',  $s );
			add_action( 'wp_head', 'stt2extat_search_page_head' );
		else :
			$wp->extra_query_vars['not_allowed'] = ( bool ) 1;
			add_action( 'wp_head', 'wp_no_robots' );
			add_action( 'pre_get_posts', 'stt2extat_if_no_result' );
		endif;
		
		return;
		
	endif;
}

/**
 * add canonical and meta robots on wp head
 *
 * @since 1.1
 *
*/
function stt2extat_search_page_head()
{
	if ( have_posts() ) :
	
		global $wp;
		$rel_canonical = ( isset( $wp->extra_query_vars['rel_canonical'] ) ) ? sprintf (
			'<link rel="canonical" href="%s"/>',
			esc_url( $wp->extra_query_vars['rel_canonical'] )
		) : get_search_link( $wp->query_vars['s'] );
		
		echo $rel_canonical;
	
	else :
		do_action( 'stt2extat_no_robots' );
	endif;
}

/**
 * filter of post type
 *
 * @since 1.1
 *
*/
function stt2extat_post_type()
{
	$post_type = array( 'post' );
	return apply_filters( 'stt2extat_post_type', $post_type );
}

/**
 * add meta robots no follow
 *
 * @since 1.1
 *
*/
function stt2extat_no_robots()
{
	return wp_no_robots();
}

/**
 * keep search page always sent header 200 for not allowed terms, not 404
 *
 * @since 1.1
 *
*/
function stt2extat_if_no_result( $query )
{
	global $wp;
	
	if ( isset( $wp->extra_query_vars['not_allowed'] ) && $wp->extra_query_vars['not_allowed'] && $query->is_main_query() )
		$query->set_404();
	
	return apply_filters( 'stt2extat_if_no_result', $query );
}

/**
 * filter wp_search_stopwords with our stopwords
 *
 * @since 1.1
 *
*/
function stt2extat_search_stopwords( $stopword )
{
	global $stt2extat_settings;
	
	if ( isset( $stt2extat_settings['stopwords'] ) )
		$stopword = wp_parse_args( $stt2extat_settings['stopwords'], $stopword );
	
	return $stopword;
}

/**
 * Filter search link structure with dashes
 *
 * @since 1.1
 *
*/
function __stt2extat_return_dash()
{
	return '-';
}

/**
 * Filter search link structure with underscore
 *
 * @since 1.1
 *
*/
function __stt2extat_return_underscore()
{
	return '_';
}

/**
 * Get Plugin Data
 *
 * @since 1.1
 *
*/
function stt2extat_get_plugin_data( $opt = null )
{
	$data = array();
	
	if ( ! function_exists( 'get_plugin_data' ) )
		require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
	
	if ( array_filter( get_plugin_data( STT2EXTAT_PLUGIN_FILE ) ) )
		$data = get_plugin_data( STT2EXTAT_PLUGIN_FILE );
	
	$data = ( object ) array_map( 'trim', $data );
	
	if( null != $opt )
		$data = ( isset( $data->$opt ) ) ? $data->$opt : '';
	
	return $data;
}

/**
 * Catch action when plugin activate
 *
 * @since 1.1
 *
*/
function stt2extat_is_activated()
{
    $return         = false;
    $activate       = filter_input( INPUT_GET, 'activate',       FILTER_SANITIZE_STRING );
    $activate_multi = filter_input( INPUT_GET, 'activate-multi', FILTER_SANITIZE_STRING );

    if( ! empty( $activate ) || ! empty( $activate_multi ) )
        $return = true;

    return $return;
}

/**
 * change default admin notice when activate plugin
 * plugin require min PHP 7.0 and WP Version 4.4
 * @see class STT2EXTAT_LOAD->deactivate_plugins()
 *
 * @since 1.1
 *
*/
function stt2extat_change_activate_notice( $translated_text, $untranslated_text, $domain )
{
	$old = array(
		'Plugin <strong>activated</strong>.',
		'Selected plugins <strong>activated</strong>.' 
	);
	
	$new = 'Plugin <strong>deactivated</strong>.';
	
	if ( in_array( $untranslated_text, $old, true ) )
		$translated_text = $new;
	
	return $translated_text;
}

/**
 * fire admin_notice
 *
 * @since 1.1
 *
*/
function stt2extat_admin_notices()
{
	global $stt2extat_settings, $wp_version;
	
	if ( ! empty( $stt2extat_settings['php_version'] ) &&  version_compare( phpversion(), $stt2extat_settings['php_version'], '<' ) )
		return stt2extat_upgrade_php();
	
	if ( ! empty( $stt2extat_settings['wp_version'] ) &&  version_compare( $wp_version, $stt2extat_settings['wp_version'], '<' ) )
		return stt2extat_upgrade_wp();
	
	if ( get_transient( 'stt2exat_go_to_settings' ) )
		echo get_transient( 'stt2exat_go_to_settings' );
	
	return stt2extat_nojs();
}

/*
 * ADMIN NOTICE
 *
 * @since 1.0
 *
 * give notice to upgrade WP under version 4.4
 *
 * @since 1.0
 *
*/
function stt2extat_upgrade_wp()
{
	global $stt2extat_settings, $wp_version;
	
	$url_version = 'https://codex.wordpress.org/Version_' . $stt2extat_settings['wp_version'];
	
	printf ( '<div id="message" class="update-nag notice is-dismissible"><b>%1$s</b> %2$s <b>%3$s</b>, %4$s <a href="%5$s" target="_blank">%6$s</a>. %7$s<button type="button" class="notice-dismiss"><span class="screen-reader-text">%8$s.</span></button></div>',
		stt2extat_get_plugin_data( 'Name' ),
		__( 'plugin can not be activated. Your WordPress version is', 'stt2extat' ),
		$wp_version,
		__( 'required minimum', 'stt2extat' ),
		esc_url( $url_version ),
		$stt2extat_settings['wp_version'],
		__( 'Please update yours.', 'stt2extat' ),
		__( 'Dismiss this notice', 'stt2extat' )
	);
}

/**
 * give notice to upgrade PHP Server, min PHP 7.0
 *
 * @since 1.1
 *
*/
function stt2extat_upgrade_php()
{
	global $stt2extat_settings;
	
	$url_version = 'http://www.php.net/downloads.php';
	$dev_url     = 'https://wordpress.org/plugins/stt2-extension-add-terms/developers/';
	
	printf ( '<div id="message" class="update-nag notice is-dismissible"><b>%1$s</b> %2$s <b>%3$s</b>, %4$s <a href="%5$s" target="_blank">%6$s</a>. %7$s <kbd>1.1.5-undev</kbd>, <a target="_blank" href="%8$s">%9$s</a>.<button type="button" class="notice-dismiss"><span class="screen-reader-text">%10$s.</span></button></div>',
		stt2extat_get_plugin_data( 'Name' ),
		__( 'plugin can not be activated. Your PHP server version is', 'stt2extat' ),
		phpversion(),
		__( 'required minimum', 'stt2extat' ),
		esc_url( $url_version ),
		$stt2extat_settings['php_version'],
		__( 'Please update your PHP, or you can still use this plugin under Undevelopment Version', 'stt2extat' ),
		esc_url( $dev_url ),
		__( 'download here' ),
		__( 'Dismiss this notice', 'stt2extat' )
	);
}

/**
 * give notice after install to manage this plugin
 *
 *
 * @since 1.0
 *
 * set as transient 'stt2exat_go_to_settings'
 *
 * @since 1.1
 *
*/
function stt2extat_go_to_settings()
{
	$plugin_data   = stt2extat_get_plugin_data();
	$currentscreen = get_current_screen();
	
	$page = 'options-general.php?page=' . sanitize_key( $plugin_data->TextDomain );
	
	if ( 'plugins' === $currentscreen->id ) :		
		$html = sprintf ( '<div id="message" class="updated notice is-dismissible"><p><b>%1$s v%2$s</b> %3$s <a href="%4$s">%5$s</a>.</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">%6$s.</span></button></div>',
			esc_html( $plugin_data->Name ),
			esc_html( $plugin_data->Version ),
			__( 'is ready! Manage', 'stt2extat' ), 
			admin_url( $page ), 
			__( 'here', 'stt2extat' ),
			__( 'Dismiss this notice', 'stt2extat' )
		);
		return $html;
	endif;
}

/**
 * give notice if browser javascript disable
 *
 * @since 1.0
 *
*/
function stt2extat_nojs()
{
	printf ( '<noscript><div id="message" class="error notice is-dismissible"><p>%1$s <b>%2$s</b> %3$s<button type="button" class="notice-dismiss"><span class="screen-reader-text">%4$s.</span></button></div></noscript>',
		__( 'Enable your browser javascript to load', 'stt2extat' ),
		stt2extat_get_plugin_data( 'Name' ),
		'plugin.',
		__( 'Dismiss this notice', 'stt2extat' )
	);
}


/**
 * give notice on edited terms
 *
 * @since 1.0
 *
*/
function stt2extat_edit_term_notice( $code, $error, $add_setting_error )
{
	if ( isset( $_GET['term_ID'] ) && '' == stt2extat_get_post_terms( absint( $_GET['post_ID'] ) ) )
		return;
	
	if ( false == $code )
		$code = 5;
	
	$code = absint( $code );
	
	$messages['searchterms'] = array(
		0  => '', // Unused. Messages start at index 1.
		1  => __( 'Term added.' ),
		2  => __( 'Term deleted.' ),
		3  => __( 'Term updated.' ),
		4  => __( 'Term not added.' ),
		5  => __( 'Term not updated.' ),
		6  => __( 'Term empty.', 'stt2extat' ),
		7  => __( 'Term was exists.', 'stt2extat' ),
		8  => __( 'Terms deleted.' ),
		9  => __( 'You do not have permission to do that.' ),
		10 => __( 'You attempted to edit an item that doesn&#8217;t exist. Perhaps it was deleted?.' ),
		11 => __( 'Term was deleted.' ),
		12 => __( 'Term too short ( min 4 characters ).', 'stt2extat' ),
		13 => __( 'Term too long ( max 70 characters ).', 'stt2extat' ),
		14 => __( 'An unidentified error has occurred.', 'stt2extat' ),
		15 => __( 'Terms migrated.', 'stt2extat' ),
	);
	
	if ( wp_validate_boolean( $add_setting_error ) ) :
		if ( isset( $messages['searchterms'][ $code ] ) ) 
			return $messages['searchterms'][ $code ];
		
		return $messages['searchterms'][1];
	endif;
	
	$class = 'updated';
	if ( wp_validate_boolean( $error ) )
		$class = 'error';
	
	printf ( '<div id="message" class="%1$s notice is-dismissible"><p>%2$s</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">%3$s.</span></button></div>',
		sanitize_html_class( $class ),
		esc_html( $messages['searchterms'][ $code ] ),
		__( 'Dismiss this notice', 'stt2extat' )
	);
	
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) :
		wp_die();
	endif;
}

/**
 * localize notice
 *
 * @since 1.1
 *
*/
function stt2extat_notice_localize()
{
	global $stt2extat_settings;
	
	$maxchar = ( isset( $stt2extat_settings['max_char'] ) ) ? $stt2extat_settings['max_char'] : 55;
	$excerpt = ( isset( $stt2extat_settings['searchexcerpt'] ) ) ? $stt2extat_settings['searchexcerpt'] : false;
		
	return array(
		1  => wp_create_nonce( 'stt2extat_action' ),
		2  => __( 'Remove Irrelevant Terms', 'stt2extat' ),
		3  => __( 'Terms of post: Empty!', 'stt2extat' ),
		4  => __( 'Not Updated.', 'stt2extat' ),
		5  => __( 'Contain Stopwords!', 'stt2extat' ),
		6  => __( 'Preview:', 'stt2extat' ),
		7  => __( 'Irrelevant!', 'stt2extat' ),
		8  => __( 'No Post Selected!', 'stt2extat' ),
		9  => __( 'No terms input!', 'stt2extat' ),
		10 => __( 'You do not have permission to do that.', 'stt2extat' ),
		11 => __( 'An unidentified error has occurred.', 'stt2extat' ),
		12 => __( 'No Post found', 'stt2extat' ),
		13 => array( __( 'Read More', 'stt2extat' ), '...' ),
		14 => __( 'Remove', 'stt2extat' ),
		15 => __( 'Add', 'stt2extat' ),
		16 => __( 'Too Short. Min 4 character (A-Z,a-z,0-9)', 'stt2extat' ),
		17 => __( 'Too Long. Max {$maxchar} character (A-Z,a-z,0-9)', 'stt2extat' ),
		18 => __( 'Success add to field', 'stt2extat' ),		
		19 => __( 'Your browser does not support for this plugin! Compatible for latest version Firefox, Safari, or Chrome.', 'stt2extat' ),
		20 => __( 'Collapse Tab', 'stt2extat' ),
		21 => intval( $maxchar ),
		22 => __( 'Success Updated!', 'stt2extat' ),
		23 => __( 'Loading...', 'stt2extat' ),
		24 => bool_from_yn( $excerpt ),
		25 => __( 'Fail to add into database.', 'stt2extat' ),
		26 => __( 'Please try another terms.', 'stt2extat' )
	);
}