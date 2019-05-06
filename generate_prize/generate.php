<?php
require_once( $parse_uri[0] . 'wp-load.php' );

add_action('wp_ajax_get_prize', 'get_prize');
add_action('wp_ajax_nopriv_get_prize', 'get_prize');
wp_enqueue_script('jquery');
wp_register_script( 'custom-script', plugins_url( 'scripts.js', __FILE__ ), array( 'jquery' ) );
wp_enqueue_script('custom-script');

global $wpdb;
sleep(3);

$db_id = $wpdb->get_var("SELECT id FROM `wp_hotpay_sms` WHERE code = '" . $_POST["hotpay_kodsms"] . "'");

$meta_key = 'category';
$product_cat = $wpdb->get_var( $wpdb->prepare( 
	"
		SELECT category 
		FROM `wp_hotpay_sms`
		WHERE id = " . $db_id ."
	", 
	$meta_key
) );

$wpdb->delete($wpdb->prefix . "hotpay_sms", array(
                                    'code' => $_POST["hotpay_kodsms"]
                                ));

/* bonusowa skrzynka */
if ($db_id <= 0) {
	$rowcount = $wpdb->get_var("SELECT COUNT(*) FROM `wp_reflinks` WHERE user_id = " . get_current_user_id()  . " AND open_box = 1");
	$is_winner = $wpdb->get_var("SELECT id FROM `wp_reflinks_winner` WHERE user_id = " . get_current_user_id());
	
	if ($rowcount >= 15 && $is_winner <= 0) {
		$bonus = 1;
		$product_cat = 'bronze';
		
		$wpdb->insert($wpdb->prefix . "reflinks_winner", array(
                              'user_id'  => get_current_user_id()
                             ));
	}
}

if ($db_id > 0 || $bonus == 1) {
	//echo "Category in: " . $product_cat;
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
	

	wp_reset_postdata();
}

if (get_current_user_id() != 0) {

	$wpdb->insert($wpdb->prefix . "prizes", array(
                              'user_id'  => get_current_user_id(),
							  'prize_id' => $prize_id
                             ));
	$is_reflinked = $wpdb->get_var("SELECT id FROM `wp_reflinks` WHERE register_id = " . get_current_user_id());
	if ($is_reflinked > 0) {
		$wpdb->update('wp_reflinks', array( 'open_box' => '1' ), array( 'register_id' => intval(get_current_user_id()) ));
	}
	
}	

	
?>
