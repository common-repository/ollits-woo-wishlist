<?php
// Check if WooCommerce is active
if ( ! class_exists( 'WooCommerce' ) ) {
    return;
}
$user_id = get_current_user_id();
$page = isset($_GET['ow_page']) ? intval($_GET['ow_page']) : 1;
$per_page = 10;
$wishlist_data = ollitsaw_get_paginated_wishlist_items($user_id, $page, $per_page);
$wishlist_items = $wishlist_data['items'];
$total_pages = $wishlist_data['total_pages'];

?>

<div class="olwooaw-wishlist">
    <div class="table-responsive wishlist_table">
        <div class="wishlist_container" id="user_wishlistpagemain">
            <?php
                if (!empty($wishlist_items)){
                    foreach ($wishlist_items as $key => $item) {
                        $variation_id = $item['variation_id'] ? $item['variation_id'] : 0;
                        $product_id = $variation_id ? $variation_id : $item['product_id'];
                        $product = wc_get_product($product_id);
                        $thumbnail_id = $product->get_image_id();
                        // $thumbnail_url = wp_get_attachment_image_url($thumbnail_id, 'medium');
                        $thumbnail_url = wp_get_attachment_image_url($thumbnail_id, 'thumbnail');
                        $regular_price = $product->get_regular_price();
                        $sale_price = $product->get_sale_price();
                        $stock_status = $product->is_in_stock() ? 'In stock' : 'Out of stock';
                        $rating = $product->get_average_rating();
                        $rating = floatval($rating);
                        $rating_percentage = ($rating / 5) * 100;
                        $rating_count = $product->get_rating_count();

                        ?>
                        <div class="wishlist_item" id="<?php echo esc_attr($item['product_id'])."_olrmywishp_".esc_attr($variation_id);?>">
                            <?php echo '<a href="#" data-product-id="' . esc_attr($item['product_id']) . '" data-variation-id="' . esc_attr($variation_id) . '" class="ol-btn_wishlist_pl remove-from-wishlist fa-1_35 heart_icon_onimage hide_on_desktop" aria-label="Remove this item"><i class="fa fa-heart h_onmwishlist"></i></a>';?>
                            <div class="product-thumbnail">
                                <a href="<?php echo esc_url(get_permalink($product_id));?>">
                                    <img src="<?php echo esc_url($thumbnail_url); ?>" alt="<?php echo esc_attr($product->get_name()); ?>">
                                </a>
                            </div>
                            <div class="product-details">
                                <div class="full_width">
                                    <div class="product-name"><a href="<?php echo esc_url(get_permalink($product_id));?>"><?php echo esc_html($product->get_name()); ?></a></div>
                                    <div class="product-rating">
                                        <div class="star-rating" title="<?php
                                            // Translators: %s is the rating value.
                                            printf(esc_html__('Rated %s out of 5', 'ollits-woo-wishlist'), esc_html($rating));
                                        ?>">
                                            <span style="width:<?php echo esc_attr($rating_percentage . '%'); ?>">
                                                <strong class="rating"><?php echo esc_html($rating); ?></strong> <?php esc_html('out of 5', 'ollits-woo-wishlist'); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="product-price">
                                        <?php 
                                        if ($sale_price) {
                                            echo wp_kses_post('<del>'.wc_price($regular_price).'</del>&nbsp;');
                                            echo wp_kses_post('<ins>'.wc_price($sale_price).'</ins>');
                                        } else {
                                            echo wp_kses_post('<ins>'.wc_price($regular_price).'</ins>');
                                        }
                                        ?>
                                    </div>
                                    <div class="product-stock-status"><?php echo esc_html($stock_status); ?></div>
                                    <div class="product-add-to-cart">
                                        <?php echo '<a data-product-id="' . esc_attr($item['product_id']) . '" data-variation-id="' . esc_attr($variation_id) . '" class="button ol-btn_wishlist_pladd_to_cart">' . esc_html__('Add to Cart', 'ollits-woo-wishlist') . '</a>'; ?>
                                    </div>
                                </div>
                                <div >
                                    <?php //echo '<a data-product-id="' . esc_attr($item['product_id']) . '" data-variation-id="' . esc_attr($variation_id) . '" class="ol-btn_wishlist_pladd_to_cart"><i class="fa fa-cart-plus" aria-hidden="true"></i></a>'; ?>
                                    <?php echo '<a href="#" data-product-id="' . esc_attr($item['product_id']) . '" data-variation-id="' . esc_attr($variation_id) . '" class="ol-btn_wishlist_pl remove-from-wishlist fa-1_35 hide_on_mobile" aria-label="Remove this item"><i class="fa fa-heart h_onmwishlist"></i></a>';?>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo '<div class="wishlist-empty">No products added to the wishlist</div>';
                }
            ?>
        </div>
    </div>
    <?php if (!empty($wishlist_items)) : ?>
        
        <?php if ($total_pages > 1) : ?>
            <div class="olwooaw-pagination">
            <ul class="pagination">
                <?php
                $pagination_links = paginate_links(array(
                    'base' => add_query_arg('ow_page', '%#%'),
                    'format' => '',
                    'prev_text' => __('&larr;', 'ollits-woo-wishlist'),
                    'next_text' => __('&rarr;', 'ollits-woo-wishlist'),
                    'total' => $total_pages,
                    'current' => $page,
                    'end_size' => 1,
                    'mid_size' => 1,
                    'type' => 'array',
                    // 'before_page_number' => '<li>',
                    // 'after_page_number' => '</li>'
                ));
                if (is_array($pagination_links)) {
                    foreach ($pagination_links as $link) {
                        echo wp_kses_post($link);
                    }
                }
                ?>
                </ul>
            </div>
        <?php endif; ?>
        <!-- <div id="socialShareButtons">
            <span>Share on</span>
            <ul class="share-wishlist">
                <li class="w_btn_list"><a href=""><i class="fa fa-facebook"></i></a></li>
                <li class="w_btn_list"><a href=""><i class="fa fa-twitter"></i></a></li>
                <li class="w_btn_list"><a href=""><i class="fa fa-pinterest"></i></a></li>
                <li class="w_btn_list"><a href=""><i class="fa fa-envelope" aria-hidden="true"></i></a></li>
                <li class="w_btn_list"><a href=""><i class="fa fa-whatsapp"></i></a></li>
            </ul>
        </div> -->
    <?php endif; ?>
</div>