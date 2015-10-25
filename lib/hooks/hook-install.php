<?php
/*
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 * @version 1.0
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

function stt2extat_install() 
{
	global $stt2extat_settings;

	$stt2extat_settings = stt2extat_settings();
	if( empty( $stt2extat_settings ) ) :
		$stt2extat_settings = array(
			'required_wp_version' => '4.2.2',
			'max_char'  => 55
		);
	else:
		$new_update = array(
			'required_wp_version' => '4.2.2',
			'max_char'  => 55
		);
		
		foreach( $new_update as $key =>	$value )
		{
			if ( ! isset( $stt2extat_settings[ $key ] ) )
			{
				$stt2extat_settings[ $key ] = $value;
			}
		}
	endif;
	
	update_option( 'stt2extat_settings', $stt2extat_settings );
	update_option( 'stt2exat_admin_notice_goto', '1' );
	
	$current_version = get_option( 'stt2extat_version' );
	if ( '' != $current_version ) 
		update_option( 'stt2extat_version_upgraded_from', $current_version );

	if ( is_network_admin() || isset( $_GET['activate-multi'] ) )
		return;
}

function stt2extat_deactivation()
{
	global $stt2extat_settings;
	delete_option( 'stt2extat_version_upgraded_from' );
	delete_option( 'stt2extat_version' );
}

function stt2extat_uninstall()
{
	global $stt2extat_settings;
	
	delete_option( 'stt2extat_settings', $stt2extat_settings );
	delete_option( 'stt2extat_version_upgraded_from' );
	delete_option( 'stt2extat_version' );
	delete_option( 'stt2exat_admin_notice_goto' );
}

function stt2extat_after_install()
{
	if ( ! is_admin() )
		return;

	$activation_pages = get_transient( '_stt2extat_activation_pages' );

	if ( false === $activation_pages )

	$current_version = get_option( 'stt2extat_version' );
	
	if ( version_compare( $current_version, '1.0.3', '<' ) )
	{
		include( STT2EXTAT_PATH_UPDATES . 'stt2extat-1.0.3.php' );
		update_option( 'stt2extat_version' , '1.0.3');
	}
	
	delete_transient( '_stt2extat_activation_pages' );
	do_action( 'stt2extat_after_install', $activation_pages );
}

function stt2extat_loaded()
{
   global $stt2extat_settings, $wp_version;

   $stt2_screen_id = searchterms_tagging2_screen_id();
   
   if ( ! empty( $stt2extat_settings['required_wp_version'] ) &&  version_compare( $wp_version, $stt2extat_settings['required_wp_version'], '<' ) )
      add_action( 'admin_notices', 'admin_notice_upgrade_wp_stt2extat' );
  
    if ( ! function_exists( 'pk_stt2_admin_menu_hook' ) ) :
      add_action( 'admin_notices', 'admin_notice_noexists_stt2extat' );
    else :
        if ( '2' !== get_option( 'pk_stt2_db_version' ) ):
            add_action( 'admin_notices', 'admin_notice_upgrade_stt2extat' );
		else:
			if ( '1' == get_option( 'stt2exat_admin_notice_goto' ) )
				add_action( 'admin_notices', 'admin_notice_goto_stt2extat' );

			if ( get_option( 'onlist_status' ) < 2 )
				add_action( 'admin_notices', 'admin_notice_off_stt2extat' ); 

			add_action( 'admin_notices', 'admin_notice_nojs_stt2extat' ); 
			add_filter( 'plugin_action_links', 'stt2extat_plugin_action_links', 10, 2 );
			add_action( 'admin_enqueue_scripts', 'stt2extat_admin_enqueu_scripts' );
			add_action( 'admin_footer-' . $stt2_screen_id, 'stt2extat_inline_js' );
			add_action( 'wp_ajax_stt2extat_action','stt2extat_form_handler' );
		endif;
   endif;
}
?>