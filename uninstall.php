<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Ensure the global $wpdb object is available
// global $wpdb;

// Delete the wishlist page created by the plugin
$wishlist_page_id = get_option('ollits_wishlist_page_id');
if ($wishlist_page_id) {
    wp_delete_post($wishlist_page_id, true);
    delete_option('ollits_wishlist_page_id');
}

// Clean up plugin data
// $wpdb->query(
//     $wpdb->prepare(
//         "DELETE FROM $wpdb->usermeta WHERE meta_key = %s",
//         '_ol_its_advanced_wishlist'
//     )
// );
// Delete metadata
delete_metadata('user', null, '_ol_its_advanced_wishlist', '', true);

// Delete plugin options
$options = [
    'ollitsaw_button_position_shop',
    'ollitsaw_button_priority_shop',
    'ollitsaw_button_position_product',
    'ollitsaw_button_priority_product',
    'ollitsaw_button_add_to_wishlist_text',
    'ollitsaw_button_remove_from_wishlist_text',
    'ollitsaw_page_style',
    'ollitsaw_email_notifications'
];

foreach ($options as $option) {
    delete_option($option);
}
?>