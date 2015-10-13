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
<div id="wp-link"><div class="link-search-wrapper search-box"><input type="search" id="wp-link-search" name="s" class="link-search-field" placeholder="<?php _e('Populate relevant terms with post above (min 3 characters)');?>" autocomplete="off" style="width: 63%" results=5><div class="btnadd"></div><span class="spinner"></span><input type="hidden" id="notmatchdata" value=""></div><label for="gsuggest"><input type="checkbox" id="gsuggest" value=""> <?php _e('Enable Google Suggestion');?></label>  <label for="notmatchfeat"><input type="checkbox" id="notmatchfeat" value="" name="notmatchfeat"> <?php _e('Ignore Irrelevant Notice');?></label>
<?php wp_die();?>