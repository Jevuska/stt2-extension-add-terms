<?php
/*
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 * @version 1.1
 */

if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

if ( ! class_exists( 'WP_List_Table' ) )
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );


class STT2EXTAT_Table extends WP_List_Table
{
	public $page;
	
	public function __construct( $args )
	{
		parent::__construct( $args );
		$this->page();
	}
	
	public function prepare_items()
    {
		$columns     = $this->get_columns();
        $hidden      = $this->get_hidden_columns();
        $sortable    = $this->get_sortable_columns();
		$table_class = $this->get_table_classes();
		$data        = $this->table_data();
		
		function usort_reorder( $a, $b )
		{
            $orderby = ( isset( $_REQUEST['orderby'] ) && '' != $_REQUEST['orderby'] ) ? sanitize_key( $_REQUEST['orderby'] ) : 'term_id';
			$order   = ( isset( $_REQUEST['order'] )  && '' != $_REQUEST['order'] ) ? sanitize_key( $_REQUEST['order'] ) : 'asc';
			$result  = strcmp( $a->$orderby, $b->$orderby );
			
			if ( 'term_id' === $orderby || 'count' === $orderby )
				$result = $b->$orderby - $a->$orderby;
			
			return ( 'asc' === $order ) ? $result : -$result;
        }
		uasort( $data, 'usort_reorder' );
		
		$per_page     = 5;
		$current_page = $this->get_pagenum();
		$total_items  = count( $data );
		
		$this->set_pagination_args( array(
            'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $total_items/$per_page ),
			'orderby'     => ( isset( $_REQUEST['orderby'] ) ) ? sanitize_key( $_REQUEST['orderby'] ) : 'term_id',
			'order'       => ( isset( $_REQUEST['order'] ) ) ? sanitize_key( $_REQUEST['order'] ) : 'asc'
			
		) );
		
		$data = array_slice( $data, ( ( $current_page-1 ) * $per_page ), $per_page );
		
		$this->_column_headers = array(
			$columns,
			$hidden,
			$sortable,
			$table_class
		);
		$this->items = $data;
	}
	
	private function table_data()
	{
		global $stt2extat_data;
		
		$data  = array_values( $stt2extat_data->terms );
		return $data;
	}
	
	public function column_name( $item )
	{
		$out  = '<strong><a class="row-name" href="' . $this->build_query( $item, 'edit') . '" title="' . sprintf( __( 'Edit &#8220;%s&#8221;' ), esc_attr( $item->name ) ) . '">' . esc_html( $item->name ) . '</a></strong>';
		
		$out .= '<div class="hidden" id="inline_' . absint( $item->term_id ) . '">';
		
		$out .= '<div class="name">' . esc_html( $item->name ) . '</div></div>';
		
        $actions = array(
            'edit'      => sprintf( '<a class="e-term" href="%1$s">%2$s</a>',
				$this->build_query( $item, 'edit'),
				__( 'Edit', 'stt2extat' )
			),
            'delete'    => sprintf( '<a class="delete-term" href="%1$s">%2$s</a>',
				$this->build_query( $item, 'delete' ),
				__( 'Delete', 'stt2extat' )
			)
		);
		
        return sprintf( '%1$s %2$s',
			$out,
			$this->row_actions( $actions )
        );
    }
	
	public function page()
	{
		$page = ( isset( $_REQUEST['page'] ) ) ? sanitize_key( $_REQUEST['page'] ) : stt2extat_get_plugin_data()->TextDomain;
		$this->page = sanitize_key( $page );
	}
	
	protected function build_query( $item, $action = null, $nonce = false )
	{
		$data = array(
			'page'      => $this->page,
			'post_ID'   => absint( $item->post_id ),
			'term_ID'   => absint( $item->term_id ),
			'term_name' => esc_html( $item->name )
		);
		
		return stt2extat_build_query_nonce( $data, $action, true );
	}
	
	public function column_post_id( $item )
	{
		$term_id    = absint( $item->term_id );
		$term_name  = sanitize_text_field( $item->name );
		$post_id    = absint( $item->post_id );
		$post_url   = get_permalink( $post_id );
		$post_title = get_the_title( $post_id );
		$edit_post  = admin_url( "/post.php?post=$post_id&action=edit" );
		$out        = sprintf( '<a class="row-post_id" href="%1$s" title="%2$s">%3$s</a>',
			$post_url,
			the_title_attribute( array(
				'echo'   => 0,
				'before' => __( 'Permalink to: ', 'stt2extat' ),
				'after'  => '',
				'post'   => $post_id
			) ),
			$post_id
		);
		
        $actions = array(
            'edit'      => sprintf( '<a href="%1$s">%2$s</a>',
				$edit_post,
				__( 'Edit', 'stt2extat' )
			)
		);
		
        return sprintf( '%1$s %2$s',
			$out,
			$this->row_actions( $actions )
        );
	}
	
	public function column_post_modified( $item )
	{
		$post_modified = absint( $item->post_modified );
		$time          = date_i18n( 'c', $post_modified );
		$updated       = human_time_diff( ( int ) current_time( 'timestamp' ), $post_modified );
		return sprintf( '<time class="row-post_modified" datetime="%1$s"><abbr title="%1$s">%2$s</abbr></time>',
			esc_attr( $time ),
			$updated . __( ' ago', 'stt2extat' )
        );
	}
	
	public function column_cb( $item )
	{
        return sprintf( '<label class="screen-reader-text" for="cb-select-%2$s">%2$s</label><input type="checkbox" value="%2$s" name="%1$s[]" id="cb-select-%2$s" class="cb-select-%2$s">',
			'delete_' . $this->_args['singular'],
			absint( $item->term_id )
        );
    }
	
	public function get_columns()
    {
		$columns = array(
			'cb'            => '<input type="checkbox" />',
			'name'          => __( 'Term', 'stt2extat' ),
			'slug'          => __( 'Slug', 'stt2extat' ),
			'post_id'       => __( 'Post ID', 'stt2extat' ),
			'count'         => __( 'Hits', 'stt2extat' ),
			'post_modified' => __( 'Updated', 'stt2extat' )
		);
        return $columns;
    }
	
	public function get_hidden_columns()
    {
        return array();
    }
	
	public function get_sortable_columns()
    {
		$sortable_columns = array(
		    'term_id'       => array( 'term_id', true ),
			'name'          => array( 'name', false ),
			'slug'          => array( 'slug', false ),
			'post_id'       => array( 'post_id', false ),
			'count'         => array( 'count', false ),
			'post_modified' => array( 'post_modified', false )
        );
        return $sortable_columns;
    }
	
	public function column_default( $item, $column_name )
    {
        switch ( $column_name )
		{
            case 'term_id':
            case 'name':
			case 'slug':
            case 'post_id':
			case 'count':
			case 'post_modified':
                return $item->$column_name;
				
            default:
                return print_r( $item, true ) ;
        }
    }
	
	public function display()
	{
		echo '<input type="hidden" id="order" name="order" value="' . $this->_pagination_args['order'] . '" />';
		echo '<input type="hidden" id="orderby" name="orderby" value="' . $this->_pagination_args['orderby'] . '" />';
		parent::display();
	}
	
	public function ajax_response()
	{
		check_ajax_referer( 'bulk-table-stt2extat', '_wpnonce' );
		
		$this->prepare_items();
		
		extract( $this->_args );
		extract( $this->_pagination_args, EXTR_SKIP );
		
		ob_start();
		
		if ( ! empty( $_REQUEST['no_placeholder'] ) )
			$this->display_rows();
		else
			$this->display_rows_or_placeholder();
				
		$rows              = ob_get_clean();
		ob_start();
		
		$this->print_column_headers();
		$headers           = ob_get_clean();
		ob_start();
		
		$this->pagination('top');
		$pagination_top    = ob_get_clean();
		ob_start();
		
		$this->pagination('bottom');
		$pagination_bottom = ob_get_clean();
		
		$response = array( 'rows' => $rows );
		$response['order']                = $this->_pagination_args['order'];
		$response['orderby']              = $this->_pagination_args['orderby'];
		$response['pagination']['top']    = $pagination_top;
		$response['pagination']['bottom'] = $pagination_bottom;
		$response['column_headers']       = $headers;
		
		if ( isset( $total_items ) )
			$response['total_items_i18n'] = sprintf( _n( '1 item', '%s items', $total_items ), number_format_i18n( $total_items ) );
		
		if ( isset( $total_pages ) ) :
			$response['total_pages']      = $total_pages;
			$response['total_pages_i18n'] = number_format_i18n( $total_pages );
		endif;
		
		die( wp_json_encode( $response ) );
	}
}