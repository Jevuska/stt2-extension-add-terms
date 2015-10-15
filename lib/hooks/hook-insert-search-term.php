<?php
/*
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 * @version 1.0
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

function stt2extat_insert_searchterm_callback()
{
    global $post;
    $terms  = $_POST['terms'];
    $ID     = $_POST['postid'];
    $ignore = $_POST['ignore'];
    $nonce  = $_REQUEST['wpnonce'];

    if ( ! wp_verify_nonce( $nonce, 'stt2extat_action' ) )
       wp_die( '1' );


    if ( wp_verify_nonce( $nonce, 'stt2extat_action' )  && check_admin_referer( 'stt2extat_action', 'wpnonce' ) )
	{
        $arr_searchterms = explode( ',', $terms );
		$result          = '';
        $listtermsArr1   = '';
		$listtermsArr2   = array();
		$listtermsArr3   = array();
		$listtermsArr4   = array();
		$listtermsArr5   = array();

        foreach ( $arr_searchterms as $term )
		{
			if ( ! empty( $term ) )
			{
				$searchterms = stt2extat_searchterms( $term );
				$termexist   = stt2extat_search_term_exist_db( $searchterms, $nonce );
				$relevant    = stt2extat_get_relevant_post( $ID, $searchterms, $ignore, $nonce );
				$opt         = json_decode( $relevant, true );
				$respons     = $opt[0]['respons'];
				
				if ( $termexist )
				{
					$post_id     = $termexist->post_id;
					$meta_value  = $termexist->meta_value;
					$meta_count  = $termexist->meta_count;
				
					stt2extat_save_search_terms_db( $meta_value, $post_id, $nonce );
					
					$listtermsArr1 .= sprintf( '<div id="message" class="notice notice-warning fade notice is-dismissible"><p style="margin: .5em 0"><kbd>%1$s</kbd> %2$s <kbd class="permalink">%3$s</kbd> <a target="_blank" href="%3$s" title="%4$s"><i class="dashicons dashicons-external"></i></a>. %5$s: <kbd>%6$s</kbd></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">%7$s</span></button></div>',
						$searchterms,
						__( 'already exist in', 'stt2extat' ),
						get_permalink( $post_id ),
						get_the_title( $post_id ),
						__( 'Hits', 'stt2extat' ),
						intval( $meta_count ),
						__( 'Dismiss this notice.', 'stt2extat' )
					);
					
				}
				else if ( ! pk_stt2_is_contain_bad_words( $searchterms ) && 3 < strlen( $searchterms ) && ( ! empty( $respons ) ) )
				{
					stt2extat_save_search_terms_db( $searchterms, $ID, $nonce );
					$listtermsArr2[] = $searchterms;
				}
				else if ( pk_stt2_is_contain_bad_words( $searchterms ) )
				{
					$listtermsArr3[] = $searchterms;
				} 
				else if ( empty( $respons ) )
				{
					$listtermsArr4[] = $searchterms;
				} 
				else 
				{
					$listtermsArr5[] = $searchterms;
				}
			}
            unset( $term );
	    }

        $searchtermlist    = implode( '</kbd><kbd>', $listtermsArr2 );
        $badwordstermlist  = implode( '</kbd><kbd>', $listtermsArr3 );
        $irrelevantermlist = implode( '</kbd><kbd>', $listtermsArr4 );
        $shortermlist      = implode( '</kbd><kbd>', $listtermsArr5 );
        $permalink         = get_permalink( $ID );
        $thetitle          = get_the_title( $ID );

		if ( ! empty( $searchtermlist ) )
		{
			$result = sprintf( '<div id="message" class="updated fade notice is-dismissible"><p><kbd>%1$s</kbd> has been added into <kbd class="permalink">%2$s</kbd> <a target="_blank" href="%2$s" title="%3$s"><i class="dashicons dashicons-external"></i></a></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">%4$s</span></button></div>',
				$searchtermlist,
				$permalink,
				$thetitle,
				__( 'Dismiss this notice.', 'stt2extat' )
			);
		}
		$result .= $listtermsArr1;

		if ( ! empty( $badwordstermlist ) )
		{
			$result .= sprintf( '<div id="message" class="error fade notice is-dismissible"><p><kbd>%1$s</kbd>%2$s</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">%3$s</span></button></div>',
				$badwordstermlist,
				__( 'contain badword(s), can not be added!.', 'stt2extat' ),
				__( 'Dismiss this notice.', 'stt2extat' )
			);
		}

		if ( ! empty( $irrelevantermlist ) )
		{
			$result .= sprintf( '<div id="message" class="error fade notice is-dismissible"><p><kbd>%1$s</kbd> %2$s</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">%3$s</span></button></div>',
				$irrelevantermlist,
				__( 'irrelevant, can not be added!.', 'stt2extat' ),
				__( 'Dismiss this notice.', 'stt2extat' )
			);
		}

		if( ! empty( $shortermlist ) )
		{
			$result .= sprintf( '<div id="message" class="error fade notice is-dismissible"><p><kbd>%1$s</kbd> %2$s</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">%3$s</span></button></div>',
				$shortermlist,
				__( 'too short, can not be added!.', 'stt2extat' ),
				__( 'Dismiss this notice.', 'stt2extat' )
			);
		}
		echo $result;
		
		wp_die(); 
	}
}
add_action( 'wp_ajax_stt2extat_insert_searchterm', 'stt2extat_insert_searchterm_callback' );