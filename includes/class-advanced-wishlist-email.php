<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class Ollits_Woo_Wishlist_Email {

    public function __construct() {
        add_action('woocommerce_product_set_stock_status', array($this, 'send_back_in_stock_email'), 10, 2);
    }

    public function send_back_in_stock_email($product_id, $stock_status) {
        if ($stock_status === 'instock') {
            $users = get_users();

            foreach ($users as $user) {
                $wishlist = get_user_meta($user->ID, '_ol_its_advanced_wishlist', true);

                if (is_array($wishlist) && in_array($product_id, $wishlist)) {
                    $product = wc_get_product($product_id);
                    $to = $user->user_email;
                    $subject = __('An item in your wishlist is back in stock', 'ollits-woo-wishlist');
                    // Translators: %1$s is the item name, %2$s is the item permalink.
                    $message = sprintf(__('The item "%1$s" is back in stock. You can purchase it here: %2$s', 'ollits-woo-wishlist'), $product->get_name(), $product->get_permalink());
                    wp_mail($to, $subject, $message);
                }
            }
        }
    }
}

new Ollits_Woo_Wishlist_Email();
?>