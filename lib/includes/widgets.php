<?php
/*
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 * @since 1.1
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

class STT2EXTAT_Widget_Terms_List extends WP_Widget
{

	public function __construct()
	{
		$widget_ops = array(
			'classname'   => 'stt2extat_terms_list',
			'description' => __( 'Add terms list to your sidebar.', 'stt2extat' )
		);
		
		parent::__construct(
			false,
			__( 'STT2EXTAT Terms List', 'stt2extat' ),
			$widget_ops
		);
		
		add_action( 'save_post', array( $this, 'flush_widget_cache' ) );
		add_action( 'deleted_post', array( $this, 'flush_widget_cache' ) );
		add_action( 'switch_theme', array( $this, 'flush_widget_cache' ) );
	}

	public function widget( $args, $instance )
	{
		$cache = array();
		if ( ! $this->is_preview() )
			$cache = wp_cache_get( 'stt2extat_widget_terms_list', 'widget' );

		if ( ! is_array( $cache ) )
			$cache = array();
		
		if ( ! isset( $args['widget_id'] ) )
			$args['widget_id'] = $this->id;
		
		if ( isset( $cache[ $args['widget_id'] ] ) ) :
			echo $cache[ $args['widget_id'] ];
			return;
		endif;

		ob_start();
		
		$title   = __( 'Popular Terms', 'stt2extat' );
		$post_id = null;
		$sort    = ( isset( $instance['sort'] ) ) ? $instance['sort'] : 'count';
		
		$obj        = get_queried_object();
		$tax        = $cloud = false;
		$tax_query  = $args_cloud = array();
		
		switch ( $sort )
		{
			case 'taxonomy' :
			
				if ( !  isset( $obj->term_id ) )
					return null;
				
				$title    .= ' in ' . $obj->name;
				$tax_query = array( 'tax_query' => array(
					'tax_query' => array(
						array(
							'taxonomy' => $obj->taxonomy,
							'terms'    => $obj->term_id
						)
					)
				) );
				$sort = 'count';
				$tax  = true;
				break;
			
			case 'post_id' :
				
				if ( ! is_single() )
					return null;
				
				$title    .= __( ' Post', 'stt2extat');
				$post_id   = $obj->ID;
				$sort      = 'count';
				break;
			
			case 'term_id' :
				$title = __( 'Recent Terms', 'stt2extat' );
				break;
			
			case 'recent' :
				
				if ( ! is_single() )
					return null;
				
				$title   = __( 'Recent Terms Post', 'stt2extat' );
				$post_id = $obj->ID;
				$sort    = 'term_id';
				break;
				
			case 'term_cloud' :
				$title      = __( 'Term Cloud', 'stt2extat' );
				$args_cloud = apply_filters( 'stt2extat_tag_cloud_args', array() );
				$sort  = 'count';
				$cloud = true;
				break;
			
			default :
				$title = $title;
				break;
		}
		
		$title    = apply_filters( 'widget_title', empty( $instance['title'] ) ? $title : $instance['title'], $instance, $this->id_base );
		$interval = ( isset( $instance['interval'] ) ) ? $instance['interval'] : 'all';
		$number   = ( isset( $instance['number'] ) ) ? $instance['number'] : 5;
		$count    = ( isset( $instance['count'] ) ) ? $instance['count'] : 'tooltips';
		$convert  = ( isset( $instance['convert'] ) ) ? $instance['convert'] : 'n';
		
		$args_query = array(
			'sort'   => $sort,
			'number' => $number,
			'order'  => 'DESC',
			'p'      => $post_id
		);
		
		$args_interval = apply_filters( 'stt2extat_widget_interval_time',
			array(
				'date_query' => array(
					'after' => $interval
				)
			)
		);
		
		if ( 'all' != $interval )
			$args_query = wp_parse_args( $args_interval, $args_query );
		
		if ( 'count' == $sort && $tax )
			$args_query = wp_parse_args( $tax_query, $args_query );
		
		$set = array(
			'text_header'  => '',
			'html_heading' => '',
			'display'      => 'ul',
			'count'        => $count,
			'convert'      => $convert
		);
		
		$args_query = wp_parse_args( $args_query, $set);
		
		$result = stt2extat_terms_list(
			$args_query,
			$widget = ( object ) array(
				'is_widget' => true,
				'cloud'     => wp_validate_boolean( $cloud )
			)
		);
		
		if ( 'count' == $sort && $cloud ) :
			unset( $args_cloud['number'] ); // use args_query['number']
			$args_cloud['filter']           = false;
			$args_cloud['topic_count_text'] = _n_noop( '%s hit', '%s hits' );
			
			if ( has_filter( 'stt2extat_term_count', 'stt2extat_count_posts' ) )
				$args_cloud['topic_count_text'] = _n_noop( '%s topic', '%s topics' );
			
			if ( 'n' == $args_query['count'] )
				$args_cloud['topic_count_text'] = '';
			$result = wp_generate_tag_cloud( $result, $args_cloud );
		endif;
		
		if ( ! empty( $result ) ) :
			echo $args['before_widget'];
			if ( $title )
				echo $args['before_title'] . $title . $args['after_title'];
			echo $result;
			echo $args['after_widget'];
		endif;
		
		if ( ! $this->is_preview() ) :
			$cache[ $args['widget_id'] ] = ob_get_flush();
			wp_cache_set( 'stt2extat_widget_terms_list', $cache, 'widget', 3600 );
		else :
			ob_end_flush();
		endif;
	}

	public function update( $new_instance, $old_instance )
	{
		$sort     = array( 'count', 'taxonomy', 'post_id', 'term_id', 'recent', 'term_cloud' );
		$interval = array( '-1 week', '-1 month', 'all' );
		$count    = array( 'n', 'tooltips', 'sup' );
		$convert  = array( 'n', 'post', 'search' );
		
		$instance = $old_instance;
		
		$instance['title']    = sanitize_text_field( $new_instance['title'] );
		$instance['sort']     = ( in_array( $new_instance['sort'], $sort ) ) ? $new_instance['sort'] : 'count';
		$instance['interval'] = ( in_array( $new_instance['interval'], $interval ) ) ? $new_instance['interval'] : 'all';
		$instance['number']   = absint( $new_instance['number'] );
		$instance['count']    = ( in_array( $new_instance['count'], $count ) ) ? $new_instance['count'] : 'tooltips';
		$instance['convert']  = ( in_array( $new_instance['convert'], $convert ) ) ? $new_instance['convert'] : 'n';
		
		$this->flush_widget_cache();
		$alloptions = wp_cache_get( 'alloptions', 'options' );
		
		if ( isset( $alloptions['widget_stt2extat_terms_list'] ) )
			delete_option( 'widget_stt2extat_terms_list' );
		
		return array_map( 'trim', $instance );
	}
	
	public function flush_widget_cache()
	{
		wp_cache_delete( 'stt2extat_widget_terms_list', 'widget' );
	}
	
	public function form( $instance )
	{
		$default = array(
			'title'    => '',
			'sort'     => 'count',
			'interval' => '-1 week',
			'number'   => 5,
			'count'    => 'tooltips',
			'convert'  => 'n'
		);
		
		$instance = wp_parse_args( $instance, $default );
		
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'sort' ); ?>"><?php _e( 'Type:' ); ?></label>
			<select name="<?php echo $this->get_field_name( 'sort' ); ?>" id="<?php echo $this->get_field_id( 'sort' ); ?>" class="widefat">
				<option value="count"<?php selected( $instance['sort'], 'count' ); ?>><?php _e( 'Popular Terms All', 'stt2extat' ); ?></option>
				<option value="taxonomy"<?php selected( $instance['sort'], 'taxonomy' ); ?>><?php _e( 'Popular in Taxonomy', 'stt2extat' ); ?></option>
				<option value="post_id"<?php selected( $instance['sort'], 'post_id' ); ?>><?php _e( 'Popular in Post', 'stt2extat' ); ?></option>
				<option value="term_id"<?php selected( $instance['sort'], 'term_id' ); ?>><?php _e( 'Recent Terms All', 'stt2extat' ); ?></option>
				<option value="recent"<?php selected( $instance['sort'], 'recent' ); ?>><?php _e( 'Recent Terms Post', 'stt2extat' ); ?></option>
				<option value="term_cloud"<?php selected( $instance['sort'], 'term_cloud' ); ?>><?php _e( 'Terms Cloud', 'stt2extat' ); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'interval' ); ?>"><?php _e( 'Interval:', 'stt2extat' ); ?></label>
			<select name="<?php echo $this->get_field_name( 'interval' ); ?>" id="<?php echo $this->get_field_id( 'interval' ); ?>" class="widefat">
				<option value="-1 week"<?php selected( $instance['interval'], '-1 week' ); ?>><?php _e( 'Last 7 Days', 'stt2extat' ); ?></option>
				<option value="-1 month"<?php selected( $instance['interval'], '-1 month' ); ?>><?php _e( 'Last 30 Days', 'stt2extat' ); ?></option>
				<option value="all"<?php selected( $instance['interval'], 'all' ); ?>><?php _e( 'All Time', 'stt2extat' ); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of terms to show:', 'stt2extat' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="number" value="<?php echo $instance['number']; ?>" min="1" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php _e( 'Display Count Type:', 'stt2extat' ); ?></label>
			<select name="<?php echo $this->get_field_name( 'count' ); ?>" id="<?php echo $this->get_field_id( 'count' ); ?>" class="widefat">
				<option value="n"<?php selected( $instance['count'], 'n' ); ?>><?php _e( 'Disable', 'stt2extat' ); ?></option>
				<option value="tooltips"<?php selected( $instance['count'], 'tooltips' ); ?>><?php _e( 'Tooltips', 'stt2extat' ); ?></option>
				<option value="sup"<?php selected( $instance['count'], 'sup' ); ?>><?php _e( 'Sup', 'stt2extat' ); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'convert' ); ?>"><?php _e( 'Convert terms:', 'stt2extat' ); ?></label>
			<select name="<?php echo $this->get_field_name( 'convert' ); ?>" id="<?php echo $this->get_field_id( 'convert' ); ?>" class="widefat">
				<option value="n"<?php selected( $instance['convert'], 'n' ); ?>><?php _e( 'Disable', 'stt2extat' ); ?></option>
				<option value="post"<?php selected( $instance['convert'], 'post' ); ?>><?php _e( 'Link to post', 'stt2extat' ); ?></option>
				<option value="search"<?php selected( $instance['convert'], 'search' ); ?>><?php _e( 'Link to search page', 'stt2extat' ); ?></option>
			</select>
		</p>
<?php
	}
}

function stt2extat_widgets_init()
{
	register_widget('STT2EXTAT_Widget_Terms_List');
}