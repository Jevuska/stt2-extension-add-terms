<?php
/*
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 * @version 1.0
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

printf( '<h3>%s</h3><p>%s</p><p>%s</p>',
	__( 'Modified version of SEO SearchTerms Tagging 2 plugin', 'stt2extat' ),
	wp_kses( __( 'Download the latest modified version of SEO SearchTerms Tagging 2 plugin <a class="dashicons dashicons-external" target="_blank" href="https://github.com/Jevuska/stt2-extension-add-terms/releases/tag/STT2-v1.535"></a>.', 'stt2extat' ), array( 'a' => array( 'href' => array(), 'target' => array(), 'class' => array() ) ) ),
	wp_kses( __( 'Support contact via email at <a href="mailto:contact@jevuska.com">contact@jevuska.com</a> or <a href="https://github.com/Jevuska/stt2-extension-add-terms" target="_blank">GitHub</a>', 'stt2extat' ), array( 'a' => array( 'href' => array(), 'target' => array() ) ) )
);
wp_die();