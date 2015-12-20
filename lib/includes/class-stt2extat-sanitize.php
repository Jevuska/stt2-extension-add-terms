<?php
/*
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 * @version 1.1
 */

if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

function stt2extat__sanitize()
{
	global $stt2extat_sanitize;
	
	$sanitize = new STT2EXTAT_Sanitize;
	return $sanitize;
}

class STT2EXTAT_Sanitize
{
	public $data;
	
	public function __construct()
	{
		$this->data = $this->data();
	}
	
	/**
	 * sanitize data from textarea input
	 *
	 * @since 1.1
	 *
	*/
	public function data_textarea( $lists, $is_int = false )
	{
		$list = array();
		
		if ( '' == $lists )
			return $list;
		
		if ( $is_int && is_array( $lists ) && array_filter( $lists ) ) :
			$result = array();
			
			foreach ( $lists as $id ) :
				$id = absint( $id );
				
				if ( '' == $id )
					continue;
				
				$result[] = $id;
			endforeach;
			
			if ( array_filter( $result ) ) :
				$list = implode( ',', array_values( array_unique( $result ) ) );
			endif;
			
		else :
		
			if ( ! is_array( $lists ) ) :
				$lists = preg_replace( '/[\r\n\t,]+/', '|', $lists );
				$lists = explode( '|', _x( trim( $lists ), 'Comma-separated list of search stopwords in your language' ) );
			endif;
			
			$list = array_filter( array_map( 'stt2extat_remove_char', $lists ) );
			$list = array_unique( $list );
			natcasesort( $list );
			
		endif;
		
		return $list;
	}
	
	/**
	 * sanitize useragent list
	 *
	 * @since 1.1
	 *
	*/
	public function useragent( $array )
	{
		global $wp_filter;
		
		$localhost = false;
		$array = wp_unslash( $array );
		
		if ( ! is_array( $array ) )
			$array = array( $array );
		
		if ( isset( $array['localhost'] )
			 && 's' == sanitize_html_class( $array['localhost'] )
			 && isset( $wp_filter['stt2extat_allow_localhost'] )
			)
			$localhost = true;
			
		$array_unique = array_unique( array_map( 'trim', array_keys( $array ) ) );
		
		$new_array = array();
		foreach ( $array_unique as $k ) :
			$v = $array[ $k ];
			if ( isset( $array[ $k ] ) && '' != $v ) :
				$k = stt2extat_parse_url( sanitize_text_field( $k ) );
				$v = sanitize_html_class( $v );
				if ( '' != $k && '' != $v )
					$new_array[ $k['host'] ] = $v;
			endif;
		endforeach;
		
		if ( $localhost )
			$new_array = wp_parse_args( array( 'localhost' => 's' ), $new_array );
		
		$new_array = array_filter( array_map( 'trim', $new_array ) );
		uksort( $new_array, 'strcasecmp' );
		
		return $new_array;
	}
	
	/**
	 * sanitize text for plugin search excerpt
	 *
	 * @since 1.1
	 *
	*/
	public function remove_char( $q )
	{
		if ( '' != $q ) :
			$q = stt2extat_filter_text( $q );
			if ( ! isset( $q['error'] ) )
				return $q;
		endif;
		return 0;
	}
	
	public function sanitize_key( $key, $other_key  )
	{
		$key       = sanitize_key( $key );
		$other_key = sanitize_key( $other_key );
		if ( isset( $this->data()[ $key ]['optional'][ $other_key ] ) )
			return $other_key;
		return sanitize_key( $this->data()[ $key ]['normal'] );
	}
	
	/**
	 * sanitize input for Auto Delete Terms field
	 *
	 * @since 1.1
	 *
	*/
	public function schedule( $schedule )
	{
		$default = $this->data()['schedule']['optional'];
		
		if ( ! is_array( $schedule ) )
			return $default;
		
		$schedule['post_modified'] = ( isset( $schedule['post_modified'] ) ) ? absint( $schedule['post_modified'] ) : $default['post_modified'];
		
		$schedule['count'] = ( isset( $schedule['count'] ) && 0 < absint( $schedule['count'] ) ) ? absint( $schedule['count'] ) : $default['count'];
		
		return wp_parse_args( $schedule, $default );
	}
	
	/**
	 * sanitize input yes and no
	 *
	 * @since 1.1
	 *
	*/
	public function bool_from_yn( $yn )
	{
		$yn = strtolower( $yn );
		if ( in_array( $yn, array( 'y', 'n' ) ) )
			return $yn;
		return false;
	}
	
	/**
	 * Data for admin plugin interface abd settings
	 *
	 * @since 1.1
	 *
	*/
	public function data()
	{
		$data = array();
		
		$additional_data = apply_filters( 'stt2extat_data', $data );
		
		if ( ! is_array( $additional_data ) )
			$additional_data = $data;
		
		$data['active'] = array(
			'id'          => 1,
			'parameter'   => 'active',
			'normal'      => 'abstain',
			'optional'    => array(
				'abstain' => __( 'Disabled', 'stt2extat' ),
				'n'       => 'PHP',
				'y'       => 'Ajax'
			),
			'subtitle'    => __( 'Activate Get Referrer', 'stt2extat' ),
			'description' => __( 'Method to get terms from referrer.', 'stt2extat' ),
			'group'       => 'general',
			'subgroup'    => __( 'general settings', 'stt2extat'),
			'lang'        => 'boolean'
		);
		
		$data['auto'] = array(
			'id'          => 2,
			'parameter'   => 'auto',
			'normal'      => 'abstain',
			'optional'    => array(
				'abstain' => __( 'Disabled', 'stt2extat' ),
				'n'       => 'HTML',
				'y'       => 'Ajax'
			),
			'subtitle'    => __( 'Auto Add', 'stt2extat' ),
			'description' => __( 'Add list automatically right after post content', 'stt2extat' ),
			'group'       => 'general',
			'subgroup'    => __( 'general settings', 'stt2extat'),
			'lang'        => 'string'
		);
		
		$data['number'] = array(
			'id'          => 3,
			'parameter'   => 'number',
			'normal'      => ( int ) 5,
			'optional'    => ( int ) 10,
			'subtitle'    => __( 'Number List', 'stt2extat' ),
			'description' => __( 'Maximum number of terms on list', 'stt2extat' ),
			'group'       => 'general',
			'subgroup'    => __( 'general settings', 'stt2extat'),
			'lang'        => 'integer'
		);
		
		$data['text_header'] = array(
			'id'          => 4,
			'parameter'   => 'text_header',
			'normal'      => __( 'Incoming Terms', 'stt2extat' ),
			'optional'    => __( 'Terms', 'stt2extat' ),
			'subtitle'    => __( 'Text Header', 'stt2extat' ),
			'description' => __( 'Title of terms list', 'stt2extat' ),
			'group'       => 'general',
			'subgroup'    => __( 'general settings', 'stt2extat'),
			'lang'        => 'string'
		);
		
		$data['html_heading'] = array(
			'id'          => 5,
			'parameter'   => 'html_heading',
			'normal'      => 'h3',
			'optional'    => array(
				'h1'   => '&lt;h1&gt;',
				'h2'   => '&lt;h2&gt;',
				'h3'   => '&lt;h3&gt;',
				'h4'   => '&lt;h4&gt;',
				'h5'   => '&lt;h5&gt;',
				'h6'   => '&lt;h6&gt;',
				'span' => '&lt;span&gt;',
				'div'  => '&lt;div&gt;',
				'p'    => '&lt;p&gt;'
			),
			'subtitle'    => __( 'HTML Heading', 'stt2extat' ),
			'description' => __( 'HTML heading of text title', 'stt2extat' ),
			'group'       => 'general',
			'subgroup'    => __( 'general settings', 'stt2extat'),
			'lang'        => 'string'
		);
		
		$data['display'] = array(
			'id'          => 6,
			'parameter'   => 'display',
			'normal'      => 'ul',
			'optional'    => array(
				'ul' => 'Bullet',
				'ol' => 'Number',
				'span' => 'Inline'
			),
			'subtitle'    => __( 'List Style', 'stt2extat' ),
			'description' => __( 'List style of terms', 'stt2extat' ),
			'group'       => 'general',
			'subgroup'    => __( 'general settings', 'stt2extat'),
			'lang'        => 'string'
		);
		
		$data['stopwords'] = array(
			'id'          => 7,
			'parameter'   => 'stopwords',
			'normal'      => array( 'abortion', 'attack', 'bomb', 'casino', 'cocaine', 'death', 'die', 'erection', 'gamble', 'gambling', 'heroin', 'marijuana', 'masturbation', 'nude', 'pedophile', 'penis', 'poker', 'porn', 'pussy', 'sex', 'squirt', 'terrorist', 'xxx' ),
			'optional'    => array(),
			'subtitle'    => __( 'Filter Words', 'stt2extat' ),
			'description' => __( 'Filter word or phrase, add per-line or by comma', 'stt2extat' ),
			'group'       => 'general',
			'subgroup'    => __( 'general settings', 'stt2extat'),
			'lang'        => 'array'
		);
		
		$data['convert'] = array(
			'id'          => 8,
			'parameter'   => 'convert',
			'normal'      => 'n',
			'optional'    => array(
				'n'      => __( 'Disabled', 'stt2extat' ),
				'post'   => 'Link to post content',
				'search' => 'Link to search page'
			),
			'subtitle'    => __( 'Convert Text Link', 'stt2extat' ),
			'description' => sprintf( wp_kses( __( 'Convert terms into links. Set custom structure for search link on <a href="%1$s">Permalink Settings</a> page.', 'stt2extat' ), array( 'a' => array( 'href' => array() ) ) ), admin_url( 'options-permalink.php#stt2extat_permalink' ) ),
			'group'       => 'general',
			'subgroup'    => __( 'general settings', 'stt2extat'),
			'lang'        => 'string'
		);
		
		$data['count'] = array(
			'id'          => 9,
			'parameter'   => 'count',
			'normal'      => 'n',
			'optional'    => array( 
				'n'        => __( 'Disabled', 'stt2extat' ),
				'tooltips' => 'Tooltips',
				'sup'      => 'Sup'
			),
			'subtitle'    => __( 'Hits Count', 'stt2extat' ),
			'description' => __( 'Type of terms counts display.', 'stt2extat' ),
			'group'       => 'general',
			'subgroup'    => __( 'general settings', 'stt2extat'),
			'lang'        => 'string'
		);
		
		$data['schedule'] = array(
			'id'          => 10,
			'parameter'   => 'schedule',
			'normal'      => array(
				'post_modified' => ( int ) 0,
				'count'         => ( int ) 1
			),
			'optional'    => array(
				'post_modified' => ( int ) 0,
				'count'         => ( int ) 1
			),
			'subtitle'    => __( 'Auto Delete Terms', 'stt2extat' ),
			'description' => __( 'Delete unused terms before these days, 0 = disabled. Scheduled once weekly.', 'stt2extat' ),
			'group'       => 'general',
			'subgroup'    => __( 'general settings', 'stt2extat'),
			'lang'        => 'array'
		);
		
		$data['useragent'] = array(
			'id'          => 11,
			'parameter'   => 'useragent',
			'normal'      => array(
				'alhea.com'             => 'q',
				'ask.com'               => 'q',
				'baidu.com'             => 'wd',
				'bing.com'              => 'q',
				'blogsearch.google.com' => 'q',
				'dogpile.com'           => 'q',
				'duckduckgo.com'        => 'q',
				'google.com'            => 'q',
				'hotbot.com'            => 'q',
				'images.google.com'     => 'q',
				'info.com'              => 'qkw',
				'local.google.com'      => 'q',
				'looksmart.com'         => 'q',
				'maps.google.com'       => 'q',
				'msxml.excite.com'      => 'q',
				'news.google.com'       => 'q',
				'search.aol.com'        => 'q',
				'search.earthlink.net'  => 'q',
				'search.lycos.com'      => 'q',
				'search.msn.com'        => 'q',
				'search.yahoo.com'      => 'p',
				'search.infospace.com'  => 'q',
				'sogou.com'             => 'query',
				'uk.ask.com'            => 'q',
				'qwant.com'             => 'q',
				'video.google.com'      => 'q',
				'wow.com'               => 'q',
				'yandex.kz'             => 'text',
				'yandex.ru'             => 'text',
				'zapmeta.com'           => 'q'
			),
			'optional'    => array(),
			'subtitle'    => __( 'User Agents/Referrer', 'stt2extat' ),
			'description' => __( 'Manage referrer from these user agents', 'stt2extat' ),
			'group'       => 'general',
			'subgroup'    => __( 'general settings', 'stt2extat'),
			'lang'        => 'array'
		);
		
		$data['max_char'] = array(
			'id'          => 12,
			'parameter'   => 'max_char',
			'normal'      => ( int ) 55,
			'optional'    => ( int ) 70,
			'subtitle'    => __( 'Max character length', 'stt2extat' ),
			'description' => __( 'Max character length of terms.', 'stt2extat' ),
			'group'       => 'general',
			'subgroup'    => __( 'general settings', 'stt2extat'),
			'lang'        => 'integer'
		);
		
		$data['searchexcerpt'] = array(
			'id'          => 13,
			'parameter'   => 'searchexcerpt',
			'normal'      => 'n',
			'optional'    => array( 'y' => __( 'Enabled', 'stt2extat' ), 'n' => __( 'Disabled', 'stt2extat' ) ),
			'subtitle'    => __( 'Excerpt', 'stt2extat' ),
			'description' => __( 'Create snippet with highlighted terms of each post on search page via plugin Search Excerpt', 'stt2extat' ),
			'group'       => 'general',
			'subgroup'    => __( 'general settings', 'stt2extat'),
			'lang'        => 'string'
		);
		$data['manual'] = array(
			'id'          => 14,
			'parameter'   => 'manual',
			'normal'      => '',
			'optional'    => '',
			'subtitle'    => '',
			'description' => '',
			'group'       => 'manual',
			'subgroup'    => __( 'manual input', 'stt2extat'),
			'lang'        => 'string'
		);
		
		$data['stats'] = array(
			'id'          => 15,
			'parameter'   => 'stats',
			'normal'      => '',
			'optional'    => '',
			'subtitle'    => '',
			'description' => '',
			'group'       => 'stats',
			'subgroup'    => __( 'term stats', 'stt2extat'),
			'lang'        => 'string'
		);
		
		$data['delete'] = array(
			'id'          => 16,
			'parameter'   => 'delete',
			'normal'      => '',
			'optional'    => '',
			'subtitle'    => '',
			'description' => '',
			'group'       => 'delete',
			'subgroup'    => __( 'migrate &amp; delete terms', 'stt2extat'),
			'lang'        => 'string'
		);
		
		$data['term_name'] = array(
			'id'          => 17,
			'parameter'   => 'term_name',
			'normal'      => '',
			'optional'    => '',
			'subtitle'    => __( 'Name', 'stt2extat' ),
			'description' => __( 'Name', 'stt2extat' ),
			'group'       => 'update_term',
			'subgroup'    => __( 'update terms', 'stt2extat'),
			'lang'        => 'string'
		);
		
		$data['term_slug'] = array(
			'id'          => 18,
			'parameter'   => 'term_slug',
			'normal'      => '',
			'optional'    => '',
			'subtitle'    => __( 'Slug', 'stt2extat' ),
			'description' => __( 'Slug', 'stt2extat' ),
			'group'       => 'update_term',
			'subgroup'    => __( 'update terms', 'stt2extat'),
			'lang'        => 'string'
		);
		
		$data['support'] = array(
			'id'          => 19,
			'parameter'   => 'sidebar',
			'normal'      => '',
			'optional'    => '',
			'subtitle'    => '',
			'description' => wp_kses( __( '<p><i class="%1$s">Remember, if you ever need a helping hand, you will find one at the end of each of your arms. As you grow older, you will discover that you have two hands, one for helping yourself and the other for helping others.</i></p><p class="%2$s"> &#126; Sam Levenson</p><p class="%2$s">Here my support contact &amp; PayPal email <i class="%3$s"></i> <a target="_blank" href="%4$s" title="%5$s">%6$s</a></p>', 'stt2extat' ), array(
				'p' => array(
					'class' => array()
				),
				'i' => array(
					'class' => array()
				),
				'a' => array(
					'target' => array(),
					'href'   => array(),
					'title'  => array()
				) 
			) ),
			'group'       => 'support',
			'subgroup'    => __( 'Help Support, Credit &amp; Donate', 'stt2extat' ),
			'lang'        => 'string'
		);
		
		$data['features'] = array(
			'id'          => 20,
			'parameter'   => 'sidebar',
			'normal'      => '',
			'optional'    => '',
			'subtitle'    => '',
			'description' => __( 'STT2EXTAT features.', 'stt2extat' ),
			'group'       => 'features',
			'subgroup'    => __( 'Features', 'stt2extat' ),
			'lang'        => 'string'
		);
		
		$data['save'] = array(
			'id'          => 21,
			'parameter'   => 'sidebar',
			'normal'      => '',
			'optional'    => '',
			'subtitle'    => '',
			'description' => '',
			'group'       => 'save',
			'subgroup'    => __( 'save your settings', 'stt2extat' ),
			'lang'        => 'string'
		);
		
		$data['help'] = array(
			'id'          => 22,
			'parameter'   => 'help',
			'normal'      => array(
				'overview' => array(
					'title'   => __( 'Overview', 'stt2extat' ),
					'content' => __( 'Manage your terms better, add terms into single post manually, get terms via referrer, and save them as post meta. Search the terms that relevant of post content as well as WordPress search default algorithm. Abuse with any extension that you made is welcome to support this plugin. Have fun!.', 'stt2extat' )
				),
				'troubleshooting' => array(
					'title'   => __( 'Troubleshooting', 'stt2extat' ),
					'content' => __( 'Try to deactivated or uninstall, then install this plugin again if you get an error php code. Using caching to make your pages become static and make your website load faster, it also lessens the load on your server&#39;s CPU, Memory and HD.', 'stt2extat' )
				),
				'instruction' => array(
					'title'   => __( 'Instructions', 'stt2extat' ),
					'content' => sprintf( '<div class="hint-box"><p><strong>%s</strong></p><ol class="hint"><li>%s</li><li>%s</li><li>%s</li><li>%s</li><li>%s <kbd>"<strong style="font-size:16px">,</strong>"</kbd> %s</li><li>%s</li><li>%s</li></ol></div>',
						__( 'Hint:', 'stt2extat' ),
						__( 'Search the post by your terms, and select the one (required).', 'stt2extat' ),
						__( 'Populate your terms for the first time then you can type in text box area directly to insert them into database.', 'stt2extat' ),
						__( 'Three methods to populate your terms - by type, by suggestion and by double click text or text selection in "Full Post" section.', 'stt2extat' ),
						__( 'Always use "comma" to separate your terms.', 'stt2extat' ),
						__( 'Comma sign ','stt2extat' ),
						__( 'in populate terms box is a trigger to searching relevant post.', 'stt2extat' ),
						__( 'Increase or decrease hits number of search term by click the term or number text.', 'stt2extat' ),
						__( 'Usage populate terms by select texts in full post area; SELECT the text(s) to add into populate box; CLICK each of selected text to remove it from your terms. The selected text(s) will be separated by comma automatically in populate box.', 'stt2extat' )
					)
				)
			),
			'optional'    => array(),
			'subtitle'    => __( 'Help Tab', 'stt2extat' ),
			'description' => sprintf( '<p><strong>%1$s</strong></p><p><a href="%2$s" target="_blank">%3$s</a></p><p><a href="%4$s" target="_blank">%5$s</a></p>',
				__( 'For more information:', 'stt2extat' ),
				esc_url( stt2extat_get_plugin_data( 'PluginURI' ) ),
				__( 'Plugin Page', 'stt2extat' ),
				'https://github.com/Jevuska/stt2-extension-add-terms',
				'GitHub'
			),
			'group'       => 'tab',
			'subgroup'    => __( 'help tab settings', 'stt2extat'),
			'lang'        => 'array'
		);
		
		$data = $data + $additional_data;
		
		return $data;
	}
	
	/**
	 * Sanitize all input from callback function
	 *
	 * @since 1.1
	 *
	*/
	public function sanitize( $c = '' )
	{
		$this->default_setting();
		$c = ( '' == $c ) ? $this->data : $c;
		$c = ( ! is_array( $c ) ) ? array() : $c;
		$b = wp_parse_args( $c, $this->data );
		
		$array = array(
			'active'        => $this->bool_from_yn( $b['active'] ),
			'auto'          => $this->bool_from_yn( $b['auto'] ),
			'number'        => absint( $b['number'] ),
			'text_header'   => sanitize_text_field( $b['text_header'] ),
			'html_heading'  => $this->sanitize_key( 'html_heading', $b['html_heading'] ),
			'display'       => $this->sanitize_key( 'display', $b['display'] ),
			'stopwords'     => $this->data_textarea( $b['stopwords'] ),
			'convert'       => $this->sanitize_key( 'convert', $b['convert'] ),
			'count'         => $this->sanitize_key( 'count', $b['count'] ),
			'schedule'      => $this->schedule( $b['schedule'] ),
			'useragent'     => $this->useragent( $b['useragent'] ),
			'max_char'      => absint( $b['max_char'] ),
			'searchexcerpt' => $this->bool_from_yn( $b['searchexcerpt'] )
		);
		
		$additional = array();
		$array = apply_filters( 'stt2extat_sanitize', $array, $additional );
		
		$keys  = array_keys( $c );
		$array = wp_array_slice_assoc( $array, $keys );
		
		if ( array_filter( $array ) )
			return $array;
		return false;
	}
	
	/**
	 * Default Settings for data reset
	 *
	 * @since 1.1
	 *
	*/
	public function default_setting()
	{
		$data = array_splice( $this->data, 0, 14 );
		
		$data = array_column( $data, 'normal', 'parameter' );
		
		$this->data = $data;
		
		return apply_filters( 'stt2extat_default_setting', $this->data );
	}
}