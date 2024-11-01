<?php


function SSVP_list_data()
{
    // Initialize the JSON object
    $json_data = array();

    $flag = false;

    if (!empty($_GET['display-type']) && $_GET['display-type'] == 'inbuild') {
        $flag = true;
    }

    // Get all registered post types
    $post_types = get_post_types(array('public' => true, '_builtin' => $flag), 'objects', 'and');

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
                    'label' => 'Term',
                    'children' => $post_data,
                );
            }

            // Add taxonomy data to the taxonomy_data array
            $taxonomy_data[] = array(
                'name' => $taxonomy,
                'label' => 'Taxonomy',
                'children' => $term_data,
            );
        }

        // Add post type data to the JSON object
        $json_data[] = array(
            'name' => $post_type->name,
            'label' => 'Post Type',
            'children' => $taxonomy_data,
        );
    }

    // Convert the array to JSON format
    $json_output = json_encode(array(
        'name' => 'post_types',
        'label' => 'Post Types',
        'children' => $json_data,
    ), JSON_PRETTY_PRINT);

    // Output the JSON data
    // echo $json_output;

    return $json_output;
}


function display_content_json_as_list($json_data)
{
    // Convert JSON data to PHP array
    $data = json_decode($json_data, true);

    echo '<ul class="tree" style="padding-top: 30px;">';
    echo '<li>Post Types';
    echo '<ol>';
    // Loop through each post type
    foreach ($data['children'] as $post_type) {
        echo '<li>' . $post_type['name'] . ' <span class="label">(' . $post_type['label'] . ')</span>';
        echo '<ol>';
        // Loop through each taxonomy
        foreach ($post_type['children'] as $taxonomy) {
            echo '<li>' . $taxonomy['name'] . ' <span class="label">(' . $taxonomy['label'] . ')</span>';
            echo '<ol>';
            // Loop through each term
            foreach ($taxonomy['children'] as $term) {
                echo '<li>' . $term['name'] . ' <span class="label">(' . $term['label'] . ')</span>';
                echo '<ol>';
                // Loop through each post
                foreach ($term['children'] as $post) {
                    echo '<li><a target="_blank" href="' . $post['link'] . '"><span class="dashicons dashicons-admin-links" style="font-size: 12px; color: #919191; line-height: unset;"></span>' . $post['name'] . '</a> <span class="label">(' . $post['label'] . ')</span></li>';
                }
                echo '</ol>';
                echo '</li>';
            }
            echo '</ol>';
            echo '</li>';
        }
        echo '</ol>';
        echo '</li>';
    }
    echo '</ol>'; // closing tag for Post Types ul
    echo '</li>'; // closing tag for Post Types li
    echo '</ol>'; // closing tag for top-level ul
}
?>

<div class="tab-content">
    <div class="title-head row">
        <h4>List View</h4>
        <form action="<?= $_SERVER['PHP_SELF']; ?>" method="GET" class="row align-items-center" style="margin-left: 50px;">
            <?php
            $flag = false;
            if (!empty($_GET['display-type']) && $_GET['display-type'] == 'inbuild') {
                $flag = true;
            }
            ?>
            <input type="text" name="page" value="ssvp-dashboard" class="d-none" hidden>
            <div class="field-row row">
                <input type="radio" value="inbuild" <?php echo (!empty($_GET['display-type']) && $_GET['display-type'] == 'inbuild') ? ' checked ' : '';   ?> name="display-type">
                <label for="">InBuild Types</label>
            </div>
            <div class="field-row row">
                <input type="radio" value="custom" <?php echo (!empty($_GET['display-type']) && $_GET['display-type'] == 'custom') ? ' checked ' : '';  ?> name="display-type" <?php echo empty($_GET['display-type']) ? ' checked ' : '';   ?>>
                <label for="">Custom Types</label>
            </div>
            <div class="wrap">
                <button class="page-title-action">Show</button>
            </div>
        </form>
    </div>
    <div id="list-container">
        <?php display_content_json_as_list(SSVP_list_data()); ?>
    </div>
</div>



<style>
    .title-head,
    .title-head * {
        width: auto !important;
    }

    .title-head form {
        margin-left: 50px;
        border: 1px solid #ddd;
    }

    .title-head .wrap button {
        padding: 3px 5px;
        top: unset;
    }

    /* Remove default list styles */
    ul.tree,
    ul.tree ul {
        list-style-type: none;
        margin: 0;
        padding: 0;
        position: relative;
    }

    ul.tree .label {
        color: #919191;
        font-weight: 500;
        font-size: 12px;
    }

    /* Create a horizontal line for each li */
    ul.tree li {
        margin: 0;
        padding: 0 2em;
        /* to create space for the line */
        line-height: 2em;
        color: #369;
        font-weight: 700;
        position: relative;
    }

    /* Create vertical line */
    ul.tree li:before {
        content: '';
        display: block;
        width: 0;
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        border-left: 1.5px solid #999;
    }

    /* Create horizontal line */
    ul.tree li:after {
        content: '';
        display: block;
        width: 2em;
        /* same as padding */
        height: 0.5em;
        /* same as padding */
        position: absolute;
        top: 1em;
        left: 0;
        border-top: 1.5px solid #999;
    }

    /* Remove line from top-level nodes */
    ul.tree>li:before,
    ul.tree>li:after {
        /* border: 0 none; */
    }

    /* Remove line from `li`s without children */
    ul.tree li:last-child:before {
        height: 1em;
        /* same as line-height */
    }

    ul.tree a {
        color: green;
    }
</style>