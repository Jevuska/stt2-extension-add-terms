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
?>
<h3><?php _e("Settings","stt2extat") ?></h3>
<p><?php _e("Maximal Characters of Search Terms: ","stt2extat") ?> <strong><span id="maxchar"><?php _e($stt2extat_settings['stt2extat_max_char']) ?></span></strong></p>
<div class="inside"><div id="slider-range-max"></div></div>
<?php do_action('stt2extat_after_form_setting'); ?>
<p><div id="msgbt"></div></p>
<?php wp_die(); ?>