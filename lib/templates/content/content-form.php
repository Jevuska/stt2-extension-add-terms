<?php
/*
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 * @version 1.0
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

do_action( 'stt2exatat_before_form' );
printf( '<form id="stt2-extat" method="post" action=""><div id="search-panel"><label><span class="search-label">%s</span></label><div id="titlediv"><input type="search" id="title" name="q" class="link-search-field" autocomplete="off" required/></div><div id="searchtermpost"></div><div id="searchdiv"></div><input type="hidden" id="id-field" name="id_post" class="link-search-field"  required/><label for="insertterms">%s</label><br><textarea name="insertterms" style="width:100%%" id="insertterms" class="searchterms" readonly="readonly" required></textarea><div id="badterms"></div><br><div id="ins-btn">',
	__( 'Search post by your terms or type post url here:', 'stt2extat' ),
	__( 'Search terms will be inserted below then you can type manually, separated by a comma ( e.g.: keyword1, keyword2 ):', 'stt2extat' )
);
submit_button( __( 'Insert', 'stt2extat' ), 'button button-primary button-medium', 'save_settings', false, array( 'id' => 'btninsert', 'disabled' => 'disabled' ) );
do_action( 'stt2exatat_after_insert_btn' );
echo( '<div class="loader" style="display: inline-block;padding-left: 10px"></div></div></div></form><div id="fullpost"></div><div id="msgb"></div>' );
do_action( 'stt2exatat_after_form' );
wp_die();
