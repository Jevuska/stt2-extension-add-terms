<?php
/*
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 * @version 1.0
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

function admin_notice_nojs_stt2extat() {
    printf( '<noscript><div id="%1$s" class="%2$s"> <p>%3$s <b>%4$s</b> %5$s</p></div></noscript>', esc_attr__( 'message', 'stt2extat' ), esc_attr__( 'error', 'stt2extat' ), esc_html__( 'Enable your browser javascript to load', 'stt2extat' ),esc_html__( 'STT2 Extension Add Terms', 'stt2extat' ),esc_html__( 'plugin.', 'stt2extat' ) );
}

function admin_notice_noexists_stt2extat() {
    printf( '<div class="%1$s"> <b>%2$s</b> %3$s <a href="%4$s">%5$s</a> %6$s</div>', 'update-nag', __( 'STT2 Extension Add Terms', 'stt2extat' ), __( 'plugin active. Please install and activate', 'stt2extat' ), 'https://github.com/Jevuska/stt2-extension-add-terms/releases/tag/STT2-v1.535', 'SEO SearchTerms Tagging 2', __( 'plugin.', 'stt2extat' )  );
}

function admin_notice_off_stt2extat() {
    printf( '<div id="%1$s" class="%2$s">%3$s</div>',esc_attr__( 'message', 'stt2extat' ),esc_attr__( 'update-nag', 'stt2extat' ),esc_html__( 'Enable your SEO SearchTerms Tagging 2 plugin.', 'stt2extat' ) );
}

function admin_notice_upgrade_stt2extat() {
    printf( '<div id="%1$s" class="%2$s">%3$s</div>',esc_attr__( 'message', 'stt2extat' ),esc_attr__( 'update-nag', 'stt2extat' ),esc_html__( 'Upgrade your SEO SearchTerms Tagging 2 plugin.', 'stt2extat' ) );
}

function admin_notice_upgrade_wp_stt2extat() {
   global $stt2extat_settings, $wp_version;
   $required_wp_version = $stt2extat_settings['required_wp_version'];
   $url_version = "http://codex.wordpress.org/Version_4.2.2";
   printf( '<div class="%1$s"> <b>%2$s</b> %3$s <b>%4$s</b>%5$s <a href="%6$s">%7$s</a>%8$s</div>',esc_attr__( 'update-nag', 'stt2extat' ),esc_html__( 'STT2 Extension Add Terms', 'stt2extat' ),esc_html__( 'plugin can not be activated. Your WordPress version is ', 'stt2extat' ), esc_html__($wp_version, 'stt2extat' ) , esc_html__(', required minimal ', 'stt2extat' ), esc_url($url_version, 'stt2extat'), esc_html__($required_wp_version, 'stt2extat' ), esc_html__('. Please update yours.', 'stt2extat' ) );
}

function admin_notice_goto_stt2extat() {
   $currentscreen = get_current_screen();
   $stt2_plugin_basename = "searchterms-tagging2.php";
   $extat_postbox = "options-general.php?page=".$stt2_plugin_basename."#stt2extat-form";
   $currentScreen = get_current_screen();
   if( $currentscreen->id === "plugins" ) {
    printf( '<div id="%1$s" class="%2$s"><p><b>%3$s</b> %4$s <a href="%5$s">%6$s</a>.</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>',esc_attr__( 'message', 'stt2extat' ),esc_attr__( 'updated notice is-dismissible', 'stt2extat' ),esc_html__( 'STT2 Extension Add Terms', 'stt2extat' ), esc_html__(' is ready! Manage', 'stt2extat' ), esc_url(admin_url($extat_postbox), 'stt2extat'), esc_html__('here', 'stt2extat' ) );
  }
}
?>