<?php
/*
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 * @version 1.1
 */

if ( !  defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

class STT2EXTAT_Setup
{
	
	/**
	 * activate plugin
	 *
	 * @since 1.1
	 *
	*/
	public static function on_activation()
	{
		global $stt2extat_settings, $required_php_version;
		
		if ( empty( $stt2extat_settings ) ) :
			$stt2extat_settings = stt2extat_default_setting();
		else :
			$new_update = stt2extat_default_setting( 'update' );
			
			foreach ( $new_update as $key => $value ) :
				if ( ! isset( $stt2extat_settings[ $key ] ) )
					$stt2extat_settings[ $key ] = $value;
			endforeach;
			
		endif;
		
		add_option( 'stt2extat_search_structure' );
		update_option( 'stt2extat_check_relevant_terms', 1 );
		update_option( 'stt2extat_settings', $stt2extat_settings );
		update_option( 'stt2extat_settings_update_term', '2' );
		set_transient( 'stt2exat_go_to_settings', stt2extat_go_to_settings(), 3 );
		$current_version = get_option( 'stt2extat_version' );
		if ( false !== $current_version )
			update_option( 'stt2extat_version_upgraded_from', $current_version );
	}
	
	/**
	 * deactivate plugin
	 *
	 * @since 1.1
	 *
	*/
	public static function on_deactivation()
	{
		delete_option( 'stt2extat_search_structure' );
		wp_clear_scheduled_hook( 'stt2extat_delete_terms' );
		flush_rewrite_rules();
	}
	
	/**
	 * uninstall plugin
	 *
	 * @since 1.1
	 *
	*/
	public static function on_uninstall()
	{
		global $stt2extat_screen_id;
		$current_user = wp_get_current_user();
		$user_id      = $current_user->ID;
		
		delete_option( 'stt2extat_version_upgraded_from' );
		delete_option( 'stt2extat_version' );
		delete_option( 'stt2extat_settings' );
		delete_option( 'stt2extat_settings_update_term' );
		delete_option( 'stt2extat_search_structure' );
		delete_option( 'widget_stt2extat_terms_list' );
		delete_option( 'stt2extat_check_relevant_terms' );
		delete_transient( 'stt2exat_go_to_settings' );
		delete_user_meta( $user_id, 'closedpostboxes_' . $stt2extat_screen_id );
		delete_user_meta( $user_id, 'metaboxhidden_' . $stt2extat_screen_id );
		delete_user_meta( $user_id, 'meta-box-order_' . $stt2extat_screen_id );
		delete_user_meta( $user_id, 'stt2extat_term_stats_per_page' );
		wp_clear_scheduled_hook( 'stt2extat_delete_terms' );
		flush_rewrite_rules();
	}
}