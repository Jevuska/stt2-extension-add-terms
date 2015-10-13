<?php
/*
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 * @version 1.0
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

function stt2extat_metabox_content_callback(){ 
  if ( check_admin_referer( 'stt2extat_action', 'stt2extat_nonce' ) ):
?>
<div class="postbox-container" style="width: 98%;"><div class="metabox-holder"><div class="meta-box-sortables ui-sortable"><div id="stt2-insert" class="postbox"><div class="handlediv" title="Click to toggle"></div><h3 class="hndle"><span class="dashicons dashicons-search"></span> <span><?php do_action('stt2extat_plugin_name'); ?></span></h3><div class="inside stt2-extat"><div id="contextual-help-wrap" class="hidden" tabindex="-1" aria-label="Contextual Help Tab" style="display: block;"><div id="contextual-help-back"><div id="tog" class="tab-arrow-right"></div></div><div id="contextual-help-columns"><div class="contextual-help-tabs"><?php do_action('stt2extat_menu_contextual'); ?></div><div class="contextual-help-sidebar"><?php do_action('stt2extat_hint'); ?></div><div class="contextual-help-tabs-wrap"><div id="tab-panel-manage" class="help-tab-content active" style=""></div><div id="tab-panel-settings" class="help-tab-content" style="display: none;"></div><div id="tab-panel-feature" class="help-tab-content" style="display: none;"></div><div id="tab-panel-help" class="help-tab-content" style="display: none;"></div><div id="tab-panel-donate" class="help-tab-content" style="display: none;"></div></div></div></div></div></div><?php do_action('stt2extat_screenmeta'); ?></div></div></div><div id="gsuggestPopup"><?php do_action('stt2extat_checkbox_google'); ?></div><div id="notmatchfeatPopup"><?php do_action('stt2extat_checkbox_irrelevant'); ?><?php do_action('stt2extat_thickbox'); ?></div><div id="thehint"><?php do_action('stt2extat_hint'); ?></div>
<?php
 endif;
 wp_die();
}
add_action('stt2extat_metabox_content','stt2extat_metabox_content_callback');
?>