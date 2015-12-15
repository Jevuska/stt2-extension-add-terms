<?php
/*
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 * @version 1.1
 */

if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

/**
 * get popular term of each post
 *
 * @since 1.1
 *
 */
function stt2extat_popular_each_post_id( $args = array() )
{
	$data_query = stt2extat_data_query( $args );
	$list = array_unique( array_column( $data_query, 'post_id' ) );
	
	$data  = array();
	$count = count( $list );
	for ( $i = 0; $i < $count ; $i++ ) :
		$post_meta = stt2extat_get_post_terms(  $list[ $i ] );
		$maxvalue = 0;
		while( list( $key, $value ) = each( $post_meta ) ) :
			if( $value[1] > $maxvalue ) :
				$maxvalue   = $value[1];
				$maxindex   = $key;
			endif;
		endwhile;
		$data[ $maxvalue ] = $data_query[ $maxindex ];
	endfor;
	krsort($data);
	return $data;
}

/**
 * get data terms with query
 *
 * @since 1.1
 *
 */
function stt2extat_data_query( $args = array() )
{
	global $stt2extat_data;
	
	if ( ! $stt2extat_data->terms )
		return array();
	
	$args = wp_parse_args(
		$args,
		array(
			'terms'       => $stt2extat_data->terms,
			'total_terms' => $stt2extat_data->total_terms
		)
	);
	
	return $stt2extat_data->query( $args );
}

/**
 * get post term by post id
 *
 * @since 1.1
 *
 */
function stt2extat_get_post_terms( $post_id )
{
	global $stt2extat_data;
	return $stt2extat_data->post_meta( $post_id );
}

/**
 * create global $stt2extat_data
 *
 * @since 1.1
 *
 */
function stt2extat_data()
{
	global $stt2extat_data;
	
	wp_cache_delete( 'stt2extat-terms', 'stt2extat-query' );
	if ( ! $data = wp_cache_get( 'stt2extat-terms', 'stt2extat-query' ) ) :
		$query   = new STT2EXTAT_Query;
		wp_cache_add( 'stt2extat-terms', $query, 'stt2extat-query', 300 );
	endif;
	
	return $query;
}

class STT2EXTAT_Query
{
	public $query_vars = array(
		'meta_key'       => '_stt2extat',
		'order'          => 'DESC',
		'fields'         => 'ids',
		'posts_per_page' => -1,
		'cache_results'  => false,
		'post_status'    => 'publish',
		'post_type'      => 'any'
	);
	
	public $query       = array();
	public $total_terms = 0;
	public $last_id     = 0;
	
	public function __construct( $query = array() )
	{
		$this->query = $query;
		if ( isset( $query['tax_query'] ) )
			$this->query_vars = wp_parse_args(
				$this->query_vars, $query['tax_query']
			);
		
		if ( isset( $query['p'] ) )
			$this->query_vars = wp_parse_args(
				$this->query_vars, array( 'p' => $query['p'] )
			);
			
		$this->data();
	}
	
	public function data()
	{
		$this->terms       = $this->terms();
		$this->total_terms = count( $this->terms );
	}
	
	public function post_ids()
	{
		return get_posts( $this->query_vars );
	}
	
	public function terms()
	{
		$post_ids = $this->post_ids();
		$count    = count( $post_ids );
		$data = array();
		$max  = 0;
		for ( $i = 0 ; $i < $count ; $i++ ) :
		
			$post_meta = $this->post_meta( $post_ids[ $i ] );
			
			if ( ! $post_meta || empty( $post_meta )  ) :
				delete_post_meta( $post_ids[ $i ], $this->query_vars['meta_key'] );
				continue;
			endif;
			
			foreach( $post_meta as $k => $v ) :
				$obj = new stdClass();
				$obj->name          = $k;
				$obj->slug          = str_replace( ' ', '-', $k );
				$obj->term_id       = $v[0];
				$obj->post_id       = $post_ids[ $i ];
				$obj->count         = $v[1];
				$obj->post_modified = $v[2];
				
				if( $obj->term_id > $max )
					$max = $obj->term_id;
				
				$data[ $k ] = $obj;
			endforeach;
			
		endfor;
		
		$this->last_id = $max;
		$this->terms   = $this->proccess_query( $data, $this->query );
		return $this->terms;
	}
	
	public function post_meta( $post_id )
	{
		return get_post_meta( $post_id, $this->query_vars['meta_key'], true );
	}
	
	public function max_postmeta( $post_id )
	{
		$maxvalue = 0;
		while( list( $key, $value ) = each( $this->post_meta( $post_id ) ) ) :
			if( $value[1] > $maxvalue ) :
				$maxvalue   = $value[1];
				$indexvalue = $value;
				$maxindex   = $key;
			endif;
		endwhile;
		
		return array( $maxindex => $indexvalue );
	}
	
	public function query( $args = array() )
	{
		if ( ! isset( $args['terms'] ) || ! $args['terms'] )
			return array();
		
		$this->date_query( $args );
		
		$data = array();
		foreach ( $args['terms'] as $key => $value ) :
			
			if ( isset( $args['date_query'] ) ) :
				$date_query = $args['date_query'];
				if ( isset( $date_query['after'] ) )
					if ( $value->post_modified < $this->date_strtotime )
						continue;
				
				if ( isset( $date_query['before'] ) )
					if ( $value->post_modified > $this->date_strtotime )
						continue;
			endif;
			
			if ( isset( $args['schedule']['count'] ) ) :
				$count = $args['schedule']['count'];
				if( $value->count !=  $count )
					continue;
			endif;
			
			$term_count   = apply_filters( 'stt2extat_term_count', $value->name, $value->count );
			
			if( has_filter('stt2extat_term_count'))
				$value->count = $term_count->number;
			
			$data[ $key ] = $value;
		endforeach;
		
		return $this->proccess_query( $data, $args );
	}
	
	public function proccess_query( $data, $args )
	{
		$this->terms = $data;
		$this->query = $args;
		
		if ( ! $this->terms )
			return array();
		
		if( ! $this->query )
			return $this->terms;
		
		if ( isset( $this->query['sort'] ) ) :
			$this->terms = call_user_func_array( array( $this, 'sort_by' ), array( $this->terms, $this->query['sort'] ) );
		endif;
		
		if ( isset( $this->query['order'] ) &&  'DESC' == strtoupper( $this->query['order'] ) )
			$this->terms = array_reverse( $this->terms, true );
		
		if ( isset( $this->query['fields'] ) )
			$this->terms  = wp_list_pluck( $this->terms, $this->query['fields'] );	
		
		if ( isset( $args['number'] ) && 0 < $this->query['number'] )
			$this->terms  = array_slice( $this->terms, 0, intval( $this->query['number'] ), true );
		
		return $this->terms;
	}
	
	public function date_query( $args = array() )
	{
		if ( ! isset( $args['date_query'] ) )
			return false;
		
		$queries    = new WP_Date_Query( array() );
		$date_query = $queries->sanitize_query( $args['date_query'] );
		
		if ( ! $queries->validate_date_values( $date_query ) )
			return false;
		
		//current time
		$this->current_time = ( int ) current_time( 'timestamp' );
		
		if ( isset( $date_query['after'] ) )
			$target = $date_query['after'];
		
		if ( isset( $date_query['before'] ) )
			$target = $date_query['before'];
		
		//format time to strtotime
		$this->date_sql       = $queries->build_mysql_datetime( $target, $default_to_max = false );
		
		//after time target
		$this->date_strtotime = strtotime( $this->date_sql, $this->current_time );
	}
	
	protected static function sort_by( $a, $b )
	{
		if ( ! $a )
			return array();
		
		switch ( $b )
		{
			case 'name' :
				uasort( $a, '_usort_terms_by_name' );
				break;
			
			case 'count' :
				uasort( $a, '_usort_terms_by_count' );
				break;
				
			case 'post_modified' :
				uasort( $a, '_usort_terms_by_post_modified' );
				break;
				
			default :
				uasort( $a, '_usort_terms_by_ID' );
				break;
		}
		
		return $a;
	}
}