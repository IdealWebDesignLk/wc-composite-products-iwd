(function ($) {
    "use strict";
    var added_products = [];
    function wccp_thousand_separator(price, thousand_sep, decimal_sep) {
        price += '';
        var x = price.split(decimal_sep);
        var x1 = x[0];
        var x2 = x.length > 1 ? decimal_sep + x[1] : '';
        var rgx = /(\d+)(\d{3})/;
        while (rgx.test(x1)) {
            x1 = x1.replace(rgx, '$1' + thousand_sep + '$2');
        }
        return x1 + x2;
    }
    function wccp_boxes_quick_view(product_id) {
        var data = {
            'action': 'wccp_boxes_quick_view',
            'product_id': product_id,
            'security': wccp_composite_boxes.ajax_nonce,
        };
        jQuery.post(wccp_composite_boxes.ajaxurl, data, function (response) {
            if (response.data.success) {
                wccp_after_request();
                jQuery('.wccp_order_builder_boxes_view .wccp_order_builder_view_content').html(response.data.html).show();
                wccp_show_item_popup();
                jQuery('.wccp_order_builder_carousel').slick({
                    dots: true,
                });
            }
        });
    }
    function wccp_load_boxes(box_index = 0) {
        var options = wccp_get_options();
        var added_prod_ids = jQuery('input[name="wccp_added_prod_ids_' + box_index + '"]').val();
        added_prod_ids = added_prod_ids ? added_prod_ids : '';
        var data = {
            action: 'wccp_get_boxes_products',
            'product_id': options.product_id,
            'box_index': box_index,
            'added_prod_ids': added_prod_ids,
            'security': wccp_composite_boxes.ajax_nonce,
        };
        wccp_load_request();
        jQuery.post(wccp_composite_boxes.ajaxurl, data, function (response) {
            if (response.success) {
                jQuery('.wccp-boxes_rows').html(response.data.boxes);
                jQuery('.wccp_box_title').html(response.data.box_title);
                jQuery('.wccp_box_description').html(response.data.box_description);
                wccp_after_request(box_index);
                wccp_load_products(box_index, 'change_step');
            }
        });

    }
    function wccp_load_products(box_index = 0, type = '') {
        var options = wccp_get_options();
        var paged = jQuery('.wccp_load_more_products').attr('data-paged');
        var data = {
            action: 'wccp_get_products_for_boxes',
            'product_id': options.product_id,
            'box_index': box_index,
            'load_products': paged,
            'security': wccp_composite_boxes.ajax_nonce,
        };
        wccp_load_request();
        jQuery.post(wccp_composite_boxes.ajaxurl, data, function (response) {
            if (response.success) {
                if (paged >= response.data.pages) {
                    jQuery('.wccp_load_more_products').hide();
                } else {
                    jQuery('.wccp_load_more_products').show();
                }
                if (type == 'load_more') {
                    jQuery('.wccp_boxes_products').append(response.data.html);
                } else {
                    jQuery('.wccp_boxes_products').html(response.data.html);
                }
                wccp_after_request();
                var total_Steps = jQuery('.wccp_next_button').attr('data-total_steps');
                total_Steps = parseInt(total_Steps);
                if (box_index == total_Steps) {
                    wccp_review_products();
                } else {
                    wccp_disable_next_button(box_index);
                }
            }
        });

        var count_prod_limit = jQuery('.wccp-boxes_rows figure').length;
        // alert(count_prod_limit);
        setTimeout(() => {
            jQuery('.input-box').attr("max", count_prod_limit);
           
            // alert(count_prod_limit);
            if (count_prod_limit == 1) {
                jQuery('.filter-toggle').hide();
            } else {
                jQuery('.filter-toggle').show();
            }
            var productsavilabel = 0;
            var previousproductid = "";
            jQuery('.wccp-boxes_rows .wccp-col-4').each(function () {
                // Check if the current element has a child with the matching product ID
                var productId = $(this).find('.wccp_remove_product').data('product_id');

                // alert("product id:" + productId);

                if (productId) {
                    productsavilabel++;
                    if (previousproductid == productId) {
                        // Find the radio button that matches the product ID and click it
                        $('.wccp_boxes_products .wccp_item').each(function () {
                            var radioProductId = $(this).data('product_id');
                            if (radioProductId == productId) {
                                $(this).addClass('itemactive');
                                $(this).find('input[type="radio"]').prop('checked', true); // Click the radio button

                                var $inputBox = $(this).find('.input-box');
                                $inputBox.val(productsavilabel);
                                // alert("input:" + productsavilabel);
                            }

                        });
                    } else {

                        previousproductid = productId;
                        productsavilabel = 0;
                        productsavilabel++;
                        $('.wccp_boxes_products .wccp_item').each(function () {
                            var radioProductId = $(this).data('product_id');
                            if (radioProductId == productId) {
                                $(this).addClass('itemactive');
                                $(this).find('input[type="radio"]').prop('checked', true); // Click the radio button
                            }

                        });
                    }

                }
            });
            product_filter();
        }, 1000);



    }

    function wccp_load_request() {
        jQuery('.wccp_loader').show();
        jQuery('.wccp_overlay').show();

    }
    function wccp_after_request() {
        jQuery('.wccp_loader').hide();
        jQuery('.wccp_overlay').hide();
    }
    function wccp_get_options() {
        return jQuery.parseJSON(jQuery('.wccp_boxes').attr('data-options'));
    }
    function wccp_disable_prev_button(box_index) {
        if (box_index == 0) {
            jQuery('.wccp_prev_button').hide();
        } else {
            jQuery('.wccp_prev_button').show();
            jQuery('.wccp_prev_button').attr('disabled', false);
        }
        var prod_range = jQuery('.wccp-boxes_rows div').attr('data-products_range');
        prod_range = parseInt(prod_range);
        var no_of_added_prods = jQuery('.wccp-boxes_rows div img').length;
        if (prod_range > no_of_added_prods) {
            jQuery('.wccp_next_button').attr('disabled', true);
        } else {
            jQuery('.wccp_next_button').attr('disabled', false);
        }
    }
    function wccp_disable_next_button(box_index) {
        var total_Steps = jQuery('.wccp_next_button').attr('data-total_steps');
        var prod_range = jQuery('.wccp-boxes_rows div').attr('data-products_range');
        var no_of_added_prods = jQuery('.wccp-boxes_rows div img').length;
        total_Steps = parseInt(total_Steps);
        prod_range = parseInt(prod_range);
        if (prod_range > no_of_added_prods) {
            jQuery('.wccp_next_button').attr('disabled', true);
        } else {
            jQuery('.wccp_next_button').attr('disabled', false);
        }
    }
    function wccp_get_all_added_prod_ids() {
        var total_steps = jQuery('.wccp_next_button').attr('data-total_steps');
        total_steps = parseInt(total_steps);
        var all_products_ids = jQuery('input[name = "wccp_added_products"]');
        all_products_ids.val('');
        var allprod_ids_array = all_products_ids.val().split('');
        var added_prods;
        for (var i = 0; i < total_steps; i++) {
            added_prods = jQuery('input[name = "wccp_added_prod_ids_' + i + '"]').val().split(',');
            if (jQuery('input[name = "wccp_added_prod_ids_' + i + '"]').val() != '') {
                allprod_ids_array.push(added_prods);
            }
        }
        all_products_ids.val(allprod_ids_array.join(','));
        return all_products_ids.val();
    }
    function wccp_review_products() {
        var all_products_ids = wccp_get_all_added_prod_ids();
        var options = wccp_get_options();
        var data = {
            action: 'wccp_review_products',
            'products_ids': all_products_ids,
            'parent_prod_id': options.product_id,
            'security': wccp_composite_boxes.ajax_nonce,
        }
        jQuery.post(wccp_composite_boxes.ajaxurl, data, function (response) {
            if (response.success) {
                jQuery('.wccp_reviews_buttons').show();
                jQuery('.wccp-boxes_rows').hide();
                jQuery('.wccp_product_information').hide();
                jQuery('.wccp_load_more_products').hide();
                jQuery('.wccp_all_prods_data').hide();
                jQuery('.filter-toggle').hide();
                jQuery('.wccp_boxes_products').html(response.data);
                // jQuery('button[name="add-to-cart"]').trigger('click');
                jQuery( "<h2 class='tankmsg' style='text-align: center;'>Thank you for your purchase. We love having customers like you, and your support means everything to us.</h2>" ).insertAfter( jQuery( "#filter-panel" ) );
                setTimeout(function () {
                    // Redirect to the desired page
                    // window.location.href = 'https://alt.mein-regionalmarkt.de/kasse/';
                }, 1200);
            }
        });
        jQuery(document).on('click', '.wccp_edit_review_products', function (e) {
            e.preventDefault();
            jQuery('.wccp-boxes_rows').show();
            jQuery('.wccp_reviews_buttons,.tankmsg').hide();
            jQuery('.wccp_product_information').show();
            jQuery('.wccp_load_more_products').show();
            jQuery('.wccp_all_prods_data').show();
            jQuery('.wccp_load_more_products').attr('data-pages', 1);
            jQuery('.wccp_next_button').attr('data-next_step', 0);
            jQuery('.wccp_prev_button').attr('data-prev_step', 0);
            wccp_load_boxes(0);
        });
    }
    function wccp_check_stock(product_data) {
        var added_product_id = product_data.product_id;
        var stock = product_data.prod_stock;
        var all_added_prods = wccp_get_all_added_prod_ids().split(',');
        var i = 0;
        all_added_prods.filter(function (id) {
            if (added_product_id == id) {
                i++;
            }
        });
        if (i == stock) {
            jQuery(".wccp_stock_alert").animate({ "opacity": "show" }, 500);
            setTimeout(function () {
                jQuery(".wccp_stock_alert").animate({ "opacity": "hide" }, 500);
            }, 3000);
            return false;
        } else {
            return true;
        }
    }
    function wccp_show_item_popup() {
        jQuery('.wccp_order_builder_boxes_view').show();
        jQuery('.wccp_order_builder_boxes_layer').show();
    }
    function wccp_hide_item_popup() {
        jQuery('.wccp_order_builder_boxes_view').hide();
        jQuery('.wccp_order_builder_boxes_layer').hide();
    }
    jQuery(document).ready(function ($) {
        jQuery(document).on('click', '.wccp_order_builder_boxes_layer, .wccp_order_builder_boxes_head > span', function (e) {
            e.preventDefault();
            wccp_hide_item_popup();
        });
        if (jQuery('.wccp_order_builder_boxes').length > 0) {
            wccp_load_boxes(0);
            var currency_position = wccp_composite_boxes.currency_pos;
            var currency_symbol = wccp_composite_boxes.currency_symbol;
            var thousand_sep = wccp_composite_boxes.thousand_sep;
            var decimal_sep = wccp_composite_boxes.decimal_sep;
            var no_of_decimal = wccp_composite_boxes.no_of_decimal;
            jQuery('.wccp_prev_button').hide();
        }
        if (wccp_composite_boxes.box_item_click == 'quickview' || wccp_composite_boxes.box_item_click == 'noaction') {
            jQuery(document).on('click', '.wccp_boxes_products .wccp_item > a', function (e) {
                e.preventDefault();
                if (wccp_composite_boxes.box_item_click == 'quickview') {
                    var product_id = jQuery(this).closest('.wccp_item').attr('data-product_id');
                    wccp_load_request();
                    wccp_boxes_quick_view(product_id);
                }
            });
        }
        jQuery(document).on('click', '.wccp_next_button', function (e) {
            e.preventDefault();
            jQuery('.wccp_load_more_products').attr('data-paged', 1);
            var box_index = jQuery(this).attr('data-next_step');
            box_index = parseInt(box_index);
            box_index = (box_index < 1) ? 1 : (box_index + 1);
            jQuery('.wccp_prev_button').attr('data-prev_step', box_index);
            jQuery(this).attr('data-next_step', box_index);
            wccp_disable_prev_button(box_index);
            wccp_disable_next_button(box_index);
            wccp_load_boxes(box_index);
        });
        jQuery(document).on('click', '.wccp_prev_button', function (e) {
            e.preventDefault();
            jQuery('.wccp_load_more_products').attr('data-paged', 1);
            var box_index = jQuery(this).attr('data-prev_step');
            box_index = parseInt(box_index);
            box_index = (box_index <= 0) ? 0 : (box_index - 1);
            jQuery('.wccp_next_button').attr('data-next_step', box_index);
            jQuery(this).attr('data-prev_step', box_index);
            wccp_disable_prev_button(box_index);
            wccp_disable_next_button(box_index);
            wccp_load_boxes(box_index);

        });
        jQuery(document).on('click', '.wccp_add_to_box', function () {
            var add_product = jQuery(this).closest('div').find('figure').html();
            var product_data = jQuery.parseJSON(jQuery(this).attr('data-prod_data'));
            var added_product_id = product_data.product_id;
            added_products[added_product_id] = product_data.box_product_price;
            var box_index = jQuery('.wccp_boxes_products').find('div').attr('data-box_index');
            box_index = parseInt(box_index);
            var count_added_prods = jQuery('.wccp-boxes_rows figure img').length;
            var count_prod_limit = jQuery('.wccp-boxes_rows figure').length;
            if (count_added_prods >= count_prod_limit) {
                jQuery(".wccp_boxes_alert").animate({ "opacity": "show" }, 500);
                setTimeout(function () {
                    jQuery(".wccp_boxes_alert").animate({ "opacity": "hide" }, 500);
                }, 3000);
                return;
            }
            if (!wccp_check_stock(product_data)) return;
            jQuery('.wccp-boxes_rows').find('figure').each(function () {
                if (jQuery(this).find('img').length == 0) {
                    jQuery(this).html(add_product);
                    jQuery(this).append('<span class = "wccp_remove_product" data-product_id = "' + added_product_id + '"></span>');
                    jQuery(this).attr('data-product_id', added_product_id);
                    return false;
                }
            });
            if (wccp_composite_boxes.enable_scroll_up == 'yes') {
                jQuery(window).scrollTop(0);
            }
            wccp_disable_next_button(box_index);
            var ids_field = jQuery('input[name = "wccp_added_prod_ids_' + box_index + '"]');
            var added_products_ids = ids_field.val().split(',');
            if (ids_field.val() == '') {
                ids_field.val(added_product_id);
            } else {
                added_products_ids.push(added_product_id);
                ids_field.val(added_products_ids.join(','));
            }
            jQuery(document).trigger('wccp_changed_products_price');
        });
        jQuery(document).on('click', '.wccp_remove_product', function () {
            var box_index = jQuery('.wccp_boxes_products').find('div').attr('data-box_index');
            box_index = parseInt(box_index);
            var remove_prod_id = jQuery(this).attr('data-product_id');
            var added_prods = jQuery('input[name="wccp_added_prod_ids_' + box_index + '"]');
            var products = added_prods.val().split(',');
            if (added_prods.val() !== '') {
                products.forEach(function (product, i) {
                    if (products[i] == remove_prod_id) {
                        products.splice(i, 1);
                        return;
                    }
                });
            }
            added_prods.val(products.join(','));
            var box_label = jQuery('.wccp_box_label').attr('data-box_label');
            jQuery(this).closest('figure').html('<span class="wccp_box_label">' + box_label + '</span>');
            var box_index = jQuery('.wccp_next_button').attr('data-next_step');
            jQuery(document).trigger('wccp_changed_products_price');
            wccp_disable_next_button(box_index);
        });
        jQuery(this).on('click', '.wccp_load_more_products', function (e) {
            e.preventDefault()
            var box_index = jQuery('.wccp_next_button').attr('data-next_step');
            box_index = parseInt(box_index);
            var paged = jQuery(this).attr('data-paged');
            paged = parseInt(paged);
            paged += 1;
            jQuery(this).attr('data-paged', paged);
            wccp_load_products(box_index, 'load_more');
        });
        jQuery(document).on('click', '.wccp_reset_button', function () {
            jQuery('.wccp_reviews_buttons').find('input[type = "hidden"]').each(function () {
                jQuery(this).val('');
            });
            jQuery('.wccp_reviews_buttons').hide();
            jQuery('.wccp_prev_button').hide();
            jQuery('.wccp_prev_button').attr('data-prev_step', 0);
            jQuery('.wccp_next_button').attr('data-next_step', 0);
            jQuery(document).trigger('wccp_changed_products_price');
            wccp_load_boxes(0);
        });
        jQuery(document).bind('wccp_changed_products_price', function () {
            var options = wccp_get_options();
            var price = 0;
            var discounted_price = 0;
            var discount = options.discount;
            var discount_type = options.discount_type;
            if (options.pricing_type == 'per_product_only' || (options.pricing_type == 'per_product_box')) {
                if (options.pricing_type == 'per_product_box') {
                    price += parseFloat(options.product_price);
                }
                var all_products_ids = wccp_get_all_added_prod_ids();
                if (all_products_ids != '') {
                    var items = all_products_ids.split(',');
                    for (let i = 0; i < items.length; ++i) {
                        if (items[i] in added_products) {
                            var item = items[i];
                            price += parseFloat(added_products[item]);
                        }
                    }
                }
            } else if (options.pricing_type == 'fixed_pricing') {
                price = parseFloat(options.product_price);
            }
            if ('percentage' == discount_type) {
                if (discount > 0) {
                    discount = ((discount / 100) * price);
                }
                discounted_price = price - discount;
            } else {
                discounted_price = price - discount;
            }
            price = wccp_thousand_separator(price.toFixed(no_of_decimal), thousand_sep, decimal_sep);
            discounted_price = wccp_thousand_separator(discounted_price.toFixed(no_of_decimal), thousand_sep, decimal_sep);
            if (currency_position == 'left') {
                price = currency_symbol + price;
                discounted_price = currency_symbol + discounted_price;
            } else if (currency_position == 'right') {
                price = price + currency_symbol;
                discounted_price = discounted_price + currency_symbol;
            } else if (currency_position == 'left_space') {
                price = currency_symbol + ' ' + price;
                discounted_price = currency_symbol + ' ' + discounted_price;
            } else if (currency_position == 'right_space') {
                price = price + ' ' + currency_symbol;
                discounted_price = discounted_price + ' ' + currency_symbol;
            }
            if (discount > 0) {
                jQuery('.wccp_final_price .wccp_show_price').html('<strike>' + price + '</strike>');
            } else {
                jQuery('.wccp_final_price .wccp_show_price').html(price);
            }
            jQuery('.wccp_product_information .wccp_show_price').html(price);
            jQuery('span.wccp_finall_price').html(discounted_price);
            var total_Steps = jQuery('.wccp_next_button').attr('data-total_steps');
            var nextstep = jQuery('.wccp_next_button').attr('data-next_step');
            total_Steps = parseInt(total_Steps);
            nextstep = parseInt(nextstep);
            if (nextstep + 1 == total_Steps) {
                // wccp_review_products();
                jQuery('.wccp_next_button').show();
            } else {
                wccp_disable_next_button(nextstep);
            }
        });
    });
    jQuery(document).on('change', 'input[name="radioGroup"]', function () {

        jQuery('.wccp_remove_product').trigger('click');

        if ($(this).is(':checked')) {
            console.log('Radio button with ID ' + $(this).attr('id') + ' is checked');
            jQuery('.wccp_item').removeClass('itemactive');
            jQuery(this).closest('div').addClass('itemactive');
            var add_product = jQuery(this).closest('div').find('figure').html();
            var product_data = jQuery.parseJSON(jQuery(this).attr('data-prod_data'));
            var added_product_id = product_data.product_id;
            added_products[added_product_id] = product_data.box_product_price;
            var box_index = jQuery('.wccp_boxes_products').find('div').attr('data-box_index');
            box_index = parseInt(box_index);
            var count_added_prods = jQuery('.wccp-boxes_rows figure img').length;
            var count_prod_limit = jQuery('.wccp-boxes_rows figure').length;
            if (count_added_prods >= count_prod_limit) {
                jQuery(".wccp_boxes_alert").animate({ "opacity": "show" }, 500);
                setTimeout(function () {
                    jQuery(".wccp_boxes_alert").animate({ "opacity": "hide" }, 500);
                }, 3000);
                return;
            }
            if (!wccp_check_stock(product_data)) return;
            jQuery('.wccp-boxes_rows').find('figure').each(function () {
                if (jQuery(this).find('img').length == 0) {
                    jQuery(this).html(add_product);
                    jQuery(this).append('<span class = "wccp_remove_product" data-product_id = "' + added_product_id + '"></span>');
                    jQuery(this).attr('data-product_id', added_product_id);
                    return false;
                }
            });
            if (wccp_composite_boxes.enable_scroll_up == 'yes') {
                jQuery(window).scrollTop(0);
            }
            wccp_disable_next_button(box_index);
            var ids_field = jQuery('input[name = "wccp_added_prod_ids_' + box_index + '"]');
            var added_products_ids = ids_field.val().split(',');
            if (ids_field.val() == '') {
                ids_field.val(added_product_id);
            } else {
                added_products_ids.push(added_product_id);
                ids_field.val(added_products_ids.join(','));
            }
            jQuery(document).trigger('wccp_changed_products_price');
            var total_Steps = jQuery('.wccp_next_button').attr('data-total_steps');
            var nextstep = jQuery('.wccp_next_button').attr('data-next_step');
            total_Steps = parseInt(total_Steps);
            nextstep = parseInt(nextstep);
            if (nextstep == total_Steps) {
                // wccp_review_products();
                jQuery('.wccp_next_button').show();
            } else {
                wccp_disable_next_button(nextstep);
            }
        } else {
            console.log('Radio button with ID ' + $(this).attr('id') + ' is not checked');
        }
    });

    // $('').on('click', function(){
    jQuery(document).on('click', '.plus', function () {
        var $inputBox = $(this).closest('.quantity').find('.input-box');
        $(this).closest('.wccp_item').find('.wccp_add_to_box').trigger('click')
        // Increase the value by 1
        var updatedValue = parseInt($inputBox.val()) + 1;
        // Update the input box value
        if ($inputBox.attr('max') >= updatedValue) {
            $inputBox.val(updatedValue);
        } else {
        }


    });

    // Minus button click event
    // $('').on('click', function(){
    jQuery(document).on('click', '.minus', function () {
        // Get the input element associated with the clicked button

        var $inputBox = $(this).closest('.quantity').find('.input-box');
        // $(this).closest('wccp_item').find('.wccp_remove_product').trigger('click')
        var productId = $(this).data('product_id');
        $('.wccp-boxes_rows .wccp-col-4').each(function () {
            // Check if the current element has a child with the matching product ID
            // alert($(this).length);
            if ($(this).find('.wccp_remove_product').attr('data-product_id') == productId) {

                // Trigger the remove product click event for the first matching element
                $(this).find('.wccp_remove_product').trigger('click');
                // Exit the each loop after removing one item
                return false;  // This breaks the  loop
            }
        });
        // Decrease the value by 1 if it's greater than 1
        var updatedValue = parseInt($inputBox.val()) - 1;
        if (updatedValue >= 0) {
            // Update the input box value
            $inputBox.val(updatedValue);
        }
    });

    function product_filter() {
        var categories = [];

        // Select all elements with the class 'wccp_item'
        $('.wccp_boxes_products .wccp_item').each(function () {
            // Get the data-product-category attribute value
            var category = $(this).data('product-category');

            // Split the categories by comma and trim any extra spaces
            var categoryArray = category.split(',').map(function (item) {
                return item.trim();
            });

            // Merge the arrays
            categories = categories.concat(categoryArray);
        });

        // Get unique categories
        var uniqueCategories = [...new Set(categories)];

        // Log the array of unique categories to the console
        console.log(uniqueCategories);
        //  $('#filter-panel').empty();
        $('.ctpanel').empty();
        // Add checkboxes to the filter-panel container
        uniqueCategories.forEach(function (category) {
           
            $('#filter-panel .ctpanel').append('<div><input type="checkbox" class="filter-checkbox" data-category="' + category + '"> ' + category + '</div>');
        });

        // Add change event listener to filter checkboxes
        $('.filter-checkbox').change(function () {
            filterProducts();
        });
        categories = [];
    }
    // jQuery('.filter-toggle').hide();
    function filterProducts() {

        var selectedCategories = [];

        // Get all checked checkboxes
        $('.filter-checkbox:checked').each(function () {
            selectedCategories.push($(this).data('category'));
        });

        if (selectedCategories.length > 0) {
            // Hide all products initially
            $('.wccp_item').hide();

            // Show products that match any of the selected categories
            $('.wccp_item').each(function () {
                var productCategories = $(this).data('product-category').split(',').map(function (item) {
                    return item.trim();
                });

                for (var i = 0; i < selectedCategories.length; i++) {
                    if (productCategories.includes(selectedCategories[i])) {
                        $(this).show();
                        break;
                    }
                }
            });
        } else {
            // Show all products if no category is selected
            $('.wccp_item').show();
        }
    }

    // Toggle filter panel visibility

    jQuery(document).on('click', '.filter-toggle', function () {
         jQuery('#filter-panel').toggleClass('visible');
    });
    jQuery('.filter-toggle').hide();
})(jQuery);