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
 * Template content of Manage form
 *
 * @since 1.0
 *
 * patch filter 
 *
 * @since 1.0.3
 * 
*/

if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'heartbeat-nonce' ) )
	wp_die( '-1' );

$before_form = '';
$before_form = apply_filters( 'stt2extat_before_form', $before_form );

$after_insert_btn = '';
$after_insert_btn = apply_filters( 'stt2extat_after_insert_btn', $after_insert_btn );

$after_form = '';
$after_form = apply_filters( 'stt2extat_after_form', $after_form );

$html_form_top = '%1$s<div id="search-panel"><label for="title"><h4 class="search-label">%2$s</h4></label><div id="titlediv"><input type="text" id="title" class="link-search-field" autocomplete="off" placeholder="Search..." /></div><div id="searchtermpost"></div><div id="searchdiv"></div><input type="hidden" id="id-field" class="link-search-field" /><label for="insertterms"><em>%3$s</em></label><br><textarea id="insertterms" class="searchterms large-text" aria-expanded="false"></textarea><div id="badterms"></div><br><div id="ins-btn"><input type="button" id="btninsert" class="button button-large" value="%4$s" disabled="disabled" />%5$s<div class="loader" style="display: inline-block;padding-left: 10px"></div></div></div>%6$s';

printf ( $html_form_top,
	$before_form,
	__( 'Search post by terms, post ID, or post url:', 'stt2extat' ),
	__( 'The terms will be inserted below, you can type or paste manually, add per-line, or separated by a comma ( e.g.: term1, term2 ):', 'stt2extat' ),
	__( 'Insert', 'stt2extat' ),
	$after_insert_btn,
	$after_form
);
wp_die();
