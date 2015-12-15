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
 * Template content of Google Suggestion Feature
 *
 * @since 1.0
 *
*/

check_admin_referer( 'heartbeat-nonce', '_wpnonce' );
printf ( '<h2>%s</h2><strong>%s</strong>',
	__( 'Google Suggestion Feature', 'stt2extat' ),
	__( 'BY ENABLING THIS, YOU UNDERSTAND AND AGREE THAT YOU WILL BE SOLELY RESPONSIBLE FOR THE RISK OF ANY AND ALL DAMAGE OR LOSS FROM USE OF, OR INABILITY TO USE, THIS FEATURE.', 'stt2extat' )
);