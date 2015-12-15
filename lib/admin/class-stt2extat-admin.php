<?php
/*
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 * @version 1.1
 */

if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

class STT2EXTAT_Admin
{
	public $plugin_data;
	public $meta_key = '_stt2extat';
	public $prefix = '';
	public $blog_prefix = '';
	private $group;
	
	static public function init()
	{
		$class = __CLASS__;
		new $class;
	}
	
	public function __construct()
	{
		global $stt2extat_settings, $stt2extat_sanitize;
		
		$this->plugin_data = stt2extat_get_plugin_data();
		$this->data        = $stt2extat_sanitize->data();
		$this->set         = $stt2extat_settings;
		
		$this->prefix = $this->blog_prefix = '';
		
		if ( function_exists( 'got_url_rewrite' ) )
			if ( ! got_url_rewrite() )
				$this->prefix = '/index.php';
		
		if ( is_multisite() && !is_subdomain_install() && is_main_site() )
			$this->blog_prefix = '/blog';
		
		add_action( 'admin_init', array(
			$this,
			'page_init'
		) );
		
		add_filter( 'admin_head', array(
			$this,
			'options_permalink_add_js'
		) );
		
		add_action( 'admin_menu', array(
			$this,
			'plugin_page'
		) );
		
		add_action( 'admin_enqueue_scripts', array(
			$this,
			'admin_enqueue_scripts'
		) );
		
		add_action( 'wp_ajax_stt2extat_ajax_table', array(
			$this,
			'ajax_table'
		) );
		
		add_action( 'wp_ajax_stt2extat_delete_all_terms', array(
			$this,
			'delete_all_terms'
		) );
		
		add_action( 'wp_ajax_stt2extat_migrate_stt2_terms', array(
			$this,
			'migrate_stt2_terms'
		) );
		
		add_action( 'wp_ajax_stt2extat_check_relevant_terms',  array(
			$this,
			'check_relevant_terms'
		) );
	}
	
	/**
	 * fire on admin init
	 *
	 * @since 1.1
	 *
	*/
	public function page_init()
	{
		$action = ( isset( $_GET['action'] ) ) ? sanitize_key( $_GET['action'] ) : null;
		do_action( 'stt2extat_edited_action', $action );
		do_action( 'stt2extat_deactivate_plugins' );
		add_action( 'admin_notices', 'stt2extat_admin_notices' );
		
		register_setting(
			'stt2extat',
			'stt2extat_settings',
			array(
				$this,
				'sanitize'
			)
		);
		
		register_setting(
			'stt2extat_update_term',
			'stt2extat_settings_update_term',
			array(
				$this,
				'sanitize_update_term'
			)
		);
		
		add_settings_section(
			'stt2extat_permalink',
			'',
			array(
				$this,
				'section_permalink'
			),
			'permalink'
		);
		
		$this->update_search_structure();
	}
	
	/**
	 * fire admin menu for plugin page
	 *
	 * @since 1.1
	 *
	*/
	public function plugin_page()
	{
		global $stt2extat_screen_id;
		
		$stt2extat_screen_id = add_options_page(
			$this->plugin_data->Name,
			strtoupper( $this->plugin_data->TextDomain ),
			'manage_options',
			$this->plugin_data->TextDomain,
			array(
				$this,
				'create_page'
			)
		);
		
		add_action( 'admin_footer-' . $stt2extat_screen_id, array(
			$this,
			'admin_footer'
		) );
		
		add_action( 'load-' . $stt2extat_screen_id, array(
			$this,
			'screen_tab' 
		), 20 );
	}
	
	/**
	 * sanitize input for stt2extat_settings option
	 *
	 * @since 1.1
	 *
	*/
	public function sanitize( $input )
	{
		global $stt2extat_sanitize;
		if ( isset( $input['useragent'] ) )
			$input['useragent'] = array_combine( $input['useragent']['k'], $input['useragent']['v'] );
		
		if ( isset( $input['reset'] ) && 'Reset' == sanitize_text_field( $input['reset'] ) ) :
			add_settings_error(
				'stt2extat_reset',
				esc_attr( 'settings_reseted' ),
				__( 'Settings Reseted.', 'stt2extat' ),
				'updated'
			);
			return stt2extat_default_setting();
		endif;
		
		$new_input = array();
		$keys      = array_keys( $this->set );
		foreach ( $keys as $k ) :
			if ( isset( $input[ $k ] ) )
				$new_input[ $k ] = $input[ $k ];
			else
				$new_input[ $k ] = false;
		endforeach;
		
		return $stt2extat_sanitize->sanitize( $new_input );
	}
	
	/**
	 * sanitize input for stt2extat_settings_update_term option
	 *
	 * @since 1.1
	 *
	*/
	public function sanitize_update_term( $input )
	{
		global $stt2extat_data;
		
		if ( isset( $input['term_postid'], $input['term_id'], $input['term_name'], $input['old_term'] ) && check_admin_referer( $this->plugin_data->TextDomain . '_update_term-options', '_wpnonce' ) ) :
		
			$update = false;
		
			$new_term  = strtolower( sanitize_text_field( $input['term_name'] ) );
			$old_term  = strtolower( sanitize_text_field( $input['old_term'] ) );
			$post_id   = absint( $input['term_postid'] );
			$term_id   = absint( $input['term_id'] );
			
			if ( $new_term == $old_term )
				$update = 5;
			else
				$update = stt2extat_update_postmeta( $new_term, $post_id, $old_term, 0,$stt2extat_data->terms, $term_id );
			
			if ( is_int( $update ) ) :
				$msg = stt2extat_edit_term_notice( $update, '', true );
				add_settings_error(
					'stt2extat_term_error',
					esc_attr( 'stt2extat_error_' . absint( $update ) ),
					$msg,
					'error'
				);
				return absint( $update );
			endif;
			
			$location = 'options-general.php?page=stt2extat';
			$location = add_query_arg( 'message', 3, $location );
			wp_safe_redirect( $location );
			exit;
		endif;
	}
	
	/**
	 * create admin plugin page
	 *
	 * @since 1.1
	 *
	*/
	public function create_page()
	{
		global $stt2extat_screen_id, $current_screen;
		
		if ( $current_screen->id != $stt2extat_screen_id )
			return;
		
		add_filter(
			'admin_footer_text',
			array(
				$this,
				'admin_footer_text'
			)
		);
		
		add_filter(
			'update_footer',
			array(
				$this,
				'update_footer'
			),
			20
		);
		
		$error = false;
		if ( isset( $_REQUEST['message'] ) && $msg = (int) $_REQUEST['message'] ) :
			$error = ( isset( $_GET['error'] ) ) ? wp_validate_boolean( $_GET['error'] ) : $error;
			do_action( 'stt2extat_notice', $msg, $error, false );
		endif;
		
		$_SERVER['REQUEST_URI'] = remove_query_arg( array( 'message', 'error' ), $_SERVER['REQUEST_URI'] );
		
		$this->create_mb();
	}
	
	/**
	 * create screen option tab
	 *
	 * @since 1.1
	 *
	*/
	public function screen_tab()
	{
		global $current_screen;
		
		foreach ( $this->data['help']['normal'] as $id => $data ) :
			$current_screen->add_help_tab( array(
				'id'       => $id,
				'title'    => $data['title'],
				'callback' => array(
					$this,
					'prepare'
				)
			) );
		endforeach;
		
		$current_screen->set_help_sidebar( $this->data['help']['description'] );
		
		foreach ( $this->group('default') as $k => $v ) :
			if ( 'tab' == $v )
				continue;
			
			$id     = ( 'save' == $v ) ? 'submitdiv' : $v;
			$screen = $current_screen->id;
			
			if ( 'sidebar' == $this->data[ $k ]['parameter'] ) :
				$context = 'side';
			elseif ( 'general' == $this->data[ $k ]['group'] || 'manual' == $this->data[ $k ]['group'] ) :
				$context = 'advanced';
			elseif ( 'update_term' == $this->data[ $k ]['group'] ) :
				$context = 'advanced';
				$screen  = $current_screen->id . '_update_term';
			else :
				$context = 'normal';
			endif;
			
			$priority = 'high';
			
			add_settings_section(
				$id . '_section',
				'',
				array(
					$this,
					'section_' . $id
				),
				$this->plugin_data->TextDomain . '_' . $id
			);
			
			add_meta_box(
				$id,
				ucwords( $this->data[ $k ]['subgroup'] ),
				array(
					$this,
					'meta_box_cb'
				),
				$screen,
				$context,
				$priority,
				array(
					'mb' => $v
				)
			);
			
			foreach ( $this->data as $item ) :
				if ( $id == $item['group'] ) :
					if ( 'sidebar' == $item['parameter'] || 'manual' == $item['parameter'] || 'stats' == $item['parameter'] )
							continue;
					add_settings_field(
						$item['parameter'] . '_setting',
						$item['subtitle'],
						array(
							$this,
							'field_cb'
						),
						$this->plugin_data->TextDomain . '_' . $id,
						$id . '_section',
						array( 'label_for' => $item['parameter'], 'parameter' => $item['parameter'], 'optional' => $item['optional'], 'description' => $item['description'] )
					);
				endif;
			endforeach;
			
		endforeach;
	}
	
	/**
	 * prepare screen option tab
	 *
	 * @since 1.1
	 *
	*/
	public function prepare( $current_screen, $tab )
	{
		printf ( '<p>%s</p>',
			$tab['callback'][0]->data['help']['normal'][ $tab['id'] ]['content']
		);
	}
	
	/**
	 * create metabox each settings section
	 *
	 * @since 1.1
	 *
	*/
	protected function create_mb()
	{
		global $current_screen;

		$col         = ( 1 == $current_screen->get_columns() ) ? 1 : 2;
		
		printf ( '<div id="%1$s" class="wrap"><h1>%2$s</h1><div>%3$s</div>',
			$this->plugin_data->TextDomain,
			$this->plugin_data->Name,
			__( 'Manage your terms better, add terms into single post manually, get terms via referrer, and save them as post meta. Search the terms that relevant of post content as well as WordPress search default algorithm.', 'stt2extat' )
		);
		
		if ( isset( $_GET['action'] ) && 'edit' == sanitize_key( $_GET['action'] ) ):
			printf ( '<h3>%1$s</h3><div id="poststuff"><div id="post-body" class="metabox-holder columns-%2$s">',
				__( 'Edit Terms', 'stt2extat' ),
				1
			);
		?>
						<form id="stt2extat-sub-form" method="post" action="options.php">
							<div id="postbox-container" class="postbox-container">
								<?php do_meta_boxes( $current_screen->id . '_update_term', 'advanced', 'stt2extat_settings_update_term' ); ?>
							</div>
							<div class="clear"></div>
						</form>
					</div>
				</div>
			</div>
		<?php
		
		else:
		
			printf ( '<div id="poststuff"><div id="post-body" class="metabox-holder columns-%1$s">',
				absint( $col )
			);
		?>				<form id="stt2extat-main-form" method="post" action="options.php">
							<div id="postbox-container-2" class="postbox-container">
								<?php do_meta_boxes( $current_screen, 'advanced', 'stt2extat_settings' ); ?>
								<?php do_meta_boxes( $current_screen, 'normal', 'stt2extat_settings' ); ?>
							</div>
							<div id="postbox-container-1" class="postbox-container">
								<?php do_meta_boxes( $current_screen, 'side', 'stt2extat_settings' ); ?>
							</div>
							<div class="clear"></div>
						</form>
					</div>
				</div>
			</div>
		<?php
		
		endif;
	}
	
	/**
	 * metabox callback each settings section
	 *
	 * @since 1.1
	 *
	*/
	public function meta_box_cb( $post = null, $args )
	{
		$option = sanitize_key( $args['args']['mb'] );
		switch ( $option )
		{
			case 'general' :
				do_settings_sections( 'stt2extat_general' );
				break;
			
			case 'manual' :
				do_settings_sections( 'stt2extat_manual' );
				break;
			
			case 'stats' :
				do_settings_sections( 'stt2extat_stats' );
				break;
			
			case 'delete' :
				$checked = get_option( 'stt2extat_check_relevant_terms' );
				printf (
					'<div class="submitbox">
						<div class="alignleft">
							<label for="check_relevant_stt2_terms"><input type="button" id="migrate_stt2_terms" class="button button-secondary migrate-searchterms button-small" value="%1$s"> <input type="checkbox" id="check_relevant_stt2_terms" class="check_relevant_terms" value="1" %2$s> %3$s<span class="spinner"></span></label>
							<div class="clear"></div>
						</div>
						<div class="alignright">
							<label for="delete_all_searchterms"><input type="button" id="delete_all_searchterms" class="button delete-searchterms submitdelete" value="%4$s"><span class="spinner"></span></label>
						</div>
						<div class="clear"></div>
					</div>',
					__( 'Migrate terms of SEO SearchTerms Tagging 2', 'stt2extat' ),
					checked( $checked, 1, false ),
					__( 'Relevant Only', 'stt2extat' ),
					__( 'Delete All Terms', 'stt2extat' )
				);
				break;
			
			case 'update_term' :
				do_settings_sections( 'stt2extat_update_term' );
			?>
				<div id="major-publishing-actions">
					<div class="publishing-action">
						<?php
							settings_fields( 'stt2extat_update_term' );
							wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
							wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
							submit_button( __( 'Update', 'stt2extat' ), 'button-primary update-term', 'update_term_settings', false, array( 'id' => 'update_term_settings' ) );
						?>
					</div>
				<div class="clear"></div>
				</div>
			<?php
				break;
			
			case 'save':
			?>
				<div id="major-publishing-actions">
					<div id="publishing-action">
						<?php
							settings_fields( 'stt2extat' );
							wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
							wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
						?>
						<div class="spinner"></div>
						<?php
							submit_button( __( 'Save Changes', 'stt2extat' ), 'button-primary settings-save', 'save_settings', false, array( 'id' => 'save_settings_footer' ) );
						?>
					</div>
					<div class="publishing-action">
						<?php
							submit_button( __( 'Reset', 'stt2extat' ), 'button-secondary reset', 'stt2extat_settings[reset]', false, array( 'id' => 'reset_settings_footer' ) );
						?>
					</div>
					<div class="clear"></div>
				</div>
			<?php
				do_settings_sections( 'stt2extat_save' );
				break;
				
			case 'support' :
				do_settings_sections( 'stt2extat_support' );
				break;
				
			case 'features' :
				do_settings_sections( 'stt2extat_features' );
				break;
				
			default :
				break;
		}
	}
	
	/**
	 * General Section Hook
	 *
	 * @since 1.1
	 *
	*/
	public function section_general()
	{
		do_action( 'stt2extat_section_general' );
	}
	
	/**
	 * Manual Input Section Hook
	 *
	 * @since 1.1
	 *
	*/
	public function section_manual()
	{
		do_action( 'stt2extat_section_manual' );
		echo '<div id="stt2extat-manual"></div>';
	}
	
	/**
	 * Update Term Section Hook
	 *
	 * @since 1.1
	 *
	*/
	public function section_update_term()
	{
		do_action( 'stt2extat_section_update_term' );
	}
	
	/**
	 * Terms Stats Section Hook
	 *
	 * @since 1.1
	 *
	*/
	public function section_stats()
	{
		global $current_screen;
		
		do_action( 'stt2extat_section_stats' );
		
		echo '<div id="ajax-response"></div><div id="stt2extat-table-stats"></div>';
		
		$args = array(
            'singular' => 'table-stt2extat',
			'plural'   => 'table-stt2extat',
			'ajax'     => true,
			'screen'   => $current_screen->id,
		);
		
		$table = new STT2EXTAT_Table( $args );
		$table->prepare_items();
		$table->display();
	}
	
	/**
	 * Support Section
	 *
	 * @since 1.1
	 *
	*/
	public function section_support()
	{
		$class      = 'stt2extat-quote';
		$textright  = 'textright';
		$icon_email = 'dashicons dashicons-email-alt';
		$url        = $this->plugin_data->AuthorURI . '/donate/';
		$title      = __( 'PayPal Donate', 'stt2extat' );
		$email      = 'contact&#64;jevuska&#46;com';
		
		printf ( $this->data['support']['description'],
			esc_attr( $class ),
			esc_attr( $textright ),
			esc_attr( $icon_email ),
			esc_url( $url ),
			esc_attr( $title ),
			esc_html( $email )
		);
	}
	
	/**
	 * Features Section
	 *
	 * @since 1.1
	 *
	*/
	public function section_features()
	{
		printf ( '<div id="features-actions"><div class="alignright"><a href="%1$s" class="button button-secondary button-small" title="%2$s">%2$s</a> <a href="%3$s" class="button button-secondary button-small" title="%4$s">%4$s</a></div><div class="alignleft"><span class="dashicons dashicons-admin-generic"></span></div><div class="clear"></div></div>',
			admin_url( 'widgets.php' ),
			__( 'Setup Widget', 'stt2extat' ),
			'#manual',
			__( 'Manual Input', 'stt2extat' )
		);
	}
	
	/**
	 * Permalink Section on option-permalink.php page
	 *
	 * @since 1.1
	 *
	*/
	public function section_permalink( $args )
	{
		global $wp_rewrite;
		
		$info =  __( 'If you like, you may enter custom structures for your search <abbr title="Universal Resource Locator">URL</abbr>s here. For example, using <code>topics</code> as your search base would make your search links like <code>%s/topics/search+term</code>. If you leave these blank the defaults will be used.', 'stt2extat' );
		
		$home_url = get_option( 'home' ) . $this->blog_prefix . $this->prefix;
		
		printf ( '<h3 class="title" id="%1$s">%2$s</h3><p>%3$s</p>',
			esc_attr( $args['id'] ),
			__( 'STT2EXTAT Permalink', 'stt2extat' ),
			sprintf( $info,
				$home_url
			)
		);
		
		$search_structure = get_option( 'stt2extat_search_structure' );
		if ( is_multisite() && !is_subdomain_install() && is_main_site() )
		{
			$search_structure = preg_replace( '|^/?blog|', '', $search_structure );
		}

		$front    = _x( 'search', 'sample permalink base' ) . '/';
		
		$disabled = '';
		if ( empty( $wp_rewrite->permalink_structure ) ) :
			$disabled = 'disabled="disabled"';
		endif;
		
		$structures = array(
			0 => '',
			1 => $this->prefix . '/' . $front . '%search%',
			2 => $this->prefix . '/' . $front . '%search-term%',
			3 => $this->prefix . '/' . $front . '%search_term%',
			4 => $this->prefix . '/' . _x( 'topics', 'sample permalink base' ) . '/%search%'
		);
		
		$fieldset = array(
			'stt2extat_default_search_structure' => array(
				'id'    => 'stt2extat_default_field',
				'title' => 'Default',
				'value' => $structures[1],
				'radio_val' => ''
			),
			'stt2extat_dashes_search_structure' => array(
				'id' => 'stt2extat_dashes_field',
				'title' => 'Dashes',
				'value' => $structures[2],
				'radio_val' => $structures[2]
			),
			'stt2extat_underscore_search_structure' => array(
				'id'        => 'stt2extat_underscore_field',
				'title'     => 'Underscore',
				'value'     => $structures[3],
				'radio_val' => $structures[3]
			),
			
			'stt2extat_search_base_structure' => array(
				'id' => 'stt2extat_search_base_structure_field',
				'title'     => 'Search Base',
				'value'     => $structures[4],
				'radio_val' => $structures[4]
			),
			
			'stt2extat_search_structure' => array(
				'id'        => 'stt2extat_search_structure_field',
				'title'     => 'Custom Structure',
				'value'     => '',
				'radio_val' => 'stt2extat_custom'
			)
		);

		foreach ( $fieldset as $k => $v ) :
		
			if ( 'stt2extat_search_structure' == $k )
				$checked = checked( ! in_array( $search_structure, $structures ),true, false );
			else
				$checked = checked( $v['radio_val'], $search_structure, false );
			
			add_settings_field(
				$v['id'],
				sprintf( '<label><input id="%1$s" class="stt2extat-permalink" type="radio" name="stt2extat_search_link" value="%2$s" %3$s %4$s/>%5$s</label>',
					esc_attr( $v['id'] ),
					esc_attr( $v['radio_val'] ),
					esc_attr( $checked ),
					esc_attr( $disabled ),
					esc_html( $v['title'] )
				),
				array(
					$this,
					'field_cb'
				),
				'permalink',
				$args['id'],
				array(
					'parameter' => $k,
					'home_url'  => $home_url,
					'value'     => $v['value'],
					'optional'  => $disabled
				)
			);
		endforeach;
		
	}
	
	/**
	 * Additional script for STT2EXTAT Permalink on option-permalink.php page
	 *
	 * @since 1.1
	 *
	*/
	public function options_permalink_add_js()
	{
		$current_screen = get_current_screen();
		
		if ( 'options-permalink' != $current_screen->id )
			return;
	?>
		<script type="text/javascript">
		jQuery( document ).ready( function() {
			
			jQuery('.permalink-structure input:radio').change( function() {
				if ( '' != this.value ) {
					jQuery( "input.stt2extat-permalink" ).prop( "disabled", false );
				} else {
					jQuery( "input.stt2extat-permalink" ).prop( "disabled", true );
					jQuery( "#stt2extat_default_field" ).attr( "checked", "checked" );
					jQuery( "input#stt2extat_search_structure" ).val( "" );
				}
			} );
			
			jQuery( "#permalink_structure" ).focus( function() {
				jQuery( "input.stt2extat-permalink" ).prop( "disabled", false );
			} );
			
			jQuery( "input[name=stt2extat_search_link]" ).change( function() {
				if ( "stt2extat_custom" == this.value )
					return;
				jQuery( "#stt2extat_search_structure" ).val( this.value );
			} );
			
			jQuery( "#stt2extat_search_structure" ).focus( function() {
				jQuery( "#stt2extat_search_structure_field" ).attr( "checked", "checked" );
			} );
		} );
		</script>
	<?php
	}
	
	/**
	 * Load Table via ajax for Terms Stats
	 *
	 * @since 1.1
	 *
	*/
	public function ajax_table()
	{
		$args = array(
            'singular' => 'table-stt2extat',
			'plural'   => 'tables-stt2extat',
			'ajax'     => true,
			'screen'   => sanitize_key( $_POST['table']['screen']['id'] ),
		);
		
		$table = new STT2EXTAT_Table( $args );
		$table->ajax_response();
	}
	
	/**
	 * Delete all terms via ajax in Terms Stats table
	 *
	 * @since 1.1
	 *
	*/
	public function delete_all_terms()
	{
		do_action( 'wp_ajax_stt2extat_delete_term', 'delete_all' );
		wp_die();
	}
	
	/**
	 * Ajax checkbox to set relevant terms in Terms Stats table
	 *
	 * @since 1.1
	 *
	*/
	public function check_relevant_terms()
	{
		if ( ! isset( $_REQUEST['_wpnonce'], $_POST['val'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'heartbeat-nonce' ) ) :
			do_action( 'stt2extat_notice', 9, true, false );
			wp_die();
		endif;
		
		$val = absint( $_POST['val'] );
		if( update_option( 'stt2extat_check_relevant_terms', $val ) );
			wp_die( $val );
			
		do_action( 'stt2extat_notice', 11, true, false );
		wp_die();
	}
	
	/**
	 * Migrate all terms of SEO SearchTerm Tagging 2 on database into postmeta table
	 *
	 * @since 1.1
	 *
	*/
	public function migrate_stt2_terms()
	{
		if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'heartbeat-nonce' ) ) :
			do_action( 'stt2extat_notice', 9, true, false );
			wp_die();
		endif;
		
		if( ! session_id() && ! headers_sent() )
			session_start();
		
		$location = 'options-general.php?page=stt2extat';
		
		$result = wp_cache_get( 'stt2extat_migrate_stt2' );
		
		if ( false == $result ) :
			global $wpdb;
			$sql = "SELECT * FROM " . $wpdb->prefix . "stt2_meta ;";
			$result = $wpdb->get_results( $sql );
			wp_cache_set( 'stt2extat_migrate_stt2', $result, 900 );
		endif;
		
		if ( false != $result ) :
			$data = array();
			foreach ( $result as $r ) :
				
				if ( 'any' != stt2extat_post_type() && ! in_array( get_post_type( $r->post_id ), stt2extat_post_type() ) )
					continue;
				
				$ignore  = get_option( 'stt2extat_check_relevant_terms' );
				
				if ( '1' == $ignore ) :
					$relevant = stt2extat_get_relevant_post(
						$r->post_id,
						sanitize_text_field( $r->meta_value ),
						false,
						true
					);
					
					if ( ! $relevant )
						continue;
				endif;
				
				$obj = new StdClass;
				$obj->post_id       = $r->post_id;
				$obj->count         = $r->meta_count;
				$obj->post_modified = strtotime( $r->last_modified );
				
				$data[ $r->meta_value ] = $obj;
			endforeach;
			
			if ( 0 == count( $data ) ) :
				$location = add_query_arg( array( 'error' => true, 'message' => 6 ), $location );
				wp_die( $location );
			endif;
			
			global $stt2extat_data;
			
			$new = array();
			foreach( $data as $key => $val ):
				$stt2extat_data->last_id = $stt2extat_data->last_id + 1;
				do_action( 'stt2extat_nopriv_update_post_meta',
					$key,
					$val->post_id,
					null,
					$stt2extat_data->last_id,
					$stt2extat_data->terms,
					null,
					$val->count,
					$val->post_modified
				);
			endforeach;
			
			$location = add_query_arg( array( 'error' => false, 'message' => 15 ), $location );
		else :
			do_action( 'stt2extat_notice', 6, true, false );
			wp_die();
		endif;
		
		wp_die( $location );
	}
	
	/**
	 * Fire search permalink structure to updated
	 *
	 * @since 1.1
	 *
	*/
	public function update_search_structure()
	{
		if ( isset( $_POST['stt2extat_search_structure'] ) || isset( $_POST['permalink_structure'] ) ) :
		
			check_admin_referer( 'update-permalink' );
			
			global $wp_rewrite;
			
			$search_structure = sanitize_text_field( $_POST['stt2extat_search_structure'] );
			
			if ( ! empty( $search_structure ) ) :
			
				$search_structure = preg_replace( '#/+#', '/', '/' . str_replace( '#', '', $search_structure ) );
				
				if ( $prefix && $blog_prefix )
					$search_structure = $this->prefix . preg_replace( '#^/?index\.php#', '', $search_structure );
				else
					$search_structure = $this->blog_prefix . $search_structure;
				
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
				
				$extra =  str_replace( $rewritecode, '', substr( $search_structure, strpos( $search_structure, $rewritecode ) ) );
				
				$search_structure = $wp_rewrite->root . $search_base . $rewritecode . $extra;
				
				if ( ! $search_base || empty( $search_base ) || ( '%search%' == $rewritecode && '/search/' == $search_base && '' == $extra ) )
					$search_structure = '';
				
			endif;
			
			if ( empty( $wp_rewrite->permalink_structure ) && '' == $_POST['permalink_structure'] || '' == $_POST['permalink_structure'] )
				$search_structure = '';
			
			update_option( 'stt2extat_search_structure', $search_structure );
			
			$wp_rewrite->init();
			
		endif;
	}
	
	/**
	 * Callback for each setting fields
	 *
	 * @since 1.1
	 *
	*/
	public function field_cb( $args )
	{
		switch ( $args['parameter'] )
		{
			case 'number' :
				$field = $this->input_form( $args['parameter'], 'number', 'stt2extat_settings', 'small-text', 'required', 1, 20, $args['description'] );
				break;
			
			case 'max_char' :
				$field = $this->input_form( $args['parameter'], 'number', 'stt2extat_settings', 'small-text', 'required', 4, 70, $args['description'] );
				break;
			
			case 'text_header' :
				$field = $this->input_form( $args['parameter'], 'text', 'stt2extat_settings', 'regular-text' );
				break;
			
			case 'term_name' :
				$field = $this->input_form( $args['parameter'], 'text', 'stt2extat_settings_update_term', 'large-text', 'aria-required="true"' );
				break;
			
			case 'term_slug' :
				$field  = $this->input_form( $args['parameter'], 'text', 'stt2extat_settings_update_term', 'large-text', 'required readonly="readonly"' );
				$field .= $this->input_form( 'old_term', 'hidden', 'stt2extat_settings_update_term', 'large-text', 'required' );
				$field .= $this->input_form( 'term_postid', 'hidden', 'stt2extat_settings_update_term', 'large-text', 'required' );
				$field .= $this->input_form( 'term_id', 'hidden', 'stt2extat_settings_update_term', 'large-text', 'required' );
				$field .= $this->input_form( '_edit_nonce', 'hidden', 'stt2extat_settings_update_term', 'large-text', 'required' );
				break;
			
			case 'stopwords' :
				$field = $this->textarea_form( $args['parameter'], $args['description'] );
				break;
			
			case 'useragent' :
				$field  = $this->multiple_input( $args['parameter'], 'text', 'stt2extat_settings', 'regular-text' );
				break;
			
			case 'active' :
			case 'auto' :
			case 'html_heading' :
			case 'display' :
			case 'convert' :
			case 'count' :
			case 'searchexcerpt' :
				$field = $this->selected_form( $args['parameter'], $this->set[ $args['parameter'] ], $args['optional'], $args['description'] );
				break;
				
			case 'schedule' :
				$field  = $this->input_form( $args['parameter'], 'number', 'stt2extat_settings', 'small-text', '', ( int ) 0, ( int ) 360, $args['description'] );
				break;
			
			case 'stt2extat_default_search_structure' :
			case 'stt2extat_dashes_search_structure' :
			case 'stt2extat_underscore_search_structure' :
			case 'stt2extat_search_base_structure' :
				$field  = '<code>' . $args['home_url'] . $args['value'] . '</code>';
				break;
				
			case 'stt2extat_search_structure' :
				$field  = $this->input_form( $args['parameter'], 'text', 'stt2extat_search_structure', 'regular-text code stt2extat-permalink', null, $args['optional'] );
				break;
				
			default;
				$field = $this->checkbox_form( $args['parameter'], $args['description'] );
				break;
		}
		
		echo ( $field );
	}
	
	/**
	 * checkbox input html format
	 *
	 * @since 1.1
	 *
	*/
	public function checkbox_form( $param, $description )
	{
		$output  = sprintf( '<label for="%1$s"><input type="checkbox" id="%1$s" name="stt2extat_settings[%1$s]" value="%2$s" %3$s/> <span class="description">%4$s</span></label>',
			esc_attr( $param ),
			( bool ) 1,
			checked( isset( $this->set[ $param ] ) && false != $this->set[ $param ], true, false ),
			$description
		);
		return $output;
	}
	
	/**
	 * number or text input html format
	 *
	 * @since 1.1
	 *
	*/
	public function input_form( $param, $type, $name, $class, $required = null, $min = '', $max = '', $description = '' )
	{
		$before = $after = $value = $last = $after_input = $additional_field = '';
		
		if ( ( isset( $this->set[ $param ] ) && '' != $this->set[ $param ] ) )
		{
			if ( is_array( $this->set[ $param ] ) )
				$value = array_map( 'sanitize_text_field', $this->set[ $param ] );
			else
				$value = sanitize_text_field( $this->set[ $param ] );
		}
		
		$name  = $name . '['  . $param . ']';
			
		if ( 'term_name' == $param || 'old_term' == $param || 'term_postid' == $param || 'term_id' == $param || 'term_slug' == $param || '_edit_nonce' == $param ) :
		
			$post_id   = ( isset( $_GET['post_ID'] ) ) ? absint( $_GET['post_ID'] ) : 0;
			$id        = ( isset( $_GET['term_ID'] ) ) ? absint( $_GET['term_ID'] ) : 0;
			$term_name = ( isset( $_GET['term_name'] ) ) ? sanitize_text_field( $_GET['term_name'] ) : '';
			
			switch ( $param )
			{
				case 'term_name':
				case 'old_term' :
					$value     = $term_name;
					break;
					
				case 'term_slug':
					$value = htmlspecialchars( urldecode( sanitize_title_with_dashes( $term_name ) ) );
					break;
					
				case 'term_id':
					$value = $id;
					break;
					
				case 'term_postid':
					$value = $post_id;
					break;
					
				case '_edit_nonce':
					$name  = $param;
					$value = ( isset( $_GET['_wpnonce'] ) ) ? sanitize_key( $_GET['_wpnonce'] ) : '';
					break;
					
				default:
					break;
			}
			
		endif;
		
		if ( 'stt2extat_search_structure' == $param ) :
			$before = '<code>' . get_option('home') . '</code>';
			$name   = $param;
			$search_structure = get_option( 'stt2extat_search_structure' );
			$value  = esc_attr( $search_structure );
			$last   = $min;
		endif;
		
		if ( 'schedule' == $param ) :
			$count = ( isset( $value['count'] ) ) ? absint( $value['count'] ) : 1;
			$additional_field = sprintf ('<label for="schedule_count"><input type="%1$s" id="schedule_count" name="%2$s" class="%3$s" value="%4$s" min="1" /> <em>%5$s</em></label>',
				esc_attr( $type ),
				esc_attr( $name . '[count]' ),
				esc_attr( $class ),
				$count,
				__( 'hit(s)', 'stt2extat' )
			);
			
			$before      = __( 'Terms updated ', 'stt2extat' );
			$value       = ( isset( $value['post_modified'] ) ) ? absint( $value['post_modified'] ) : ( int ) 0;
			$after_input = __( 'days ago. Having count', 'stt2extat' );
			$name        = $name . '[post_modified]';
			$next        = wp_next_scheduled( 'stt2extat_delete_terms', $args = array() );
			
			if( ! empty( $next ) && 0 < absint( $this->set[ $param ]['post_modified'] ) ) :
				$time   = date_i18n( 'd F Y \a\t g:ia', $next );
				$after  = '<small>' . __( 'Next schedule', 'stt2extat' ) . ': <time>' . $time . '</time></small>'; 
			endif;
			
		endif;
		
		if ( 'number' == $type && ! empty( $min ) )
			$last .= 'min=' . intval( $min ) . ' ';
		
		if ( 'number' == $type && ! empty( $max ) )
			$last .= 'max=' . intval( $max ) . '';
		
		$output = sprintf( '<label for="%1$s">%2$s<input type="%3$s" id="%1$s" name="%4$s" class="%5$s" value="%6$s" %7$s %8$s /> <em>%9$s</em></label> %10$s<p class="description">%11$s</p><span>%12$s</span>',
			esc_attr( $param ),
			$before,
			esc_attr( $type ),
			esc_attr( $name ),
			esc_attr( $class ),
			esc_attr( $value ),
			esc_attr( $required ),
			esc_attr( $last ),
			esc_attr( $after_input ),
			$additional_field,
			$description,
			$after
		);
		return $output;
	}
	
	/**
	 * multiple input html format
	 *
	 * @since 1.1
	 *
	*/
	public function multiple_input( $param, $type, $name, $class )
	{
		$array = ( isset( $this->set[ $param ] ) ) ? $this->set[ $param ] : array();
		if ( ! is_array( $array ) )
			$array = array();
		
		$n         = count( $array );
		$num_field = ( 0 < $n ) ? absint( $n ) . ' ' . __( 'item', 'stt2extat' ) : '';
		
		$toggle    = '<span title="' . esc_attr__( 'Show All', 'stt2extat' ) . '" class="dashicons dashicons-menu"></span>';
		
		$html_top  = sprintf( '<div id="ua-list"><div class="tablenav"><span class="displaying-num">%1$s</span>%2$s</div>',
			$num_field,
			( 5 < $n ) ? $toggle : ''
		);
		
		$html  = ( '' == $num_field ) ? '' : $html_top;
		$html .= '<label for="ua-top"><input id="ua-top-k" type="text" class="regular-text" value="" placeholder="' . __( 'Referrer', 'stt2extat' ) . '" readonly="readonly"><input id="ua-top-v" type="text" class="medium-text" value="" placeholder="' . __( 'Query Param', 'stt2extat' ) . '" readonly="readonly"></label>';
		
		$i = 0;
		foreach ( $array as $k => $v ) :
			$i++;
			
			$html .= wp_sprintf( '<label for="%1$s-%2$s"><input type="%3$s" id="%1$s-%2$s" name="%4$s[%1$s][k][]" class="%1$s %5$s" value="%6$s"/><input type="%3$s" name="%4$s[%1$s][v][]" class="%1$s medium-text" value="%7$s"/></label>',
				esc_attr( $param ),
				absint( $i ),
				esc_attr( $type ),
				esc_attr( $name ),
				sanitize_html_class( $class ),
				sanitize_text_field( $k ),
				sanitize_text_field( $v )
			);
			
		endforeach;
		
		$html .= '<div id="ua-additional"></div><div id="ua-bottom"><span class="more" title="' . __( 'Add More', 'stt2extat' ) . '">&#46;&#46;&#46;</span></div></div>';
		
		return $html;
	}
	
	/**
	 * textarea input html format
	 *
	 * @since 1.1
	 *
	*/
	public function textarea_form( $param, $description = null )
	{
		$value = '';
		if ( ( isset( $this->set[ $param ] ) && '' != $this->set[ $param ] ) ) :
			$value = $this->set[ $param ];
			if ( ! is_array( $value ) )
				$value = array( $value );
			
			$value = implode( "\n", array_map( 'esc_textarea', $value ) );
		endif;
		
		$output = sprintf( '<label for="%1$s"><textarea id="%1$s" name="stt2extat_settings[%1$s]" class="%1$s-area large-text" aria-expanded="false"/>%2$s</textarea><p class="description">%3$s</p></label><div id="textarea-bottom"></div>',
			esc_attr( $param ),
			esc_textarea( $value ),
			esc_html( $description )
		);
		return $output;
	}
	
	/**
	 * selected input html format
	 *
	 * @since 1.1
	 *
	*/
	public function selected_form( $param, $val, $opt, $description )
	{
		$html = '';
		foreach ( $opt as $k => $v ) :
			$html .= sprintf( '<option value=%1$s %2$s>%3$s</option>',
				esc_attr( $k ), 
				selected( $val, esc_attr( $k ), false ),
				esc_html( $v )
			);
		endforeach;
		
		$input_field = sprintf( '<label for="%1$s"><select id="%1$s" name="stt2extat_settings[%1$s]">%2$s</select></label><p class="description">%3$s</p>',
			esc_attr( $param ),
			$html,
			$description
		);
		return $input_field;
	}
	
	/**
	 * group of metabox on screen option tab
	 *
	 * @since 1.1
	 *
	*/
	protected function group( $opt )
	{
		$group = array();
		
		foreach ( $this->data as $key => $value ) :
			switch ( $opt )
			{
				case 'screen_tab':
					$screen_tab_option = array( 'general', 'delete', 'manual', 'stats' );
					if ( in_array( $this->data[ $key ]['group'], $screen_tab_option ) )
						$group[ $key ] = $this->data[ $key ]['group'];
					break;
				
				default:
					$group[ $key ] = $this->data[ $key ]['group'];
					break;
			}
		endforeach;
		
		return array_unique( $group );
	}
	
	/**
	 * stt2extat_admin_enqueu_scripts
	 * enqueu stt2extat css and js scripts on plugin page
	 *
	 * @since 1.0
	 *
	 * sanitize variable
	 *
	 * @since 1.0.2
	 *
	*/

	public function admin_enqueue_scripts( $hook )
	{
		global $stt2extat_settings, $stt2extat_screen_id;
		
		if ( $stt2extat_screen_id != $hook )
			return;
		
		wp_enqueue_style( 'editor-styles', includes_url( '/css/editor.min.css' ) );
		wp_enqueue_style( 'jquery-ui-custome-style', plugin_dir_url( __FILE__ ) . 'includes/assets/css/jquery-ui.min.css',array( 'editor-styles' ), STT2EXTAT_VER, false );
		
		wp_register_script( 'jquery-stt2extat', plugin_dir_url( __FILE__ ) . 'includes/assets/js/jquery-stt2extat.js' , array( 'jquery' ), STT2EXTAT_VER, true );
		
		wp_register_script( 'jquery-googlesuggest-stt2extat', plugin_dir_url( __FILE__ ) . 'includes/assets/js/jquery-google-suggest-autocomplete.js', array('jquery'), STT2EXTAT_VER, true );
		
		wp_enqueue_script( 'jquery-stt2extat' );
		wp_enqueue_script( 'jquery-googlesuggest-stt2extat' );
		wp_enqueue_script( 'postbox' );
		wp_enqueue_script( 'jquery-ui-autocomplete' );
		wp_enqueue_script( 'wp-ajax-response' );
		wp_enqueue_script( 'tags-box' );
		
		wp_localize_script( 'jquery-stt2extat', 'stt2extatL10n', stt2extat_notice_localize() );
		
		$spinner = '"' . admin_url( '/images/spinner.gif') . ' "';
		$css     = 'input.ui-autocomplete-loading
{
	background: url(' . $spinner . ') center right no-repeat;
}
#searchtermpost
{
	padding:10px 0;
}

#wp-link .link-search-wrapper span.existlink
{
	-moz-osx-font-smoothing:grayscale;
	-webkit-font-smoothing:antialiased;
	background-image:none!important;
	float:right;
	font:400 20px/1 dashicons;
	margin:0;
	padding:0;
	position:relative;
	speak:none;
}

span.existlink:before
{
	content:"\f139";
	text-decoration:none;
}

#gsuggestPopup,#notmatchPopup,#thehint,#fullpost,#loading
{
	display:none;
}

#stt2extat-wo-terms
{
	display:inline-block;
}

#stt2extat-wo-terms span
{
	background-color: #d54e21;
    color: #fff;
    display: inline;
    padding: 1px 6px;
    font-size: 10px;
    font-weight: 700;
	vertical-align: top;
    -webkit-border-radius: 10px;
    border-radius: 10px
}

#fullpost .post-permalink
{
	word-break:break-all;
}
#fullpost p
{
	font-size:20px;
}
#stt2extat-form
{
	display:table;
	width:100%;
}

.more
{
	color:#ccc;
	display:inline-table;
	font-size:41px;
	line-height:21px;
	margin:10px 0 0;
	padding:0;
	vertical-align:top;
	width:50px;
}

i.termlist:hover,i.termcnt:hover,.more:hover,.alltag:hover,#ua-list .dashicons-menu:hover
{
	color:#0073aa;
	cursor:pointer;
}

a i.dashicons
{
	text-decoration:none;
}

.termadd,.closebtn
{
	float:left;
}

#ins-btn
{
	line-height:27px;
}

#loading
{
	background-color:#ffd700;
	border-radius:4px;
	box-shadow:1px 6px 6px 0 #ccc;
	font-weight:600;
	height:20px;
	left:50%;
	padding:5px;
	position:fixed;
	text-align:center;
	top:40px;
	width:300px;
	z-index:2;
}

.key-inline
{
	background:#f5f5f5;
	border:1px solid #0073aa;
	border-radius:2px;
	color:#0073aa;
	display:none;
	line-height:21px;
	margin-bottom:8px;
	padding:5px;
	position:absolute;
}

.btn-key
{
	float:right;
	margin-left:4px!important;
}

.key
{
	padding:0 2px;
	text-decoration:underline;
}

div.key-inline.key-inline-active
{
	opacity:1;
	transition:top .1s ease-out, left .1s ease-out, opacity .1s ease-in-out;
}

.key-inline.key-arrow-up:before,.key-inline.key-arrow-up:after,.key-inline.key-arrow-down:before,.key-inline.key-arrow-down:after
{
	border-color:transparent;
	border-style:solid;
	content:"";
	display:block;
	height:0;
	left:50%;
	position:absolute;
	width:0;
}

.key-inline.key-arrow-down:before,.key-inline.key-arrow-up:before
{
	border-width:9px;
	margin-left:-9px;
}

.key-inline.key-arrow-down:after,.key-inline.key-arrow-up:after
{
	border-width:8px;
	margin-left:-8px;
}

.key-inline.key-arrow-down:before
{
	border-top-color:#0073aa;
	bottom:-18px;
}

.key-inline.key-arrow-down:after
{
	border-top-color:#f5f5f5;
	bottom:-16px;
}

.key-inline.key-arrow-up:before
{
	border-bottom-color:#0073aa;
	top:-18px;
}

.key-inline.key-arrow-up:after
{
	border-bottom-color:#f5f5f5;
	top:-16px;
}

#stt2extat-form a:focus
{
	box-shadow:none;
}

#message.notice p
{
	word-break:break-all;
}

.table-stt2extat .column-link
{
	width:30%;
}

.table-stt2extat .column-count,.table-stt2extat .column-post_id
{
	width:10%;
}

.table-stt2extat .column-post_modified
{
	width:12%;
}

#ua-list label:nth-child(1n+8)
{
	display:none;
}

.collapse-textarea:before
{
	content:"\f142";
	font:400 20px/1 dashicons;
}';
		add_thickbox();					
		wp_add_inline_style( 'editor-styles', $css );
	}
	
	/**
	 * add script admin footer plugin page
	 *
	 * @since 1.1
	 *
	*/
	public function admin_footer()
	{
		$stt2extat_footer = apply_filters( 'stt2extat_footer', '<div></div>' );
		echo ( $stt2extat_footer );
		if ( wp_script_is( 'jquery', 'done' ) ) :
			echo "<script type='text/javascript'>\n";
			echo "jQuery( document ).ready( function( $ ) {\n";
			echo "$( this ).stt2extat();\n";
			echo "} );\n";
			echo "</script>\n";
		endif;
	}
	
	/**
	 * filter the text of admin footer plugin page
	 *
	 * @since 1.1
	 *
	*/
	public function admin_footer_text()
	{
		$html = '<span id="footer-thankyou">&copy; 2015 - %s %s %s</p>';
		printf ( $html,
			$this->plugin_data->Name,
			__( 'plugin by', 'stt2extat' ),
			$this->plugin_data->Author
		);
	}
	
	/**
	 * filter the text of update footer plugin page
	 *
	 * @since 1.1
	 *
	*/
	public function update_footer()
	{
		$txt = '%s %s';
		printf ( $txt,
			__( 'Version', 'stt2extat' ),
			$this->plugin_data->Version
		);
	}
}