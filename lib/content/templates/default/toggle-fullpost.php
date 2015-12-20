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
 * full post toggle template
 *
 * @since 1.0
 * 
 * old file name metabox-screenmeta.php
 * fixes html
 *
 * @since 1.1.6
 *
*/
printf ( '<div id="fullpost-toggle"><a href="#fullpost" class="fullpost-link" aria-controls="fullpost" aria-expanded="false"><span class="view-fullpost more" title="%1$s">%2$s</span></a></div>',
	esc_attr__( 'View Full Post', 'stt2extat' ),
	'...'
);
?>

