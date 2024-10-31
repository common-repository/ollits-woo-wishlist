<div class="wrap">
    <h1><?php esc_html_e('User Wishlist', 'ollits-woo-wishlist'); ?></h1>
    <?php
    $user_id = isset($_GET['user_id']) ? absint($_GET['user_id']) : 0;
    if ($user_id) {
        $user = get_userdata($user_id);
        if ($user) {
            echo '<p><a href="' . esc_url(admin_url('user-edit.php?user_id=' . $user_id)) . '" class="row-title">'. esc_html($user->display_name, 'ollits-woo-wishlist') . '\'s </a> Wishlist<a href="'.esc_url(admin_url( 'admin.php?page=ollits-woo-wishlist' )).'" class="o-button-link o-float-to-right"><span class="dashicons dashicons-arrow-left-alt"></span> Back to wishlist</a></p><hr>';
            // echo '<h2>' . esc_html($user->display_name, 'ollits-woo-wishlist') . '\'s Wishlist</h2>';
            $paged = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
            $items_per_page = 10;
            $offset = ($paged - 1) * $items_per_page;

            $wishlist = get_user_meta($user_id, '_ol_its_advanced_wishlist', true);
            if (!empty($wishlist)) {
                $total_items = count($wishlist);
                $total_pages = ceil($total_items / $items_per_page);
                $wishlist = array_slice($wishlist, $offset, $items_per_page);
                echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
                wp_nonce_field('remove_user_wishlist_items_nonce');
                echo '<input type="hidden" name="action" value="remove_user_wishlist_items">';
                echo '<div class="tablenav top" style="margin-bottom:9px;">';
                echo '<button type="submit" name="bulk_remove" class="button button-secondary">' . esc_html__('Remove Selected', 'ollits-woo-wishlist') . '</button>';
                if ($total_pages > 1) {
                    echo '<div class="tablenav-pages">';
                    $page_links = paginate_links(array(
                        'base' => add_query_arg('paged', '%#%'),
                        'format' => '',
                        'prev_text' => __('&laquo;'),
                        'next_text' => __('&raquo;'),
                        'total' => $total_pages,
                        'current' => $paged
                    ));
                    if (is_array($page_links)) {
                        foreach ($page_links as $page_link) {
                            echo wp_kses_post($page_link);
                        }
                    }
                    echo '</div>';
                }
                echo '</div>';
                echo '<input type="hidden" name="user_id" value="' . esc_attr($user_id) . '">';
                echo '<table class="wp-list-table widefat fixed striped table-view-list" cellspacing="0">';
                echo '<thead><tr><td class="manage-column column-cb check-column"><input type="checkbox" id="cb-select-all-1"><label for="cb-select-all-1"><span class="screen-reader-text">Select All</span></label></td><td class="manage-column column-thumb"> </td><td>' . esc_html__('Product', 'ollits-woo-wishlist') . '</td><td class="manage-column column-is_in_stock">' . esc_html__('Stock', 'ollits-woo-wishlist') . '</td><td class="manage-column column-price">' . esc_html__('Price', 'ollits-woo-wishlist') . '</td><td class="manage-column column-date o-text-align-center" style="width:10%;">' . esc_html__('Actions', 'ollits-woo-wishlist') . '</td></tr></thead><tbody id="the-list">';
                
                foreach ($wishlist as $key=>$item) {
                    $variation_id = $item['variation_id']?$item['variation_id']:0;
                    $product_id = $variation_id?$variation_id:$item['product_id'];
                    $product = wc_get_product($product_id);
                    $quantity = $item['quantity'];
                    // $variation_data = $variation_id ? ' (Variation ID: ' . $variation_id . ')' : '';
                    // $product = wc_get_product($product_id);
                    if ($product) {
                        $product_id_display = $product->is_type('variation') ? $product->get_parent_id() . ':' . $product->get_id() : $product->get_id();
                        $thumbnail_id = $product->get_image_id();
                        $thumbnail_url = wp_get_attachment_image_url($thumbnail_id, 'thumbnail');
                        $ollits_product_name = $product->get_name();
                        // Get the product regular price
                        $regular_price = $product->get_regular_price();
                        // Get the product sale price
                        $sale_price = $product->get_sale_price();
                        echo '<tr>';
                        echo '<th scope="row" class="check-column"><input id="cb-select-'. esc_attr($product_id) .'" type="checkbox" name="product_ids[]" value="' . esc_attr($product_id) . '"><label for="cb-select-'. esc_attr($product_id) .'"><span class="screen-reader-text">Select '. esc_html($ollits_product_name) .'</span></label><div class="locked-indicator"><span class="locked-indicator-icon" aria-hidden="true"></span><span class="screen-reader-text">“'. esc_html($ollits_product_name) .'” is locked</span></div></th>';
                        echo '<td><a href="' . esc_url(get_permalink($product_id)) . '" target="_blank" class="row-title">';
                        if ($thumbnail_url) {
                            echo '<img src="' . esc_url($thumbnail_url) . '" width="50" height="50">';
                        } 
                        echo '</a></td>';
                        echo '<td><a href="' . esc_url(get_permalink($product_id)) . '" target="_blank" class="row-title">' . esc_html($ollits_product_name) .'</a></td>';
                        echo '<td class="is_in_stock column-is_in_stock">';
                        // if ($product->needs_shipping()) {
                        //     echo 'This product requires shipping.';
                        // } else {
                        //     echo 'This product does not require shipping.';
                        // }
                        if ($product->is_in_stock()) {
                            echo '<span class="instock">In stock</span>';
                        } else {
                            echo '<span class="outofstock">Out of stock</span>';
                        }
                        echo '</td>';
                        echo '<td>';
                        if ($sale_price) {
                            echo wp_kses_post('<del>' . wc_price($regular_price).'</del>&nbsp;');
                            echo wp_kses_post('<ins>' . wc_price($sale_price) . '</ins>');
                        } else {
                            echo wp_kses_post('<ins>' . wc_price($regular_price) . '</ins>');
                        }
                        echo '</td>';
                        echo '<td><button type="submit" name="single_remove" value="' . esc_attr($product_id) . '" class="delete-button o-float-to-right"><i class="dashicons dashicons-trash"></i> '. esc_html__('Remove', 'ollits-woo-wishlist') .'</button></td>';
                        echo '</tr>';
                    }
                }
                echo '<tfoot><tr><td class="manage-column column-cb check-column"><input type="checkbox" id="cb-select-all-2"><label for="cb-select-all-2"><span class="screen-reader-text">Select All</span></label></td><td>' . esc_html__('Product', 'ollits-woo-wishlist') . '</td><td> </td><td>' . esc_html__('Stock', 'ollits-woo-wishlist') . '</td><td>' . esc_html__('Price', 'ollits-woo-wishlist') . '</td><td class="o-text-align-center" style="width:10%;">' . esc_html__('Actions', 'ollits-woo-wishlist') .'</td></tr></tfoot>';
                echo '</tbody></table>';
                echo '<div class="tablenav bottom">';
                echo '<button type="submit" name="bulk_remove" class="button button-secondary">' . esc_html__('Remove Selected', 'ollits-woo-wishlist') . '</button>';
                if ($total_pages > 1) {
                    echo '<div class="tablenav-pages">';
                    $page_links = paginate_links(array(
                        'base' => add_query_arg('paged', '%#%'),
                        'format' => '',
                        'prev_text' => __('&laquo;'),
                        'next_text' => __('&raquo;'),
                        'total' => $total_pages,
                        'current' => $paged
                    ));
                    if (is_array($page_links)) {
                        foreach ($page_links as $page_link) {
                            echo wp_kses_post($page_link);
                        }
                    }
                    echo '</div>';
                }
                echo '</div>';
                echo '</form>';
            } else {
                esc_html_e('No items in wishlist.', 'ollits-woo-wishlist');
            }
        } else {
            esc_html_e('Invalid user ID.', 'ollits-woo-wishlist');
        }
    } else {
        esc_html_e('No user selected.', 'ollits-woo-wishlist');
    }
    ?>
</div>