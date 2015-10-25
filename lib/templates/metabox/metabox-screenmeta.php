<?php
/*
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 * @version 1.0
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

printf( '<div id="screen-meta-links" style="margin-top:-21px"><div id="contextual-help-link-wrap" class="hide-if-no-js screen-meta-toggle"><a href="#fullpost" id="contextual-help-link" class="show-settings" aria-controls="fullpost" aria-expanded="false"><span class="view-fullpost" title="%s"></span></a></div></div>',
	esc_attr__( 'View Full Post', 'stt2extat' )
);
?>

