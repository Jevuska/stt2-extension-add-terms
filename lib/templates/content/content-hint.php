
<?php
/*
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 * @version 1.0
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

printf( '<p><strong>%s</strong></p><ol class="hint"><li>%s</li><li>%s</li><li>%s</li><li>%s</li><li>%s <kbd>"<strong style="font-size:16px">,</strong>"</kbd> %s</li><li>%s</li></ol>',
	__( 'Hint:', 'stt2extat' ),
	__( 'Search the post by your terms, and select the one (required).', 'stt2extat' ),
	__( 'Populate your search terms for the first time then you can type in text box area directly to insert them into database.', 'stt2extat' ),
	__( 'Three methods to populate your search terms - by type, by suggestion and by double click text or text selection in "Full Post" section.', 'stt2extat' ),
	__( 'Always use "comma" to separate your search terms.', 'stt2extat' ),
	__( 'Comma sign ','stt2extat' ),
	__( 'in populate terms box is a trigger to searching relevant post.', 'stt2extat' ),
	__( 'Increase or decrease hits number of search term by click the term or number text.', 'stt2extat' )
);