<?php
if ( !defined('ABSPATH') ) {
	wp_die(); 
}
if( !class_exists('WCCP_Composite_Products_Settings') ) {
	class WCCP_Composite_Products_Settings {
		public function __construct() {
			$current = isset( $_GET['tab']) ? $_GET['tab'] : 'basic-settings';
			$tabs = array(
			'basic-settings' => esc_html__('General', 'wc-cp'),
			'label-settings' => esc_html__('Labels', 'wc-cp'),
			);
			self::wccp_composite_products_init_tabs(apply_filters('wccp_setting_tabs', $tabs));
			self::wccp_composite_products_current_tab(apply_filters('wccp_current_setting_tab', $current));
		}
		public static function wccp_register_settings() {
            register_setting('wccp_composite_products_settings', 'wccp_composite_products_settings');
        }
		public static function wccp_composite_products_init_tabs( $tabs=array() ) {
			$current = isset($_GET['tab']) ? $_GET['tab'] : 'basic-settings';
			echo '<h5 class="nav-tab-wrapper">';
			foreach ( $tabs as $tab => $name ) {
			$class = ($tab == $current) ? 'nav-tab-active' : '';
			printf('<a class="nav-tab %s" href="%sadmin.php?page=wccp-composite-products&tab=%s">%s</a>', esc_attr($class), admin_url(), esc_attr($tab), esc_html($name));
			}
			echo '</h5>';
		}
		public static function wccp_composite_products_current_tab( $current = 'basic-settings' ) {
			switch( $current ) {
				case 'basic-settings':
					self::wccp_composite_products_settings();
					break;
				case 'label-settings':
					self::wccp_label_settings();
					break;
				default:
					self::wccp_composite_products_settings();
					break;
				}
		}
		public static function wccp_composite_products_settings() { ?>
			<form method="post" action="options.php">
				<?php settings_errors();
				settings_fields('wccp_composite_products_settings');
				do_settings_sections('wccp_composite_products_settings');
				$settings = get_option('wccp_composite_products_settings'); ?>
				<h3><?php esc_html_e('Box Products Basic Settings', 'wc-cp'); ?></h3>
				<table class="form-table">
					<tr>
						<?php $enable_scroll_up = !empty($settings['enable_scroll_top']) ? $settings['enable_scroll_top'] : ''; ?>
						<th><label for="enable_scroll_top"><?php esc_html_e('Enable Scroll Top', 'wc-cp'); ?></label></th>
						<td><label><input type="checkbox" name="wccp_composite_products_settings[enable_scroll_top]" class="regular-text" id="enable_scroll_top" value="yes" <?php checked($enable_scroll_up,'yes'); ?> />
							<span><?php esc_html_e('Enable this option to scroll up on add item to box', 'wc-cp'); ?></span></label>
						</td>
					</tr>
					<tr>
						<?php $box_item_click = !empty($settings['box_item_click']) ? $settings['box_item_click'] : ''; ?>
						<th><label for="enable_scroll_top"><?php esc_html_e('On Click Box Items Feature', 'wc-cp'); ?></label></th>
						<td>
							<p>
								<label><input type="radio" name="wccp_composite_products_settings[box_item_click]" class="regular-text" value="redirect" <?php checked($box_item_click, 'redirect'); ?> /> <i><?php esc_html_e('Redirect to product item\'s single product page on click of product item\'s image or title link.', 'wc-cp'); ?></i></label>
							</p>
							<p>
								<label><input type="radio" name="wccp_composite_products_settings[box_item_click]" class="regular-text" value="quickview" <?php checked($box_item_click, 'quickview'); ?> /> <i><?php esc_html_e('Open quick view popup on click of product item\'s image or title link.', 'wc-cp'); ?></i></label>
							</p>
							<p>
								<label><input type="radio" name="wccp_composite_products_settings[box_item_click]" class="regular-text" value="noaction" <?php checked($box_item_click, 'noaction'); ?> /> <i><?php esc_html_e('Disable any feature on click of product item\'s image or title link.', 'wc-cp'); ?></i></label>
							</p>
						</td>
					</tr>
					<tr>
					<?php $enable_variable_prod = !empty($settings['enable_variable_product']) ? $settings['enable_variable_product'] : ''; ?>
						<th><label for="enable_variable_product"><?php esc_html_e('Enable Variable Products', 'wc-cp'); ?></label></th>
						<td><label><input type="checkbox" name="wccp_composite_products_settings[enable_variable_product]" class="regular-text" id="enable_variable_product" value="yes" <?php checked($enable_variable_prod,'yes'); ?> />
							<span><?php esc_html_e('Enable this option to allow variations of variable products', 'wc-cp'); ?></span></label>
						</td>
					</tr>
					<tr>
                        <?php $enable_prod_box = !empty($settings['enable_product_Box']) ? $settings['enable_product_Box'] : ''; ?>
                        <th><label for="enable_product_Box"><?php esc_html_e('Disable Product Box', 'wc-cp'); ?></label></th>
                        <td><label><input type="checkbox" name="wccp_composite_products_settings[enable_product_Box]" class="regular-text" id="enable_product_Box" value="yes" <?php checked($enable_prod_box, 'yes'); ?> />
                            <span><?php esc_html_e('Enable this option to hide the chosen product boxes from being displayed.', 'wc-cp'); ?></span></label>
                        </td>
                    </tr>
                    <tr>
                        <?php $enable_prod_review = !empty($settings['enable_product_review']) ? $settings['enable_product_review'] : ''; ?>
                        <th><label for="enable_product_review"><?php esc_html_e('Disable Product Review', 'wc-cp'); ?></label></th>
                        <td><label><input type="checkbox" name="wccp_composite_products_settings[enable_product_review]" class="regular-text" id="enable_product_review" value="yes" <?php checked($enable_prod_review, 'yes'); ?> />
                            <span><?php esc_html_e('Enable this option to disable product reviews before adding to the cart.', 'wc-cp'); ?></span></label>
                        </td>
                    </tr>
				</table>
				<?php submit_button(); ?>
			</form>
			<?php
		}
		public static function wccp_label_settings() {
			?>
			<form method="post" action="options.php">
				<?php settings_errors();
				settings_fields('wccp_label_settings');
				do_settings_sections('wccp_label_settings');
				?>
				<h3><?php esc_html_e('Box Products Basic Settings', 'wc-cp'); ?></h3>
				<table class="form-table">
					<tr>
						<?php $value = get_option('wccp_add_to_cart_label'); ?>
						<th><label for="wccp_add_to_cart_label"><?php esc_html_e('Cart Button Label', 'wc-cp'); ?></label></th>
						<td><input type="text" name="wccp_add_to_cart_label" class="regular-text" id="wccp_add_to_cart_label" value="<?php echo esc_attr($value); ?>" /></td>
					</tr>
					<tr>
						<?php $value = get_option('wccp_add_to_box_label'); ?>
						<th><label for="wccp_add_to_box_label"><?php esc_html_e('Add to Box Button Label', 'wc-cp'); ?></label></th>
						<td><input type="text" name="wccp_add_to_box_label" class="regular-text" id="wccp_add_to_box_label" value="<?php echo esc_attr($value); ?>" /></td>
					</tr>
					<tr>
						<?php $value = get_option('wccp_next_button_label'); ?>
						<th><label for="wccp_next_button_label"><?php esc_html_e('Next Button Label', 'wc-cp'); ?></label></th>
						<td><input type="text" name="wccp_next_button_label" class="regular-text" id="wccp_next_button_label" value="<?php echo esc_attr($value); ?>" /></td>
					</tr>
					<tr>
						<?php $value = get_option('wccp_reset_button_label'); ?>
						<th><label for="wccp_reset_button_label"><?php esc_html_e('Reset Button Label', 'wc-cp'); ?></label></th>
						<td><input type="text" name="wccp_reset_button_label" class="regular-text" id="wccp_reset_button_label" value="<?php echo esc_attr($value); ?>" /></td>
					</tr>
					<tr>
						<?php $value = get_option('wccp_prev_button_label'); ?>
						<th><label for="wccp_prev_button_label"><?php esc_html_e('Previous Button Label', 'wc-cp'); ?></label></th>
						<td><input type="text" name="wccp_prev_button_label" class="regular-text" id="wccp_prev_button_label" value="<?php echo esc_attr($value); ?>" /></td>
					</tr>
					<tr>
						<?php $value = get_option('wccp_discount_label'); ?>
						<th><label for="wccp_discount_label"><?php esc_html_e('Discount Label', 'wc-cp'); ?></label></th>
						<td><input type="text" name="wccp_discount_label" class="regular-text" id="wccp_discount_label" value="<?php echo esc_attr($value); ?>" /></td>
					</tr>
					<tr>
						<?php $value = get_option('wccp_price_label'); ?>
						<th><label for="wccp_price_label"><?php esc_html_e('Price Label', 'wc-cp'); ?></label></th>
						<td><input type="text" name="wccp_price_label" class="regular-text" id="wccp_price_label" value="<?php echo esc_attr($value); ?>" /></td>
					</tr>
					<tr>
						<?php $value = get_option('wccp_edit_products_label'); ?>
						<th><label for="wccp_edit_products_label"><?php esc_html_e('Edit Products Label', 'wc-cp'); ?></label></th>
						<td><input type="text" name="wccp_edit_products_label" class="regular-text" id="wccp_edit_products_label" value="<?php echo esc_attr($value); ?>" /></td>
					</tr>
					<tr>
						<?php $value = get_option('wccp_cart_message_label'); ?>
						<th><label for="wccp_cart_message_label"><?php esc_html_e('Cart Message Label', 'wc-cp'); ?></label></th>
						<td><input type="text" name="wccp_cart_message_label" class="regular-text" id="wccp_cart_message_label" value="<?php echo esc_attr($value); ?>" /></td>
					</tr>
					<tr>
						<?php $value = get_option('wccp_load_more_button_label'); ?>
						<th><label for="wccp_load_more_button_label"><?php esc_html_e('Load More Button Label', 'wc-cp'); ?></label></th>
						<td><input type="text" name="wccp_load_more_button_label" class="regular-text" id="wccp_load_more_button_label" value="<?php echo esc_attr($value); ?>" /></td>
					</tr>
					<tr>
						<?php $value = get_option('wccp_review_products_label'); ?>
						<th><label for="wccp_review_products_label"><?php esc_html_e('Review Products Label', 'wc-cp'); ?></label></th>
						<td><input type="text" name="wccp_review_products_label" class="regular-text" id="wccp_review_products_label" value="<?php echo esc_attr($value); ?>" /></td>
					</tr>
					<tr>
						<?php $value = get_option('wccp_stock_warning_text'); ?>
						<th><label for="wccp_stock_warning_text"><?php esc_html_e('Stock Warning Text', 'wc-cp'); ?></label></th>
						<td><input type="text" name="wccp_stock_warning_text" class="regular-text" id="wccp_stock_warning_text" value="<?php echo esc_attr($value); ?>" /></td>
					</tr>
					<tr>
						<?php $value = get_option('wccp_box_completion_warning'); ?>
						<th><label for="wccp_box_completion_warning"><?php esc_html_e('Box Completion Warning Text', 'wc-cp'); ?></label></th>
						<td><input type="text" name="wccp_box_completion_warning" class="regular-text" id="wccp_box_completion_warning" value="<?php echo esc_attr($value); ?>" /></td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
			<?php
		}
	}
	new WCCP_Composite_Products_Settings();
}