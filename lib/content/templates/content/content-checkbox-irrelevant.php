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
 * Template content of Ignore Irrelevant Post
 *
 * @since 1.0
 *
*/
check_admin_referer( 'heartbeat-nonce', '_wpnonce' );
printf ( '<h2>%s</h2><strong>%s</strong>',
	__( 'Ignore Irrelevant Post', 'stt2extat' ),
	__( 'By checking this, you can add irrelevant terms into selected post. So, be a wise if you need to use it, provide users with the most relevant terms and a great user experience.', 'stt2extat' )
);