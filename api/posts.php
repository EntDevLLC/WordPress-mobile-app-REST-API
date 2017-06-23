<?php
class Tech_Labs_Posts_Controller {
 
    // Here initialize our namespace and resource name.
    public function __construct() {
        $this->namespace     = '/tech-labs/v1';
        $this->resource_name = 'posts';
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
        register_rest_route( $this->namespace, '/' . $this->resource_name . '/(?P<id>[\d]+)', array(
            // Notice how we are registering multiple endpoints the 'schema' equates to an OPTIONS request.
            array(
                'methods'   => 'GET',
                'callback'  => array( $this, 'get_item' ),
                'permission_callback' => array( $this, 'get_item_permissions_check' ),
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
        if ( get_option('close_json_posts') ) {
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
        // prepare our query.
        $page = $request->get_param( 'page' ) ? (int)$request->get_param( 'page' ) : 1;
        $per_page = $request->get_param( 'per_page' ) ? (int)$request->get_param( 'per_page' ) : 10;
        $offset = ( $page - 1 ) * $per_page;
        $order = $request->get_param( 'order' ) ? strtoupper($request->get_param( 'order' )) : 'DESC';
        $orderby = $request->get_param( 'orderby' ) ? $request->get_param( 'orderby' ) : 'ID';
        $category = $request->get_param( 'category' ) ? (int)$request->get_param( 'category' ) : 0;
        
        $args = array(
            'posts_per_page' => $per_page,
            'offset' =>$offset,
            'orderby' => $orderby,
            'order' => $order,
            'post_type' => 'post',
            'post_status' => 'publish'
        );
        
        if($category)
        {
            $args['category'] = $category;
        }
        
        $posts = get_posts( $args );
        $data = array();
 
        if ( empty( $posts ) ) {
            return rest_ensure_response( $data );
        }
 
        foreach ( $posts as $post ) {
            $response = $this->prepare_item_for_response( $post, $request );
            $data[] = $this->prepare_response_for_collection( $response );
        }
 
        // Return all of our posts response data.
        return rest_ensure_response( $data );
    }
 
    /**
     * Check permissions for the posts.
     *
     * @param WP_REST_Request $request Current request.
     */
    public function get_item_permissions_check( $request ) {
        if ( get_option('close_json_posts') ) {
            return new WP_Error( 'rest_forbidden', esc_html__( 'You cannot view the post resource.' ), array( 'status' => $this->authorization_status_code() ) );
        }
        return true;
    }
 
    /**
     * Grabs the five most recent posts and outputs them as a rest response.
     *
     * @param WP_REST_Request $request Current request.
     */
    public function get_item( $request ) {
        $id = (int) $request['id'];
        $post = get_post( $id );
 
        if ( empty( $post ) ) {
            return rest_ensure_response( array() );
        }
 
        $response = $this->prepare_item_for_response( $post, $request );
 
        // Return all of our post response data.
        return $response;
    }
 
    /**
     * Matches the post data to the schema we want.
     *
     * @param WP_Post $post The comment object whose response is being prepared.
     */
    public function prepare_item_for_response( $post, $request ) {
        $schema = $this->get_item_schema( $request );
        if ( isset( $schema['properties']['id'] ) ) {
            $post_data['id'] = (int) $post->ID;
        }
 
        if ( isset( $schema['properties']['title'] ) ) {
            $post_data['title'] = strip_tags(apply_filters( 'the_title', $post->post_title, $post ));
        }
 
        if ( isset( $schema['properties']['date'] ) ) {
            $post_data['date'] = human_time_diff(strtotime($post->post_date), current_time('timestamp'));
        }
 
        if ( isset( $schema['properties']['content'] ) ) {
            $post_data['content'] = strip_tags(apply_filters( 'the_content', $post->post_content, $post ));
        }
 
        if ( isset( $schema['properties']['comments_count'] ) ) {
            $post_data['comments_count'] = (int)$post->comment_count;
        }
 
        if ( isset( $schema['properties']['views_count'] )) {
    
            $post_data['views_count'] = (int)get_post_meta( $post->ID, 'views', true );
        }
 
        if ( isset( $schema['properties']['future_image'] )) {
    
            $post_data['future_image'] = array(
                'thumbnail' => get_the_post_thumbnail_url( $post->ID, 'thumbnail' ),
                'medium' => get_the_post_thumbnail_url( $post->ID, 'medium' ),
                'large' => get_the_post_thumbnail_url( $post->ID, 'large' ),
                'full' => get_the_post_thumbnail_url( $post->ID, 'full' )
            );
        } 
        if ( isset( $schema['properties']['attached_images'] )) {
            foreach(get_attached_media( 'image', $post->ID ) as $image)
            {
                $post_data['attached_images'][]=$image->guid;
            }
        }
        if ( isset( $schema['properties']['has_embed'] )) {
                $post_data['has_embed']=  preg_match( '|^\s*(https?://[^\s"]+)\s*$|im', $content, $matches )? true : false;
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
     * Get our sample schema for a post.
     *
     * @param WP_REST_Request $request Current request.
     */
    public function get_item_schema( $request ) {
        $schema = array(
            // This tells the spec of JSON Schema we are using which is draft 4.
            '$schema'              => 'http://json-schema.org/draft-04/schema#',
            // The title property marks the identity of the resource.
            'title'                => 'post',
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
                    'description'  => esc_html__( 'The content title.', 'tl-json' ),
                    'type'         => 'string',
                ),
                'future_image' => array(
                    'description'  => esc_html__( 'The content future image.', 'tl-json' ),
                    'type'         => 'array',
                ),
                'content' => array(
                    'description'  => esc_html__( 'The content for the object.', 'tl-json' ),
                    'type'         => 'string',
                ),
                'date' => array(
                    'description'  => esc_html__( 'The post date.', 'tl-json' ),
                    'type'         => 'string',
                ),
                'comments_count' => array(
                    'description'  => esc_html__( 'The post comments count.', 'tl-json' ),
                    'type'         => 'integer',
                ),
                'views_count' => array(
                    'description'  => esc_html__( 'The post views count.', 'tl-json' ),
                    'type'         => 'integer',
                ),
                'attached_images' => array(
                    'description'  => esc_html__( 'The post attached images.', 'tl-json' ),
                    'type'         => 'array',
                ),
                'has_embed' => array(
                    'description'  => esc_html__( 'Is the post has embed.', 'tl-json' ),
                    'type'         => 'integer',
                ),
            ),
        );
 
        return $schema;
    }
 
    // Sets up the proper HTTP status code for authorization.
    public function authorization_status_code() {
 
        $status = 401;
 
        if ( is_user_logged_in() ) {
            $status = 403;
        }
 
        return $status;
    }
}