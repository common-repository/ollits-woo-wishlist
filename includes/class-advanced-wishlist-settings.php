<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
class Ollits_Woo_Wishlist_Settings {

    public function __construct() {
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function register_settings() {
        register_setting('ollitsaw_settings_group', 'ollitsaw_button_position_shop');
        register_setting('ollitsaw_settings_group', 'ollitsaw_button_priority_shop');
        register_setting('ollitsaw_settings_group', 'ollitsaw_button_position_product');
        register_setting('ollitsaw_settings_group', 'ollitsaw_button_priority_product');
        register_setting('ollitsaw_settings_group', 'ollitsaw_button_add_to_wishlist_text');
        register_setting('ollitsaw_settings_group', 'ollitsaw_button_remove_from_wishlist_text');
        register_setting('ollitsaw_settings_group', 'ollitsaw_page_style');
        register_setting('ollitsaw_settings_group', 'ollitsaw_email_notifications');
    }
}

new Ollits_Woo_Wishlist_Settings();
?>