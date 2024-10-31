<?php
// Load WordPress environment.
// require_once('../wp-load.php');

if (!isset($_GET['wishlist'])) {
    echo '<p>Invalid wishlist.</p>';
    exit;
}

$user_hash = sanitize_text_field(wp_unslash($_GET['wishlist']));
$advanced_wishlist = new Ollits_Woo_Wishlist();
$advanced_wishlist->display_shared_wishlist($user_hash);
?>
