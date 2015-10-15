<?php
/*
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 * @version 1.0
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;
 
function stt2extat_get_settings()
{
	global $stt2extat_settings;
	
	if ( ! empty( $stt2extat_settings ) )
		return $stt2extat_settings;
	
	$stt2extat_settings = get_option( 'stt2extat_settings' );

	return $stt2extat_settings;
}

function stt2extat_settings( $meta_key = '' )
{
	global $stt2extat_settings;
	if ( ! empty( $stt2extat_settings ) )
		return $stt2extat_settings;
	
	$stt2extat_settings = get_option( 'stt2extat_settings' );
	if ( ! empty( $meta_key ) ) :
		return $stt2extat_settings[ $meta_key ];
	else:
		return $stt2extat_settings;
	endif;
	return $stt2extat_settings;
}
