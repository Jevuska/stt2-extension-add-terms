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
 * Build template for Manual Input
 *
 * @since 1.0
 *
 * change hook stt2extat_metabox_content
 *
 * @since 1.1.6
 *
*/

?>

<?php do_action( 'stt2extat_before_manage-field' ); ?>
<div id="stt2extat-manage" class="stt2extat-manage-field"></div>
<?php do_action( 'stt2extat_after_manage-field' ); ?>
<hr />
<div id="stt2extat-excerpt" class="stt2extat-excerpt-field sidebar-name"></div>
<?php do_action( 'stt2extat_toggle_fullpost' ); ?>
<div id="fullpost"></div>
<div id="msgb"></div>
<div id="gsuggest-popup">
	<?php do_action( 'stt2extat_checkbox_google' ); ?>
</div>
<div id="notmatch-popup">
	<?php do_action( 'stt2extat_checkbox_irrelevant' ); ?>
</div>
<?php do_action( 'stt2extat_thickbox' ); ?>
<div id="thehint"></div>
<?php die; ?>