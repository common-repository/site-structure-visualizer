<?php

add_action('rest_api_init', function () {

    register_rest_route('SSVP', 'generate-data', array(
        'methods'               => 'GET',
        'callback'              => 'SSVP_generate_data',
        'permission_callback'   => '__return_true',
        'args' => array(
            'data_type' => array(
                'required'      => false,
                'type'          => 'string',
                'description'   => 'Data Type Of Structure',
            ),
        )
    ));
});



function SSVP_generate_data($request)
{
    // get data parameters sent while api request
    $data_type  = $request->get_param('data_type');

    switch ($data_type) {

        case 'post':
            return SSVP_post_data();
            break;

        case 'tree':
            return SSVP_tree_data();
            break;

        default:
            return SSVP_respond_error();
            break;
    }
}


// -------- functions to generate data ---------

function SSVP_respond_error()
{
    return rest_ensure_response('Error : Data Type Undefined');
}


function SSVP_tree_data()
{
    // Initialize the JSON object
    $json_data = array();

    // Get all registered post types
    $post_types = get_post_types(array('public' => true, '_builtin' => false), 'objects', 'and');

    if (!empty($post_types['attachment'])) {
        unset($post_types['attachment']);
    }
    // Loop through each post type
    foreach ($post_types as $post_type) {
        // Initialize an array to store taxonomy data
        $taxonomy_data = array();

        // Get the taxonomies associated with the post type
        $taxonomies = get_object_taxonomies($post_type->name);

        // Loop through each taxonomy
        foreach ($taxonomies as $taxonomy) {
            // Get all terms of the taxonomy
            $terms = get_terms(array(
                'taxonomy' => $taxonomy,
                'hide_empty' => false,
            ));

            // Initialize an array to store term data
            $term_data = array();

            // Loop through each term
            foreach ($terms as $term) {
                // Get all posts associated with the term
                $args = array(
                    'post_type' => $post_type->name,
                    'tax_query' => array(
                        array(
                            'taxonomy' => $taxonomy,
                            'field' => 'slug',
                            'terms' => $term->slug,
                        ),
                    ),
                );

                $query = new WP_Query($args);

                // Initialize an array to store post data
                $post_data = array();

                // Loop through each post and store its data
                if ($query->have_posts()) {
                    while ($query->have_posts()) {
                        $query->the_post();
                        $post_data[] = array(
                            'name' => get_the_title(),
                            'label' => 'Post',
                            'link' => get_permalink(),
                        );
                    }
                }

                // Reset the post data
                wp_reset_postdata();

                // Add term data to the term_data array
                $term_data[] = array(
                    'name' => $term->name,
                    'label' => 'term',
                    'children' => $post_data,
                );
            }

            // Add taxonomy data to the taxonomy_data array
            $taxonomy_data[] = array(
                'name' => $taxonomy,
                'label' => 'taxonomy',
                'children' => $term_data,
            );
        }

        // Add post type data to the JSON object
        $json_data[] = array(
            'name' => $post_type->name,
            'label' => 'post_type',
            'children' => $taxonomy_data,
        );
    }

    // Convert the array to JSON format
    $json_output = json_encode(array(
        'name' => 'post_types',
        'label' => 'post_types',
        'children' => $json_data,
    ), JSON_PRETTY_PRINT);

    // Output the JSON data
    // echo $json_output;

    return rest_ensure_response($json_output);
}

function SSVP_post_data()
{

    $post_author = 'all';

    // get the posts
    $posts_list = get_posts(array('type' => 'post'));
    $post_data = array();

    foreach ($posts_list as $posts) {
        $post_id = $posts->ID;
        $post_author = $posts->post_author;
        $post_title = $posts->post_title;
        $post_content = $posts->post_content;

        $post_data[$post_id]['author'] = $post_author;
        $post_data[$post_id]['title'] = $post_title;
        $post_data[$post_id]['content'] = $post_content;
    }

    wp_reset_postdata();

    return rest_ensure_response($post_data);
}
