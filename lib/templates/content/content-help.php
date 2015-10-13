<?php
/*
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 * @version 1.0
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

?><h3><?php _e('Error in SEO SearchTerms Tagging 2') ?></h3><p><?php _e('However, SEO SearchTerms Tagging 2 plugin never been updated since over 4 years but it still support for the latest version of WordPress (4.2.2). Some commons error you get from, could be fixed.') ?></p><ul><li><strong><?php _e('Fixing Error "Warning: Missing argument 2 for wpdb::prepare()"') ?></strong><br><?php _e('Open <kbd>searchterms-tagging2.php</kbd> plugin file, edit the code in line 658 and 695<br><code>$post_count = $wpdb->get_var($wpdb->prepare( $sql ));</code><br> change with <br><code>$post_count = $wpdb->get_var($wpdb->prepare( $sql, &#39;&#39; ));</code><br>Save your work then.') ?></li></ul>
<?php wp_die();?>