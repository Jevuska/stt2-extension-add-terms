<?php
if ( ! defined( 'ABSPATH' ) ) exit;
register_activation_hook( STT2EXTAT_PLUGIN_FILE, 'stt2extat_install' );
register_deactivation_hook( STT2EXTAT_PLUGIN_FILE, 'stt2extat_uninstall' );
add_action( 'admin_init', 'stt2extat_after_install' );
add_action( 'admin_init', 'stt2extat_plugin_updates' );
?>