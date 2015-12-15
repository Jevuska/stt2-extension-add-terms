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
 * metabox of screen meta "full post" toggle
 *
 * @since 1.0
 *
*/
printf ( '<div id="fullpost-links"><div id="fullpost-wrap" class="hide-if-no-js fullpost-toggle"><a href="#fullpost" id="fullpost-link" class="show-settings" aria-controls="fullpost" aria-expanded="false"><span class="view-fullpost more" title="%1$s">%2$s</span></a></div></div>',
	esc_attr__( 'View Full Post', 'stt2extat' ),
	'...'
);
?>

