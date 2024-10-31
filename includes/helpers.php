<?php
function ollitsaw_get_wishlist_items($user_id) {
    return get_user_meta($user_id, '_ol_its_advanced_wishlist', true);
}

function ollitsaw_get_paginated_wishlist_items($user_id, $page = 1, $per_page = 10) {
    $wishlist = ollitsaw_get_wishlist_items($user_id);

    if (!is_array($wishlist)) {
        $wishlist = array();
    }

    $total_items = count($wishlist);
    $total_pages = ceil($total_items / $per_page);
    $offset = ($page - 1) * $per_page;

    return array(
        'items' => array_slice($wishlist, $offset, $per_page),
        'total_items' => $total_items,
        'total_pages' => $total_pages
    );
}
?>