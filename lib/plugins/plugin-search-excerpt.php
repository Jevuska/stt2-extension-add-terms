<?php
//Search Excerpt Plugin by Scott Yang http://fucoder.com/code/search-excerpt/ 
//:: Modify

/*
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 * @version 1.0
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

function stt2exat_get_content($id) {
        $post = get_post($id); 
        if (!empty($post->post_password) ) {
            if (stripslashes($_COOKIE['wp-postpass_'.COOKIEHASH]) != 
                $post->post_password ) 
            {
                return get_the_password_form();
            }
        }
        return $post->post_content;
    }
    
function stt2exat_get_query($text) {
        static $last = null;
        static $lastsplit = null;

        if ($last == $text)
            return $lastsplit;
        $text = preg_replace('/[._-]+/', '', $text);

        $words = explode(' ', $text);

        $last = $text;
        $lastsplit = $words;

        return $words;
    }

function stt2exat_highlight_excerpt($keys, $text) {
        $text = strip_tags($text);

        for ($i = 0; $i < sizeof($keys); $i ++)
            $keys[$i] = preg_quote($keys[$i], '/');

        $workkeys = $keys;

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

                if (!isset($included[$key])) {
                    $included[$key] = 0;
                }

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

                        $q = stt2exat_jamul_find_1stbyte($text, max(0, $p - 60));
                        $q = stt2exat_jamul_find_delimiter($text, $q);
                        $s = stt2exat_jamul_find_1stbyte_reverse($text, $p + 80, $p);
                        $s = stt2exat_jamul_find_delimiter($text, $s);
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

        if (sizeof($ranges) == 0)
            return '<p>' . stt2exat_jamul_truncate($text, 256) . '&nbsp;...</p>';

        ksort($ranges);

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

        $out = array();
        foreach ($newranges as $from => $to)
            $out[] = substr($text, $from, $to - $from);

        $text = (isset($newranges[0]) ? '' : '...&nbsp;').
            implode('&nbsp;...&nbsp;', $out).'&nbsp;...';
        $text = preg_replace('/('.implode('|', $keys) .')/iu', 
                             '<strong class="search-excerpt">\0</strong>', 
                             $text);
        return "<p>$text</p>";
    }

function stt2exat_the_excerpt($text,$id) {
        static $filter_deactivated = false;
        global $more;
        global $wp_query;

        if (!$filter_deactivated) {
            remove_filter('the_excerpt', 'wpautop');
            $filter_deactivated = true;
        }

        $more = 1;
        $query = stt2exat_get_query($text);
        $content = stt2exat_get_content($id);

        return stt2exat_highlight_excerpt($query, $content);
}
define('_JAMUL_LEN_SEARCH', 15);

function stt2exat_jamul_find_1stbyte($string, $pos=0, $stop=-1) {
    $len = strlen($string);
    if ($stop < 0 || $stop > $len) {
        $stop = $len;
    }
    for (; $pos < $stop; $pos++) {
        if ((ord($string[$pos]) < 0x80) || (ord($string[$pos]) >= 0xC0)) {
            break;
        }
    }
    return $pos;
}

function stt2exat_jamul_find_1stbyte_reverse($string, $pos=-1, $stop=0) {
    $len = strlen($string);
    if ($pos < 0 || $pos >= $len) {
        $pos = $len - 1;
    }
    for (; $pos >= $stop; $pos--) {
        if ((ord($string[$pos]) < 0x80) || (ord($string[$pos]) >= 0xC0)) {
            break;
        }
    }
    return $pos;
}

function stt2exat_jamul_find_delimiter($string, $pos=0, $min = -1, $max=-1) {
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
        $pos = stt2exat_jamul_find_1stbyte($string, $pos, $max);
    }
    return $pos;
}

function stt2exat_jamul_truncate($string, $byte) {
    $len = strlen($string);
    if ($len <= $byte)
        return $string;
    $byte = stt2exat_jamul_find_1stbyte_reverse($string, $byte);
    return substr($string, 0, $byte);
}
?>