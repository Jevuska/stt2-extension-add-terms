<?php
/*
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 * @version 1.0
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

$before_form = '';
$before_form = apply_filters( 'stt2extat_before_form', $before_form );

$after_insert_btn = '';
$after_insert_btn = apply_filters( 'stt2extat_after_insert_btn', $after_insert_btn );

$after_form = '';
$after_form = apply_filters( 'stt2extat_after_form', $after_form );

$html_form_top = '%1$s<form id="stt2-extat" method="post" action=""><div id="search-panel"><label><span class="search-label">%2$s</span></label><div id="titlediv"><input type="search" id="title" name="q" class="link-search-field" autocomplete="off" required/></div><div id="searchtermpost"></div><div id="searchdiv"></div><input type="hidden" id="id-field" name="id_post" class="link-search-field"  required/><label for="insertterms">%3$s</label><br><textarea name="insertterms" style="width:100%%" id="insertterms" class="searchterms" readonly="readonly" required></textarea><div id="badterms"></div><br><div id="ins-btn">';

$html_form_bottom = '%1$s<div class="loader" style="display: inline-block;padding-left: 10px"></div></div></div></form><div id="fullpost"></div><div id="msgb"></div>%2$s';

printf( $html_form_top,
	$before_form,
	__( 'Search post by your terms or type post url here:', 'stt2extat' ),
	__( 'Search terms will be inserted below then you can type manually, separated by a comma ( e.g.: keyword1, keyword2 ):', 'stt2extat' )
);

submit_button( 
	__( 'Insert', 'stt2extat' ),
	'button button-primary button-medium',
	'save_settings',
	false,
	array(
		'id' => 'btninsert',
		'disabled' => 'disabled'
	)
);

printf( $html_form_bottom,
	$after_insert_btn,
	$after_form
);
wp_die();
