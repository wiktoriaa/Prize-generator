<?php

require_once( $parse_uri[0] . 'wp-load.php' );

global $wpdb;

if (get_current_user_id() != 0) {
	$ids = $wpdb->get_results("SELECT prize_id FROM `wp_prizes` WHERE user_id=" . get_current_user_id() );
	$product_array = array();
	for ($i = 0; $i < sizeof($ids); $i++) {
		$product_array[$i] = array();
		$product_array[$i][0] = $id;
		$product = wc_get_product( $id->prize_id );
		$product_array[$i][1] = $product->get_price();
	}

	foreach ($ids as $id) {
    	echo '<div class="prize">';
		echo '<h2>';
		//echo get_the_title($id->prize_id);
		echo '</h2>';
		$product = wc_get_product( $id->prize_id );
		echo get_the_post_thumbnail($id->prize_id, 'thumbnail');
		echo '<a href="' . $product->add_to_cart_url() .'">';
		echo '<input type="button" value="Odbierz nagrodę" class="cart_prize" />';
		echo '</a>';
		echo "</div><p></p>";
	}
}

?>