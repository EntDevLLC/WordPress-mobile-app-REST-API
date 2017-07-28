<?php
class Tech_Labs_Menus_Controller {
 
    // Here initialize our namespace and resource name.
    public function __construct() {
        $this->namespace     = '/tech-labs/v1';
        $this->resource_name = 'menus';
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
        if ( get_option('close_json_menus') ) {
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
        $menu_name = 'Mobile';
        $locations = get_nav_menu_locations();
        $menu_id = $locations[ $menu_name ] ;
        $menus = wp_get_nav_menu_items($menu_id);
        if ( empty( $menus ) ) {
            return rest_ensure_response( $data );
        }
 
        foreach ( $menus as $menu ) {
            $response = $this->prepare_item_for_response( $menu, $request );
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
    public function prepare_item_for_response( $menu, $request ) {
        $schema = $this->get_item_schema( $request );
        if ( isset( $schema['properties']['id'] ) ) {
            $post_data['id'] = (int) $menu->ID;
        }
 
        if ( isset( $schema['properties']['title'] ) ) {
            $post_data['title'] = $menu->title;
        }
 
        if ( isset( $schema['properties']['type'] ) ) {
            $post_data['type'] = $menu->object;
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
     * Get our sample schema for a menus.
     *
     * @param WP_REST_Request $request Current request.
     */
    public function get_item_schema( $request ) {
        $schema = array(
            // This tells the spec of JSON Schema we are using which is draft 4.
            '$schema'              => 'http://json-schema.org/draft-04/schema#',
            // The title property marks the identity of the resource.
            'title'                => 'menus',
            'type'                 => 'object',
            // In JSON Schema you can specify object properties in the properties attribute.
            'properties'           => array(
                'id' => array(
                    'description'  => esc_html__( 'Unique identifier for the object.', 'tl-json' ),
                    'type'         => 'integer',
                    'context'      => array( 'view', 'edit', 'embed' ),
                    'readonly'     => true,
                ),
                'title' => array(
                    'description'  => esc_html__( 'The menu item title.', 'tl-json' ),
                    'type'         => 'string',
                ),
                'type' => array(
                    'description'  => esc_html__( 'The menu item type.', 'tl-json' ),
                    'type'         => 'string',
                )
            ),
        );
 
        return $schema;
    }
}