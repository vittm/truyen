<?php
    $arg = array(
        'posts_per_page' => 1,
        'post_type'           => 'post',
        'ignore_sticky_posts' => true,
        'category__in'        => $current_category,
        'no_found_rows'       => true
    );
    $postTop = get_posts($arg);
    $id = null;
    foreach($postTop as $p){
        $id = $p->ID;
    }
?>

<?php foreach($postTop as $postTops){
    echo '<br>';
    echo 'phần 1'; 
    echo '<br>';
    echo $postTops->post_title;
    ?>
<?php
}
?>

    <?php 
        $paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
        $arg_2 = array(
            'posts_per_page' => 5,
            'paged'               => $paged,
            'post_type'           => 'post',
            'ignore_sticky_posts' => true,
            'category__in'        => $current_category,
            'post__not_in'        => array($id),
        );
        $the_query = new WP_Query( $arg_2 );
        $the_query_post = get_posts($arg_2);

        
        if ($the_query->have_posts()) : 
            foreach($the_query_post as $pl){
                echo '<br>'; ?>
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
            <?php
                echo $pl->post_title;
                echo '<br>';
            }

            $total_pages = $the_query->max_num_pages;

            if ($total_pages > 1){

                $current_page = max(1, get_query_var('paged'));

                echo paginate_links(array(
                    'base' => get_pagenum_link(1) . '%_%',
                    'format' => 'page/%#%',
                    'current' => $current_page,
                    'total' => $total_pages,
                    'prev_text'    => __('« prev'),
                    'next_text'    => __('next »'),
                ));
            }
        endif;
        
        // Restore original query object
        $wp_query = null;
        $wp_query = $tmp_query;
    ?>
