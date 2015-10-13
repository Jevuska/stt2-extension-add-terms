<?php
/*
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 * @version 1.0
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

function stt2extat_plugin_file() {
  $pluginfile = STT2EXTAT_PLUGIN_BASENAME;
  return apply_filters( 'stt2extat_plugin_file',$pluginfile  );
}

function stt2extat_plugin_name_callback() {
	$plugin_ = get_plugins();
	$pluginfile_ = stt2extat_plugin_file();
    $plugin_name = $plugin_ [$pluginfile_]['Name'];
    print $plugin_name;
}
add_action('stt2extat_plugin_name','stt2extat_plugin_name_callback');

function stt2extat_menu_callback() {
  return stt2extat_get_template_part('content', 'menu-contextual');
}
add_action('stt2extat_menu_contextual','stt2extat_menu_callback');

function stt2extat_hint_callback() {
  return stt2extat_get_template_part('content', 'hint');
}
add_action('stt2extat_hint','stt2extat_hint_callback');

function stt2extat_tab_content_callback() {
  $nonce = $_REQUEST['wpnonce'];
  $tab = $_POST['tab'];
  if (! wp_verify_nonce( $nonce, 'stt2extat_action' ) )
	wp_die(__('Olala.. you are in wrong way.'));
  if (wp_verify_nonce( $nonce, 'stt2extat_action') && check_admin_referer( 'stt2extat_action', 'wpnonce' ) && isset($tab)):
	switch ($tab) {
		case "tab-panel-feature":
					return stt2extat_get_template_part('content', 'next-feature');
			break;
		case "tab-panel-settings":
					return stt2extat_get_template_part('content', 'settings');
			break;
		case "tab-panel-help":
					return stt2extat_get_template_part('content', 'help');
			break;
		case "tab-panel-donate":
					return stt2extat_get_template_part('content', 'donate');
			break;
		default:
					return stt2extat_get_template_part('content', 'form');
			break;
	}
  endif;
}
add_action('wp_ajax_stt2extat_tab_content','stt2extat_tab_content_callback');

function stt2extat_thickbox_callback() {
  return stt2extat_get_template_part('content', 'thickbox');
}
add_action('stt2extat_thickbox','stt2extat_thickbox_callback');

function stt2extat_checkbox_google_callback() {
  return stt2extat_get_template_part('content', 'checkbox-google');
}
add_action('stt2extat_checkbox_google','stt2extat_checkbox_google_callback');

function stt2extat_checkbox_irrelevant_callback() {
  return stt2extat_get_template_part('content', 'checkbox-irrelevant');
}
add_action('stt2extat_checkbox_irrelevant','stt2extat_checkbox_irrelevant_callback');

function stt2extat_footer() {
  return stt2extat_get_template_part('content', 'searchterms');
}

function stt2extat_screenmeta_callback() {
  return stt2extat_get_template_part('metabox', 'screenmeta');
}
add_action('stt2extat_screenmeta','stt2extat_screenmeta_callback');

function stt2extat_theme_default($nonce) {
	if ( ! wp_verify_nonce( $nonce, 'stt2extat_action')) 
		wp_die( __( 'Olala... please try again.' ) );
		return stt2extat_get_template_part('themes', 'default');
}

function stt2extat_template_path() {
  return apply_filters( 'stt2extat_template_path', STT2EXTAT_PATH_TEMPLATES );
}

function stt2extat_get_template_part($template,$name) {
  $path = stt2extat_template_path() . $template;
  $filename = $template.'-'.$name.'.php';
  $templates = $path.'/'.$filename;
  include( $templates);
}
?>
