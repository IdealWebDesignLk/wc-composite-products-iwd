<?php
if ( !defined( 'ABSPATH' ) ) {
	wp_die();
}
if ( !class_exists('WCCP_Composite_Products_Admin') ) {
	class WCCP_Composite_Products_Admin{
		public function __construct() {
			add_action('admin_enqueue_scripts', array(__CLASS__, 'wccp_admin_enqueue'));
			add_action('admin_menu', array(__CLASS__, 'wccp_composite_products_menu'));
			add_action('admin_init', array(__CLASS__, 'wccp_composite_products_menu_settings'));
			add_filter('product_type_selector', array(__CLASS__, 'wccp_select_product_type'));
			add_filter('woocommerce_product_data_tabs', array(__CLASS__, 'wccp_composite_products_tabs'), 10, 1);
			add_action('woocommerce_product_data_panels', array(__CLASS__, 'wccp_products_data_panel'));
			add_filter('woocommerce_json_search_found_products', array(__CLASS__, 'wccp_search_found_products'), 10, 1);
			add_filter('woocommerce_product_pre_search_products', array(__CLASS__, 'wccp_disable_search_products'), 10, 1);
			add_filter('woocommerce_json_search_limit', array(__CLASS__, 'wccp_disable_search_products'), 10, 1);
			add_action('woocommerce_process_product_meta_wccp_composite_product', array(__CLASS__, 'wccp_save_boxs_data'));
			add_filter('woocommerce_screen_ids', array(__CLASS__, 'wccp_screen_ids'), 10 , 1);
		}
		public static function wccp_admin_enqueue() {
			global $post_type;
			if ( 'product' == $post_type || ( isset($_GET['page']) && 'wccp-composite-products' == $_GET['page']) ) {
				wp_enqueue_style('wp-color-picker');
				wp_enqueue_script('wp-color-picker');
				wp_enqueue_script('wccp-admin-script', WP_CP_URL . 'assets/js/backend_scripts.js', array('jquery'), '1.0.6');
				wp_localize_script('wccp-admin-script', 'wccp_composite_steps', array(
					'ajaxurl'		     => admin_url('admin-ajax.php'),
					'confirmation_alert' => esc_html__('Are you sure to delete the step', 'wc-cp'),
					'delete_box'	     => esc_html__('Can\'t delete the only step', 'wc-cp'),
					'boxes_step_label'	 => esc_html__('Box#', 'wc-cp'),
					)
				);
			}
		}
		public static function wccp_screen_ids( $screen_ids ) {
			$screen_ids = array_merge($screen_ids, array('toplevel_page_wccp-composite-products') );
			return $screen_ids;
		}
		public static function wccp_composite_products_menu() {
			add_menu_page( esc_html__('Box Products', 'wc-cp'), esc_html__('Box Products', 'wc-cp'), 'manage_options', 'wccp-composite-products', array(__CLASS__, 'wccp_composite_products_settings_page' ), 'dashicons-products', 57 );
			add_submenu_page( 'wccp-composite-products', esc_html__('Settings', 'wc-cp'), esc_html__('Settings', 'wc-cp'), 'manage_options', 'wccp-composite-products', array(__CLASS__, 'wccp_composite_products_settings_page') );
		}
		public static function wccp_composite_products_settings_page() {
			require_once WP_CP_DIR . 'include/wccp-settings.php';
		}
		public static function wccp_composite_products_menu_settings() {
			register_setting('wccp_composite_products_settings', 'wccp_composite_products_settings');
			register_setting('wccp_label_settings', 'wccp_next_button_label');
			register_setting('wccp_label_settings', 'wccp_add_to_box_label');
			register_setting('wccp_label_settings', 'wccp_add_to_cart_label');
			register_setting('wccp_label_settings', 'wccp_reset_button_label');
			register_setting('wccp_label_settings', 'wccp_prev_button_label');
			register_setting('wccp_label_settings', 'wccp_discount_label');
			register_setting('wccp_label_settings', 'wccp_price_label');
			register_setting('wccp_label_settings', 'wccp_edit_products_label');
			register_setting('wccp_label_settings', 'wccp_cart_message_label');
			register_setting('wccp_label_settings', 'wccp_load_more_button_label');
			register_setting('wccp_label_settings', 'wccp_review_products_label');
			register_setting('wccp_label_settings', 'wccp_stock_warning_text');
			register_setting('wccp_label_settings', 'wccp_box_completion_warning');
		}
		public static function wccp_select_product_type( $types ) {
			$types[ 'wccp_composite_product' ] = esc_html__('Composite Product', 'wc-cp');
			return $types;
		}
		public static function wccp_composite_products_tabs ( $tabs ) {
			$tabs['wccp_composite_product'] = array(
				'label'  => esc_html__('Products Boxes', 'wc-cp'),
				'target' => 'wccp_composite_product_options',
				'class'  => 'show_if_wccp_composite_product',
			);
			$tabs['wccp_display_settings'] = array(
				'label'  => esc_html__('Color Settings', 'wc-cp'),
				'target' => 'wccp_display_settings',
				'class'  => 'show_if_wccp_composite_product',
			);
			return $tabs;
		}
		public static function wccp_products_data_panel() {
			global $post;
			$post_id = $post->ID; ?>
			<div id='wccp_composite_product_options' class='panel woocommerce_options_panel wc-metaboxes-wrapper wccp_composite_product_options'>
			<?php 
			wp_nonce_field('wccp_composite_product_options_nonce', 'wccp_composite_product_options_nonce');
			$composite_product_options = get_post_meta( $post_id, 'wccp_composite_product_options', true);
			$composite_product_options = !empty($composite_product_options) ? $composite_product_options : array();
			$pricing_type = !empty($composite_product_options['box_pricing_type']) ? $composite_product_options['box_pricing_type'] : 'per_product_box';
			woocommerce_wp_select(array(
				'id'          => 'wccp_composite_product_options[box_pricing_type]',
				'label'       => esc_html__('Pricing Type', 'wc-cp'),
				'description' => esc_html__('Select composite box products pricing type', 'wc-cp'),
				'desc_tip'    => true,
				'value'       => esc_attr($pricing_type),
				'options'     => array(
					'per_product_box'  => esc_html__('Product Addons Price + Regular Price', 'wc-cp'),
					'fixed_pricing'    => esc_html__('Fixed Regular Price', 'wc-cp'),
					'per_product_only' => esc_html__('Product Addons Price Only', 'wc-cp'),
				),
			));
			$boxes_columns = !empty($composite_product_options['boxes_columns']) ? $composite_product_options['boxes_columns'] : '3';
			woocommerce_wp_select(array(
				'id'          => 'wccp_composite_product_options[boxes_columns]',
				'label'       => esc_html__( 'Box Layout Columns', 'wc-cp' ),
				'desc_tip'    => true,
				'description' => esc_html__('Set the composite products boxes layout columns', 'wc-cp'),
				'value'       => esc_attr($boxes_columns),
				'options'     => array(
					'3' => 3,
					'4' => 4,
					'5'	=> 5,
					'6' => 6,
				)
			));
			$product_columns = !empty($composite_product_options['products_columns']) ? $composite_product_options['products_columns'] : '3';
			woocommerce_wp_select(array(
				'id'          => 'wccp_composite_product_options[products_columns]',
				'label'       => esc_html__('Products Layout Columns', 'wc-cp'),
				'desc_tip'    => true,
				'description' => esc_html__( 'Set the composite products layout columns', 'wc-cp'),
				'value'       => esc_attr($product_columns),
				'options'     => array(
					'3' => 3,
					'4'	=> 4,
					'5'	=> 5,
					'6'	=> 6,
				)
			));
			$prod_per_page = !empty($composite_product_options['products_per_page']) ? $composite_product_options['products_per_page'] : '3';
			woocommerce_wp_text_input( array(
				'id' 		  => 'wccp_composite_product_options[products_per_page]',
				'label' 	  => esc_html__('Show Products Per Page', 'wc-cp'),
				'desc_tip'    => true,
				'description' => esc_html__('Should be equal or greater than product layout columns', 'wc-cp'),
				'type' 		  => 'number',
				'value'		  => esc_attr($prod_per_page),
			));
			$enable_price = !empty($composite_product_options['enable_product_price']) ? $composite_product_options['enable_product_price'] : '';
			woocommerce_wp_checkbox(array(
				'id'    => 'wccp_composite_product_options[enable_product_price]',
				'label' => esc_html__('Show Price', 'wc-cp'),
				'value' => esc_attr($enable_price),
				'desc_tip'    => true,
				'description' => esc_html__('Show addon price', 'wc-cp'),
			));
			$product_discount_type = !empty($composite_product_options['product_discount_type']) ? $composite_product_options['product_discount_type'] : '3';
			woocommerce_wp_select(array(
				'id'          => 'wccp_composite_product_options[product_discount_type]',
				'label'       => esc_html__( 'Discount Type', 'wc-cp' ),
				'desc_tip'    => true,
				'description' => esc_html__('Set bundle products discount.', 'wc-cp'),
				'value'       => esc_attr($product_discount_type),
				'options'     => array(
					'fixed' => esc_html__('Fixed', 'wc-cp'),
					'percentage' => esc_html__('Percentage', 'wc-cp'),
				)
			));
			$products_discount = !empty($composite_product_options['products_discount']) ? $composite_product_options['products_discount'] : '';
			woocommerce_wp_text_input(array(
				'id' 		  => 'wccp_composite_product_options[products_discount]',
				'label' 	  => esc_html__('Discount', 'wc-cp'),
				'desc_tip'    => true,
				'description' => esc_html__('Set bundle products discount.', 'wc-cp'),
				'type' 		  => 'number',
				'value'		  => esc_attr($products_discount),
			));
			$enable_message = !empty($composite_product_options['enable_cart_message']) ? $composite_product_options['enable_cart_message'] : '';
			woocommerce_wp_checkbox(array(
				'id'    => 'wccp_composite_product_options[enable_cart_message]',
				'label' => esc_html__('Enable Cart Message', 'wc-cp'),
				'value' => esc_attr($enable_message),
				'desc_tip'    => true,
				'description' => esc_html__('Enable cart page message', 'wc-cp'),
			)); ?>
			<div class="toolbar toolbar-top">
				<a class="button wccp_add_box"><?php esc_html_e('Add New Box', 'wc-cp'); ?></a>
			</div>
			<div class="wc-metaboxes ui-sortable wccp_box_section" data-product_id=<?php echo esc_attr($post_id); ?>>
				<?php
				$boxes_data = get_post_meta( $post_id, 'wccp_composite_boxes_data', true);
				$boxes_data = !empty($boxes_data) ? $boxes_data : array('');
				for ( $i = 0; $i < count($boxes_data); $i++ ) { ?>
				<div class="wc-metabox closed wccp_composite_boxes">
					<h3 class="">
						<a href="#" class="remove_variation delete dashicons dashicons-trash"></a>
						<div class="tips sort ui-sortable-handle"></div>
						<div class="handlediv"></div>
						<span class="wccp_box_serial"><?php esc_html_e('Box#' . ($i + 1), 'wc-cp'); ?></span>
					</h3>
					<div style="display:none;" class="wc-metabox-content">
						<?php
						$box_name = !empty($boxes_data[$i]['wccp_box_name']) ? $boxes_data[$i]['wccp_box_name'] : '';
						woocommerce_wp_text_input(array(
							'id' 	   => 'wccp_box_name[]',
							'class'	   => 'wccp_box_name',
							'label'    => esc_html__('Box Name', 'wc-cp'),
							'desc_tip' => true,
							'value'    => esc_attr($box_name),
						) );
						$box_title = !empty( $boxes_data[$i]['wccp_box_title'] ) ? $boxes_data[$i]['wccp_box_title'] : '';
						woocommerce_wp_text_input(array(
							'id' 	   => 'wccp_box_title[]',
							'class'	   => 'wccp_box_title regular-text',
							'label'    => esc_html__('Box Title', 'wc-cp'),
							'desc_tip' => true,
							'value'    => esc_attr( $box_title ),
						));
						$no_of_boxes = !empty($boxes_data[$i]['wccp_no_of_boxes']) ? $boxes_data[$i]['wccp_no_of_boxes'] : 4;
						woocommerce_wp_text_input(array(
							'id' 	   => 'wccp_no_of_boxes[]',
							'class'	   => 'wccp_no_of_boxes regular-text',
							'label'    => esc_html__('No of Composite Product Boxes', 'wc-cp'),
							'desc_tip' => true,
							'type'     => 'number',
							'value'    => esc_attr($no_of_boxes),
						));
						$box_min_range = !empty($boxes_data[$i]['wccp_box_min_range']) ? $boxes_data[$i]['wccp_box_min_range'] : 4;
						woocommerce_wp_text_input(array(
							'id' 	   => 'wccp_box_min_range[]',
							'class'	   => 'wccp_box_min_range regular-text',
							'label'    => esc_html__('Add minimum Products to Boxes', 'wc-cp' ),
							'desc_tip' => true,
							'type'     => 'number',
							'value'    => esc_attr($box_min_range),
						));
						$box_desc = !empty($boxes_data[$i]['wccp_box_description']) ? $boxes_data[$i]['wccp_box_description'] : '';
						woocommerce_wp_textarea_input(array(
							'id' 	   => 'wccp_box_description[]',
							'class'	   => 'wccp_box_description',
							'label'    => esc_html__('Box Description', 'wc-cp'),
							'desc_tip' => true,
							'value'    => esc_attr($box_desc),
						));
						$items = !empty( $boxes_data[$i]['wccp_box_products'] ) ? $boxes_data[$i]['wccp_box_products'] : array('');
						$options = self::wccp_composite_items($items);
						woocommerce_wp_select(
							array(
							 'id'                => 'wccp_box_products[' . esc_attr($i) . '][]',
							 'label'             => esc_html__('Choose Products', 'wc-cp'),
							 'style'			 => 'width: 50%;',
							 'desc_tip'    	     => true,
							 'description'       => esc_html__('Select products for add to box.', 'wc-cp'),
							 'options'           => $options,
							 'value'             => $items,
							 'class'	         => 'wc-product-search wccp_box_products',
							 'custom_attributes' =>	array(
								  'data-placeholder' => esc_html__('Select Products', 'wc-cp'),
								  'data-action'	     =>	'woocommerce_json_search_products_and_variations',
								  'data-exclude'	 =>	'wccp_selectwoo',
								  'multiple'	     =>	'multiple'
							  )
						  ));
						$items = !empty( $boxes_data[$i]['wccp_box_categories'] ) ? $boxes_data[$i]['wccp_box_categories'] : array();
						$product_cat = get_terms(array('taxonomy' 	 => 'product_cat', 'hide_empty' => true));
						$selected_cat = array();
						$terms = array();
						foreach ( $product_cat as $term ) {
							$terms[$term->term_id] = $term->name;
						}
						woocommerce_wp_select(
							array(
							 'id'                => 'wccp_box_categories[' . esc_attr($i) . '][]',
							 'label'             => esc_html__('Choose Categories', 'wc-cp' ),
							 'style'			 => 'width: 50%;',
							 'desc_tip'    	     => true,
							 'description'       => esc_html__('Select product categories for displaying its products.', 'wc-cp'),
							 'options'           => $terms,
							 'value'             => $items,
							 'class'	         => 'wc-enhanced-select wccp_select_categories',
							 'custom_attributes' =>	array(
								  'data-placeholder' => esc_html__('Select categories', 'wc-cp'),
								  'multiple'	     =>	'multiple'
							  )
						  )); ?>						
						</div>
					</div><?php }?>
				</div>
			</div>
			<div id = 'wccp_display_settings' class='panel woocommerce_options_panel wc-metaboxes-wrapper wccp_display_settings'>
				<?php
				$value = !empty($composite_product_options['prod_boxes_bg_color']) ? $composite_product_options['prod_boxes_bg_color'] : '#999';
				woocommerce_wp_text_input( array(
					'id' 		  => 'wccp_composite_product_options[prod_boxes_bg_color]',
					'label' 	  => esc_html__('Product Boxes Background Color', 'wc-cp'),
					'value'		  => esc_attr($value),
					'class' => 'wccp_color_picker',
				));
				$value = !empty($composite_product_options['prod_boxes_border_color']) ? $composite_product_options['prod_boxes_border_color'] : '#fff';
				woocommerce_wp_text_input( array(
					'id' 		  => 'wccp_composite_product_options[prod_boxes_border_color]',
					'label' 	  => esc_html__('Product Boxes Border Color', 'wc-cp'),
					'value'		  => esc_attr($value),
					'class' => 'wccp_color_picker',
				));
			$value = !empty($composite_product_options['prod_boxes_text_color']) ? $composite_product_options['prod_boxes_text_color'] : '#fff';
				woocommerce_wp_text_input( array(
					'id' 		  => 'wccp_composite_product_options[prod_boxes_text_color]',
					'label' 	  => esc_html__('Product Boxes Text Color', 'wc-cp'),
					'value'		  => esc_attr($value),
					'class' => 'wccp_color_picker',
				));
			?>
			</div>	
			<?php
		}
		public static function wccp_composite_items( $selected_products ) {
			$options = array();
			if ( !empty( $selected_products ) ) {
				foreach ( $selected_products as $item ) {
					$_prod = wc_get_product( $item );
					if ( !is_wp_error( $_prod ) && is_object( $_prod ) )
					$options[$item] = sprintf('%s %s', $_prod->get_name(), $_prod->get_id());
				}
			}
			return $options;
		}
		public static function wccp_search_found_products( $products ) {
			if ( !empty($_GET['exclude']) && $_GET['exclude'] == 'wccp_selectwoo' && !empty($_GET['term']) ) {
				$args = array(
					'post_type'      => array('product','product_variation'),
					'post_status'    => 'publish',
					'posts_per_page' => -1,
					's'	             =>	wp_unslash( $_GET['term'] ),
					'tax_query'      => array(
					   array(
						   'taxonomy' => 'product_type',
						   'field'    => 'slug',
						   'terms'    => array( 'grouped', 'subscription', 'variable-subscription', 'wccp_composite_product'),
						   'operator' => 'NOT IN'
					   )
					)    
				);
				$args = apply_filters('wccp_composite_items_search_args', $args);
				$arr = array();
				$_products = new WP_Query( $args );
				if ( $_products->have_posts() ) {
					while ( $_products->have_posts() ) {
						$_products->the_post();
						$product_id=get_the_ID();
						$arr[$product_id] = sprintf('%s %s', get_the_title(), $product_id);
					}
				}
				$products = $arr;
			}
			return $products;
		}
		public static function wccp_disable_search_products( $status ) {
			if ( !empty($_GET['exclude']) && $_GET['exclude'] == 'wccp_selectwoo' && !empty($_GET['term']) ) {
				$status = 0;
			}
			return $status;
		}
		public static function wccp_save_boxs_data( $post_id ) {
			if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
				return;
			}
			if ( !isset( $_POST['wccp_composite_product_options_nonce'] ) || !wp_verify_nonce( $_POST['wccp_composite_product_options_nonce'], 'wccp_composite_product_options_nonce') ) {
				return;
			}
			if ( !current_user_can('edit_post', $post_id) ) {
				return;
			}
			if ( isset( $_POST['wccp_composite_product_options'] ) ) {
				update_post_meta($post_id, 'wccp_composite_product_options', wc_clean($_POST['wccp_composite_product_options']));
			} else {
				update_post_meta($post_id, 'wccp_composite_product_options', array(''));
			}
			$box_data = array();
			for ( $i = 0; $i < count($_POST['wccp_box_name']); $i++ ) {
				if ( !empty($_POST['wccp_box_products'][$i]) || !empty($_POST['wccp_box_categories'][$i]) ) {
					$box_data[$i]['wccp_box_name'] = !empty($_POST['wccp_box_name'][$i]) ? wc_clean($_POST['wccp_box_name'][$i]) : '';
					$box_data[$i]['wccp_box_title'] = !empty($_POST['wccp_box_title'][$i]) ? wc_clean($_POST['wccp_box_title'][$i]) : '';
					$box_data[$i]['wccp_no_of_boxes'] = !empty($_POST['wccp_no_of_boxes'][$i]) ? wc_clean($_POST['wccp_no_of_boxes'][$i]) : '';
					$box_data[$i]['wccp_box_min_range'] = !empty($_POST['wccp_box_min_range'][$i]) ? wc_clean($_POST['wccp_box_min_range'][$i]) : '';
					if ( $box_data[$i]['wccp_no_of_boxes'] < $box_data[$i]['wccp_box_min_range'] ) {
						$box_data[$i]['wccp_box_min_range'] = '';
					}
					$box_data[$i]['wccp_box_description'] = !empty($_POST['wccp_box_description'][$i]) ? wc_clean($_POST['wccp_box_description'][$i]) : '';
					$box_data[$i]['wccp_box_products'] = wc_clean($_POST['wccp_box_products'][$i]);
					$box_data[$i]['wccp_box_categories'] =  wc_clean($_POST['wccp_box_categories'][$i]);
				}
			}
			if ( isset($_POST['wccp_box_name']) ) {
				update_post_meta($post_id, 'wccp_composite_boxes_data', $box_data);
			} else {
				update_post_meta($post_id, 'wccp_composite_boxes_data', array(''));
			}
		}
	}
	new WCCP_Composite_Products_Admin();
}