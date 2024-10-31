<?php
/**
 * Plugin Name: OLLITS Wishlist for WooCommerce
 * Description: An advanced wishlist plugin for WooCommerce with enhanced features.
 * Author: OLLITS
 * Version: 3.16
 * Author URI: https://www.ollits.com/
 * Text Domain: ollits-woo-wishlist
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Tested up to: 6.6
 * Requires PHP: 7.4
 * Requires Plugins: woocommerce
 */

if (!defined('ABSPATH')) {
    exit;
}
// Check if WooCommerce is active and prevent activation if it's not
register_activation_hook(__FILE__, 'ollits_woocommerce_check');

function ollits_woocommerce_check() {
    if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(esc_html__('This plugin cannot be activated because WooCommerce is not active. Please activate WooCommerce and try again.', 'ollits-woo-wishlist'));
    }
}
// Define constants
define("OLLITSAW_WISHLIST_PLUGIN_VERSION",3.16);
define('OLLITSAW_PLUGIN_URL', plugin_dir_url(__FILE__));
define('OLLITSAW_PLUGIN_DIR', plugin_dir_path(__FILE__));

// Include necessary files
include_once OLLITSAW_PLUGIN_DIR . 'includes/class-advanced-wishlist-admin.php';
include_once OLLITSAW_PLUGIN_DIR . 'includes/class-advanced-wishlist-ajax.php';
include_once OLLITSAW_PLUGIN_DIR . 'includes/class-advanced-wishlist-email.php';
include_once OLLITSAW_PLUGIN_DIR . 'includes/helpers.php';
include_once OLLITSAW_PLUGIN_DIR . 'includes/class-advanced-wishlist-settings.php';

// Main Plugin Class
class Ollits_Woo_Wishlist {

    public function __construct() {
        // Initialize settings
        new Ollits_Woo_Wishlist_Settings();

        // Add necessary hooks
        add_action('wp_enqueue_scripts', array($this, 'conditionally_enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_action('init', array($this, 'init_hooks'));
        add_action('template_redirect', array($this, 'wishlist_page_redirect'));
        add_shortcode( 'ollits_wishlist', array( $this, 'wishlist_shortcode' ) );

        // Initialize AJAX and Email classes
        new Ollits_Woo_Wishlist_Ajax();
        new Ollits_Woo_Wishlist_Email();
        // Add Wishlist link to My Account menu
        add_filter('woocommerce_account_menu_items', array($this, 'add_wishlist_link_to_account'), 10, 1);
        // Register the Wishlist endpoint
        add_action('init', array($this, 'add_wishlist_endpoint'));
        // Display Wishlist content on My Account page
        add_action('woocommerce_account_wishlist_endpoint', array($this, 'display_wishlist_content'));
    }

    public function init_hooks() {
        $position_shop = get_option('ollitsaw_button_position_shop', 'woocommerce_after_shop_loop_item');
        $priority_shop = get_option('ollitsaw_button_priority_shop', 20);
        add_action($position_shop, array($this, 'add_wishlist_button_shop'), $priority_shop);
        
        $position_product = get_option('ollitsaw_button_position_product', 'woocommerce_single_product_summary');
        $priority_product = get_option('ollitsaw_button_priority_product', 31);
        add_action($position_product, array($this, 'add_wishlist_button'), $priority_product);
    }

    // Add Wishlist link to My Account page
    public function add_wishlist_link_to_account($menu_links) {
        $new = array('wishlist' => __('Wishlist', 'ollits-woo-wishlist'));
        $menu_links = array_slice($menu_links, 0, 2, true) + $new + array_slice($menu_links, 2, NULL, true);
        return $menu_links;
    }

    // Register Wishlist endpoint
    public function add_wishlist_endpoint() {
        add_rewrite_endpoint('wishlist', EP_PAGES);
    }

    // Display Wishlist content
    public function display_wishlist_content() {
        if (!is_user_logged_in()) {
            echo '<p>' . esc_html__('You need to be logged in to view your wishlist.', 'ollits-woo-wishlist') . '</p>';
            return;
        }
        // Display the content using the [ollits_wishlist] shortcode
        echo do_shortcode('[ollits_wishlist]');
    }

    public function enqueue_scripts() {
        // Enqueue scripts and styles
        wp_enqueue_style('olwooaw-styles', OLLITSAW_PLUGIN_URL . 'assets/css/olwooaw-styles.css',[],OLLITSAW_WISHLIST_PLUGIN_VERSION);
        // Enqueue custom CSS
        $custom_css = get_option('ollitsaw_page_style', '');
        if ($custom_css) {
            wp_add_inline_style('olwooaw-styles', $custom_css);
        }
        wp_enqueue_style( 'olwooaw-font-awesome', OLLITSAW_PLUGIN_URL.'assets/font-awesome-4.7.0/css/font-awesome.min.css', [], "4.7.0");
        wp_enqueue_script('olwooaw-scripts', OLLITSAW_PLUGIN_URL . 'assets/js/olwooaw-scripts.js', array('jquery'), OLLITSAW_WISHLIST_PLUGIN_VERSION, true);
        wp_localize_script('olwooaw-scripts', 'aw_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'aw_nonce' => wp_create_nonce('aw_nonce'),
            'login_url' => wp_login_url(),
            'is_user_logged_in' => is_user_logged_in()
        ));
    }

    public function conditionally_enqueue_scripts() {
        // Check if the page contains any WooCommerce shortcodes
        if (is_shop() || is_product() || is_product_category() || is_page("user-wishlist") || (is_page() && $this->has_woocommerce_shortcode()) || is_account_page()) {
            $this->enqueue_scripts();
        }
    }

    function has_woocommerce_shortcode() {
        if (is_page()) {
            global $post;
            // Define an array of WooCommerce shortcodes you want to check for
            $woocommerce_shortcodes = array('products', 'product_category', 'product_page', 'woocommerce_cart', 'woocommerce_checkout');
    
            // Check if any of the shortcodes are in the post content
            foreach ($woocommerce_shortcodes as $shortcode) {
                if (has_shortcode($post->post_content, $shortcode)) {
                    return true;
                }
            }
        }
        return false;
    }

    public function admin_enqueue_scripts() {
        wp_enqueue_style('olwooaw-admin-styles', OLLITSAW_PLUGIN_URL . 'assets/css/olwooaw-admin-styles.css',[],OLLITSAW_WISHLIST_PLUGIN_VERSION,true);
        wp_enqueue_script('olwooaw-admin-scripts', OLLITSAW_PLUGIN_URL . 'assets/js/olwooaw-admin-scripts.js', array('jquery'), OLLITSAW_WISHLIST_PLUGIN_VERSION, true);
    }

    public function add_wishlist_button() {
        global $product;
        $product_id = $product->get_id();
        $user_id = get_current_user_id();
        $wishlist = get_user_meta($user_id, '_ol_its_advanced_wishlist', true);
        $variation_id = 0; // Default to no variation

        $add_to_wishlist_text = get_option('ollitsaw_button_add_to_wishlist_text', 'Add to Wishlist');
        $remove_from_wishlist_text = get_option('ollitsaw_button_remove_from_wishlist_text', 'Remove from Wishlist');

        $button_text = '<i class="ollitsaw-font-heart fa fa-heart-o"></i><span>'.$add_to_wishlist_text.'</span>';
        $button_class = 'add-to-wishlist';
        if ($product->is_type('variable')) {
            // If it's a variable product, check the selected variation
            $variations = $product->get_available_variations();
            foreach ($variations as $variation) {
                $variation_id = $variation['variation_id'];
                if (isset($wishlist[$variation_id])) {
                    $button_text = '<i class="ollitsaw-font-heart fa fa-heart"></i><span>'.$remove_from_wishlist_text.'</span>';
                    $button_class = 'remove-from-wishlist';
                    break;
                }
            }
        } else {
            if (isset($wishlist[$product_id])) {
                $button_text = '<i class="ollitsaw-font-heart fa fa-heart"></i><span>'.$remove_from_wishlist_text.'</span>';
                $button_class = 'remove-from-wishlist';
            }
        }
        echo '<div class="olwooaw-wishlist-btn-wrap s_prod"><div class="hidden-box"></div><a id="ol-btn_wishlist_pl'.esc_attr($product_id).'" class="olwooaw-button ol-btn_wishlist_pl ' . esc_attr($button_class) . '" data-product-id="' . esc_attr($product_id) . '" data-variation-id="' . esc_attr($variation_id) . '" data-placeholdertext="'.esc_attr($add_to_wishlist_text.'@'.$remove_from_wishlist_text).'">' . wp_kses_post($button_text) . '</a></div>';
    }

    public function add_wishlist_button_shop() {
        global $product;
        $product_id = $product->get_id();
        $user_id = get_current_user_id();
        $wishlist = get_user_meta($user_id, '_ol_its_advanced_wishlist', true);
        $variation_id = 0; // Default to no variation

        $add_to_wishlist_text = get_option('ollitsaw_button_add_to_wishlist_text', 'Add to Wishlist');
        $remove_from_wishlist_text = get_option('ollitsaw_button_remove_from_wishlist_text', 'Remove from Wishlist');

        $ShopLoopItemHtmlElementPosition = get_option('ollitsaw_button_position_shop', 'woocommerce_before_shop_loop_item');
        $additional_css_class = (($ShopLoopItemHtmlElementPosition==="woocommerce_before_shop_loop_item")?" btn_on_image":"");

        $button_text = '<i class="ollitsaw-font-heart fa fa-heart-o"></i><span>'.$add_to_wishlist_text.'</span>';
        $button_class = 'add-to-wishlist';
    
        if ($product->is_type('variable')) {
            // If it's a variable product, check the selected variation
            $variations = $product->get_available_variations();
            foreach ($variations as $variation) {
                $variation_id = $variation['variation_id'];
                if (isset($wishlist[$variation_id])) {
                    $button_text = '<i class="ollitsaw-font-heart fa fa-heart"></i><span>'.$remove_from_wishlist_text.'</span>';
                    $button_class = 'remove-from-wishlist';
                    break;
                }
            }
        } else {
            if (isset($wishlist[$product_id])) {
                $button_text = '<i class="ollitsaw-font-heart fa fa-heart"></i><span>'.$remove_from_wishlist_text.'</span>';
                $button_class = 'remove-from-wishlist';
            }
        }
    
        echo '<div class="olwooaw-wishlist-btn-wrap p_loop'.esc_attr($additional_css_class).'"><div class="hidden-box"></div><a id="ol-btn_wishlist_pl'.esc_attr($product_id).'" class="olwooaw-button ol-btn_wishlist_pl ' . esc_attr($button_class) . '" data-product-id="' . esc_attr($product_id) . '" data-variation-id="' . esc_attr($variation_id) . '" data-placeholdertext="'.esc_attr($add_to_wishlist_text.'@'.$remove_from_wishlist_text).'">' . wp_kses_post($button_text) . '</a></div>';
    }

    public function wishlist_shortcode() {
        if (!is_user_logged_in()) {
            return '<p>' . esc_html__('You need to be logged in to view your wishlist.', 'ollits-woo-wishlist') . '</p>';
        }

        ob_start();
        include OLLITSAW_PLUGIN_DIR . 'templates/user-wishlist-page.php';
        return ob_get_clean();
    }

    public function wishlist_page_redirect() {
        if (is_page('wishlist') && !is_user_logged_in()) {
            wp_redirect(wp_login_url(get_permalink()));
            exit;
        }
    }
    
}

// Initialize the plugin
new Ollits_Woo_Wishlist();
?>