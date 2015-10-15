<?php
/*
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 * @version 1.0
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

printf( '<ul><li id="tab-link-manage" class="active"><a href="#tab-panel-manage" aria-controls="tab-panel-manage">%s</a></li><li id="tab-link-settings" class=""><a href="#tab-panel-settings" aria-controls="tab-panel-settings">%s</a></li><li id="tab-link-feature" class=""><a href="#tab-panel-feature" aria-controls="tab-panel-feature">%s</a></li><li id="tab-link-help" class=""><a href="#tab-panel-help" aria-controls="tab-panel-help">%s</a></li><li id="tab-link-donate" class=""><a href="#tab-panel-donate" aria-controls="tab-panel-donate">%s</a></li></ul>',
	__( 'Manage', 'stt2extat' ),
	__( 'Settings', 'stt2extat' ),
	__( 'Next Features', 'stt2extat' ),
	__( 'Help', 'stt2extat' ),
	__( 'Donate', 'stt2extat' )
);