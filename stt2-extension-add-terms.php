<?php
/*
Plugin Name: STT2 Extension Add Terms
Plugin URI: http://www.jevuska.com/2015/06/28/injeksi-manual-keyword-add-onsextension-plugin-seo-searchterms-tagging-2/
Description: Manage your terms better, add terms into single post manually, get terms via referrer, and save them as post meta. Search the terms that relevant of post content as well as WordPress search default algorithm.
Author: Jevuska
Author URI: http://www.jevuska.com
Version: 1.1.0
Text Domain: stt2extat
License: GPL version 2 - http://www.gnu.org/licenses/gpl-2.0.html
 ** update: December 15, 2015
 * :: required latest version of WordPress, min.version 4.4
 * :: required laters version PHP Server, min.version 7.0
 * :: required browser JavaScript enabled
 * :: Add terms into single post manually
 * :: Search the terms that match post content as well as WordPress search default algorithm
 * STT2 Extension Add Terms is free software: you can copy and distribute verbatim copies
 * of this license document, changing it is allowed.
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License.
 * STT2 Extension Add Terms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with STT2 Extension Add Terms. If not, see <http://www.gnu.org/licenses/>.
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 * @version 1.1
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Initial_STT2EXTAT' ) ) :
	
	final class Initial_STT2EXTAT {
		
		private static $instance;
		
		/**
		 * Fires when the Initial_STT2EXTAT instance is initialized.
		 *
		 * @since 1.0
		 *
		 * add instance load_textdomain
		 * @since 1.0.2
		 */
		public static function instance()
		{
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Initial_STT2EXTAT ) ) :
				self::$instance = new Initial_STT2EXTAT;
				self::$instance->setup_constants();
				self::$instance->includes();
				self::$instance->load_textdomain();
				define( 'STT2EXTAT_RUNNING', true );
			endif;
			
			return self::$instance;
		}
		
		/**
		 * setup constants
		 *
		 * @since 1.0
		 *
		 * Add define STT2EXTAT_PLUGIN_BASENAME
		 *
		 * @since 1.0.2
		 *
		 */
		public function setup_constants()
		{
			if ( ! defined( 'STT2EXTAT_VER' ) )
				define( 'STT2EXTAT_VER', '1.1.0' );
			
			if ( ! defined( 'STT2EXTAT_DB_VER' ) )
				define( 'STT2EXTAT_DB_VER', '1.1' );
			
			if ( ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
				define( 'STT2EXTAT_PLUGIN_FILE', __FILE__ );
			
			if ( ! defined( 'STT2EXTAT_PLUGIN_BASENAME' ) )
				define( 'STT2EXTAT_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
			
			if ( ! defined( 'STT2EXTAT_PLUGIN_URL' ) )
				define( 'STT2EXTAT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
			
			if ( ! defined( 'STT2EXTAT_PLUGIN_PATH' ) )
				define( 'STT2EXTAT_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
			
			if ( ! defined( 'STT2EXTAT_PATH_LIB' ) )
				define( 'STT2EXTAT_PATH_LIB', STT2EXTAT_PLUGIN_PATH . 'lib/' );
			
			if ( ! defined( 'STT2EXTAT_PATH_LIB_ADMIN' ) )
				define( 'STT2EXTAT_PATH_LIB_ADMIN', STT2EXTAT_PATH_LIB . 'admin/' );
			
			if ( ! defined( 'STT2EXTAT_PATH_LIB_INCLUDES' ) )
				define( 'STT2EXTAT_PATH_LIB_INCLUDES', STT2EXTAT_PATH_LIB . 'includes/' );
			
			if ( ! defined( 'STT2EXTAT_PATH_LIB_CONTENT' ) )
				define( 'STT2EXTAT_PATH_LIB_CONTENT', STT2EXTAT_PATH_LIB . 'content/' );
		}

		/**
		 * all functions and filters
		 *
		 * @since 1.0
		 *
		 * remove some hooks files, and add their function
		 * into one files - hook-functions.php
		 *
		 * @since 1.0.3
		 *
		 * global stt2extat_data
		 *
		 * @since 1.1.0
		 *
		 */
		private function includes()
		{
			global $stt2extat_settings, $stt2extat_sanitize, $stt2extat_data;
			require_once( STT2EXTAT_PATH_LIB . 'settings.php' );
			require_once( STT2EXTAT_PATH_LIB_INCLUDES . 'functions.php' );
			require_once( STT2EXTAT_PATH_LIB_INCLUDES . 'class-stt2extat-query.php' );
			require_once( STT2EXTAT_PATH_LIB_INCLUDES . 'class-stt2extat-sanitize.php' );
			
			$stt2extat_settings = stt2extat_settings();
			$stt2extat_sanitize = stt2extat__sanitize();
			$stt2extat_data     = stt2extat_data();
			
			require_once( STT2EXTAT_PATH_LIB_INCLUDES . 'widgets.php' );
			require_once( STT2EXTAT_PATH_LIB_INCLUDES . 'class-stt2extat-load.php' );
			require_once( STT2EXTAT_PATH_LIB_INCLUDES . 'hook.php' );
			require_once( STT2EXTAT_PATH_LIB_CONTENT . 'plugin/class-stt2extat-searchexcerpt.php' );
			
			if ( is_admin() ) :
				
				//defined function wp_get_current_user
				require_once( ABSPATH . 'wp-includes/pluggable.php' );
				
				if ( current_user_can( 'manage_options' ) ) :
					require_once( STT2EXTAT_PATH_LIB_INCLUDES . 'class-stt2extat-table.php' );
					require_once( STT2EXTAT_PATH_LIB_INCLUDES . 'template-functions.php' );
					require_once( STT2EXTAT_PATH_LIB_CONTENT . 'templates/metabox/metabox-form.php' );
					require_once( STT2EXTAT_PATH_LIB_CONTENT . 'templates/themes/themes.php' );
					require_once( STT2EXTAT_PATH_LIB_ADMIN . 'class-stt2extat-admin.php' );
					require_once( STT2EXTAT_PATH_LIB_ADMIN . 'class-stt2extat-setup.php' );
				endif;
				
			endif;
			require_once STT2EXTAT_PATH_LIB . 'install.php';
		}
		
		/**
		 * internationalize plugin
		 *
		 * @since 1.0.2
		 *
		 */
		public function load_textdomain()
		{
			$domain        = 'stt2extat';
			$lang_dir      = apply_filters( 'stt2extat_lang_dir', STT2EXTAT_PATH_LIB_CONTENT . 'languages/' );
			$locale        = apply_filters( 'plugin_locale', get_locale(), $domain );
			$mofile        = sprintf( '%1$s-%2$s.mo', $domain, $locale );
			$mofile_local  = $lang_dir . $mofile;
			$mofile_global = trailingslashit( WP_LANG_DIR ) . $domain . '/' . $mofile;
			
			if ( file_exists( $mofile_global ) ) :
				load_textdomain( $domain, $mofile_global );
			elseif ( file_exists( $mofile_local ) ) :
				load_textdomain( $domain, $mofile_local );
			else :
				load_plugin_textdomain( $domain, false, $lang_dir );
			endif;
		}
	}
endif;

/**
 * STT2EXTAT
 *
 * @since 1.0
 *
 */

function STT2EXTAT()
{
	return Initial_STT2EXTAT::instance();
}
STT2EXTAT();
?>