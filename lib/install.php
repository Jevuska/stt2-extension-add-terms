<?php
/*
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 *
 * @since 1.0
 *
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

/**
 * Installing
 *
 * @since 1.0
 *
*/
register_activation_hook( STT2EXTAT_PLUGIN_FILE, array(
	'STT2EXTAT_Setup',
	'on_activation'
) );

register_deactivation_hook( STT2EXTAT_PLUGIN_FILE, array(
	'STT2EXTAT_Setup',
	'on_deactivation'
) );

register_uninstall_hook( STT2EXTAT_PLUGIN_FILE, array(
	'STT2EXTAT_Setup',
	'on_uninstall'
) );