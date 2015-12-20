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
 * global setting function
 * @use global $stt2extat_settings
 *
 * @since 1.0
 *
*/
function stt2extat_settings()
{
	global $stt2extat_settings;
	
	if ( ! empty( $stt2extat_settings ) )
		return $stt2extat_settings;
	
	$stt2extat_settings = get_option( 'stt2extat_settings' );
	
	return $stt2extat_settings;
}

function stt2extat_default_setting( $option = '' )
{
	global $stt2extat_sanitize;
	
	$args = $stt2extat_sanitize->sanitize();
	
	switch ( $option )
	{
		case 'update' :
			return $args;
			break;
			
		case 'shortcode' :
			
			$default = array(
				'text_header',
				'html_heading',
				'number',
				'display',
				'count',
				'convert'
			);
			
			$args = wp_array_slice_assoc( $args, $default );
			
			return $args;
			break;
			
		default :
			$args = apply_filters( 'stt2extat_default_settings', $args );
			return $args;
			break;
	}
}