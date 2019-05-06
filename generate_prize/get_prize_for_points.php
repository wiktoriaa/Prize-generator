<?php
require_once( $parse_uri[0] . 'wp-load.php' );

$points = $wpdb->get_results('SELECT sum(points) as points FROM `wp_wordpoints_points_logs` WHERE AND user_id = ' . intval(get_current_user_id()) . ' AND is_open = 1');

$points = $points[0]->points;

if ($points > 0) {
	$req = 0;
	
	if ($_POST['category'] == 'bronze' && $points >= 100)
		$req = 100;
	else if ($_POST['category'] == 'bronze' && $points >= 200)
		$req = 200;
	else if ($_POST['category'] == 'bronze' && $points >= 300)
		$req = 300;
	
	$product_cat = 'none';
	$curr_points = $points - $req;
	
	if ($req != 0) {
		while ($points != $curr_points) {
			$row = $wpdb->get_results("SELECT id FROM wp_wordpoints_points_logs WHERE user_id = ". intval(get_current_user_id()) . " AND is_open = 1 LIMIT 1"); 
			$val = $wpdb->get_results("SELECT points FROM wp_wordpoints_points_logs WHERE user_id = ". intval(get_current_user_id()) . " AND is_open = 1 LIMIT 1");
			$val = $val[0]->points;
			
			if ($points - $curr_points =< $val) {
				$wpdb->query("UPDATE wp_wordpoints_points_logs SET points = " . $val - $points - $curr_points . " WHERE user_id = " . intval(get_current_user_id()) . " AND is_open = 1 LIMIT 1");
				$points = 0;
				continue;
			}
			
			else {
				
			}
			
		}
		$product_cat = $_POST['category'];
	}
}


	$parse_uri = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );
	$args = array(
	    'post_type' => 'product',
    	'posts_per_page' => 100,
    	'product_cat' => $product_cat,
    	'orderby'   => 'meta_value_num',
    	'meta_key'  => '_price',
    	'order' => 'desc'
	);
		 
	$my_query = new WP_Query( $args );
	global $post;
	if ( $my_query->have_posts() ) {

	    while ( $my_query->have_posts() ) {
		 
	        $my_query->the_post();
			
			$product = wc_get_product($post->ID);
			
			if (intval($product->get_price()) <= 5)
				$min_5_ids[] += $post->ID;
			
			else if (intval($product->get_price()) < 20)
				$min_20_ids[] += $post->ID;
			
			else if (intval($product->get_price()) < 40)
				$med_40_ids[] += $post->ID;
			
			else if (intval($product->get_price()) < 100)
				$med_100_ids[] += $post->ID;
			
			else if (intval($product->get_price()) > 100)
				$high_100_ids[] += $post->ID;
	    }
	}

	while (!wc_get_product( $prize_id )) {
		$generate = random_int(1, 1000);
	
		// prizes > 100pln
		if ($generate == 1 || (get_current_user_id() == 44 && $product_cat == 'gold'))
			$prize_id = $high_100_ids[random_int(0, sizeof($high_100_ids) - 1)];

		// prizes 40-100pln
		else if ($generate > 1 && $generate <= 30)
			$prize_id = $med_100_ids[random_int(0, sizeof($med_100_ids) - 1)];

		// prizes 20-40pln
		else if ($generate > 30 && $generate <= 150)
			$prize_id = $med_40_ids[random_int(0, sizeof($med_40_ids) - 1)];

		//prizes 6-20pln
		else if ($generate > 150 && $generate <= 350 && $product_cat == 'bronze')
			$prize_id = $min_20_ids[random_int(0, sizeof($min_20_ids) - 1)];

		else if ($generate > 150 && $generate <= 300 && $product_cat == 'silver')
			$prize_id = $min_20_ids[random_int(0, sizeof($min_20_ids) - 1)];

		else if ($generate > 150 && $generate <= 400 && $product_cat == 'gold')
			$prize_id = $min_20_ids[random_int(0, sizeof($min_20_ids) - 1)];

		//prizes < 5pln
		else if ($generate > 300 && $product_cat == 'bronze')
			$prize_id = $min_5_ids[random_int(0, sizeof($min_5_ids) - 1)];

		else if ($generate > 350 && $product_cat == 'silver')
			$prize_id = $min_5_ids[random_int(0, sizeof($min_5_ids) - 1)];

		else if ($generate > 400 && $product_cat == 'gold')
			$prize_id = $min_5_ids[random_int(0, sizeof($min_5_ids) - 1)];
		
	}
	
	
	echo '<div class="prize">';
	echo '<h2>Gratulacje! Udało ci się wygrać</h2>';
	echo get_the_title($prize_id);
	$product = wc_get_product( $prize_id );
	echo '<p>';
	echo $product->get_price_html();
	echo '</p>';
	echo get_the_post_thumbnail($prize_id, 'thumbnail');
	echo '<a href="' . $product->add_to_cart_url() .'">';
	echo '<input type="button" value="Odbierz nagrodę" class="cart_prize" />';
	echo '</a>';
	echo '<p></p><p>Nagrody możesz też odebrać później w zakładce "Moje nagrody" w Twoim profilu</p>';
	
?>