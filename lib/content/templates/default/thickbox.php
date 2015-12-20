<?php
/*
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 * @since 1.0
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

/**
 * Template content of WP Thickbox
 *
 * @since 1.0
 * 
*/
check_admin_referer( 'heartbeat-nonce', '_wpnonce' );
printf ( '<input alt="#TB_inline?height=160&amp;width=400&amp;inlineId=gsuggest-popup" title="%s" class="thickbox gsuggest" type="hidden" value="" /><input alt="#TB_inline?height=160&amp;width=400&amp;inlineId=notmatch-popup" title="%s" class="thickbox notmatch" type="hidden" value="" />',
	__( 'Attention', 'stt2extat' ),
	__( 'Warning', 'stt2extat' )
);