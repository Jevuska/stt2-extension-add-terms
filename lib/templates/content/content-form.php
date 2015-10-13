<?php
/*
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 * @version 1.0
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

?>
<?php do_action('stt2exatat_before_form') ?>
<form id="stt2-extat" method="post" action=""><div id="search-panel"><label><span class="search-label"><?php _e('Search post by your terms or type post url here:', 'stt2extat');?></span></label><div id="titlediv"><input type="search" id="title" name="q" class="link-search-field" autocomplete="off" required/></div><div id="searchtermpost"></div><div id="searchdiv"></div><input type="hidden" id="id-field" name="id_post" class="link-search-field"  required/><label for="insertterms"><?php _e('Search terms will be inserted below then you can type manually, separated by a comma ( e.g.: keyword1,keyword2 ):', 'stt2extat');?></label><br><textarea name="insertterms" style="width:100%" id="insertterms" class="searchterms" readonly="readonly" required></textarea><div id="badterms"></div><br><div id="ins-btn"><input type="button" id="btninsert" class="button button-primary button-medium" value="Insert" disabled="disabled"/> <?php do_action('stt2exatat_after_insert_btn') ?><div class="loader" style="display: inline-block;padding-left: 10px"></div></div></div></form><div id="fullpost"></div><div id="msgb"></div><?php do_action('stt2exatat_after_form') ?>
<?php wp_die();?>