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
    ?>
    <div class="entry-listing-top">
        <a href="<?php the_permalink($postTops->ID); ?>" title="<?php the_title_attribute($postTops->ID); ?>">
            <img src="<?php $feat_image = wp_get_attachment_url( get_post_thumbnail_id($postTops->ID) ); echo $feat_image;?>" />
        </a>
        <a class="entry-listing__content__title" href="<?php the_permalink($postTops->ID); ?>" title="<?php the_title_attribute($postTops->ID); ?>">
         <?php echo $postTops->post_title;?>
        </a>
        <div class="entry-listing__content">
                <div class="entry-listing__content__left">
                    <p class="entry-listing__content__text-info">Thông tin sơ lược</p>
                    <ul class="entry-listing__content__detail">
                        <li>
                        <strong> Tác giả:</strong> <?php echo esc_attr( get_post_meta( $postTops->ID, 'hcf_author', true ) );?>
                        </li>
                        <li>
                        <strong> Thể loại:</strong> <?php $category_detail=get_the_category($postTops->ID);//$post->ID
                                                    foreach($category_detail as $key => $cd){
                                                    echo $cd->cat_name;
                                                    if($key+1 < count($category_detail)){
                                                        echo " , ";
                                                    } 
                                                    } ?> 
                                    
                        </li>
                        <li>
                        <strong> Năm:</strong> <?php echo esc_attr( get_post_meta( $postTops->ID, 'hcf_year', true ) );?>
                        </li>
                        <li>
                        <strong> Số tập:</strong> <?php echo esc_attr( get_post_meta( $postTops->ID, 'hcf_chap', true ) );?>
                        </li>
                    </ul>
                </div>
                <div class="entry-listing__content__right">
                    <div class="reivew-box__point">
                            <?php  
                                $total = totalReview($postTops->ID);
                            ?>
                            <div class="reivew-box__point--line"></div>
                            <svg viewbox="0 0 36 36" class="circular-chart organ">
                                <path class="circle-bg"
                                    d="M18 2.0845
                                    a 15.9155 15.9155 0 0 1 0 31.831
                                    a 15.9155 15.9155 0 0 1 0 -31.831"
                                />
                                <path class="circle"
                                    stroke-dasharray="<?php echo $total * 10; ?>, 100"
                                    d="M18 2.0845
                                    a 15.9155 15.9155 0 0 1 0 31.831
                                    a 15.9155 15.9155 0 0 1 0 -31.831"
                                />
                                </svg>
                            <p class="reivew-box__point__text">
                                <?php 
                                    textReview($postTops->ID);
                                ?>
                            </p>
                            <p class="reivew-box__point__number">
                                <?php
                                    echo $total;
                                ?>
                            </p>
                            <fieldset class="rating">
                                <?php 
                                    $totalConvert4 =  round(($total/2)*2) /2;
                                    for ($i=5; $i > 0; $i--) {?>
                                        <input <?php if( $totalConvert4 % 2 === 0 &&  $totalConvert4 == $i ){ echo "checked ";} ?>type="radio" id="star<?php echo $i; ?>" name="rating"/>
                                        <label class = "full" for="star<?php echo $i; ?>" title="Awesome - <?php echo $i; ?> stars"></label>
                                        <?php if ($i < 5){ 
                                            ?>
                                            <input 
                                                <?php if($totalConvert4 > $i &&  $totalConvert4 < ($i + 0.5)) echo "checked "; else ""; ?> 
                                                type="radio" id="star<?php echo $i; ?>half" name="rating" value="<?php echo $i; ?> and a half" />
                                            <label class="half" for="star<?php echo $i; ?>half" title="Meh - 1.5 stars"></label>
                                        <?php
                                        }
                                        ?>
                                    <?php
                                    }
                                ?>
                            </fieldset>
                        </div>
                </div>
        </div>
    </div>
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
            'no_found_rows'       => true,
        );
        $the_query = new WP_Query( $arg_2 );
        $the_query_post = get_posts($arg_2);

        foreach($the_query_post as $pl){
            ?>
            <div class="entry-listing_item">
                <div class="entry-listing_item__left">
                    <figure class="featured-image">
                        <a href="<?php the_permalink($pl->ID); ?>" title="<?php the_title_attribute(); ?>">
                            <img src="<?php $feat_image = wp_get_attachment_url( get_post_thumbnail_id($pl->ID) ); echo $feat_image;?>" />
                        </a>
                    </figure>
                </div>
                    <div class="entry-listing_item__right">
                    <div class="nameCategoryPost" style="background:<?php echo the_newsmag_category_color($term->term_id); ?>">
                        <?php
                            $category_detail=get_the_category($pl->ID);//$post->ID
                            $i=0;
                            foreach($category_detail as $key => $cd){
                                if($i == 0){
                                    ?>
                                    <div class="entry-content__listing nameCategoryPost" style="background:<?php echo the_newsmag_category_color($cd->term_id); ?>">
                                    <div id="triangle-up" style="border-bottom-color:<?php echo the_newsmag_category_color($cd->term_id); ?>" class="triangle-up__right">
                                        
                                    </div>
                                        <p class="nameCategoryPost__title">
                                            <?php echo $cd->name; ?>
                                        </p>
                                    </div>
                                    </div>
                                    <?php
                                    $i++; //added here after edit.
                                    continue;
                                }else if($i > 0) {
                                    break;
                                }
                                $i++;
                            }
                        ?>
                        <div class="entry-date">
                            <div style="padding-right: 15px;display:inline-block;">
                            <i class="far fa-clock"></i> <?php echo get_the_time('d.m.Y', $pl->ID);?></div>
                            <i class="fas fa-pencil-alt"></i> 
                                <?php 
                                    $name = get_the_author_meta('display_name',$pl->post_author); 
                                    echo $name;
                                ?>				
                        </div>
                        <a href="<?php the_permalink(); ?>" class="entry_content__title--single">
                            <?php echo $pl->post_title; ?>
                        </a>
                        <a class="entry_content__link" href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                            Xem chi tiết <i class="fas fa-angle-double-right"></i>
                        </a>
                    </div>
            </div>
        <?php
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
    ?>
