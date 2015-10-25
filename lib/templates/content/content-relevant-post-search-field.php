<?php
/*
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 * @version 1.0
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

printf( '<div id="wp-link"><div class="link-search-wrapper search-box"><input type="search" id="wp-link-search" name="s" class="link-search-field" placeholder="%1$s" autocomplete="off" style="width: 63%%" results=5><div class="btnadd"></div><span class="spinner"></span><input type="hidden" id="notmatchdata" value=""></div><label for="gsuggest"><input type="checkbox" id="gsuggest" value=""> %2$s</label>  <label for="notmatchfeat"><input type="checkbox" id="notmatchfeat" value=""> %3$s</label>',
	__( 'Populate relevant terms by comma ( min 4 characters )', 'stt2extat' ),
	__( 'Enable Google Suggestion', 'stt2extat' ),
	__( 'Ignore Irrelevant Notice', 'stt2extat' )
);

wp_die();