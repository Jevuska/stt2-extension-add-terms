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
 * metabox of plugin content
 * 
 * @since 1.0
 *
*/
function stt2extat_metabox_content_callback()
{
	check_admin_referer( 'heartbeat-nonce', '_wpnonce' );
	?><div id="stt2extat-manage" class="stt2extat-manage-field"></div>
		<hr>
		<div id="stt2extat-excerpt" class="stt2extat-excerpt-field sidebar-name">
		</div>
		<?php do_action( 'stt2extat_screenmeta' ); ?>
		<div id="fullpost"></div>
		<div id="msgb"></div>
		<div id="gsuggest-popup">
			<?php do_action( 'stt2extat_checkbox_google' ); ?>
		</div>
		<div id="notmatch-popup">
			<?php do_action( 'stt2extat_checkbox_irrelevant' ); ?>
			<?php do_action( 'stt2extat_thickbox' ); ?>
		</div>
		<div id="thehint"></div><?php
	wp_die();
}