<?php
/*
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 * @since 1.1
 *
 * all template functions
 *
 */

if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

/**
 * template form
 *
 * @since 1.0.0
 *
 * change function's name from stt2extat_template_content_ajax
 *
 * @since 1.1.6
 *
 */
function stt2extat_template_form_ajax()
{
	return stt2extat_get_template_part( 'form' );
}

/**
 * get search field to populate relevant terms
 * @deprecated see stt2extat_relevant_post_search_field_callback
 * 
 * @since 1.0.0
 *
 * sanitize $_POST and $_REQUEST and other variable
 *
 * @since 1.0.3
 *
 */
function stt2extat_search_field_ajax()
{
	if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'heartbeat-nonce' ) )
		wp_die( '-1' );
	
	return stt2extat_get_template_part( 'relevant-post-search-field' );
}

/**
 * template hint section
 *
 * @since 1.0.0
 *
 */
function stt2extat_hint_callback()
{
	return stt2extat_get_template_part( 'hint' );
}

/**
 * template thickbox on donate tab
 *
 * @since 1.0.0
 *
 */
function stt2extat_thickbox_callback()
{
	return stt2extat_get_template_part( 'thickbox' );
}

/**
 * template google sugestion fiture
 *
 * @since 1.0.0
 *
 */
function stt2extat_checkbox_google_callback()
{
	return stt2extat_get_template_part( 'checkbox-google' );
}

/**
 * template ignore irrelevant fiture
 *
 * @since 1.0.0
 *
 */
function stt2extat_checkbox_irrelevant_callback()
{
	return stt2extat_get_template_part( 'checkbox-irrelevant' );
}

/**
 * template list of terms post
 *
 * @since 1.0.0
 *
 */
function stt2extat_footer_callback()
{
	return stt2extat_get_template_part( 'searchterms' );
}

/**
 * template toggle view full post
 * @see content-toggle-fullpost.php
 *
 * @since 1.0.0
 *
 * rename function stt2extat_screenmeta_callback
 *
 * @since 1.1.6
 *
 */
function stt2extat_toggle_fullpost_callback()
{
	return stt2extat_get_template_part( 'toggle-fullpost' );
}