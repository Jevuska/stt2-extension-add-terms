<?php
/*
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 * @version 1.1
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

function stt2extat_load()
{
	$load = new STT2EXTAT_Load;
	return $load->init();
}

class STT2EXTAT_Load
{
	public $meta_key = '_stt2extat';
	
	protected static $instance;
	protected $referrer = false;
	
	public static function init()
	{
		is_null( self::$instance ) AND self::$instance = new self;
		self::$instance->after_install();
		return self::$instance;
	}
	
	public function __construct()
	{
		add_shortcode( 'stt2extat', 'stt2extat_shortcode' );
		add_action( 'stt2extat_show_searchterms', 'stt2extat_terms_list', 11 );
		
		add_action( 'stt2extat_delete_terms_cron', array( $this, 'delete_terms_cron' ) );
		add_action( 'stt2extat_register_meta',
			array(
				$this,
				'register_meta'
			),
			10,
			1
		);
	}
	
	/**
	 * fire after installation done
	 *
	 * @since 1.1
	 *
	*/
	public function after_install()
	{
		global $stt2extat_settings, $stt2extat_sanitize;
		
		$set = $stt2extat_settings;
		
		$this->stt2extat_search_rewrite_rules();
		
		if ( is_admin() ) :
		
			add_action( 'stt2extat_notice', 'stt2extat_edit_term_notice', 10, 3 );
			add_filter( 'plugin_action_links', array( $this, 'plugin_action_links' ), 10, 2 );
			add_action( 'wp_ajax_stt2extat_action','stt2extat_form_handler' );
			add_action( 'stt2extat_deactivate_plugins', array( $this, 'deactivate_plugins' ) );
			
			$activation_pages = get_transient( '_stt2extat_activation_pages' );
			
			if ( false === $activation_pages )
				$current_version = get_option( 'stt2extat_version' );
			
			if ( version_compare( $current_version, STT2EXTAT_VER, '<=' ) ) :
				$filename = STT2EXTAT_PATH_LIB_ADMIN . 'updates/stt2extat-1.1.0.php';
				if ( file_exists( $filename ) ) :
					include( $filename );
					update_option( 'stt2extat_version' , STT2EXTAT_DB_VER );
				endif;
			endif;
			
			delete_transient( '_stt2extat_activation_pages' );
			do_action( 'stt2extat_create_admin_page' );
			do_action( 'after_install', $activation_pages );
		else :
		
			add_filter( 'script_loader_tag', array( $this, 'script_loader_tag' ), 10, 2 );
			
		endif;
		
		if ( isset( $set['active'] ) && false != $set['active'] ) :
			
			if ( bool_from_yn( $set['active'] ) ) :
				add_action( 'wp_ajax_stt2extat_ref', array(
					$this,
					'ref_ajax' 
				) );
				add_action( 'wp_ajax_nopriv_stt2extat_ref', array(
					$this,
					'ref_ajax' 
				) );
			else :
				add_action( 'wp_head', array(
					$this,
					'ref_php' 
				) );
			endif;
				
		endif;
			
		if ( isset( $set['auto'] ) && false != $stt2extat_sanitize->bool_from_yn( $set['auto'] ) ) :
			add_filter( 'the_content', 'stt2extat_filter_the_content', 1, 2 );
				
			if ( bool_from_yn( $set['auto'] ) ) :
				add_action( 'wp_ajax_stt2extat_terms_list', 'stt2extat_terms_list_ajax', 40 );
				add_action( 'wp_ajax_nopriv_stt2extat_terms_list', 'stt2extat_terms_list_ajax', 40 );
			endif;
			
		endif;
			
		if ( ( isset( $set['active'] ) && bool_from_yn( $set['active'] ) ) || ( isset( $set['auto'] ) && bool_from_yn( $set['auto'] ) ) ) :
			add_action( 'wp_footer', array(
				$this,
				'inline_js'
			) );
				
			add_action( 'wp_enqueue_scripts', array(
				$this,
				'enqueue_scripts'
			) );
		endif;
		
		do_action( 'stt2extat_delete_terms_cron' );
		do_action( 'stt2extat_register_meta', $this->meta_key );
	}
	
	/**
	 * deactivate plugin automatically when your wp or server under plugin requirement
	 *
	 * @since 1.1
	 *
	*/
	public function deactivate_plugins()
	{
		global $stt2extat_settings, $wp_version, $required_php_version;
		
		if ( ( ! empty( $stt2extat_settings['php_version'] ) &&  version_compare( phpversion(), $stt2extat_settings['php_version'], '<' ) ) || ( ! empty( $stt2extat_settings['wp_version'] ) &&  version_compare( $wp_version, $stt2extat_settings['wp_version'], '<' ) ) )
		{
			deactivate_plugins( STT2EXTAT_PLUGIN_BASENAME );
			stt2extat_is_activated() && add_filter( 'gettext', 'stt2extat_change_activate_notice', 99, 3 );
			remove_filter( 'plugin_action_links', array( $this, 'plugin_action_links' ), 10, 2 );
		}
	}
	
	/**
	 * function filter plugin_action_links
	 *
	 * @since 1.0
	 *
	*/
	public function plugin_action_links( $actions, $file )
	{
		static $plugin;
		
		if ( ! isset( $plugin ) )
			$plugin = STT2EXTAT_PLUGIN_BASENAME;
		
		if ( $plugin == $file ) :
			$link = sprintf( '<a href="%s">%s</a>', 
				admin_url( 'options-general.php?page=' . stt2extat_get_plugin_data( 'TextDomain' ) ),
				__( 'Manage', 'stt2extat' )
			);
			
			$settings = array(
				'settings' => $link
			);
			
			$actions = array_merge( $settings, $actions );
		endif;
		return $actions;
	}
	
	/**
	 * register private post meta key _stt2extat
	 *
	 * @since 1.1
	 *
	*/
	public function register_meta( $meta_key )
	{
		register_meta(
			'post',
			sanitize_key( $meta_key ),
			array(
				$this,
				'sanitize_meta_key'
			)
		);
	}
	
	/**
	 * sanitize post meta _stt2extat
	 *
	 * @since 1.1
	 *
	*/
	public function sanitize_meta_key( $input )
	{
		if ( ! is_array( $input ) )
			$input = array();
		
		$new_value = array();
		foreach ( $input as $key => $value ) :
			$key = stt2extat_filter_text( $key );
			
			if ( is_array( $value ) )
				$value = array_map( 'absint', $value );
			$new_value[ $key ] = $value;
			
		endforeach;
		
		return $new_value;
	}
	
	/**
	 * rewrite rule for search link structure
	 *
	 * @since 1.1
	 *
	*/
	public function stt2extat_search_rewrite_rules()
	{
		global $wp_rewrite;
		
		$search_structure = get_option( 'stt2extat_search_structure' );
		
		if ( $search_structure && ! empty( $search_structure ) && ! empty( $wp_rewrite->permalink_structure ) ) :
			
			$structures = array(
				'%search%',
				'%search-term%',
				'%search_term%',
			);
			
			$search_base = false;
			$rewritecode = '';
			foreach ( $structures as $structure ) :
				$pos = strpos( $search_structure, $structure );
				if ( false !== $pos ) :
					$search_base = substr( $search_structure, 0,  $pos );
					$rewritecode = $structure;
					break;
				endif;
			endforeach;
			
			if ( ! $search_base || empty( $search_base ) )
				return false;
			
			$wp_rewrite->search_base      = preg_replace( '#/+#', '', $search_base );
			$wp_rewrite->search_structure = $search_structure;
			
			$wp_rewrite->add_rewrite_tag( $rewritecode, '([^/]+)', 's=' );
			
			if ( false !== strpos( $rewritecode, '-' ) )
				add_filter( 'stt2extat_sep_search_link', '__stt2extat_return_dash' );
			
			if ( false !== strpos( $rewritecode, '_' ) )
				add_filter( 'stt2extat_sep_search_link', '__stt2extat_return_underscore' );
			
			return $wp_rewrite;
		endif;
	}
	
	/**
	 * delete term via wp cron
	 *
	 * @since 1.1
	 *
	*/
	public function delete_terms_cron()
	{
		global $stt2extat_settings;
		$set = $stt2extat_settings;
		
		if ( isset( $set['schedule'] ) && 0 < absint( $set['schedule']['post_modified'] ) ) :
			
			add_filter( 'cron_schedules', array(
				$this,
				'delete_terms_schedules' 
			) );
			
			add_action( 'stt2extat_delete_terms', array(
				$this,
				'delete_unused_terms' )
			);
			
			if ( ! wp_next_scheduled( 'stt2extat_delete_terms' ) ) :
				wp_schedule_event( current_time( 'timestamp' ), 'inseconds', 'stt2extat_delete_terms' );
			endif;
		else :
			if ( wp_next_scheduled( 'stt2extat_delete_terms' ) )
				wp_clear_scheduled_hook( 'stt2extat_delete_terms' );
		endif;
	}
	
	/**
	 * schedule event delete terms
	 *
	 * @since 1.1
	 *
	*/
	public function delete_terms_schedules( $schedules )
	{
		$seconds                = 604800; //once weekly
		$schedules['inseconds'] = array(
			'interval' => $seconds,
			'display'  => __( 'Once Weekly' )
		);
		return apply_filters( 'delete_terms_schedules', $schedules );
	}
	
	/**
	 * fire to delete unused terms
	 * argument was set on plugin panel
	 *
	 * @since 1.1
	 *
	*/
	public function delete_unused_terms()
	{
		global $stt2extat_settings;
		$set = $stt2extat_settings;
		
		$args = array(
			'date_query' => array(
				'before' => intval( - $set['schedule']['post_modified'] ) . ' day'
			),
			'schedule' => array(
				'count' => absint( $set['schedule']['count'] )
			)
		);
		
		$data = stt2extat_data_query( $args );
		
		if ( 0 == count( $data ) )
			exit;
		
		$list = array_unique( array_column( $data, 'post_id' ) );
		
		$func = function( $terms, $list, $meta_key )
		{
			$update = array();
			
			foreach ( $list as $k ) :
				$prev_value = stt2extat_get_post_terms( $k );
				
				if ( empty( $prev_value ) ) :
					delete_post_meta( $k , $meta_key );
					continue;
				endif;
				
				$meta_value = array();
				foreach ( $prev_value as $key => $val ) :
				
					if ( isset( $terms[ $key ] ) )
						continue;
					
					$meta_value[ $key ] = $val;
					
				endforeach;
				
				if ( array_filter( $meta_value ) )
					$update['update'][] = update_post_meta( $k , $meta_key, $meta_value );
				else
					$update['delete'][] = delete_post_meta( $k , $meta_key );
				
			endforeach;
			
			return $update;
		};
		
		$success  = call_user_func_array( $func, array( $data, $list, '_stt2extat' ) );
		
		if ( isset( $success['update'] ) )
			echo ( 'Update ' . count( $success['update'] ) . ' postmeta.<br>' );
		
		if ( isset( $success['delete'] ) )
			echo ( 'Delete ' . count( $success['delete'] ) . ' postmeta.' );
		
		exit;
	}
	
	/**
	 * referrer callback from wp_head
	 *
	 * @since 1.1
	 *
	*/
	public function ref_php()
	{
		$is_single = apply_filters( 'stt2extat_is_single', true );
		if ( ! $is_single && ! is_search() )
			return false;
		
		if ( wp_get_referer() ) :
			$referrer = wp_get_referer();
			if ( false !== strpos( $referrer, get_admin_url() ) )
				return false;
			return $this->ref_proccess( $referrer );
		endif;
		return false;
	}
	
	/**
	 * referrer callback from wp_ajax_stt2extat_ref
	 *
	 * @since 1.1
	 *
	*/
	public function ref_ajax()
	{
		if ( 'wp_ajax_nopriv_stt2extat_ref' != current_action()
			|| ! defined( 'DOING_AJAX' )
			|| ! DOING_AJAX
			|| ! isset( $_POST['ref'], $_POST['post_ID'] ) )
			wp_die();
		
		$referrer  = wp_unslash( $_POST['ref'] );
		$this->ref_proccess( $referrer );
		wp_die();
	}
	
	/**
	 * proccess terms from referrer
	 *
	 * @since 1.1
	 *
	*/
	public function ref_proccess( $referrer )
	{
		$this->referrer = ( ! empty( $referrer ) ) ? esc_url( $referrer ) : false;
		
		$post_ids      = ( isset( $_POST['post_ID'] ) && is_array( $_POST['post_ID'] ) ) ? $_POST['post_ID'] : stt2extat_post_ids_query();
		$post_ids      = array_map( 'absint', $post_ids );
		
		$localhost     = $this->allow_localhost();
		
		$this->referrer = $this->get_referer();
		
		if( false != $localhost )
			$this->referrer = $localhost;
		
		if ( ! $this->referrer || ! is_array( $this->referrer ) || ! array_filter( $this->referrer ) )
			return false;
		
		if ( false != $this->get_delimiter() ) :
		
			if ( 3 < mb_strlen( $this->get_term() ) && 70 >= mb_strlen( $this->get_term() ) ) :
			
				global $stt2extat_data;
				$last_id = ( int ) $stt2extat_data->last_id + ( int ) 1;
				
				$q = stt2extat_filter_text( $this->get_term() );
				
				if ( isset( $q['error'] ) || '' == $q )
					return false;
				
				$ignore  = apply_filters( 'stt2extat_ignore_relevant', true );
				$post_id = stt2extat_get_relevant_post_on_search_page( $post_ids, $q, $ignore );
				
				if ( null == $post_id )
					return false;
				
				do_action( 'stt2extat_nopriv_update_post_meta',
					$q,
					$post_id,
					null,
					$last_id,
					$stt2extat_data->terms,
					null
				);
			endif;
			
		endif;
		
		return false;
	}
	
	/**
	 * get pair useragent and delimiter
	 *
	 * @since 1.1
	 *
	*/
	protected function get_delimiter()
	{
		global $stt2extat_settings;
		$set = $stt2extat_settings;
		
		$delim = false;
		$search_engines = $set['useragent'];
		
		if ( isset( $search_engines[ $this->referrer['host'] ] ) ) :
			$delim = $search_engines[ $this->referrer['host'] ];
			
		else :
			if ( strpos( 'ref:' . $this->referrer['host'], 'google' ) )
				$delim = 'q';
			elseif ( strpos( 'ref:' . $this->referrer['host'],'search.atomz.' ) )
				$delim = 'sp-q';
			elseif ( strpos( 'ref:' . $this->referrer['host'],'search.msn.' ) )
				$delim = 'q';
			elseif ( strpos( 'ref:' . $this->referrer['host'],'search.yahoo.' ) )
				$delim = 'p';
			elseif ( strpos( 'ref:' . $this->referrer['host'],'yandex' ) )
				$delim = 'text';
			elseif ( strpos( 'ref:' . $this->referrer['host'],'baidu' ) )
				$delim = 'wd';
			elseif ( strpos( 'ref:' . $this->referrer['host'],'baidu' ) )
				$delim = 'word';
			elseif ( strpos( 'ref:' . $this->referrer['host'],'ask.com' ) )
				$delim = 'q';
		endif;
		
		return $delim;
	}
	
	/**
	 * get terms of referrer
	 *
	 * @since 1.1
	 *
	*/
	protected function get_term( )
	{
		static $term = '';
        static $pattern = '';
		
		$delim_part = '?' . $this->get_delimiter() . '=';
		
		if ( false === strpos( $this->referrer['url'], $delim_part ) ) :
			$delim_part = '&#038;' . $this->get_delimiter() . '=';
			$delim_part = ( false !== strpos( $this->referrer['url'], $delim_part ) ) ? $delim_part : false;
		endif;
		
		if ( false == $delim_part )
			return '';
		
		$term = explode( $delim_part, $this->referrer['url'] );
		
		if ( isset( $term[1] ) )
			$term = explode( '&#038;', $term[1] );
		
		if ( isset( $term[0] ) )
			$term = sanitize_text_field( urldecode( $term[0] ) );
		
		if ( is_array( $term ) || is_email( $term ) || false != preg_match( '#^https?://.#i', $term ) )
			return '';
		
		if ( '' == $pattern )
			$pattern = apply_filters( 'stt2extat_email_regexp', '/([a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4})/' );
		
		$term = preg_replace( $pattern, '', $term );
		
		return $term;
	}
	
	/**
	 * get info host and uri of referrer
	 *
	 * @since 1.1
	 *
	*/
	protected function get_referer()
	{
		if ( ! $this->referrer  )
			return false;
		
		$parsed_url = stt2extat_parse_url( $this->referrer );
		
		if ( ! isset( $parsed_url['host'], $parsed_url['query'] ) || ! array_filter( $parsed_url ) )
			return false;
		
		$parsed_url['url'] = strtolower( $this->referrer );
		
		return wp_parse_args( $parsed_url['url'], $parsed_url );
	}
	
	/**
	 * Test referrer between localhost, or localhost to your live network site
	 * use add_filter( 'stt2extat_allow_localhost', '__return_true' );
	 * and add your url and parameter in useragent list admin area.
	 *
	 * @since 1.1
	 *
	*/
	public function allow_localhost()
	{
		if ( ! $this->referrer  )
			return false;
		
		$siteurl   = get_option( 'siteurl' );
		$home      = parse_url( $siteurl );
		$local     = 'localhost';
		$local     = parse_url( esc_url( $local ) );
		$local     = apply_filters( 'stt2extat_allow_localhost_to_network', $local );
		
		if ( ! isset( $local['host'] ) )
			return false;
		
		if ( strtolower( $local['host'] ) == strtolower( $home['host'] ) ) :
			$referrer = strtolower(  $this->referrer  );
			$local   = strtolower(  $local['host']  );
			
			if ( false !== strpos( $referrer , trailingslashit( esc_url( $local ) ) ) ) :
				$parse_url        = parse_url( $referrer );
				$parse_url['url'] = $referrer;
				
				if ( has_filter( 'stt2extat_allow_localhost', '__return_true' ) )
					return $parse_url;
			endif;
		endif;
		return false;
	}
	
	/**
	 * add defer into jquery-stt2extat-res.js
	 *
	 * @since 1.1
	 *
	*/
	public function script_loader_tag( $tag, $handle )
	{
		$scripts_to_defer = array( 'jquery-stt2extat-res.js' );
		
		foreach( $scripts_to_defer as $defer_script )
			if( false !== strpos( $tag, $defer_script ) )
				return str_replace( ' src', ' defer src', $tag );
		return $tag;
	}
	
	/**
	 * enqueue scripts
	 *
	 * @since 1.1
	 *
	*/
	public function enqueue_scripts()
	{
		global $stt2extat_settings;
		$set = $stt2extat_settings;
		
		$is_single = apply_filters( 'stt2extat_is_single', true );
		if ( ! $is_single && ! is_search() )
			return false;
		
		$post_ids = stt2extat_post_ids_query();
		
		if ( ! $post_ids )
			return false;
		
		$settings = array(
			'post_ID'  => $post_ids,
			'showList' => ( isset( $set['auto'] ) ) ? bool_from_yn( $set['auto'] ) : ( bool ) 0,
			'single' => is_single()
		);
		
		$heartbeat_set   = wp_heartbeat_settings( $settings );
		$localize_script = wp_parse_args( $heartbeat_set, $settings );
		
		wp_register_script( 'jquery-stt2extat-res', plugin_dir_url( __FILE__ ) . 'assets/js/jquery-stt2extat-res.js' , array( 'jquery-core' ), STT2EXTAT_VER, true );
		wp_enqueue_script( 'jquery-stt2extat-res' );
		wp_localize_script( 'jquery-stt2extat-res', 'stt2extatJs', $localize_script );
	}
	
	/**
	 * add inline scripts
	 *
	 * @since 1.1
	 *
	*/
	public function inline_js()
	{
		$is_single = apply_filters( 'stt2extat_is_single', true );
		if ( ! $is_single && ! is_search() )
			return false;
		
		if ( wp_script_is( 'jquery', 'done' ) ) :
			echo "<script type='text/javascript'>\n";
			echo "jQuery( document ).ready( function( $ ) {\n";
			echo "$( this ).stt2extat();\n";
			echo "} );\n";
			echo "</script>\n";
		endif;
	}
}