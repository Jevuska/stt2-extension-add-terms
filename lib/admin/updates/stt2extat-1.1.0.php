<?php
/* @package STT2EXTAT
 * @category Core
 * @author Jevuska
 *
 **
 * if update version exist, get all new options after install
 *
 * @since 1.1.0
 *
 */

 if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

$stt2extat_settings = stt2extat_settings();

$new_fields_defaults = array(
	'plugin_version'    => STT2EXTAT_VER,
	'plugin_db_version' => STT2EXTAT_DB_VER,
	'wp_version'        => '4.4',
	'php_version'       => '7.0',
);

foreach ( $new_fields_defaults as $key => $value ) :
	if ( ! isset( $stt2extat_settings[ $key ] ) )
		$stt2extat_settings[ $key ] = $value;
endforeach;