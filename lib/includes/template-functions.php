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
 * template hint section
 *
 * @since 1.0.0
 *
 */
function stt2extat_hint_callback()
{
	return stt2extat_get_template_part( 'content', 'hint' );
}

/**
 * template tab content
 *
 * @since 1.0.0
 *
 */
function stt2extat_template_content_ajax()
{
	return stt2extat_get_template_part( 'content', 'form' );
}


/**
 * template thickbox on donate tab
 *
 * @since 1.0.0
 *
 */
function stt2extat_thickbox_callback()
{
	return stt2extat_get_template_part( 'content', 'thickbox' );
}

/**
 * template google sugestion fiture
 *
 * @since 1.0.0
 *
 */
function stt2extat_checkbox_google_callback()
{
	return stt2extat_get_template_part( 'content', 'checkbox-google' );
}

/**
 * template ignore irrelevant fiture
 *
 * @since 1.0.0
 *
 */
function stt2extat_checkbox_irrelevant_callback()
{
	return stt2extat_get_template_part( 'content', 'checkbox-irrelevant' );
}

/**
 * template list of terms post
 *
 * @since 1.0.0
 *
 */
function stt2extat_footer_callback()
{
	return stt2extat_get_template_part( 'content', 'searchterms' );
}

/**
 * template screenmeta to toggle view full post
 *
 * @since 1.0.0
 *
 */
function stt2extat_screenmeta_callback()
{
	return stt2extat_get_template_part( 'metabox', 'screenmeta' );
}

/**
 * template theme default
 *
 * @since 1.0.0
 *
 */
function stt2extat_theme_default()
{
	return stt2extat_get_template_part( 'themes', 'default' );
}

/**
 * filter of template path
 * use it to modified the theme
 *
 * @since 1.0.0
 *
 */
function stt2extat_template_path()
{
	return apply_filters( 'stt2extat_template_path', wp_normalize_path( STT2EXTAT_PATH_LIB_CONTENT . 'templates/' ) );
}

/**
 * function to get template name
 *
 * @since 1.0.0
 *
 */
function stt2extat_get_template_part( $template, $name )
{
	$path      = stt2extat_template_path() . $template;
	$filename  = $template . '-' . $name . '.php';
	$templates = $path . '/' . sanitize_file_name( $filename );
	include( $templates );
}
?>
