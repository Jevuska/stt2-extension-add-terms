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
 * fix variables and object
 * 
 * @since 1.1.9
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
			
			$searchterms = ( isset( $args['searchterms'] ) ) ? sanitize_text_field( $args['searchterms'] ) : null;
			$post_id     = ( isset( $args['post_id'] ) ) ? absint( $args['post_id'] ) : null;
			$highlight   = ( isset( $args['highlight'] ) ) ? $args['highlight'] : '<mark>\0</mark>';
			
			if ( null == $searchterms ) :
				$text = stt2extat_filter_text( $wp->query_vars['s'] );
				$this->text = ( ! is_array( $text ) ) ? $text : '';
			else :
				$this->text = $searchterms;
			endif;
			
			$this->post_id   = $post_id;
			$this->highlight = $highlight;
			
		}
		
		public function get_content() {
			// Get the content of current post. We like to have the entire
			// content. If we call get_the_content() we'll only get the teaser +
			// page 1.

		   $post = get_post( $this->post_id );
			
			// Password checking copied from
			// template-functions-post.php/get_the_content()
			// Search shouldn't match a passworded entry anyway.
			if (!empty($post->post_password) ) { // if there's a password
				if (stripslashes($_COOKIE['wp-postpass_'.COOKIEHASH]) != 
					$post->post_password ) 
				{      // and it doesn't match the cookie
					return get_the_password_form();
				}
			}

			return $post->post_content;
		}
		
		public function get_query($text) {
			static $last = null;
			static $lastsplit = null;

			if ($last == $text)
				return $lastsplit;

			// The dot, underscore and dash are simply removed. This allows
			// meaningful search behaviour with acronyms and URLs.
			$text = preg_replace('/[._-]+/', '', $text);

			// Process words
			$words = explode(' ', $text);

			// Save last keyword result
			$last = $text;
			$lastsplit = $words;

			return $words;
		}

		public function highlight_excerpt($keys, $text) {
			$text = strip_tags($text);

			for ($i = 0; $i < sizeof($keys); $i ++)
				$keys[$i] = preg_quote($keys[$i], '/');

			$workkeys = $keys;

			// Extract a fragment per keyword for at most 4 keywords.  First we
			// collect ranges of text around each keyword, starting/ending at
			// spaces.  If the sum of all fragments is too short, we look for
			// second occurrences.
			$ranges = array();
			$included = array();
			$length = 0;
			while ($length < 256 && count($workkeys)) {
				foreach ($workkeys as $k => $key) {
					if (strlen($key) == 0) {
						unset($workkeys[$k]);
						continue;
					}
					if ($length >= 256) {
						break;
					}
					// Remember occurrence of key so we can skip over it if more
					// occurrences are desired.
					if (!isset($included[$key])) {
						$included[$key] = 0;
					}

					// NOTE: extra parameter for preg_match requires PHP 4.3.3
					if (preg_match('/'.$key.'/iu', $text, $match, 
								   PREG_OFFSET_CAPTURE, $included[$key])) 
					{
						$p = $match[0][1];
						$success = 0;
						if (($q = strpos($text, ' ', max(0, $p - 60))) !== false && 
							 $q < $p)
						{
							$end = substr($text, $p, 80);
							if (($s = strrpos($end, ' ')) !== false && $s > 0) {
								$ranges[$q] = $p + $s;
								$length += $p + $s - $q;
								$included[$key] = $p + 1;
								$success = 1;
							}
						}

						if (!$success) {
							// for the case of asian text without whitespace
							$q = _jamul_find_1stbyte($text, max(0, $p - 60));
							$q = _jamul_find_delimiter($text, $q);
							$s = _jamul_find_1stbyte_reverse($text, $p + 80, $p);
							$s = _jamul_find_delimiter($text, $s);
							if (($s >= $p) && ($q <= $p)) {
								$ranges[$q] = $s;
								$length += $s - $q;
								$included[$key] = $p + 1;
							} else {
								unset($workkeys[$k]);
							}
						}
					} else {
						unset($workkeys[$k]);
					}
				}
			}

			// If we didn't find anything, return the beginning.
			if (sizeof($ranges) == 0)
				return '<p>' . _jamul_truncate($text, 256) . '&nbsp;...</p>';

			// Sort the text ranges by starting position.
			ksort($ranges);

			// Now we collapse overlapping text ranges into one. The sorting makes
			// it O(n).
			$newranges = array();
			foreach ($ranges as $from2 => $to2) {
				if (!isset($from1)) {
					$from1 = $from2;
					$to1 = $to2;
					continue;
				}
				if ($from2 <= $to1) {
					$to1 = max($to1, $to2);
				} else {
					$newranges[$from1] = $to1;
					$from1 = $from2;
					$to1 = $to2;
				}
			}
			$newranges[$from1] = $to1;

			// Fetch text
			$out = array();
			foreach ($newranges as $from => $to)
				$out[] = substr($text, $from, $to - $from);

			$text = (isset($newranges[0]) ? '' : '...&nbsp;').
				implode('&nbsp;...&nbsp;', $out).'&nbsp;...';
			$text = preg_replace('/('.implode('|', $keys) .')/iu', 
								 $this->highlight, 
								 $text);
			return "<p>$text</p>";
		}

		public function the_excerpt($text) {
			static $filter_deactivated = false;
			global $more;
			
			// If we are not in a search - simply return the text unmodified.
			if ( ! is_search() && ! is_admin() )
				return $text;
			
			// Deactivating some of the excerpt text.
			if (!$filter_deactivated) {
				remove_filter('the_excerpt', 'wpautop');
				$filter_deactivated = true;
			}

			// Get the whole document, not just the teaser.
			$more = 1;
			$query = STT2EXTAT_SearchExcerpt::get_query( $this->text );
			$content = STT2EXTAT_SearchExcerpt::get_content();

			return STT2EXTAT_SearchExcerpt::highlight_excerpt($query, $content);
		}
	}
}
// The number of bytes used when WordPress looking around to find delimiters
// (either a whitespace or a point where ASCII and other character switched).
// This also represents the number of bytes of few characters.

if ( ! defined( '_JAMUL_LEN_SEARCH' ) )
	define( '_JAMUL_LEN_SEARCH', 15 );

if ( ! function_exists( '_jamul_find_1stbyte' ) ) :
	function _jamul_find_1stbyte($string, $pos=0, $stop=-1) {
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
	function _jamul_find_1stbyte_reverse($string, $pos=-1, $stop=0) {
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
	function _jamul_find_delimiter($string, $pos=0, $min = -1, $max=-1) {
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
	function _jamul_truncate($string, $byte) {
		$len = strlen($string);
		if ($len <= $byte)
			return $string;
		$byte = _jamul_find_1stbyte_reverse($string, $byte);
		return substr($string, 0, $byte);
	}
endif;

function stt2exat_the_excerpt( $text, $searchterms = null, $post_id = null, $highlight = '<mark>\0</mark>' )
{
	global $stt2extat_settings;
	
	$option = ( isset( $stt2extat_settings['searchexcerpt'] ) ) ? $stt2extat_settings['searchexcerpt'] : 'y';
	$option = apply_filters( 'stt2exat_enable_excerpt', $option );
	
	if ( ! is_admin() && 'y' != $option )
		return $text;
	
	$filter = array( $searchterms, $post_id, $highlight );
	
	if ( ! is_admin() )
	{
		$filter = array( get_search_query(), get_the_ID(), $highlight );
		$filter = apply_filters( 'stt2exat_excerpt_option', $filter );
	}
	
	if ( is_admin() && ( 'get_the_excerpt' != current_action() || ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) )
		return false;
	
	$args = array(
		'searchterms' => esc_attr( $filter[0] ),
		'post_id'     => absint( $filter[1] ),
		'highlight'   => ( false !== strpos( $filter[2], '\0' ) ) ? $filter[2] : $highlight
	);
	
	$excerpt = new STT2EXTAT_SearchExcerpt( $args );
	return $excerpt->the_excerpt( $text );
}

/*

History:

1.1 (2006-05-08)
- Merge in Jam's unicode fixes. 
  http://pobox.com/~jam/unix/wordpress/#plugins
- Try to be executed before wp_trim_excerpt() to avoid Aleksandar's issue.
- Use own get_content() function to bypass WP's pagination.

1.0 (2005-08-22)
- Initial release.

*/
?>