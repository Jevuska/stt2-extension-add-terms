<?php
/*
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 * @version 1.0
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

if ( ! is_admin() )
	return;

function stt2extat_plugin_action_links( $links, $file )
{
    $stt2_plugin_basename = 'searchterms-tagging2.php';
    $extat_postbox        = "options-general.php?page=$stt2_plugin_basename#stt2extat-form";
	$settings_link        = sprintf( '<a href="%s">%s</a>', 
		admin_url( $extat_postbox ),
		__( 'Manage', 'stt2extat' )
	);
	if ( 'stt2-extension-add-terms/stt2-extension-add-terms.php' == $file )
		array_unshift( $links, $settings_link );
	return $links;
}
?>