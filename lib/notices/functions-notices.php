<?php
/*
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 * @version 1.0
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

function admin_notice_nojs_stt2extat()
{
    printf( '<noscript><div id="message" class="error"> <p>%1$s <b>%2$s</b> %3$s</p></div></noscript>', 
		__( 'Enable your browser javascript to load', 'stt2extat' ),
		'STT2 Extension Add Terms',
		'plugin.'
	);
}

function admin_notice_noexists_stt2extat()
{
    printf( '<div class="update-nag"> <b>%1$s</b> %2$s <a href="%3$s">%4$s</a> %5$s</div>',
		'STT2 Extension Add Terms',
		__( 'plugin active. Please install and activate', 'stt2extat' ),
		'https://github.com/Jevuska/stt2-extension-add-terms/releases/tag/STT2-v1.535',
		'SEO SearchTerms Tagging 2',
		'plugin.'
	);
}

function admin_notice_off_stt2extat()
{
    printf( '<div id="message" class="update-nag">%s</div>',
		__( 'Enable your SEO SearchTerms Tagging 2 plugin.', 'stt2extat' ) 
	);
}

function admin_notice_upgrade_stt2extat()
{
    printf( '<div id="message" class="update-nag">%s</div>',
		__( 'Upgrade your SEO SearchTerms Tagging 2 plugin.', 'stt2extat' ) 
	);
}

function admin_notice_upgrade_wp_stt2extat()
{
	global $stt2extat_settings, $wp_version;
	
	$url_version = 'http://codex.wordpress.org/Version_4.2.2';
	
	printf( '<div class="update-nag"> <b>%1$s</b> %2$s <b>%3$s</b>, %4$s <a href="%5$s">%6$s</a>. %7$s.</div>',
		'STT2 Extension Add Terms',
		__( 'plugin can not be activated. Your WordPress version is', 'stt2extat' ),
		$wp_version,
		__( 'required minimal', 'stt2extat' ),
		esc_url( $url_version ),
		$stt2extat_settings['required_wp_version'],
		__( 'Please update yours', 'stt2extat' )
	);
}
		
function admin_notice_goto_stt2extat()
{
	if ( ! function_exists( 'get_plugin_data' ) )
	{
		require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
	}
	$plugin_data 	      = get_plugin_data( STT2EXTAT_PLUGIN_FILE );
	$currentscreen        = get_current_screen();
	
	$stt2_plugin_basename = 'searchterms-tagging2.php';
	$extat_postbox        = "options-general.php?page=$stt2_plugin_basename#stt2extat-form";
	
	if( 'plugins' == $currentscreen->id )
	{
		printf( '<div id="message" class="updated notice is-dismissible"><p><b>%1$s V.%2$s</b> %3$s <a href="%4$s">%5$s</a>.</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">%6$s.</span></button></div>',
			'STT2 Extension Add Terms',
			$plugin_data['Version'],
			__( 'is ready! Manage', 'stt2extat' ), 
			esc_url( admin_url( $extat_postbox ), 'stt2extat' ), 
			__( 'here', 'stt2extat' ),
			__( 'Dismiss this notice', 'stt2extat' )
		);
	}
}
?>