<?php
if( !defined('ABSPATH') ) {
	wp_die();
}
if( !class_exists ( 'WCCP_Add_to_Cart' ) ) {
	class WCCP_Add_to_Cart {
		public function __construct() {
			add_action('woocommerce_add_to_cart', array(__CLASS__, 'wccp_add_to_cart'), 10, 6);
			add_filter('woocommerce_add_cart_item_data', array(__CLASS__, 'wccp_add_cart_item_data'),10,2);
			add_filter('woocommerce_cart_item_remove_link', array(__CLASS__, 'wccp_cart_item_remove_link'), 10, 3);
			add_filter('woocommerce_cart_contents_count', array(__CLASS__, 'wccp_cart_contents_count'));
			add_action('woocommerce_after_cart_item_quantity_update', array(__CLASS__, 'wccp_product_update_cart_item_quantity'), 1, 2);
			add_action('woocommerce_before_cart_item_quantity_zero', array(__CLASS__, 'wccp_product_update_cart_item_quantity'), 1, 2);	
			add_action('woocommerce_cart_item_removed', array(__CLASS__, 'wccp_cart_item_removed'), 10, 2);
			add_filter('woocommerce_cart_item_quantity', array(__CLASS__, 'wccp_cart_item_quantity'), 1, 2);
			add_filter('woocommerce_cart_item_price', array(__CLASS__, 'wccp_cart_item_price'), 10, 3);
			add_filter('woocommerce_cart_item_subtotal', array(__CLASS__, 'wccp_cart_item_subtotal'), 10, 3);
			add_action('woocommerce_before_calculate_totals', array(__CLASS__, 'wccp_before_calculate_totals'), 99, 1);
			add_filter( 'woocommerce_get_item_data', array($this, 'wccp_cart_item_description'), 10, 2);
            add_action( 'woocommerce_checkout_create_order_line_item', array($this, 'wccp_order_items_description'), 20, 4);
		}
		public static function wccp_product_update_cart_item_quantity( $cart_item_key, $quantity = 0 ) {
			if ( !empty(WC()->cart->cart_contents[$cart_item_key]) && (isset( WC()->cart->cart_contents[$cart_item_key]['wccp_keys'])) ) {
				if ( $quantity <= 0 ) {
					$quantity = 0;
				} else {
					$quantity = WC()->cart->cart_contents[$cart_item_key]['quantity'];
				}
				foreach ( WC()->cart->cart_contents[$cart_item_key]['wccp_keys'] as $wccp_product_key ) {
					WC()->cart->set_quantity( $wccp_product_key, $quantity * ( WC()->cart->cart_contents[$wccp_product_key]['wccp_product_qty'] ? WC()->cart->cart_contents[$wccp_product_key]['wccp_product_qty'] : 1 ), false);
				}
			}
		}
		public static function wccp_add_to_cart( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
			if ( !isset($cart_item_data['wccp_added_products']) ) {
				return;
			}
			$items = explode(',', $cart_item_data['wccp_added_products'] );
			if ( is_array( $items ) && count($items) > 0 ) {
				$items = array_count_values( $items );
				foreach ( $items as $item => $qty ) {
					$wccp_item_id = $item;
					$wccp_item_variation_id = 0;
					$wccp_item_variation = array();
					$wccp_product = wc_get_product( $wccp_item_id );
					if ( $wccp_product ) {
						$wccp_product->set_price(0);
						$wccp_cart_id = WC()->cart->generate_cart_id($wccp_item_id, $wccp_item_variation_id, $wccp_item_variation, array(
								'wccp_parent_id'  => $product_id,
								'wccp_parent_key' => $cart_item_key,
								'wccp_product_qty' => $qty
						));
						$wccp_item_key = WC()->cart->find_product_in_cart( $wccp_cart_id);
						if ( !$wccp_item_key ) {
							$wccp_item_key = $wccp_cart_id;
							WC()->cart->cart_contents[ $wccp_item_key ] = array(
								'product_id'      => $wccp_item_id,
								'variation_id'    => $wccp_item_variation_id,
								'variation'       => $wccp_item_variation,
								'quantity'        => $qty,
								'data'            => $wccp_product,
								'wccp_parent_id'  => $product_id,
								'wccp_parent_key' => $cart_item_key,
								'wccp_product_qty'=> $qty
							);
						}
						WC()->cart->cart_contents[$cart_item_key]['wccp_keys'][] = $wccp_item_key;
					}
				}
			}
		}
		public static function wccp_add_cart_item_data( $cart_item_data, $product_id ) {
			$cart_item_data['wccp_added_products'] = isset($_POST['wccp_added_products']) ? wc_clean($_POST['wccp_added_products']) : '';
			$cart_item_data['wccp_cart_message'] = isset($_POST['wccp_cart_message']) ? wc_clean($_POST['wccp_cart_message']) : '';
			return $cart_item_data;
		}
		public static function wccp_before_calculate_totals( $cart_object ) {
			if ( is_admin() && !defined('DOING_AJAX') ) {
				return;
			} 
			foreach ( $cart_object->get_cart() as $cart_item_key => $cart_item ) {
				$wccp_sub_price = 0;
				if ( isset($cart_item['wccp_parent_id']) && ('' != $cart_item['wccp_parent_id']) ) {
					$cart_item['data']->set_price(0);
				}
				if ( isset($cart_item['wccp_added_products']) && ('' != $cart_item['wccp_added_products']) ) {
					$wccp_items = explode(',', $cart_item['wccp_added_products']);
					$product_id = $cart_item['product_id'];
					if ( is_array($wccp_items) && count($wccp_items) > 0 ) {
						foreach ( $wccp_items as $wccp_item ) {
							$wccp_item_id = !empty($wccp_item) ? absint($wccp_item) : 0;
							$wccp_item_product = wc_get_product($wccp_item_id);
							if ( !$wccp_item_product || $wccp_item_product->is_type('wccp_composite_product') ) {
								continue;
							}
							$wccp_sub_price += floatval($wccp_item_product->get_price());
						}
					}
					$cart_price = 0;
					$prodcuct_data = wc_get_product($product_id);
					$product_info = get_post_meta($product_id, 'wccp_composite_product_options',true);
					$product_info = !empty($product_info) ? $product_info : array();
					$discount_type = !empty($product_info['product_discount_type']) ? $product_info['product_discount_type'] : '';
					$discount = !empty($product_info['products_discount']) ? $product_info['products_discount'] : 0;
					$pricing_type = !empty($product_info['box_pricing_type']) ? $product_info['box_pricing_type'] : 'per_product_only';
					$parent_prod_price = $prodcuct_data->get_price();
					if ( 'per_product_only' == $pricing_type || 'per_product_box' == $pricing_type ) {
						if ( 'per_product_box' == $pricing_type ) {
							$cart_price += $parent_prod_price;
						}
						$cart_price += $wccp_sub_price;
						if ( 'percentage' == $discount_type ) {
							if ( $discount > 0 ) {
								$discount = ($discount / 100 * $cart_price);
							}
						}
						$cart_price  = $cart_price - $discount;
						$cart_item['data']->set_price(floatval($cart_price));
					} else if ( 'fixed_pricing' == $pricing_type ) {
						$cart_price = $parent_prod_price;
						if ( 'percentage' == $discount_type ) {
							if ( $discount > 0 ) {
								$discount = ($discount / 100 * $cart_price);
							}
						}
						$cart_price  = $cart_price - $discount;
						$cart_item['data']->set_price(floatval($cart_price));
					}
				} 
			}
		}
		public static function wccp_cart_item_remove_link( $link, $cart_item_key ) {
			if ( isset(WC()->cart->cart_contents[$cart_item_key]['wccp_parent_id']) ) {
				return '';
			}
			return $link;
		}
		public static function wccp_cart_contents_count( $count ) {
			$cart_contents = WC()->cart->cart_contents;
			$bundled_items = 0;
			foreach ( $cart_contents as $cart_item_key => $cart_item ) {
				if ( !empty($cart_item['wccp_parent_id']) ) {
					$bundled_items+=$cart_item['quantity'];
				}
			}
			return intval($count-$bundled_items);
		}
		public static function wccp_cart_item_removed( $cart_item_key, $cart ) {
			if ( isset($cart->removed_cart_contents[$cart_item_key]['wccp_keys']) ) {
				$wccp_keys = $cart->removed_cart_contents[$cart_item_key]['wccp_keys'];
				foreach ( $wccp_keys as $wccp_key ) {
					unset($cart->cart_contents[$wccp_key]);
				}
			}
		}
		public static function wccp_cart_item_quantity( $quantity, $cart_item_key ) {
			if ( isset(WC()->cart->cart_contents[$cart_item_key]['wccp_parent_id']) ) {
				return WC()->cart->cart_contents[$cart_item_key]['quantity'];
			}
			return $quantity;
		}
		public static function wccp_cart_item_price( $price, $cart_item, $cart_item_key ) {
			if ( isset(WC()->cart->cart_contents[ $cart_item_key]['wccp_parent_id']) ) {
				return '';
			}
			return $price;
		}
		public static function wccp_cart_item_subtotal( $subtotal, $cart_item, $cart_item_key ) {
			if ( isset(WC()->cart->cart_contents[$cart_item_key]['wccp_parent_id']) ) {
				return '';
			}
			return $subtotal;
		}
		public function wccp_cart_item_description( $item_data, $cart_item ) {
		    if ( isset($cart_item['wccp_cart_message']) ) {
				$item_data[] = array(
					'key'     => esc_html__('Message', 'wc-cp'),
					'value'   => $cart_item['wccp_cart_message'],
					'display' => '',
				);
		    }
		    return $item_data;
		}
		public function wccp_order_items_description( $item, $cart_item_key, $values, $order ) {
            $item->update_meta_data( esc_html__('Cart Message', 'wc-cp'), isset($values['wccp_cart_message']) ? $values['wccp_cart_message'] : '' );
		}
	}
	new WCCP_Add_to_Cart();
}