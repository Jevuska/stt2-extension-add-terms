<?php
/*
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 * @since 1.1
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

/**
 * Template content inside form if relevant posts exists
 *
 * @since 1.0
 * 
*/
check_admin_referer( 'heartbeat-nonce', '_wpnonce' );

printf ( '<div id="wp-link"><div class="link-search-wrapper search-box"><input type="text" id="wp-link-search" class="link-search-field" placeholder="%1$s" autocomplete="off" style="width: 63%%" /><div class="btnadd"></div><span class="spinner"></span><input type="hidden" id="notmatchdata" value=""></div><label for="gsuggest"><input type="checkbox" id="gsuggest" value="" /> %2$s</label>  <label for="notmatch"><input type="checkbox" id="notmatch" value="" /> %3$s</label>',
	__( 'Populate terms by comma ( min 4 characters )', 'stt2extat' ),
	__( 'Enable Google Suggestion', 'stt2extat' ),
	__( 'Add Irrelevant Term', 'stt2extat' )
);
	
wp_die();