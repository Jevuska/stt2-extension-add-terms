<?php
/*
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 * @since 1.0
 *
 * create class STT2EXTAT_SearchExcerpt
 * modified class of Search Excerpt Plugin by Scott Yang
 * @ https://vip-svn.wordpress.com/plugins/search-excerpt/ylsy_search_excerpt.php
 *
 * @since 1.1.0
 *
 */

if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

/**
 * get the post excerpt of each terms
 *
 * @since 1.0
 *
 * class STT2EXTAT_SearchExcerpt
 * hook filter stt2exat_excerpt_option
 * @param $searchterms = null
 * @param $post_id = null
 * @param $highlight = <mark>\0</mark>
 *
 * hook filter stt2exat_enable_excerpt
 * @param $searchexcerpt ( 'y' or 'n')
 *
 * @since 1.1.0
 *
*/

if ( ! class_exists( 'STT2EXTAT_SearchExcerpt' ) )
{
	class STT2EXTAT_SearchExcerpt
	{
		public function __construct ( $args )
		{
			global $stt2extat_query, $wp;
			
			if ( is_admin() )
				$wp = $stt2extat_query;
			
			if ( null == $args['searchterms'] )
				$this->text = stt2extat_filter_text( $wp->query_vars['s'] );
			else
				$this->text = sanitize_text_field( $args['searchterms'] );
			
			$this->post_id   = absint( $args['post_id'] );
			$this->highlight = $args['highlight'];
		}
		
		public function get_content()
		{
			$post = get_post( $this->post_id );
			
			if ( ! empty( $post->post_password ) )
				if ( stripslashes( $_COOKIE['wp-postpass_'.COOKIEHASH] ) != $post->post_password ) 
					return get_the_password_form();
				
			return $post->post_content;
		}
		
		public function get_query()
		{
			static $last      = null;
			static $lastsplit = null;

			if ( $last == $this->text )
				return $lastsplit;
			
			$text      = preg_replace( '/[._-]+/', '', $this->text );
			$words     = explode( ' ', $text );
			$last      = $text;
			$lastsplit = $words;
			return $words;
		}
		
		public function highlight_excerpt()
		{
			$keys = $this->get_query();
			$text = $this->get_content();
			
			$text = strip_tags( $text );

			for ( $i = 0; $i < sizeof( $keys ); $i ++ )
				$keys[ $i ] = preg_quote( $keys[ $i ], '/' );
			
			$workkeys   = $keys;
			$ranges     = array();
			$included   = array();
			$length     = 0;
			while ( 256 > $length && count( $workkeys ) ) :
			
				foreach ( $workkeys as $k => $key ) :
				
					if ( 0 == strlen( $key ) ) :
						unset( $workkeys[ $k ] );
						continue;
					endif;
						
					if ( 256 <= $length )
						break;
						
					if ( ! isset( $included[ $key ] ) )
						$included[ $key ] = 0;
						
					if ( preg_match( '/'.$key.'/iu', $text, $match, PREG_OFFSET_CAPTURE, $included[ $key ] ) ) :
					
						$p       = $match[0][1];
						$success = 0;
						
						if ( ( $q = strpos( $text, ' ', max( 0, $p - 60 ) ) ) !== false && $q < $p ) :
						
							$end = substr( $text, $p, 80 );
							
							if ( ( $s = strrpos( $end, ' ' ) ) !== false && 0 < $s ) :
								$ranges[ $q ]     = $p + $s;
								$length          += $p + $s - $q;
								$included[ $key ] = $p + 1;
								$success          = 1;
							endif;
							
						endif;
							
						if ( ! $success ) :
						
							$q = _jamul_find_1stbyte( $text, max( 0, $p - 60 ) );
							$q = _jamul_find_delimiter( $text, $q );
							$s = _jamul_find_1stbyte_reverse( $text, $p + 80, $p );
							$s = _jamul_find_delimiter( $text, $s );
								
							if ( ( $s >= $p ) && ( $q <= $p ) ) :
							
								$ranges[ $q ]     = $s;
								$length          += $s - $q;
								$included[ $key ] = $p + 1;
								
							else :
							
								unset( $workkeys[ $k ] );
								
							endif;
							
						endif;
						
					else :
					
						unset( $workkeys[ $k ] );
						
					endif;
					
				endforeach;
				
			endwhile;

			if ( 0 == sizeof( $ranges ) )
				return '<p>' . _jamul_truncate( $text, 256 ) . '&nbsp;...</p>';

			ksort( $ranges );
			$newranges = array();
				
			foreach ( $ranges as $from2 => $to2 ) :
				if ( ! isset( $from1 ) ) :
					$from1 = $from2;
					$to1   = $to2;
					continue;
				endif;
				
				if ( $from2 <= $to1 ) :
					$to1 = max( $to1, $to2 );
				else :
					$newranges[ $from1 ] = $to1;
					$from1               = $from2;
					$to1                 = $to2;
				endif;
			endforeach;
			
			$newranges[ $from1 ] = $to1;

			$out = array();
			foreach ( $newranges as $from => $to )
				$out[] = substr( $text, $from, $to - $from );
				
			$text = ( isset( $newranges[0] ) ? '' : '...&nbsp;' ) . implode( '&nbsp;...&nbsp;', $out ) . '&nbsp;...';
			$text = preg_replace( '/( ' . implode( ' | ', array_map( 'trim', $keys ) ) . ' )/iu',
				$this->highlight,
				$text
			);
			return "<p>$text</p>";
		}
	
		public function the_excerpt()
		{
			static $filter_deactivated = false;
			global $more;

			if ( '' == $this->text )
				return false;
			
			if ( ! $filter_deactivated ) :
				remove_filter( 'the_excerpt', 'wpautop' );
				$filter_deactivated = true;
			endif;
			
			$more    = 1;
			return $this->highlight_excerpt();
		}
	}
}

if ( ! defined( '_JAMUL_LEN_SEARCH' ) )
	define('_JAMUL_LEN_SEARCH', 15);

if ( ! function_exists( '_jamul_find_1stbyte' ) ) :
	function _jamul_find_1stbyte($string, $pos=0, $stop=-1)
	{
		$len = strlen($string);
		if ($stop < 0 || $stop > $len) {
			$stop = $len;
		}
		for (; $pos < $stop; $pos++) {
			if ((ord($string[$pos]) < 0x80) || (ord($string[$pos]) >= 0xC0)) {
				break;      // find 1st byte of multi-byte characters.
			}
		}
		return $pos;
	}
	
endif;

if ( ! function_exists( '_jamul_find_1stbyte_reverse' ) ) :
	function _jamul_find_1stbyte_reverse($string, $pos=-1, $stop=0)
	{
		$len = strlen($string);
		if ($pos < 0 || $pos >= $len) {
			$pos = $len - 1;
		}
		for (; $pos >= $stop; $pos--) {
			if ((ord($string[$pos]) < 0x80) || (ord($string[$pos]) >= 0xC0)) {
				break;      // find 1st byte of multi-byte characters.
			}
		}
		return $pos;
	}
	
endif;

if ( ! function_exists( '_jamul_find_delimiter' ) ) :

	function _jamul_find_delimiter($string, $pos=0, $min = -1, $max=-1)
	{
		$len = strlen($string);
		if ($pos == 0 || $pos < 0 || $pos >= $len) {
			return $pos;
		}
		if ($min < 0) {
			$min = max(0, $pos - _JAMUL_LEN_SEARCH);
		}
		if ($max < 0 || $max >= $len) {
			$max = min($len - 1, $pos + _JAMUL_LEN_SEARCH);
		}
		if (ord($string[$pos]) < 0x80) {
			// Found ASCII character at the trimming point.  So, trying
			// to find new trimming point around $pos.  New trimming point
			// should be on a whitespace or the transition from ASCII to
			// other character.
			$pos3 = -1;
			for ($pos2 = $pos; $pos2 <= $max; $pos2++) {
				if ($string[$pos2] == ' ') {
					break;
				} else if ($pos3 < 0 && ord($string[$pos2]) >= 0x80) {
					$pos3 = $pos2;
				}
			}
			if ($pos2 > $max && $pos3 >= 0) {
				$pos2 = $pos3;
			}
			if ($pos2 > $max) {
				$pos3 = -1;
				for ($pos2 = $pos; $pos2 >= $min; $pos2--) {
					if ($string[$pos2] == ' ') {
						break;
					} else if ($pos3 < 0 && ord($string[$pos2]) >= 0x80) {
						$pos3 = $pos2 + 1;
					}
				}
				if ($pos2 < $min && $pos3 >= 0) {
					$pos2 = $pos3;
				}
			}
			if ($pos2 <= $max && $pos2 >= $min) {
				$pos = $pos2;
			}
		} else if ((ord($string[$pos]) >= 0x80) || (ord($string[$pos]) < 0xC0)) {
			$pos = _jamul_find_1stbyte($string, $pos, $max);
		}
		return $pos;
	}
	
endif;

if ( ! function_exists( '_jamul_truncate' ) ) :

	function _jamul_truncate($string, $byte)
	{
		$len = strlen($string);
		if ($len <= $byte)
			return $string;
		$byte = _jamul_find_1stbyte_reverse($string, $byte);
		return substr($string, 0, $byte);
	}
	
endif;

function stt2exat_the_excerpt( $searchterms = null, $post_id = null, $highlight = '<mark>\0</mark>' )
{
	global $stt2extat_settings, $current_screen, $stt2extat_screen_id;
	
	if ( ! isset( $stt2extat_settings['searchexcerpt'] ) ) :
		$stt2extat_settings = array();
		$stt2extat_settings['searchexcerpt'] = 'y';
	endif;
	
	$searchexcerpt = apply_filters( 'stt2exat_enable_excerpt', $stt2extat_settings['searchexcerpt'] );
	
	if ( ! is_admin() && 'y' !== $searchexcerpt )
		return false;
	
	$filter = array( $searchterms, $post_id, $highlight );
	
	if ( ! is_admin() )
		$filter = apply_filters( 'stt2exat_excerpt_option', $filter );
	
	if ( ! is_array( $filter ) || ! array_filter( $filter ) )
		return false;
	
	if ( is_admin() )
	{
		if( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX )
			return false;
	}
	
	$args = array(
		'searchterms' => esc_attr( $filter[0] ),
		'post_id'     => absint( $filter[1] ),
		'highlight'   => ( false !== strpos( $filter[2], '\0' ) ) ? $filter[2] : $highlight
	);

	$excerpt = new STT2EXTAT_SearchExcerpt( $args );
	return $excerpt->the_excerpt();
}
?>