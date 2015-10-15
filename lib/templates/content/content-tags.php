<?php
/*
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 * @version 1.0
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

printf( '<label for="astag"><input type="checkbox" id="astag"> %s</label>',
	__( 'as post tag', 'stt2extat' )
);