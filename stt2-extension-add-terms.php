<?php
/*
Plugin Name: STT2 Extension Add Terms
Plugin URI: http://www.jevuska.com/2015/06/28/injeksi-manual-keyword-add-onsextension-plugin-seo-searchterms-tagging-2/
Description:  Manage your search terms better, extended version of <strong>SEO SearchTerms Tagging 2</strong> plugin. Add search terms into single post manually. Search the terms that relevant of post content as well as WordPress search default algorithm. JavaScript browser enabled required and the latest modified version of <a href="https://github.com/Jevuska/stt2-extension-add-terms/releases/tag/STT2-v1.535">SEO SearchTerms Tagging 2</a> plugin installed.
Author: Jevuska
Author URI: http://www.jevuska.com
Version: 1.0.4
Text Domain: stt2extat
License: GPL version 3 - http://www.gnu.org/licenses/gpl-3.0.html
 ** update: October 26, 2015
 * :: required latest version of WordPress, min.version 4.2.2
 * :: required SEO SearchTerms Tagging 2 plugin installed https://github.com/Jevuska/stt2-extension-add-terms/releases/tag/STT2-v1.535 ( modified version )
 * :: required browser JavaScript enabled
 * :: Add search terms into single post manually
 * :: Search the terms that match post content as well as WordPress search default algorithm
 * STT2 Extension Add Terms is free software: you can copy and distribute verbatim copies 
 * of this license document, but changing it is not allowed.
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
 * @version 1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Initial_STT2EXTAT' ) ) :

	final class Initial_STT2EXTAT {
		
		private static $instance;

		public static function instance()
		{
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Initial_STT2EXTAT ) ) :
				self::$instance = new Initial_STT2EXTAT;
				self::$instance->setup_constants();
				self::$instance->includes();
				self::$instance->load_textdomain();
				define( 'STT2EXTAT_RUNNING',true );
			endif;
			return self::$instance;
		}
		
		public function setup_constants()
		{

			if ( ! defined( 'STT2EXTAT_VER' ) )
				define( 'STT2EXTAT_VER', '1.0.4' );
			
			if ( ! defined( 'STT2EXTAT_DB_VER' ) )
				define( 'STT2EXTAT_DB_VER', '1.0' );

			if ( ! defined( 'SEARCHTERMS_TAGGING2_SCREEN_ID' ) )
				define( 'SEARCHTERMS_TAGGING2_SCREEN_ID', 'settings_page_searchterms-tagging2' );

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

			if ( ! defined( 'STT2EXTAT_PATH_UPDATES' ) )
				define( 'STT2EXTAT_PATH_UPDATES', STT2EXTAT_PATH_LIB . 'updates/' );

			if ( ! defined( 'STT2EXTAT_PATH_TEMPLATES' ) )
				define( 'STT2EXTAT_PATH_TEMPLATES', STT2EXTAT_PATH_LIB . 'templates/' );
		}

		private function includes()
		{
			global $stt2extat_settings;

			if ( is_admin() ) :
				require_once( ABSPATH . 'wp-includes/pluggable.php' );
				require_once( STT2EXTAT_PATH_LIB . 'includes/register-settings.php' );
				$stt2extat_settings = stt2extat_get_settings();
				require_once( STT2EXTAT_PATH_LIB . 'includes/functions.php' );
				require_once( STT2EXTAT_PATH_LIB . 'includes/functions-notices.php' );
				require_once( STT2EXTAT_PATH_LIB . 'includes/plugins.php' );
				require_once( STT2EXTAT_PATH_LIB . 'includes/template-functions.php' );
				require_once( STT2EXTAT_PATH_LIB . 'includes/plugins/search-excerpt.php' );
				require_once( STT2EXTAT_PATH_LIB . 'templates/metabox/metabox-form.php' );
				require_once( STT2EXTAT_PATH_LIB . 'templates/themes/themes.php' );
				require_once( STT2EXTAT_PATH_LIB . 'assets/assets.php' );

				if ( current_user_can( 'manage_options' ) ) :
					require_once( STT2EXTAT_PATH_LIB . 'hooks/hook-functions.php' );
					require_once( STT2EXTAT_PATH_LIB . 'hooks/hook-install.php' );
					require_once( STT2EXTAT_PATH_LIB . 'hooks/hook.php' );
				endif;
			endif;
			require_once STT2EXTAT_PATH_LIB . 'includes/install.php';
		}
		
		public function load_textdomain()
		{
			$domain        = 'stt2extat';
			$lang_dir      = apply_filters( 'stt2extat_languages_directory', STT2EXTAT_PATH_LIB . 'languages/' );
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

function STT2EXTAT()
{
	return Initial_STT2EXTAT::instance();
}
STT2EXTAT();
?>