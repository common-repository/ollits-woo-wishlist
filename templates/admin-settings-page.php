<?php

if (!current_user_can('manage_options')) {
    return;
}

?>

<div class="wrap">
    <h1><?php esc_html_e('Wishlist Settings', 'ollits-woo-wishlist'); ?></h1>
    <form method="post" action="admin-post.php">
        <input type="hidden" name="action" value="save_ollitsaw_settings">
        <?php wp_nonce_field('save_ollitsaw_settings', 'aw_nonce'); ?>

        <h2><?php esc_html_e('Button Position Settings', 'ollits-woo-wishlist'); ?></h2>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="ollitsaw_button_position_shop"><?php esc_html_e('Wishlist Button ( Product loop)', 'ollits-woo-wishlist'); ?></label></th>
                <td>
                    <select name="ollitsaw_button_position_shop" id="ollitsaw_button_position_shop">
                        <option value="woocommerce_after_shop_loop_item" <?php selected(get_option('ollitsaw_button_position_shop'), 'woocommerce_after_shop_loop_item'); ?>>After Shop Loop Item</option>
                        <option value="woocommerce_before_shop_loop_item" <?php selected(get_option('ollitsaw_button_position_shop'), 'woocommerce_before_shop_loop_item'); ?>>On product image</option>
                        <option value="woocommerce_after_shop_loop_item_title" <?php selected(get_option('ollitsaw_button_position_shop'), 'woocommerce_after_shop_loop_item_title'); ?>>After Shop Loop Item Title</option>
                        <option value="woocommerce_before_shop_loop_item_title" <?php selected(get_option('ollitsaw_button_position_shop'), 'woocommerce_before_shop_loop_item_title'); ?>>Before Shop Loop Item Title</option>
                    </select>
                    <!-- <input type="text" id="ollitsaw_button_position_shop" name="ollitsaw_button_position_shop" value="<?php //echo esc_attr(get_option('ollitsaw_button_position_shop', 'woocommerce_after_shop_loop_item')); ?>"> -->
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="ollitsaw_button_priority_shop"><?php esc_html_e('Wishlist Button Priority (Product loop)', 'ollits-woo-wishlist'); ?></label></th>
                <td>
                    <input type="number" id="ollitsaw_button_priority_shop" name="ollitsaw_button_priority_shop" value="<?php echo esc_attr(get_option('ollitsaw_button_priority_shop', 20)); ?>">
                </td>
                <input type="hidden" id="ollitsaw_button_position_product" name="ollitsaw_button_position_product" value="<?php echo esc_attr(get_option('ollitsaw_button_position_product', 'woocommerce_single_product_summary')); ?>">
                <input type="hidden" id="ollitsaw_button_priority_product" name="ollitsaw_button_priority_product" value="<?php echo esc_attr(get_option('ollitsaw_button_priority_product', 31)); ?>">
            </tr>
            <!-- <tr>
                <th scope="row"><label for="ollitsaw_button_position_product"><?php //esc_html_e('Single Product Page Button Position', 'ollits-woo-wishlist'); ?></label></th>
                <td>
                    <select name="ollitsaw_button_position_product" id="ollitsaw_button_position_product">
                        <option value="woocommerce_single_product_summary" <?php //selected(get_option('ollitsaw_button_position_product'), 'woocommerce_single_product_summary'); ?>>After Add to Cart Button</option>
                        <option value="woocommerce_before_single_product_summary" <?php //selected(get_option('ollitsaw_button_position_product'), 'woocommerce_before_single_product_summary'); ?>>Before product summary</option>
                        <option value="woocommerce_after_single_product_summary" <?php //selected(get_option('ollitsaw_button_position_product'), 'woocommerce_after_single_product_summary'); ?>>After product summary</option>
                    </select>
                </td>
            </tr> -->
            <!-- <tr>
                <th scope="row"><label for="ollitsaw_button_priority_product"><?php //esc_html_e('Single Product Page Button Priority', 'ollits-woo-wishlist'); ?></label></th>
                <td>
                    <input type="number" id="ollitsaw_button_priority_product" name="ollitsaw_button_priority_product" value="<?php //echo esc_attr(get_option('ollitsaw_button_priority_product', 31)); ?>">
                </td>
            </tr> -->
            <tr>
                <th scope="row"><label for="ollitsaw_button_add_to_wishlist_text"><?php esc_html_e('"Add to wishlist" text', 'ollits-woo-wishlist'); ?></label></th>
                <td>
                    <input type="text" id="ollitsaw_button_add_to_wishlist_text" name="ollitsaw_button_add_to_wishlist_text" value="<?php echo esc_attr(get_option('ollitsaw_button_add_to_wishlist_text', "Add to Wishlist")); ?>">
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="ollitsaw_button_remove_from_wishlist_text"><?php esc_html_e('"Remove from Wishlist" text', 'ollits-woo-wishlist'); ?></label></th>
                <td>
                    <input type="text" id="ollitsaw_button_remove_from_wishlist_text" name="ollitsaw_button_remove_from_wishlist_text" value="<?php echo esc_attr(get_option('ollitsaw_button_remove_from_wishlist_text', "Remove from Wishlist")); ?>">
                </td>
            </tr>
        </table>

        <h2><?php esc_html_e('Custom css', 'ollits-woo-wishlist'); ?></h2>
        <textarea name="ollitsaw_page_style" rows="5" cols="50"><?php echo esc_textarea(get_option('ollitsaw_page_style', '')); ?></textarea>

        <h2><?php esc_html_e('Email Notifications', 'ollits-woo-wishlist'); ?></h2>
        <label>
            <input type="checkbox" name="ollitsaw_email_notifications" <?php checked(get_option('ollitsaw_email_notifications', 0), 1); ?>>
            <?php esc_html_e('Send email notifications when items are back in stock', 'ollits-woo-wishlist'); ?>
        </label>

        <?php submit_button(); ?>
    </form>
</div>