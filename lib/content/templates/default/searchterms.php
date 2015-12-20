<?php
/*
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 * @since 1.1
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

/**
 * Template content of button input field search terms
 *
 * @since 1.0
 * 
*/

if ( 'stt2extat_footer' != current_action() )
	return;

printf ( '<div id="prepare-key" class="key-inline"><span id="keylist"></span><input type="button" id="btn-key" class="btn-key button tagadd button-small" data-value="" value="%s" /></div>',
	__( 'OK', 'stt2extat' )
);