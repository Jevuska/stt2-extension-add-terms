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

$html_after = '';
$html = '<h3>%1$s</h3><p>%2$s: <strong><span id="maxchar">%3$s</span></strong></p><div class="inside"><div id="slider-range-max"></div></div>%4$s<p><div id="msgbt"></div></p>';

$after_form_setting = apply_filters( 'stt2extat_after_form_setting', $html_after );

printf( $html,
	__( 'Settings', 'stt2extat' ),
	__( 'Maximal Characters of Search Terms', 'stt2extat' ),
	intval( $stt2extat_settings['max_char'] ),
	$after_form_setting
);
wp_die();