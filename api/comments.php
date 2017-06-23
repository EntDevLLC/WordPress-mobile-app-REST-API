<?php
class Tech_Labs_Comments_Controller
{

    // Here initialize our namespace and resource name.
    public function __construct()
    {
        $this->namespace = '/tech-labs/v1';
        $this->resource_name = 'comments';
    }

    // Register our routes.
    public function register_routes()
    {
        register_rest_route($this->namespace, '/' . $this->resource_name . '/(?P<id>[\d]+)',
            array(
            array(
                'methods' => 'GET',
                'callback' => array($this, 'get_post_comments'),
                'permission_callback' => array($this, 'comments_permissions_check'),
                ),
            'schema' => array($this, 'comments_schema'),
            ));
        register_rest_route($this->namespace, '/' . $this->resource_name .
            '/make_comment', array(
            array(
                'methods' => 'POST',
                'callback' => array($this, 'make_comment'),
                'permission_callback' => array($this, 'comments_permissions_check'),
                ),
            // Register our schema callback.
            'schema' => array($this, 'make_comment_schema'),
            ));
    }

    /**
     * Check permissions for the comments.
     *
     * @param WP_REST_Request $request Current request.
     */
    public function comments_permissions_check($request)
    {
        if (get_option('close_json_comments')) {
            return new WP_Error('rest_forbidden', esc_html__('You cannot view the post resource.'),
                array('status' => $this->authorization_status_code()));
        }
        return true;
    }

    /**
     * Authenticate user informations .
     *
     * @param WP_REST_Request $request Current request.
     */
    public function get_post_comments($request)
    {
        $id = (int)$request->get_param('id');
        if (!$id) {
            return rest_ensure_response(['code' => 0, 'message' => esc_html__('No comments.',
                'tl-json')]);
        }
        $comments = get_comments(['post_id' => $id, 'status' => 'approve']);
        $schema = $this->comments_schema($request);
        if (is_wp_error($comments)) {
            $post_data = array('code' => 0, 'message' => strip_tags($user->
                    get_error_message()));
        } else {
            foreach ($comments as $comment) {
                $response = $this->prepare_item_for_response($comment, $request);
                $post_data[] = $this->prepare_response_for_collection($response);
            }
        }

        // Return all of our posts response data.
        return rest_ensure_response($post_data);
    }
    /**
     * Matches the coment data to the schema we want.
     *
     * @param comment object whose response is being prepared.
     */
    public function prepare_item_for_response($comment, $request)
    {
        $schema = $this->comments_schema($request);
        if (isset($schema['properties']['id'])) {
            $post_data['id'] = (int)$comment->comment_ID;
        }

        if (isset($schema['properties']['date'])) {
            $post_data['date'] = $comment->comment_date;
        }

        if (isset($schema['properties']['content'])) {
            $post_data['content'] = strip_tags(apply_filters('the_content', $comment->
                comment_content, $comment));
        }

        if (isset($schema['properties']['author'])) {
            $post_data['author'] = $comment->comment_author;
        }

        if (isset($schema['properties']['is_registed_user']) && $comment->user_id) {

            $post_data['is_registed_user'] = true;
        }

        if (isset($schema['properties']['author_comments_number']) && $comment->user_id) {
            $cargs = array('user_id' => $comment->user_id, 'count' => true);
            $comments_counts = get_comments($cargs);

            $post_data['author_comments_number'] = $comments_counts;
        }
        return rest_ensure_response($post_data);
    }

    /**
     * Prepare a response for inserting into a collection of responses.
     *
     * This is copied from WP_REST_Controller class in the WP REST API v2 plugin.
     *
     * @param WP_REST_Response $response Response object.
     * @return array Response data, ready for insertion into collection data.
     */
    public function prepare_response_for_collection($response)
    {
        if (!($response instanceof WP_REST_Response)) {
            return $response;
        }

        $data = (array )$response->get_data();
        $server = rest_get_server();

        if (method_exists($server, 'get_compact_response_links')) {
            $links = call_user_func(array($server, 'get_compact_response_links'), $response);
        } else {
            $links = call_user_func(array($server, 'get_response_links'), $response);
        }

        if (!empty($links)) {
            $data['_links'] = $links;
        }

        return $data;
    }

    /**
     * schema for a login.
     *
     * @param WP_REST_Request $request Current request.
     */
    public function comments_schema($request)
    {
        $schema = array(
            // This tells the spec of JSON Schema we are using which is draft 4.
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            // The title property marks the identity of the resource.
            'title' => 'comments',
            'type' => 'object',
            // In JSON Schema you can specify object properties in the properties attribute.
            'properties' => array(
                'id' => array(
                    'description' => esc_html__('Unique identifier for comment.', 'tl-json'),
                    'type' => 'integer',
                    ),
                'author' => array(
                    'description' => esc_html__('Comment author name.', 'tl-json'),
                    'type' => 'string',
                    ),
                'date' => array(
                    'description' => esc_html__('Comment date.', 'tl-json'),
                    'type' => 'string',
                    ),
                'content' => array(
                    'description' => esc_html__('Comment content.', 'tl-json'),
                    'type' => 'string',
                    ),
                'is_registed_user' => array(
                    'description' => esc_html__('Is Registed User.', 'tl-json'),
                    'type' => 'integer',
                    ),
                'author_comments_number' => array(
                    'description' => esc_html__('Author Comments Number.', 'tl-json'),
                    'type' => 'integer',
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
    public function make_comment($request)
    {

        $username = $request->get_param('username');
        $password = $request->get_param('password');
        $content = $request->get_param('content');
        $fullname = $request->get_param('fullname');
        $post_id = $request->get_param('post_id');

        if (!$post_id) {
            return rest_ensure_response(['code' => 0, 'message' => esc_html__('No data.',
                'tl-json')]);
        }
        if ($username && $password) {
            $user = wp_authenticate($username, $password);
            if (is_wp_error($user)) {
                $post_data = array('code' => 0, 'message' => strip_tags($user->get_error_message()));
                return rest_ensure_response($post_data);
            } else {
                $commentdata = array(
                    'comment_post_ID' => $post_id,
                    'comment_content' => strip_tags($content),
                    'user_id' => $user->ID,
                    'comment_author' => $user->display_name,
                    'comment_author_email' => $user->user_email,
                    'comment_agent' => 'Tech Labs JSON'
                    );
            }
        } else {
            $commentdata = array(
                'comment_post_ID' => $post_id,
                'comment_author' => $fullname,
                'comment_content' => strip_tags($content),
                'comment_agent' => 'Tech Labs JSON'
                );
        }

        wp_new_comment( $commentdata );
        $post_data = array(
            'code' => 200,
            'message' => esc_html__('Done .', 'tl-json'));

        // Return all of our posts response data.
        return rest_ensure_response($post_data);
    }
    /**
     * schema for a register.
     *
     * @param WP_REST_Request $request Current request.
     */
    public function make_comment_schema($request)
    {
        $schema = array(
            // This tells the spec of JSON Schema we are using which is draft 4.
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            // The title property marks the identity of the resource.
            'title' => 'Comment Done',
            'type' => 'object',
            // In JSON Schema you can specify object properties in the properties attribute.
            'properties' => array(
                'code' => array(
                    'description' => esc_html__('The response code.', 'tl-json'),
                    'type' => 'integer',
                    ),
                'message' => array(
                    'description' => esc_html__('The response message.', 'tl-json'),
                    'type' => 'string',
                    ),
                ),
            );

        return $schema;
    }
}
