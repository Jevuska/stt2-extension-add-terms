<?php
/*
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 * @version 1.0
 */

if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

add_action( 'stt2extat_plugin_name', 'stt2extat_plugin_name_callback' );
add_action( 'stt2extat_menu_contextual', 'stt2extat_menu_callback' );
add_action( 'stt2extat_hint', 'stt2extat_hint_callback' );
add_action( 'wp_ajax_stt2extat_tab_content', 'stt2extat_tab_content_callback' );
add_action( 'stt2extat_thickbox', 'stt2extat_thickbox_callback' );
add_action( 'stt2extat_checkbox_google', 'stt2extat_checkbox_google_callback' );
add_action( 'stt2extat_checkbox_irrelevant', 'stt2extat_checkbox_irrelevant_callback' );
add_filter( 'stt2extat_footer', 'stt2extat_footer_callback' );
add_action( 'stt2extat_screenmeta', 'stt2extat_screenmeta_callback' );

function stt2extat_plugin_file()
{
	$pluginfile = STT2EXTAT_PLUGIN_BASENAME;
	return apply_filters( 'stt2extat_plugin_file', $pluginfile );
}

function stt2extat_plugin_name_callback()
{
	$plugin      = get_plugins();
	$pluginfile  = stt2extat_plugin_file();
    $plugin_name = $plugin[ $pluginfile ]['Name'] . ' v.' . $plugin[ $pluginfile ]['Version'];
    echo $plugin_name;
}

function stt2extat_menu_callback()
{
	return stt2extat_get_template_part( 'content', 'menu-contextual' );
}

function stt2extat_hint_callback()
{
	return stt2extat_get_template_part( 'content', 'hint' );
}

function stt2extat_tab_content_callback()
{
	if ( ! wp_verify_nonce( $_REQUEST['wpnonce'], 'stt2extat_action' ) )
		wp_die( __( 'Olala.. you are in wrong way.' ) );

	if ( check_admin_referer( 'stt2extat_action', 'wpnonce' ) && isset( $_POST['tab'] ) ) :
		$tab   = sanitize_html_class( $_POST['tab'] );
		switch ( $tab )
		{
			case 'tab-panel-feature':
				return stt2extat_get_template_part( 'content', 'next-feature' );
			break;
		
			case 'tab-panel-settings':
				return stt2extat_get_template_part( 'content', 'settings' );
			break;
		
			case 'tab-panel-help':
				return stt2extat_get_template_part( 'content', 'help' );
			break;
		
			case 'tab-panel-donate':
				return stt2extat_get_template_part( 'content', 'donate' );
			break;
		
			default:
				return stt2extat_get_template_part( 'content', 'form' );
			break;
		}
	endif;
}

function stt2extat_thickbox_callback()
{
	return stt2extat_get_template_part( 'content', 'thickbox' );
}

function stt2extat_checkbox_google_callback()
{
	return stt2extat_get_template_part( 'content', 'checkbox-google' );
}

function stt2extat_checkbox_irrelevant_callback()
{
	return stt2extat_get_template_part( 'content', 'checkbox-irrelevant' );
}

function stt2extat_footer_callback()
{
	return stt2extat_get_template_part( 'content', 'searchterms' );
}

function stt2extat_screenmeta_callback()
{
	return stt2extat_get_template_part( 'metabox', 'screenmeta' );
}

function stt2extat_theme_default()
{
	return stt2extat_get_template_part( 'themes', 'default' );
}

function stt2extat_template_path()
{
	return apply_filters( 'stt2extat_template_path', STT2EXTAT_PATH_TEMPLATES );
}

function stt2extat_get_template_part( $template, $name )
{
	$path      = stt2extat_template_path() . $template;
	$filename  = $template . '-' . $name . '.php';
	$templates = $path . '/' . sanitize_file_name( $filename );
	include( $templates );
}
?>
