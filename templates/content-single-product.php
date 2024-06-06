<?php
defined('ABSPATH') || exit;
do_action('woocommerce_before_single_product');
if ( post_password_required() ) {
	echo get_the_password_form();
	return;
} ?>
<div id="product-<?php the_ID(); ?>" <?php wc_product_class(); ?>>
<?php
	do_action('wccp_composite_product_layout');
	do_action('woocommerce_after_single_product_summary'); ?>
</div>
<?php do_action('woocommerce_after_single_product'); ?>