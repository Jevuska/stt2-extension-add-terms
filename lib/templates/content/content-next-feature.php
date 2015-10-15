<?php
/*
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 * @version 1.0
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

printf( '<h3>%s</h3><ul><li>%s</li><li>%s</li><li>%s</li></ul>',
	__( 'NEW FEATURES', 'stt2extat' ),
	__( 'Bulk update search terms to make them relevant to post.', 'stt2extat' ),
	__( 'Insert search terms as post tag.', 'stt2extat' ),
	__( 'Front end search terms delete.', 'stt2extat' )
);
wp_die();