<?php
if(!defined('ABSPATH')){
	wp_die(); 
}
if(!class_exists('WC_Product_WCCP_Composite_Product')){
	class WC_Product_WCCP_Composite_Product extends WC_Product {
		public function __construct($product){
			$this->product_type='wccp_composite_product';
			parent::__construct($product);
		}
	}
}