
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
 * Template content of Hint section
 *
 * @since 1.0
 * 
*/

check_admin_referer( 'heartbeat-nonce', '_wpnonce' );
global $stt2extat_sanitize;
echo ( $stt2extat_sanitize->data['help']['normal']['instruction']['content']
);