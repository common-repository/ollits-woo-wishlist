<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
class Ollits_Woo_Wishlist_Ajax {

    public function __construct() {
        add_action('wp_ajax_add_to_wishlist', array($this, 'add_to_wishlist'));
        add_action('wp_ajax_nopriv_add_to_wishlist', array($this, 'add_to_wishlist'));
        add_action('wp_ajax_remove_from_wishlist', array($this, 'remove_from_wishlist'));
        add_action('wp_ajax_nopriv_remove_from_wishlist', array($this, 'remove_from_wishlist'));
        add_action('wp_ajax_check_variation_in_wishlist', array($this, 'check_variation_in_wishlist'));
        add_action('wp_ajax_nopriv_check_variation_in_wishlist', array($this, 'check_variation_in_wishlist'));
        add_action( 'wp_ajax_aw_add_to_cart', array( $this, 'aw_add_to_cart' ) );
        add_action( 'wp_ajax_nopriv_aw_add_to_cart', array( $this, 'aw_add_to_cart' ) );
    }

    public function add_to_wishlist() {
        check_ajax_referer('aw_nonce', 'security');

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => __('You need to be logged in to add items to your wishlist.', 'ollits-woo-wishlist')));
            return;
        }

        if (!isset($_POST['product_id'])) {
            wp_send_json_error(array('message' => __('Invalid product ID.', 'ollits-woo-wishlist')));
            return;
        }

        $product_id = intval($_POST['product_id']);
        $variation_id = isset($_POST['variation_id']) ? intval($_POST['variation_id']) : 0;
        $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

        if ($product_id <= 0 || $quantity <= 0) {
            wp_send_json_error(array('message' => 'Invalid product ID or quantity'), 400);
        }
        $user_id = get_current_user_id();

        $wishlist = get_user_meta($user_id, '_ol_its_advanced_wishlist', true);

        if (!$wishlist) {
            $wishlist = array();
        }

        // Check if the product or variation is already in the wishlist
        if ($variation_id) {
            if (isset($wishlist[$variation_id])) {
                wp_send_json_error(array('message' => __('This item is already in your wishlist.', 'ollits-woo-wishlist')), 400);
            }
            $wishlist[$variation_id] = array('product_id' => $product_id, 'variation_id'=>$variation_id, 'quantity' => $quantity);
        } else {
            if (isset($wishlist[$product_id])) {
                wp_send_json_error(array('message' => __('This item is already in your wishlist.', 'ollits-woo-wishlist')), 400);
            }
            $wishlist[$product_id] = array('product_id' => $product_id, 'variation_id'=>$variation_id, 'quantity' => $quantity);
        }

        // if (in_array($product_id, $wishlist)) {
        //     wp_send_json_error(array('message' => __('This item is already in your wishlist.', 'ollits-woo-wishlist')));
        //     return;
        // }

        update_user_meta($user_id, '_ol_its_advanced_wishlist', $wishlist);
        wp_send_json_success(array('message' => __('Item added to your wishlist.', 'ollits-woo-wishlist')));
    }

    public function remove_from_wishlist() {
        check_ajax_referer('aw_nonce', 'security');

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => __('You need to be logged in to add items to your wishlist.', 'ollits-woo-wishlist')));
            return;
        }

        if (!isset($_POST['product_id'])) {
            wp_send_json_error(array('message' => __('Invalid product ID.', 'ollits-woo-wishlist')));
            return;
        }
        
        $product_id = intval($_POST['product_id']);
        $variation_id = isset($_POST['variation_id']) ? intval($_POST['variation_id']) : 0;
        if ($product_id <= 0) {
            wp_send_json_error(array('message' => __('Product not found in wishlist.', 'ollits-woo-wishlist')), 400);
        }

        $user_id = get_current_user_id();
        $wishlist = get_user_meta($user_id, '_ol_its_advanced_wishlist', true);

        if ($variation_id) {
            if (isset($wishlist[$variation_id])) {
                unset($wishlist[$variation_id]);
            }
        } else {
            if (isset($wishlist[$product_id])) {
                unset($wishlist[$product_id]);
            }
        }

        update_user_meta($user_id, '_ol_its_advanced_wishlist', $wishlist);
        wp_send_json_success(array('message' => __('Product removed from wishlist.', 'ollits-woo-wishlist')));

    }

    public function check_variation_in_wishlist() {
        check_ajax_referer('aw_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => __('You need to be logged in to add items to your wishlist.', 'ollits-woo-wishlist')));
            return;
        }

        if (!isset($_POST['product_id'])) {
            wp_send_json_error(array('message' => __('Invalid product ID.', 'ollits-woo-wishlist')));
            return;
        }

        $product_id = intval($_POST['product_id']);
        $variation_id = isset($_POST['variation_id']) ? intval($_POST['variation_id']) : 0;

        if ($product_id <= 0 || $variation_id <= 0) {
            wp_send_json_error(array('message' => 'Invalid product or variation ID'), 400);
        }

        $user_id = get_current_user_id();
        $wishlist = get_user_meta($user_id, '_ol_its_advanced_wishlist', true) ?: [];

        $in_wishlist = isset($wishlist[$variation_id]);

        wp_send_json_success(array('in_wishlist' => $in_wishlist));
    }

    public function aw_add_to_cart() {
        check_ajax_referer( 'aw_nonce', 'nonce' );
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => __('You need to be logged in to add items to your wishlist.', 'ollits-woo-wishlist')));
            return;
        }

        $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
        $variation_id = isset($_POST['variation_id']) ? absint($_POST['variation_id']) : 0;
        $quantity = isset($_POST['quantity']) ? absint($_POST['quantity']) : 1;

        if($product_id) {
            if ($variation_id) {
                $product = wc_get_product($variation_id);
            } else {
                $product = wc_get_product($product_id);
            }
            // if ($product && $product->is_in_stock() && $product->get_stock_quantity() >= $quantity) {
            if ($product && $product->is_in_stock()) {
                $cart_item_key = WC()->cart->add_to_cart($product_id, $quantity, $variation_id);
                if ($cart_item_key) {
                    wp_send_json_success(array(
                        'message' => __('Product added to cart.', 'ollits-woo-wishlist'),
                        'fragments' => apply_filters('woocommerce_add_to_cart_fragments', array())
                    ));
                } else {
                    wp_send_json_error(array('message' => __('Failed to add product to cart.', 'ollits-woo-wishlist')));
                    return;
                }
            } else {
                wp_send_json_error( array( 'message' => __( 'Out of stock.', 'ollits-woo-wishlist' ) ) );
            }
        } else {
            wp_send_json_error( array( 'message' => __( 'Invalid product ID.', 'ollits-woo-wishlist' ) ) );
        }
    }
    

    
}

new Ollits_Woo_Wishlist_Ajax();
?>