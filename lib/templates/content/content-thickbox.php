<?php
/*
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 * @version 1.0
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

printf( '<input alt="#TB_inline?height=160&amp;width=400&amp;inlineId=gsuggestPopup" title="%s" class="thickbox gsuggest" type="hidden" value=""/><input alt="#TB_inline?height=160&amp;width=400&amp;inlineId=notmatchfeatPopup" title="%s" class="thickbox notmatchfeat" type="hidden" value=""/>',
	esc_attr__( 'Attention', 'stt2extat' ),
	esc_attr__( 'Warning', 'stt2extat' )
);