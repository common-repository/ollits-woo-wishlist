<div class="wrap">
    <h1><?php esc_html_e( 'User Wishlists', 'ollits-woo-wishlist' ); ?></h1>
    <p><?php esc_html_e( 'See all wishlists created by users', 'ollits-woo-wishlist' ); ?></p>
    <hr>
    <?php
    // Check if the current user has the necessary capability
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'ollits-woo-wishlist'));
    }

    // Sanitize and validate the 'paged' parameter
    $paged = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
    $users_per_page = 10;
    $offset = ($paged - 1) * $users_per_page;

    // Set cache key and attempt to retrieve data from cache
    $cache_key = "user_wishlists_page_{$paged}";
    $user_results = wp_cache_get($cache_key, 'user_wishlists');
    $total_users = wp_cache_get("user_wishlists_total", 'user_wishlists');

    if ($user_results === false || $total_users === false) {
        // Query users with the specific meta key using WP_User_Query
        $args = array(
            'meta_query' => array(
				array(
					'key'     => '_ol_its_advanced_wishlist', // Replace with your meta key
					'compare' => 'EXISTS', // To ensure meta_key exists
				),
			),
            'fields' => array(
                'ID', 'display_name', 'user_email'
            ),
            'number' => $users_per_page,
            'offset' => $offset,
            'orderby'=> 'ID',
            'order'  => 'ASC', // Or 'DESC' based on your requirements
            'paged'  => $paged,
        );
        $user_query = new WP_User_Query($args);
        $user_results = $user_query->get_results();
        $total_users = $user_query->get_total();
        
        // Store results in cache
        wp_cache_set($cache_key, $user_results, 'user_wishlists', 3600);
        wp_cache_set("user_wishlists_total", $total_users, 'user_wishlists', 3600);
    }

    $total_pages = ceil($total_users / $users_per_page);
    
    if (!empty($user_results)) {
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        wp_nonce_field('bulk_remove_wishlists_nonce','brem_win_field');
        echo '<div class="tablenav top" style="margin-bottom:9px;">';
        echo '<button type="submit" class="button button-secondary">' . esc_html__('Delete Selected Wishlists', 'ollits-woo-wishlist') . '</button>';
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
        echo '<input type="hidden" name="action" value="bulk_remove_wishlists">';
        echo '<table class="wp-list-table widefat fixed striped table-view-list" cellspacing="0">';
        echo '<thead><tr><td class="manage-column column-cb check-column"><input type="checkbox" id="cb-select-all-1"><label for="cb-select-all-1"><span class="screen-reader-text">Select All</span></label></td><td>' . esc_html__('User', 'ollits-woo-wishlist') . '</td><td>' . esc_html__('Wishlist Items', 'ollits-woo-wishlist') .'</td></tr></thead><tbody id="the-list">';
        foreach ($user_results as $user) {
            $wishlist_count = count(get_user_meta($user->ID, '_ol_its_advanced_wishlist', true));
            if ($wishlist_count > 0) {
                echo '<tr>';
                echo '<th scope="row" class="check-column"><input id="cb-select-'. esc_attr($user->ID) .'" type="checkbox" name="user_ids[]" value="' . esc_attr($user->ID) . '"><label for="cb-select-'. esc_attr($user->ID) .'"><span class="screen-reader-text">Select '. esc_html($user->display_name) .'</span></label><div class="locked-indicator"><span class="locked-indicator-icon" aria-hidden="true"></span><span class="screen-reader-text">“'. esc_html($user->display_name) .'” is locked</span></div></th>';
                echo '<td><a href="' . esc_url(admin_url('user-edit.php?user_id=' . $user->ID)) . '"><strong class="row-title">'. esc_html($user->display_name) . '</strong><br>' . esc_html($user->user_email) . '</a></td>';
                echo '<td><a href="' . esc_url(admin_url('admin.php?page=ollits-user-wishlist&user_id=' . $user->ID)) . '">' . esc_html($wishlist_count) . ' ' . esc_html__('items', 'ollits-woo-wishlist') . '</a></td>';
                echo '</tr>';
            }
        }
        echo '<tfoot><tr><td class="manage-column column-cb check-column"><input type="checkbox" id="cb-select-all-2"><label for="cb-select-all-2"><span class="screen-reader-text">Select All</span></label></td><td>' . esc_html__('User', 'ollits-woo-wishlist') . '</td><td>' . esc_html__('Wishlist Items', 'ollits-woo-wishlist') .'</td></tr></tfoot>';
        echo '</tbody></table>';
        echo '<div class="tablenav bottom">';
        echo '<button type="submit" class="button button-secondary">' . esc_html__('Delete Selected Wishlists', 'ollits-woo-wishlist') . '</button>';
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
        esc_html_e('No users found.', 'ollits-woo-wishlist');
    }
    ?>
</div>