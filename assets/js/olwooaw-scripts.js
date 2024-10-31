document.addEventListener('DOMContentLoaded', function () {
    // Ensure jQuery is loaded
    if (typeof jQuery === 'undefined') {
        console.error('jQuery is not loaded.');
        return;
    }

    // jQuery is used for easier DOM manipulation
    jQuery(function ($) {
        // Function to get the selected variation ID for a given form
        function getSelectedVariationId($form) {
            if (!$form.length) {
                console.error('Variations form not found.');
                return null;
            }

            // WooCommerce stores variation data in a data attribute on the form
            var variations = $form.data('product_variations');
            if (!variations) {
                console.error('Product variations data not found.');
                return null;
            }

            var selectedAttributes = {};
            $form.find('.variations select').each(function () {
                var attributeName = $(this).data('attribute_name') || $(this).attr('name');
                selectedAttributes[attributeName] = $(this).val();
            });

            var selectedVariationId = null;

            // Loop through variations to find the matching one
            $.each(variations, function (index, variation) {
                var variationMatch = true;
                $.each(selectedAttributes, function (name, value) {
                    if (variation.attributes[name] !== value) {
                        variationMatch = false;
                        return false;
                    }
                });
                if (variationMatch) {
                    selectedVariationId = variation.variation_id;
                    return false; // Break the loop
                }
            });

            return selectedVariationId;
        }

        function checkVariationInWishlist(product_id, variation_id, form) {
            var nonce = aw_ajax.aw_nonce;
            var button = $("body").find("#ol-btn_wishlist_pl"+product_id+'.olwooaw-button');
            var abovebottonhiddendiv = button.parents(".olwooaw-wishlist-btn-wrap").find(".hidden-box");
            if (variation_id) {
                button.attr("data-variation-id",variation_id);
                var product_placeholdertext = button.data('placeholdertext').split('@');
                var wishlist_add_text = product_placeholdertext[0];
                var wishlist_remove_text = product_placeholdertext[1];
                $.ajax({
                    url: aw_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'check_variation_in_wishlist',
                        product_id: product_id,
                        variation_id: variation_id,
                        nonce: nonce
                    },
                    beforeSend: function() {
                        abovebottonhiddendiv.addClass("show");
                        button.css("opacity","0.5").prop("disabled",true);
                    },
                    success: function(response) {
                        abovebottonhiddendiv.removeClass("show");
                        button.css("opacity","1").prop("disabled",false);
                        if (response.success && response.data.in_wishlist) {
                            // '<i class="ollitsaw-font-heart fa fa-heart-o"></i><span>'+wishlist_add_text+'</span>' : '<i class="ollitsaw-font-heart fa fa-heart"></i><span>'+wishlist_remove_text+'</span>'
                            // button.removeClass('add-to-wishlist').addClass('remove-from-wishlist').text('Remove from Wishlist').data('variation-id', variation_id);
                            button.removeClass('add-to-wishlist').addClass('remove-from-wishlist').html('<i class="ollitsaw-font-heart fa fa-heart"></i><span>'+wishlist_remove_text+'</span>').data('variation-id', variation_id);
                        } else {
                            button.removeClass('remove-from-wishlist').addClass('add-to-wishlist').html('<i class="ollitsaw-font-heart fa fa-heart-o"></i><span>'+wishlist_add_text+'</span>').data('variation-id', variation_id);
                        }
                    }
                });
            }
        }

        // Function to handle changes on variation selection for a given form
        function handleVariationChange($form) {
            $form.on('change', '.variations select', function () {
                var variationId = getSelectedVariationId($form);
                var product_id = $form.closest('.variations_form').data('product_id');
                if (!variationId) {
                    variationId = 0;
                }
                checkVariationInWishlist(product_id, variationId, $form);
            });

            // Initial call to get the selected variation ID for the form
            var initialVariationId = getSelectedVariationId($form);
            if (initialVariationId) {
                // console.log('Initial selected variation ID for product:', initialVariationId);
            }
        }

        // Loop through each variation form on the page
        $('.variations_form').each(function () {
            var $form = $(this);
            handleVariationChange($form);
        });
    });
});

jQuery(document).ready(function($) {
    // jQuery( 'form.variations_form' ).on( 'found_variation', function( event, variation ){
    //     var o_variation_id = variation.variation_id;
    //     console.log( variation.variation_id );
    // });
    // $(document).on( 'found_variation', 'form.cart', function( event, variation ) {
    //     /* Your code */           
    //     console.log( variation );
    //     })
    // jQuery( 'form.variations_form' ).on( 'woocommerce_variation_has_changed', function( event, variation ){
    //     console.log( variation );
    // });
    // jQuery( 'form.variations_form' ).on( 'show_variation', function( event, variation ){
    //     var o_variation_id = variation.variation_id;
    //     $(document).find(".ol-btn_wishlist_pl").data("variation-id",o_variation_id);
    //     // .prop('disabled', true)
    //     console.log( variation.variation_id );
    //     console.log( variation);
    // });
    $('.ol-btn_wishlist_pl.add-to-wishlist, .ol-btn_wishlist_pl.remove-from-wishlist').on('click', function(e) {
        e.preventDefault();

        if (!aw_ajax.is_user_logged_in) {
            window.location.href = aw_ajax.login_url;
            return;
        }

        var button = $(this);
        var abovebottonhiddendiv = $(this).parents(".olwooaw-wishlist-btn-wrap").find(".hidden-box");
        var isMainUserWishlistpage = $("body").find("#user_wishlistpagemain").length?true:false;
        var is_heartIcon = button.find(".h_onmwishlist").length?button.find(".h_onmwishlist"):false;
        var product_id = button.data('product-id');
        var variation_id = button.data('variation-id');
        var product_placeholdertext = button.data('placeholdertext');
        if (product_placeholdertext && product_placeholdertext.includes('@')) {
            var placeholdertextParts = product_placeholdertext.split('@');
            if (placeholdertextParts.length === 2) {
                var wishlist_add_text = placeholdertextParts[0];
                var wishlist_remove_text = placeholdertextParts[1];
            }
        }
        var action = button.hasClass('add-to-wishlist') ? 'add_to_wishlist' : 'remove_from_wishlist';
        

        $.ajax({
            url: aw_ajax.ajax_url,
            method: 'POST',
            data: {
                action: action,
                product_id: product_id,
                variation_id:variation_id,
                security: aw_ajax.aw_nonce
            },
            beforeSend: function() {
                abovebottonhiddendiv.addClass("show");
                button.css("opacity","0.5").prop("disabled",true);
            },
            success: function(response) {
                if (response.success) {
                    abovebottonhiddendiv.removeClass("show");
                    button.css("opacity","1").toggleClass('add-to-wishlist remove-from-wishlist');
                    if(is_heartIcon) {
                        is_heartIcon.toggleClass('fa-heart fa-heart-o');
                        // button.text(button.hasClass('add-to-wishlist') ? 'Add to Wishlist' : 'Remove from Wishlist');
                    } else {
                        // button.text(button.hasClass('add-to-wishlist') ? 'Add to Wishlist' : 'Remove from Wishlist');
                        button.html(button.hasClass('add-to-wishlist') ? '<i class="ollitsaw-font-heart fa fa-heart-o"></i><span>'+wishlist_add_text+'</span>' : '<i class="ollitsaw-font-heart fa fa-heart"></i><span>'+wishlist_remove_text+'</span>');
                    }
                    if(isMainUserWishlistpage) {
                        var parentlist = $("body").find("#"+product_id+"_olrmywishp_"+variation_id)[0];
                        console.log(parentlist);
                        // $(parentlist).fadeOut(300, function() { $(this).remove(); });
                        // if($("#user_wishlistpagemain").find(".wishlist_item").length <=1) {

                        // }
                    }

                } else {
                    // console.log(response.data.message);
                }
            }
        });
    });

    $('.ol-btn_wishlist_pladd_to_cart').on('click', function(e) {
        e.preventDefault();

        var button = $(this);
        var productId = button.data('product-id');
        var variationId = button.data('variation-id') || 0;
        var quantity = button.data('quantity') || 1;

        $.ajax({
            url: aw_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'aw_add_to_cart',
                product_id: productId,
                variation_id: variationId,
                quantity: quantity,
                nonce: aw_ajax.aw_nonce
            },
            beforeSend: function () {
                button.text('Please wait...');
                button.prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    button.text('Added to Cart');
                    button.prop('disabled', false);
                    if (response.data.fragments) {
                        $.each(response.data.fragments, function(key, value) {
                            $(key).replaceWith(value);
                        });
                    }
                    // location.reload();
                } else {
                    button.text(response.data.message);
                    // console.log(response.data && response.data.message ? response.data.message : 'An error occurred.');
                }
            }
        });
    });

    // if (!aw_ajax.is_user_logged_in) {
    //     $('.add-to-wishlist, .remove-from-wishlist').on('click', function(e) {
    //         e.preventDefault();
    //         $('<div>').dialog({
    //             modal: true,
    //             title: 'Login Required',
    //             open: function() {
    //                 $(this).html('<p>You need to <a href="' + aw_ajax.login_url + '">log in</a> to manage your wishlist.</p>');
    //             }
    //         });
    //     });
    // }
});