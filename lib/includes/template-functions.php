<?php
/*
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 * @since 1.0
 *
 * move hook action and filter into hook.php
 *
 * @since 1.1.0
 *
 */

if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

/**
 * handling function via wp ajax to show form of this plugin
 *
 * @since 1.0
 *
*/
function stt2extat_form_handler()
{
	if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'heartbeat-nonce' ) ) 
		wp_die( '-1' );
	
	$index = stt2extat_tempate_index();
	return apply_filters( 'stt2extat_tempate_index', $index );
}


/**
 * template theme default
 *
 * @since 1.0.0
 *
 * Change function's name stt2extat_theme_default
 *
 * @since 1.1.6
 *
 */
function stt2extat_tempate_index()
{
	return stt2extat_get_template_part( 'index' );
}

/**
 * function to get template name
 *
 * @since 1.0.0
 *
 * fixes variables
 * 
 * @since 1.1.6
 *
 */
function stt2extat_get_template_part( $name, $part_name = null )
{
	$template_path = stt2extat_template_path();
	
	if ( null != $part_name )
		$name = "{$name}-{$part_name}";
	
	$filename = "{$name}.php";
	$part     = $template_path . '/' . sanitize_file_name( $filename );
	
	if ( file_exists( $part ) )
		include( $part );
	else
		wp_die( '<kbd>' . $filename . '</kbd> ' . __( 'not exists.', 'stt2extat' ) );
}

/**
 * filter of template path
 * use it to modified the theme
 *
 * @since 1.0.0
 *
 * fixes variables
 *
 * @since 1.1.6
 *
 */
function stt2extat_template_path()
{
	$template_name = get_option( 'stt2extat_template_name' );
	$template_path = wp_normalize_path( STT2EXTAT_PATH_LIB_CONTENT . 'templates/' );
	$path          = $template_path . 'default';
	if ( $template_name )
		$path = $template_path . $template_name;
	return $path;
}

require_once( stt2extat_template_path() . '/functions.php' );
?>
