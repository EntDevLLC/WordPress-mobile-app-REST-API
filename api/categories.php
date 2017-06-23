<?php
class Tech_Labs_Categories_Controller {
 
    // Here initialize our namespace and resource name.
    public function __construct() {
        $this->namespace     = '/tech-labs/v1';
        $this->resource_name = 'categories';
    }
 
    // Register our routes.
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->resource_name, array(
            // Here we register the readable endpoint for collections.
            array(
                'methods'   => 'GET',
                'callback'  => array( $this, 'get_items' ),
                'permission_callback' => array( $this, 'get_items_permissions_check' ),
            ),
            // Register our schema callback.
            'schema' => array( $this, 'get_item_schema' ),
        ) );
    }
 
    /**
     * Check permissions for the posts.
     *
     * @param WP_REST_Request $request Current request.
     */
    public function get_items_permissions_check( $request ) {
        if ( get_option('close_json_categories') ) {
            return new WP_Error( 'rest_forbidden', esc_html__( 'You cannot view the post resource.' ), array( 'status' => $this->authorization_status_code() ) );
        }
        return true;
    }
 
    /**
     * Grabs the five most recent posts and outputs them as a rest response.
     *
     * @param WP_REST_Request $request Current request.
     */
    public function get_items( $request ) {
        
        $order = $request->get_param( 'order' ) ? strtoupper($request->get_param( 'order' )) : 'ASC';
        $orderby = $request->get_param( 'orderby' ) ? $request->get_param( 'orderby' ) : 'name';
        $hide_empty = $request->get_param( 'hide_empty' ) ? 1 : 0;
        $exclude = $request->get_param( 'exclude' ) ? $request->get_param( 'exclude' ) : '';
        
        $args = array(
            'orderby' => $orderby,
            'order' => $order,
            'hide_empty' => $hide_empty
        );
        
        if($exclude)
        {
            $args['exclude'] = $exclude;
        }
        $categories = get_categories();
        $data = array();
 
        if ( empty( $categories ) ) {
            return rest_ensure_response( $data );
        }
 
        foreach ( $categories as $category ) {
            $response = $this->prepare_item_for_response( $category, $request );
            $data[] = $this->prepare_response_for_collection( $response );
        }
 
        // Return all of our posts response data.
        return rest_ensure_response( $data );
    }
 
    /**
     * Matches the post data to the schema we want.
     *
     * @param WP_Post $post The comment object whose response is being prepared.
     */
    public function prepare_item_for_response( $category, $request ) {
        $schema = $this->get_item_schema( $request );
        if ( isset( $schema['properties']['id'] ) ) {
            $post_data['id'] = (int) $category->term_id;
        }
 
        if ( isset( $schema['properties']['name'] ) ) {
            $post_data['name'] = $category->name;
        }
 
        return rest_ensure_response( $post_data );
    }
 
    /**
     * Prepare a response for inserting into a collection of responses.
     *
     * This is copied from WP_REST_Controller class in the WP REST API v2 plugin.
     *
     * @param WP_REST_Response $response Response object.
     * @return array Response data, ready for insertion into collection data.
     */
    public function prepare_response_for_collection( $response ) {
        if ( ! ( $response instanceof WP_REST_Response ) ) {
            return $response;
        }
 
        $data = (array) $response->get_data();
        $server = rest_get_server();
 
        if ( method_exists( $server, 'get_compact_response_links' ) ) {
            $links = call_user_func( array( $server, 'get_compact_response_links' ), $response );
        } else {
            $links = call_user_func( array( $server, 'get_response_links' ), $response );
        }
 
        if ( ! empty( $links ) ) {
            $data['_links'] = $links;
        }
 
        return $data;
    }
 
    /**
     * Get our sample schema for a categories.
     *
     * @param WP_REST_Request $request Current request.
     */
    public function get_item_schema( $request ) {
        $schema = array(
            // This tells the spec of JSON Schema we are using which is draft 4.
            '$schema'              => 'http://json-schema.org/draft-04/schema#',
            // The title property marks the identity of the resource.
            'title'                => 'categories',
            'type'                 => 'object',
            // In JSON Schema you can specify object properties in the properties attribute.
            'properties'           => array(
                'id' => array(
                    'description'  => esc_html__( 'Unique identifier for the object.', 'tl-json' ),
                    'type'         => 'integer',
                    'context'      => array( 'view', 'edit', 'embed' ),
                    'readonly'     => true,
                ),
                'name' => array(
                    'description'  => esc_html__( 'The category name.', 'tl-json' ),
                    'type'         => 'string',
                )
            ),
        );
 
        return $schema;
    }
}