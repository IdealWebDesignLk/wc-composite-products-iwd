<?php
if (!defined('ABSPATH')) {
	wp_die();
}
if (!class_exists('WCCP_Composite_Products_Frontend')) {
	class WCCP_Composite_Products_Frontend
	{
		public function __construct()
		{
			add_action('wp_enqueue_scripts', array(__CLASS__, 'wccp_frontend_enqueue'));
			add_filter('wc_get_template_part', array(__CLASS__, 'wccp_composite_product_replace_template'), 10, 3);
			add_action('wccp_composite_product_layout', array(__CLASS__, 'wccp_composite_box_layout'));
			add_action('wp_footer', array(__CLASS__, 'wccp_order_builder_boxes_view'));
			add_action('wp_head', array(__CLASS__, 'wccp_box_label'));
		}
		public static function wccp_box_label()
		{
			if (!is_product()) {
				return;
			}
			$product_info = get_post_meta(get_the_ID(), 'wccp_composite_product_options', true);

			$background = !empty($product_info['prod_boxes_bg_color']) ? $product_info['prod_boxes_bg_color'] : '#ccc';
			$border = !empty($product_info['prod_boxes_border_color']) ? $product_info['prod_boxes_border_color'] : '#ccc';
			$color = !empty($product_info['prod_boxes_text_color']) ? $product_info['prod_boxes_text_color'] : '#fff';
?>
			<style>
				.wccp_box_label {
					color: <?php echo $color; ?>;
				}

				.wccp-inner {
					border: 1px solid <?php echo $border; ?>;
					background: <?php echo $background; ?>;
				}
			</style>
			<?php }
		public static function wccp_frontend_enqueue()
		{
			if (is_product()) {
				$product = wc_get_product(get_the_ID());
				$product_info = get_post_meta($product->get_id(), 'wccp_composite_product_options', true);
				

				if ($product->is_type('wccp_composite_product')) {
					wp_enqueue_style('dashicons');
					wp_enqueue_style('slick', WP_CP_URL . 'assets/css/slick.css', '', '1.0.6');
					wp_enqueue_style('slick-theme', WP_CP_URL . 'assets/css/slick-theme.css', '', '1.0.6');
					wp_enqueue_style('wccp-frontend-style', WP_CP_URL . 'assets/css/frontend_styles.css', '', '1.0.6');
					wp_enqueue_script('slick', WP_CP_URL . 'assets/js/slick.min.js', array('jquery'), '1.0.6');
					wp_enqueue_script('wccp-frontend-script', WP_CP_URL . 'assets/js/frontend_scripts.js', array('jquery'), '1.0.6');
					$general_settings = get_option('wccp_composite_products_settings');
					$box_label = !empty($general_settings['add_to_box_label']) ? esc_html__($general_settings['add_to_box_label'], 'wc-cp') : esc_html__('Add to Box', 'wc-cp');
					$enable_scroll_up = !empty($general_settings['enable_scroll_top']) ? $general_settings['enable_scroll_top'] : '';
					$box_item_click = !empty($general_settings['box_item_click']) ? esc_html__($general_settings['box_item_click'], 'wc-cp') : 'redirect';
					wp_localize_script(
						'wccp-frontend-script',
						'wccp_composite_boxes',
						array(
							'ajaxurl'          => admin_url('admin-ajax.php'),
							'ajax_nonce'		=> wp_create_nonce('wccp_composite_products'),
							'product_price'    => $product->get_price(),
							'add_to_box'       => esc_html($box_label),
							'limit_reached'    => esc_html__('Stock Limit reached.', 'wc-cp'),
							'enable_scroll_up' => $enable_scroll_up,
							'currency_pos'     => esc_attr(get_option('woocommerce_currency_pos')),
							'thousand_sep'     => esc_attr(get_option('woocommerce_price_thousand_sep')),
							'decimal_sep'      => esc_attr(get_option('woocommerce_price_decimal_sep')),
							'no_of_decimal'    => esc_attr(get_option('woocommerce_price_num_decimals')),
							'box_item_click'   => $box_item_click,
							'currency_symbol'  => get_woocommerce_currency_symbol(),
							'box_text_color' => !empty($product_info['prod_boxes_text_color']) ? $product_info['prod_boxes_text_color'] : '#fff',
						)
					);
				}
			}
		}
		public static function wccp_order_builder_boxes_view()
		{
			if (is_product()) {
				$product = wc_get_product(get_the_ID());
				if (!$product->is_type('wccp_composite_product')) {
					return;
				} ?>
				<div class="wccp_overlay"></div>
				<div class="wccp_loader">
					<div></div>
					<div></div>
					<div></div>
					<div></div>
				</div>
				<div class="wccp_order_builder_boxes_layer"></div>
				<div class="wccp_order_builder_boxes_view" style="display: none;">
					<div class="wccp_order_builder_boxes_head"><span class="wccp_boxes_popup_close dashicons dashicons-no-alt"></span></div>
					<div class="wccp_order_builder_view_content">
					</div>
				</div>
			<?php
			}
		}
		public static function wccp_composite_product_replace_template($template, $slug, $name)
		{
			global $product;
			if (is_singular('product') && 'single-product' === $name && 'content' === $slug && $product->is_type('wccp_composite_product')) {
				$template = apply_filters('wccp_composite_step_product_template', WP_CP_DIR . 'templates/content-single-product.php');
			}
			return $template;
		}
		public static function wccp_composite_box_layout()
		{
			$product_id = get_the_ID();

			$boxes_data = get_post_meta($product_id, 'wccp_composite_boxes_data', true);
			$boxes_data = !empty($boxes_data) ? $boxes_data : array();
			$total_steps = count($boxes_data);
			$product = wc_get_product($product_id);
			$product_price = $product->get_price();
			$product_info = get_post_meta($product_id, 'wccp_composite_product_options', true);
			$product_info = !empty($product_info) ? $product_info : array();
			$general_settings = get_option('wccp_composite_products_settings');
			$cart_button_label = !empty($general_settings['add_to_cart_label']) ? esc_html__($general_settings['add_to_cart_label'], 'wc-cp') : esc_html__('Add to cart', 'wc-cp');
			$pricing_type = !empty($product_info['box_pricing_type']) ? $product_info['box_pricing_type'] : '';
			$discount_type = !empty($product_info['product_discount_type']) ? $product_info['product_discount_type'] : '';
			$discount_value = !empty($product_info['products_discount']) ? $product_info['products_discount'] : 0;
			$options = array('product_id' => $product_id, 'product_price' => $product_price, 'pricing_type' => $pricing_type, 'discount_type' => $discount_type, 'discount' => $discount_value);
			$options = json_encode($options);


			$settings = get_option('wccp_composite_products_settings');

			if (!empty($settings['enable_product_Box'])) {
				$enable_product_Box = $settings['enable_product_Box'];
				echo "<style>.wccp_boxes {display: none !important;} </style>";
			}

			if (!empty($settings['enable_product_review'])) {
				$enable_product_review = $settings['enable_product_review'];
				echo "<style>.wccp_reviews_buttons{display: none !important;} </style>";
			}
			?>
			<div class="wccp_order_builder_boxes">
				<?php if (function_exists('bcn_display')) {
					bcn_display();
				} ?>

				<h1 class="wccp_box_product_title" align="center"><?php esc_html_e(the_title(), 'wc-cp'); ?></h1>
				<div class="stepper-wrapper"></div>
				<div class="filter-toggle">Filter <span class="wd-tools-icon">
					</span></div>
				<div id="filter-panel">
					<h3>Filter <span class="filter-toggle close">X</span></h3><!-- Checkboxes will be dynamically added here -->
					<div class="ctpanel"></div>
				</div>

				<div class="wccp_boxes" data-options="<?php echo esc_attr($options); ?>">
					<div class="wccp-row wccp-boxes_rows"></div>
				</div>
				<div class="wccp_product_information">
					<span><?php esc_html_e('Price: ', 'wc-cp'); ?></span>
					<?php if ('per_product_only' == $pricing_type) {
						$product_price = 0;
					}
					$next_label = get_option('wccp_next_button_label');
					$next_label = !empty($next_label) ? esc_html__($next_label, 'wc-cp') : esc_html__('Next', 'wc-cp');
					$reset_label = get_option('wccp_reset_button_label');
					$reset_label = !empty($reset_label) ? esc_html__($reset_label, 'wc-cp') : esc_html__('Reset', 'wc-cp');
					$prev_label = get_option('wccp_prev_button_label');
					$prev_label = !empty($prev_label) ? esc_html__($prev_label, 'wc-cp') : esc_html__('Prev', 'wc-cp'); ?>
					<span class="wccp_show_price"><?php echo wc_price(($product_price)); ?></span>
					<button class="button wccp_next_button" data-next_step="0" data-total_steps="<?php echo esc_attr($total_steps); ?>"><?php echo esc_html($next_label); ?></button>
					<button class="button wccp_reset_button"><?php echo esc_html($reset_label); ?></button>
					<button class="button wccp_prev_button" data-prev_step="0"><?php echo esc_html($prev_label); ?></button>
				</div>
				<div class="wccp_reviews_buttons">
					<form class="cart" method="post" enctype="multipart/form-data" action="<?php the_permalink($product_id); ?>">
						<div class="wccp_final_price">
							<span class="button"><?php
													$price_label = get_option('wccp_price_label');
													$price_label = !empty($price_label) ? esc_html__($price_label, 'wc-cp') : esc_html__('Price', 'wc-cp');
													echo esc_html($price_label); ?></span>
							<span class="button wccp_show_price"></span>
							<?php
							if ('percentage' == $discount_type) {
								$discount = sprintf('%s %s', $discount_value, '%');
							} else {
								$discount = wc_price($product_info['products_discount']);
							}
							$discount_label = get_option('wccp_discount_label');
							$discount_label = !empty($discount_label) ? esc_html__($discount_label, 'wc-cp') : esc_html__('Discount', 'wc-cp');
							if ($discount_value > 0) { ?>
								<span class="button"><?php printf('%s %s', $discount, esc_html($discount_label)); ?></span>
								<span class="wccp_finall_price button"></span>
							<?php } ?>
						</div>
						<?php
						for ($i = 0; $i < $total_steps; $i++) { ?>
							<input type="hidden" name="wccp_added_prod_ids_<?php echo esc_attr($i); ?>" value="" />
						<?php } ?>
						<input type="hidden" name="wccp_added_products" id="wccp_added_products" value="" />
						<div class="wccp_review_buttons">
							<button class="button wccp_edit_review_products"><?php
																				$edit_products_label = get_option('wccp_edit_products_label');
																				$edit_products_label = !empty($edit_products_label) ? esc_html__($edit_products_label, 'wc-cp') : esc_html__('Edit Products', 'wc-cp');
																				echo esc_html($edit_products_label); ?></button>
							<button type="submit" class="button" name="add-to-cart" value="<?php echo esc_attr($product_id); ?>"><?php echo esc_html($cart_button_label); ?></button>
						</div>
						<?php if ($product_info['enable_cart_message']) {
							$message_label = get_option('wccp_cart_message_label');
							$message_label = !empty($message_label) ? esc_html__($message_label, 'wc-cp') : esc_html__('Any Message', 'wc-cp'); ?>
							<div class="wccp_cart_message">
								<label><?php echo esc_html($message_label); ?></label>
								<textarea name="wccp_cart_message"></textarea>
							</div>
						<?php } ?>
					</form>
				</div>
				<div class="wccp_all_prods_data">
					<h3 class="wccp_box_title" align="center"></h3>
					<p class="wccp_box_description" align="center"></p>
					<div class="wccp_boxes_products wccp-row"></div>
					<?php $load_more_label = get_option('wccp_load_more_button_label');
					$load_more_label = !empty($load_more_label) ? esc_html__($load_more_label, 'wc-cp') : esc_html__('load More', 'wc-cp'); ?>
					<button class="button wccp_load_more_products" data-paged="1"><?php echo esc_html($load_more_label); ?></button>
				</div>
			</div>
			<?php $stock_warning = get_option('wccp_stock_warning_text');
			$stock_warning = !empty($stock_warning) ? esc_html__($stock_warning, 'wc-cp') : esc_html__('Stock limit reached for this product!', 'wc-cp');
			$box_warning = get_option('wccp_box_completion_warning');
			$box_warning = !empty($box_warning) ? esc_html__($box_warning, 'wc-cp') : esc_html__('Boxes completed!', 'wc-cp'); ?>
			<div class="wccp_stock_alert"><?php echo esc_html($stock_warning); ?></div>
			<div class="wccp_boxes_alert"><?php echo esc_html($box_warning); ?></div>
<?php
		}
	}
	new WCCP_Composite_Products_Frontend();
}
