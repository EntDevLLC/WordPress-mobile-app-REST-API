<?php
class Tech_Labs_Users_Controller {
 
    // Here initialize our namespace and resource name.
    public function __construct() {
        $this->namespace     = '/tech-labs/v1';
        $this->resource_name = 'users';
    }
 
    // Register our routes.
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->resource_name.'/login', array(
            array(
                'methods'   => 'POST',
                'callback'  => array( $this, 'make_authenticate' ),
                'permission_callback' => array( $this, 'make_authenticate_permissions_check' ),
            ),
            'schema' => array( $this, 'login_user_schema' ),
        ) );
        register_rest_route( $this->namespace, '/' . $this->resource_name . '/register', array(
            array(
                'methods'   => 'POST',
                'callback'  => array( $this, 'register' ),
                'permission_callback' => array( $this, 'make_authenticate_permissions_check' ),
            ),
            // Register our schema callback.
            'schema' => array( $this, 'register_user_schema' ),
        ) );
    }
 
    /**
     * Check permissions for the users.
     *
     * @param WP_REST_Request $request Current request.
     */
    public function make_authenticate_permissions_check( $request ) {
        if ( get_option('close_json_users') ) {
            return new WP_Error( 'rest_forbidden', esc_html__( 'You cannot view the post resource.' ), array( 'status' => $this->authorization_status_code() ) );
        }
        return true;
    }
 
    /**
     * Authenticate user informations .
     *
     * @param WP_REST_Request $request Current request.
     */
    public function make_authenticate( $request ) {
        
        $username = $request->get_param( 'username' );
        $password = $request->get_param( 'password' );
        
        $user = wp_authenticate($username,$password);
        $schema = $this->login_user_schema( $request );
        if ( is_wp_error( $user ) )
        {
            $post_data = array(
                'code' => 0,
                'message' => strip_tags($user ->get_error_message())
            );
        }else{
            $post_data = array(
                'userid' => (int)$user->ID,
                'code' => 200,
                'message' => esc_html__( 'login informations is correct.', 'tl-json' )
            );
        }
 
        // Return all of our posts response data.
        return rest_ensure_response( $post_data );
    }
    /**
     * schema for a login.
     *
     * @param WP_REST_Request $request Current request.
     */
    public function login_user_schema( $request ) {
        $schema = array(
            // This tells the spec of JSON Schema we are using which is draft 4.
            '$schema'              => 'http://json-schema.org/draft-04/schema#',
            // The title property marks the identity of the resource.
            'title'                => 'users login',
            'type'                 => 'object',
            // In JSON Schema you can specify object properties in the properties attribute.
            'properties'           => array(
                'userid' => array(
                    'description'  => esc_html__( 'Unique identifier for logged user.', 'tl-json' ),
                    'type'         => 'integer',
                ),
                'code' => array(
                    'description'  => esc_html__( 'The response code.', 'tl-json' ),
                    'type'         => 'integer',
                ),
                'message' => array(
                    'description'  => esc_html__( 'The response message.', 'tl-json' ),
                    'type'         => 'string',
                ),
            ),
        );
 
        return $schema;
    }
    
 
    /**
     * Grabs the five most recent posts and outputs them as a rest response.
     *
     * @param WP_REST_Request $request Current request.
     */
    public function register( $request ) {
        
        $user['user_login'] = $request->get_param( 'username' );
        $user['user_pass'] = $request->get_param( 'password' );
        $user['user_email'] = $request->get_param( 'email' );
        $user['display_name'] = $request->get_param( 'fullname' );
        
        $user = wp_insert_user($user);
        $schema = $this->login_user_schema( $request );
        if ( is_wp_error( $user ) )
        {
            $post_data = array(
                'code' => 0,
                'message' =>$user ->get_error_message()
            );
        }else{
            $post_data = array(
                'userid' => (int)$user,
                'code' => 200,
                'message' => esc_html__( 'Done .', 'tl-json' )
            );
        }
 
        // Return all of our posts response data.
        return rest_ensure_response( $post_data );
    }
    /**
     * schema for a register.
     *
     * @param WP_REST_Request $request Current request.
     */
    public function register_user_schema( $request ) {
        $schema = array(
            // This tells the spec of JSON Schema we are using which is draft 4.
            '$schema'              => 'http://json-schema.org/draft-04/schema#',
            // The title property marks the identity of the resource.
            'title'                => 'users register',
            'type'                 => 'object',
            // In JSON Schema you can specify object properties in the properties attribute.
            'properties'           => array(
                'userid' => array(
                    'description'  => esc_html__( 'Unique identifier for logged user.', 'tl-json' ),
                    'type'         => 'integer',
                ),
                'code' => array(
                    'description'  => esc_html__( 'The response code.', 'tl-json' ),
                    'type'         => 'integer',
                ),
                'message' => array(
                    'description'  => esc_html__( 'The response message.', 'tl-json' ),
                    'type'         => 'string',
                ),
            ),
        );
 
        return $schema;
    }
}