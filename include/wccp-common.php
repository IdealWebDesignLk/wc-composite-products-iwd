<?php

if (!defined('ABSPATH')) {
    wp_die();
}
if (!class_exists('WCCP_Composite_Products_Loop')) {
    class WCCP_Composite_Products_Loop
    {
        private static $rageproductsforcustum = 0;
        public function __construct()
        {
            add_action('wp_ajax_wccp_get_boxes_products', array(__CLASS__, 'wccp_get_product_boxes'));
            add_action('wp_ajax_nopriv_wccp_get_boxes_products', array(__CLASS__, 'wccp_get_product_boxes'));
            add_action('wp_ajax_wccp_get_products_for_boxes', array(__CLASS__, 'wccp_get_products_for_boxes'));
            add_action('wp_ajax_nopriv_wccp_get_products_for_boxes', array(__CLASS__, 'wccp_get_products_for_boxes'));
            add_action('wp_ajax_wccp_review_products', array(__CLASS__, 'wccp_get_review_products'));
            add_action('wp_ajax_nopriv_wccp_review_products', array(__CLASS__, 'wccp_get_review_products'));
            add_action('wp_ajax_wccp_boxes_quick_view', array(__CLASS__, 'wccp_boxes_quick_view'));
            add_action('wp_ajax_nopriv_wccp_boxes_quick_view', array(__CLASS__, 'wccp_boxes_quick_view'));
        }

        public static function wccp_boxes_quick_view()
        {
            check_ajax_referer('wccp_composite_products', 'security');
            if (!isset($_POST['product_id'])) {
                wp_send_json_error(esc_html__('Something is wrong please try again.', 'wc-cp'));
            }
            $product_id = wc_clean($_POST['product_id']);
            if ('publish' != get_post_status($product_id)) {
                $html = esc_html__('Product can not be found!', 'wc-cp');
                wp_send_json_success(array('success' => true, 'html' => $html));
            }
            $product = wc_get_product($product_id);
            $args = array(
                'post_type' => array('product', 'product_variation'),
                'post__in' => array($product_id),
                'post_status' => 'publish',
                'posts_per_page' => 1
            );
            $product_query = new WP_Query($args);
            ob_start(); ?>
            <div id="product-<?php echo esc_attr($product_id); ?>" <?php wc_product_class('', $product); ?>>

                <div class="woocommerce-product-gallery woocommerce-product-gallery--with-images images">
                    <figure class="woocommerce-product-gallery__wrapper">
                        <?php
                        $attachment_ids = $product->get_gallery_image_ids();
                        array_unshift($attachment_ids, $product->get_image_id());
                        if (!empty($attachment_ids)) {
                            echo '<div class="wccp_order_builder_carousel">';
                            foreach ($attachment_ids as $attachment_id) {
                                echo '<div class="wccp_order_builder_slide">' . wp_get_attachment_image($attachment_id, 'full') . '</div>';
                            }
                            echo '</div>';
                        } ?>
                    </figure>
                </div>
                <?php
                while ($product_query->have_posts()) {
                    $product_query->the_post();
                ?>
                    <div class="summary entry-summary">
                        <?php do_action('woocommerce_single_product_summary'); ?>
                    </div>
                <?php
                }
                wp_reset_postdata(); ?>
            </div>
<?php
            $html = ob_get_clean();
            wp_send_json_success(array('success' => true, 'html' => $html));
        }
        public static function wccp_get_product_boxes()
        {
            check_ajax_referer('wccp_composite_products', 'security');
            if (empty($_POST['product_id'])) {
                wp_send_json_error(esc_html__('No boxes to load', 'wc-cp'));
            }
            $product_id = wc_clean($_POST['product_id']);
            $box_index = wc_clean($_POST['box_index']);
            $added_prod_ids = wc_clean($_POST['added_prod_ids']);
            if (!empty($added_prod_ids)) {
                $added_prod_ids = explode(',', $added_prod_ids);
            }
            if (empty($product_id)) {
                wp_send_json_error(esc_html__('No Boxes to load', 'wc-cp'));
            }
            ob_start();
            $product_info = get_post_meta($product_id, 'wccp_composite_product_options', true);
            $product_info = !empty($product_info) ? $product_info : array();
            $boxes_columns = !empty($product_info['boxes_columns']) ? $product_info['boxes_columns'] : '';
            $boxes_data = get_post_meta($product_id, 'wccp_composite_boxes_data', true);
            // var_dump($boxes_data);
            $boxNames = array_column($boxes_data, 'wccp_box_name');
            $jsBoxNames = json_encode($boxNames);
            // Echoing JavaScript code to log the array
    //         echo "<script>
             
    //         let currentStep = 1;
    //         var stepNames = $jsBoxNames;
    //         const totalSteps = stepNames.length;

    // function createStepper(steps) {
    //   const stepperWrapper = jQuery('.stepper-wrapper');
    //   stepperWrapper.empty(); // Clear any existing steps

    //   for (let i = 0; i < steps.length; i++) {
    //     const stepItem = jQuery('<div>', { class: 'stepper-item' });
    //     const stepCounter = jQuery('<div>', { class: 'step-counter', text: i + 1 });
    //     const stepName = jQuery('<div>', { class: 'step-name', text: steps[i] });

    //     stepItem.append(stepCounter, stepName);

    //     if (i === 0) {
    //       stepItem.addClass('active');
    //     }

    //     stepItem.on('click', function() {
    //       currentStep = i + 1;
    //       updateStepper();
    //     });

    //     stepperWrapper.append(stepItem);
    //   }
    // }
    // function updateStepper() {
    //     jQuery('.stepper-item').removeClass('active');
    //     jQuery('.stepper-item').eq(currentStep - 1).addClass('active');
    //   updateButtons();
    // }
    // function updateButtons() {
    //     jQuery('.wccp_prev_button').toggle(currentStep > 1);
    //     jQuery('.wccp_next_button').toggle(currentStep < totalSteps);
    // }
    // jQuery('.wccp_next_button').on('click', function() {
    //   if (currentStep < totalSteps) {
    //     currentStep++;
    //     updateStepper();
    //   }
    // });
    // jQuery('.wccp_prev_button').on('click', function() {
    //   if (currentStep > 1) {
    //     currentStep--;
    //     updateStepper();
    //   }
    // });
    // // Initialize stepper with given step names
    // createStepper(stepNames);
    // updateButtons();
     
    //         </script>";

            $boxes_data = !empty($boxes_data) ? $boxes_data : array();
            $no_of_boxes = !empty($boxes_data[$box_index]['wccp_no_of_boxes']) ? $boxes_data[$box_index]['wccp_no_of_boxes'] : '3';
            $boxes_name = !empty($boxes_data[$box_index]['wccp_box_name']) ? $boxes_data[$box_index]['wccp_box_name'] : '';
            $products_range = !empty($boxes_data[$box_index]['wccp_box_min_range']) ? $boxes_data[$box_index]['wccp_box_min_range'] : 1;



            $general_settings = get_option('wccp_composite_products_settings');
            for ($i = 0; $i < $no_of_boxes; $i++) {
                echo '<div class="wccp-col-' . esc_attr($boxes_columns) . '" data-box_index="' . esc_attr($box_index) . '" data-products_range="' . esc_attr($products_range) . '" >';
                echo '<div class="wccp-inner">';
                if (!empty($added_prod_ids[$i])) {
                    $id = $added_prod_ids[$i];
                    $product = wc_get_product($id);
                    echo '<figure>';
                    echo $product->get_image();
                    echo '<span class="wccp_remove_product" data-product_id="' . esc_attr($id) . '"></span>';
                    echo '</figure>';
                } else {
                    echo '<figure class="wccp_box_label" data-prod_data="" data-box_label="' . esc_attr($boxes_name) . '" ><span class="wccp_box_label">' . esc_html($boxes_name) . '</span></figure>';
                }
                echo '</div>
				</div>';
            }
            $html = ob_get_clean();
            $box_description = !empty($boxes_data[$box_index]['wccp_box_description']) ? $boxes_data[$box_index]['wccp_box_description'] : '';
            $box_title = !empty($boxes_data[$box_index]['wccp_box_title']) ? $boxes_data[$box_index]['wccp_box_title'] : '';
            $data = array('boxes' => $html, 'box_title' => $box_title, 'box_description' => $box_description);
            wp_send_json_success($data);
        }
        public static function wccp_get_products_for_boxes()
        {
            check_ajax_referer('wccp_composite_products', 'security');
            if (empty($_POST['product_id'])) {
                wp_send_json_error(esc_html__('No Boxes to load', 'wc-cp'));
            }
            $product_id = wc_clean($_POST['product_id']);
            $box_index = wc_clean($_POST['box_index']);
            $paged = wc_clean($_POST['load_products']);
            $paged = !empty($paged) ? $paged : 1;
            $product_info = get_post_meta($product_id, 'wccp_composite_product_options', true);
            $products_columns = !empty($product_info['products_columns']) ? $product_info['products_columns'] : 3;
            $prod_per_page = !empty($product_info['products_per_page']) ? $product_info['products_per_page'] : 8;
            $boxes_data = get_post_meta($product_id, 'wccp_composite_boxes_data', true);
            $boxes_data = !empty($boxes_data) ? $boxes_data : array();
            $boxes_products = !empty($boxes_data[$box_index]['wccp_box_products']) ? $boxes_data[$box_index]['wccp_box_products'] : array();
            $boxes_categories = !empty($boxes_data[$box_index]['wccp_box_categories']) ? $boxes_data[$box_index]['wccp_box_categories'] : array();
            $show_price = !empty($product_info['enable_product_price']) ? $product_info['enable_product_price'] : '';
            $general_settings = get_option('wccp_composite_products_settings');
            $general_settings = !empty($general_settings) ? $general_settings : array();
            $box_label = get_option('wccp_add_to_box_label');
            $box_label = !empty($box_label) ? esc_html__($box_label, 'wc-cp') : esc_html__('Add to Box', 'wc-cp');
            $products_range = !empty($boxes_data[$box_index]['wccp_box_min_range']) ? $boxes_data[$box_index]['wccp_box_min_range'] : 1;
            // echo "check2:" . $boxes_data;

            $rageproductsforcustum = $products_range;
            ob_start();
            $args = array(
                'post_type'      => array('product'),
                'posts_per_page' => -1,
                'fields'         => 'ids',
                'tax_query'      => array(
                    array(
                        'taxonomy' => 'product_cat',
                        'field'    => 'term_id',
                        'terms'    => $boxes_categories,
                    )
                )
            );
            $cat_products_ids = get_posts($args);
            $all_products = array_merge($cat_products_ids, $boxes_products);
            $settings = !empty(get_option('wccp_composite_products_settings')) ? get_option('wccp_composite_products_settings') : array();
            $enable_variable_prods = $settings['enable_variable_product'];
            if ('yes' == $enable_variable_prods) {
                foreach ($all_products as $all_product) {
                    $prod_data = wc_get_product($all_product);
                    if ('variable' == $prod_data->get_type()) {
                        $variations_ids = $prod_data->get_children();
                        $all_products = array_merge($all_products, $variations_ids);
                    }
                }
            }
            $args = array(
                'post_type'         => array('product', 'product_variation'),
                'post__in'         => $all_products,
                'posts_per_page' => esc_attr($prod_per_page),
                'paged'             => esc_attr($paged),
                'orderby' => 'title',
                'order' => 'ASC',
                'tax_query'       => array(
                    'relation' => 'OR',
                    array(
                        'taxonomy' => 'product_type',
                        'field'    => 'slug',
                        'terms'    => array('simple'),
                        'operator' => 'IN',
                    ),
                    array(
                        'taxonomy' => 'product_type',
                        'operator' => 'NOT EXISTS',
                    )
                )
            );
            $products = new WP_Query($args);
            if (!empty($products)) {
                $count = 0;
                while ($products->have_posts()) {
                    $count++;
                    $products->the_post();

                    $_product = wc_get_product(get_the_ID());
                    $terms = wp_get_post_terms($_product->get_id(), 'product_cat');

                    // Check if the product has any categories
                    if (!empty($terms) && !is_wp_error($terms)) {
                        $categories = array();

                        // Loop through each term and get the name
                        foreach ($terms as $term) {
                            $categories[] = $term->name;
                        }

                        // Display the category names
                        $cat = implode(', ', $categories);
                    }
                    echo '<div class="wccp-col-' . esc_attr($products_columns) . ' wccp_item" data-product-category="' . $cat . '" data-box_index=' . esc_attr($box_index) . ' data-product_id="' . esc_attr($_product->get_id()) . '">';
                    echo '<a href="' . esc_url(get_permalink($_product->get_id())) . '" target="_blank">';
                    echo '<figure>' . $_product->get_image() . '</figure>';
                    echo '<p class="woocommerce-loop-product__title">' . esc_html($_product->get_name()) . '</p>';
                    echo '</a>';
                    if ('yes' == $show_price) {
                        echo '<p>' . wp_kses_post($_product->get_price_html()) . '</p>';
                    }
                    if ($_product->is_purchasable() && $_product->is_in_stock()) {
                        $disable = '';
                        $box_name = $box_label;
                    } else {
                        $disable = 'disabled';
                        $box_name = esc_html__('Out of stock', 'wc-cp');
                    }
                    $data = wp_json_encode(array('product_id' => $_product->get_id(), 'box_product_price' => $_product->get_price(), 'prod_stock' => $_product->get_max_purchase_quantity()));
                    if ($rageproductsforcustum == 1) {
                        echo '<span class="round">
                      <input type="radio" name="radioGroup" id="radio' . $count . '" value="" data-prod_data="' . wc_esc_json($data) . '" data-product_id="' . esc_attr($_product->get_id()) . '" /><label for="radio' . $count . '" ></label></span>';
                    } else {
                        // echo '<span class="round"><input type="checkbox" class="checkbox" /><label for="checkbox"></label></span>' .$rageproductsforcustum;
                        echo '<button class="button wccp_add_to_box" data-prod_data="' . wc_esc_json($data) . '" ' . esc_attr($disable) . '>' . esc_html($box_name) . '</button>';
                        echo '<span class="quantity">
                        <input type="number" class="input-box" value="0" min="0" max="" ><span>
                                <button class="minus" aria-label="Decrease" data-product_id="' . esc_attr($_product->get_id()) . '">&minus;</button>
                                <button class="plus" aria-label="Increase" data-prod_data="' . wc_esc_json($data) . '" data-product_id="' . esc_attr($_product->get_id()) . '">&plus;</button></span></span>';
                    }
                    echo '</div>';
                }
                wp_reset_postdata();
            }
            $html = ob_get_clean();
            $data = array('html' => $html, 'pages' => $products->max_num_pages);
            wp_send_json_success($data);
        }
        public static function wccp_get_review_products()
        {
            check_ajax_referer('wccp_composite_products', 'security');
            if (empty($_POST['products_ids'])) {
                wp_send_json_error(esc_html__('No products for review', 'wc-cp'));
            }
            $parent_prod_id = !empty($_POST['parent_prod_id']) ? $_POST['parent_prod_id'] : 0;
            $products_ids = wc_clean($_POST['products_ids']);
            $general_options = get_option('wccp_composite_products_settings');
            $general_options = !empty($general_options) ? $general_options : array();
            $cart_button_label = get_option('wccp_add_to_cart_label');
            $cart_button_label = !empty($cart_button_label) ? esc_html__($cart_button_label, 'wc-cp') : esc_html__('Add to cart', 'wc-cp');
            $products_ids = explode(',', $products_ids);
            ob_start();
            echo '<div class="wccp_review_products_layout">';
            $review_label = get_option('wccp_review_products_label');
            $review_label = !empty($review_label) ? esc_html__($review_label, 'wc-cp') : esc_html__('Review Products', 'wc-cp');
            echo '<h3>' . esc_html($review_label) . '</h3>';
            echo '<div class="wccp-row">';
            foreach ($products_ids as $product_id) {
                $_product = wc_get_product($product_id);
                echo '<div class="wccp-col-4">';
                echo '<a href="' . esc_url(get_permalink($_product->get_id())) . '" target="_blank">';
                echo $_product->get_image();
                echo '<p class="woocommerce-loop-product__title">' . esc_html($_product->get_name()) . '</p>';
                echo '</a>';
                echo '<p>' . wp_kses_post($_product->get_price_html()) . '</p>';
                echo '</div>';
            }
            echo '</div>
			</div>';
            $html = ob_get_clean();
            wp_send_json_success($html);
        }
    }
    new WCCP_Composite_Products_Loop();
}
