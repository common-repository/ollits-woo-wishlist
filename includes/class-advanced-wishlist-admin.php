<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class Ollits_Woo_Wishlist_Admin {
    const WISHLIST_PAGE_SLUG = 'user-wishlist';
    public function __construct() {
        add_action('admin_menu', array($this, 'register_admin_menu'));
        add_action('admin_post_save_ollitsaw_settings', array($this, 'save_settings'));
        add_action('admin_post_remove_user_wishlist_items', array($this, 'remove_user_wishlist_items'));
        add_action( 'admin_post_bulk_remove_wishlists', array( $this, 'bulk_remove_wishlists' ) );
        add_action('delete_user', array($this, 'clear_wishlist_cache_on_user_delete'));
        // Ensure the wishlist page exists
        add_action('init', array($this, 'ensure_wishlist_page'));
    }

    public function register_admin_menu() {
        add_menu_page(
            __('Wishlist', 'ollits-woo-wishlist'),
            __('Wishlist', 'ollits-woo-wishlist'),
            'manage_options',
            'ollits-woo-wishlist',
            array($this, 'wishlist_page_content'),
            'dashicons-heart',
            6
        );

        add_submenu_page(
            null,
            __( 'User Wishlists', 'ollits-woo-wishlist' ),
            __( 'User Wishlists', 'ollits-woo-wishlist' ),
            'manage_options',
            'ollits-user-wishlist',
            array( $this, 'admin_user_wishlists_page' )
        );

        add_submenu_page(
            'ollits-woo-wishlist',
            __('Wishlist Settings', 'ollits-woo-wishlist'),
            __('Settings', 'ollits-woo-wishlist'),
            'manage_options',
            'ollits-wishlist-settings',
            array($this, 'settings_page_content')
        );
    }

    public function wishlist_page_content() {
        include OLLITSAW_PLUGIN_DIR . 'templates/admin-user-wishlists-page.php';
    }

    public function admin_user_wishlists_page() {
        include OLLITSAW_PLUGIN_DIR . 'templates/admin-single-user-wishlist-page.php';
    }

    public function settings_page_content() {
        include OLLITSAW_PLUGIN_DIR . 'templates/admin-settings-page.php';
    }

    public function bulk_remove_wishlists() {
        // Check for nonce field
        if (!isset($_POST['brem_win_field']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['brem_win_field'])), 'bulk_remove_wishlists_nonce')) {
            wp_die(esc_html__('Security check failed.', 'ollits-woo-wishlist'));
        }
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'ollits-woo-wishlist' ) );
        }

        if ( ! isset( $_POST['user_ids'] ) || ! is_array( $_POST['user_ids'] ) ) {
            wp_die( esc_html__( 'Invalid request.', 'ollits-woo-wishlist' ) );
        }

        $user_ids = array_map( 'absint', $_POST['user_ids'] );

        foreach ( $user_ids as $user_id ) {
            delete_user_meta( $user_id, '_ol_its_advanced_wishlist' );
        }

        wp_safe_redirect( admin_url( 'admin.php?page=ollits-woo-wishlist' ) );
        exit;
    }

    public function remove_user_wishlist_items() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission to perform this action.', 'ollits-woo-wishlist' ) );
        }

        // check_admin_referer( 'aw_nonce' );
        check_admin_referer('remove_user_wishlist_items_nonce');

        $user_id = isset($_POST['user_id']) ? absint($_POST['user_id']) : 0;
        if ($user_id) {
            $wishlist = get_user_meta($user_id, '_ol_its_advanced_wishlist', true);

            if (is_array($wishlist)) {
                if (isset($_POST['bulk_remove']) && !empty($_POST['product_ids'])) {
                    $product_ids = array_map('absint', $_POST['product_ids']);
                    foreach($product_ids as $product_id) {
                        if (isset($wishlist[$product_id])) {
                            unset($wishlist[$product_id]);
                        }
                    }
                    // $wishlist = array_diff($wishlist, $product_ids);
                    update_user_meta($user_id, '_ol_its_advanced_wishlist', $wishlist);
                }

                if (isset($_POST['single_remove']) && !empty($_POST['single_remove'])) {
                    $product_id = absint($_POST['single_remove']);
                    if (isset($wishlist[$product_id])) {
                        unset($wishlist[$product_id]);
                    }
                    // $wishlist = array_diff($wishlist, array($product_id));
                    update_user_meta($user_id, '_ol_its_advanced_wishlist', $wishlist);
                }
            }
        }

        wp_safe_redirect( admin_url( 'admin.php?page=ollits-user-wishlist&user_id=' . $user_id ) );
        exit;
    }

    public function ensure_wishlist_page() {
        $wishlist_page_id = get_page_by_path(self::WISHLIST_PAGE_SLUG, OBJECT, 'page');

        if (!$wishlist_page_id) {
            $wishlist_page = array(
                'post_title'    => __('Wishlist', 'ollits-woo-wishlist'),
                'post_content'  => '[ollits_wishlist]', // Shortcode for wishlist content
                'post_status'   => 'publish',
                'post_type'     => 'page',
                'post_name'     => self::WISHLIST_PAGE_SLUG
            );

            $wishlist_page_id = wp_insert_post($wishlist_page);
            if ($wishlist_page_id && !is_wp_error($wishlist_page_id)) {
                update_option('ollits_wishlist_page_id', $wishlist_page_id);
            }
        } else {
            update_option('ollits_wishlist_page_id', $wishlist_page_id->ID);
        }
    }

    public function save_settings() {
        if (!isset($_POST['aw_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['aw_nonce'])), 'save_ollitsaw_settings')) {
            return;
        }
        
        $AW_button_position_shop = (isset($_POST['ollitsaw_button_position_shop'])?sanitize_text_field(wp_unslash($_POST['ollitsaw_button_position_shop'])):"woocommerce_after_shop_loop_item");
        $AW_button_priority_shop = (isset($_POST['ollitsaw_button_priority_shop'])?intval($_POST['ollitsaw_button_priority_shop']):0);
        $AW_button_position_product = (isset($_POST['ollitsaw_button_position_product'])?sanitize_text_field(wp_unslash($_POST['ollitsaw_button_position_product'])):"woocommerce_single_product_summary");
        $AW_button_priority_product = (isset($_POST['ollitsaw_button_priority_product'])?intval($_POST['ollitsaw_button_priority_product']):31);
        
        $AW_button_add_to_wishlist_text = (isset($_POST['ollitsaw_button_add_to_wishlist_text'])?sanitize_text_field(wp_unslash($_POST['ollitsaw_button_add_to_wishlist_text'])):"Add to Wishlist");
        $AW_ollitsaw_button_remove_from_wishlist_text = (isset($_POST['ollitsaw_button_remove_from_wishlist_text'])?sanitize_text_field(wp_unslash($_POST['ollitsaw_button_remove_from_wishlist_text'])):"Remove from Wishlist");

        $AW_page_style = (isset($_POST['ollitsaw_page_style'])?sanitize_text_field(wp_unslash($_POST['ollitsaw_page_style'])):"");

        update_option('ollitsaw_button_position_shop', $AW_button_position_shop);
        update_option('ollitsaw_button_priority_shop', $AW_button_priority_shop);
        update_option('ollitsaw_button_position_product', $AW_button_position_product);
        update_option('ollitsaw_button_priority_product', $AW_button_priority_product);
        update_option('ollitsaw_button_add_to_wishlist_text', $AW_button_add_to_wishlist_text);
        update_option('ollitsaw_button_remove_from_wishlist_text', $AW_ollitsaw_button_remove_from_wishlist_text);
        update_option('ollitsaw_page_style', $AW_page_style);
        update_option('ollitsaw_email_notifications', isset($_POST['ollitsaw_email_notifications']) ? 1 : 0);

        wp_safe_redirect(admin_url('admin.php?page=ollits-wishlist-settings&updated=true'));
        exit;
    }

    public function clear_wishlist_cache_on_user_delete($user_id) {
        wp_cache_delete('user_wishlists_total', 'user_wishlists');
        $paged = 1;
        while (wp_cache_get("user_wishlists_page_{$paged}", 'user_wishlists')) {
            wp_cache_delete("user_wishlists_page_{$paged}", 'user_wishlists');
            $paged++;
        }
    }
}

new Ollits_Woo_Wishlist_Admin();
?>