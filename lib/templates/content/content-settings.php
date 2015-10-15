<?php
/*
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 * @version 1.0
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

global $stt2extat_settings;
printf( '<h3>%s</h3><p>%s: <strong><span id="maxchar">%s</span></strong></p><div class="inside"><div id="slider-range-max"></div></div>',
	__( 'Settings', 'stt2extat' ),
	__( 'Maximal Characters of Search Terms', 'stt2extat' ),
	intval( $stt2extat_settings['stt2extat_max_char'] )
);
do_action( 'stt2extat_after_form_setting' );
echo( '<p><div id="msgbt"></div></p>' ),
wp_die();