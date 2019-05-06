<?php
/*
Plugin Name: Generator Nagród Woocommerce
Description: Tworzy formularz generujący nagrody. Shortcode: [prize-generator]
Version: 0.1.0
*/

wp_enqueue_style('style', plugins_url( 'style.css', __FILE__ ));

add_action( 'wp_enqueue_scripts', 'ajax_test_enqueue_scripts' );
function ajax_test_enqueue_scripts() {
	wp_register_script( 'custom-script', plugins_url( 'scripts.js', __FILE__ ), array( 'jquery' ) );
	wp_enqueue_script('jquery');
	wp_enqueue_script('custom-script');
}


/* Create database for prizes */

global $wpdb;

$charset_collate = $wpdb->get_charset_collate();
$table_name = $wpdb->prefix . "prizes";

$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		user_id TEXT NOT NULL,
		prize_id TEXT NOT NULL,
		UNIQUE KEY id (id)
	) $charset_collate;";

require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
dbDelta( $sql );


/* Shortcode and form */

function wpc_create_form_prize() {
	?>
	<div id="prize_generator" class="coupon_form">
		<input type="button" class="prize_generator" value="Losuj">
		<input type="hidden" id="action_adress" value="<?php echo plugins_url( 'generate.php', __FILE__ ); ?>">
		<input type="hidden" id="hotpay_kodsms" name="hotpay_kodsms" value="">
		<div id="gif_loading"></div>
		<p></p>
		<div id="msg_alert"></div>
	</div>
<?php
	
}

function cmp($a, $b)
{
    if ($a[1] == $b[1]) {
        return 0;
    }
    return ($a[1] < $b[1]) ? -1 : 1;
}


function get_prize_button() {
	
	global $wpdb;

	if (get_current_user_id() != 0) {
		$ids = $wpdb->get_results("SELECT prize_id FROM `wp_prizes` WHERE user_id=" . intval(get_current_user_id()) );
		
		$product_array = array();
		for ($i = 0; $i < sizeof($ids); $i++) {
			$product_array[$i] = array();
			$product_array[$i][0] = $ids[$i]->prize_id;
			$product = wc_get_product( $ids[$i]->prize_id );
			$product_array[$i][1] = $product->get_price();
		}
		usort($product_array,"cmp");

		for ($i = sizeof($ids) - 1; $i >= 0; $i--) {
	    	echo '<div class="prize">';
			echo '<h3>';
			echo get_the_title($product_array[$i][0]);
			echo '</h3>';
			$product = wc_get_product( $product_array[$i][0] );
			echo get_the_post_thumbnail($product_array[$i][0], 'thumbnail');
			echo '<a href="' . $product->add_to_cart_url() .'">';
			echo '<input type="button" value="Odbierz nagrodę" class="cart_prize" />';
			echo '</a>';
			echo "</div><p></p>";
		}
	}

}

function loggedincheck( $atts, $content = null ) {
     if ( is_user_logged_in() && !is_null( $content ) && !is_feed() ) {
          return $content;
     return '';
     }
}

function loggedoutcheck( $atts, $content = null ) {
     if ( !is_user_logged_in() && !is_null( $content ) && !is_feed() ) {
          return $content;
     return '';
     }
}

function get_prize_for_activity() {
	global $wpdb;
	$reflink_points = $wpdb->get_results('SELECT count(*) as counter FROM wp_wordpoints_points_logs WHERE user_id=' . intval(get_current_user_id()) ." AND log_type = 'reflink';");

	if ($reflink_points[0]->counter <= 0) {
		$have_friend = $wpdb->get_results('SELECT count(*) as counter FROM wp_reflinks WHERE user_id=' . intval(get_current_user_id()));
		if ($have_friend[0]->counter > 0){
			$wpdb->query(
					"
					INSERT INTO wp_wordpoints_points_logs (user_id, is_social, log_type, points, date, text) VALUES(". intval(get_current_user_id()) .", 1, 'reflink', 10, CURDATE(), 'reflink');
					"
			);
		}
	}
	
		
	$result = $wpdb->get_results('SELECT sum(points) as points FROM `wp_wordpoints_points_logs` WHERE user_id = '. intval(get_current_user_id()).' AND is_open = 1');
	$daily = $wpdb->get_results('SELECT sum(points) as points FROM `wp_wordpoints_points_logs` WHERE DATE(date) = CURDATE() AND user_id = ' . intval(get_current_user_id()) . ' AND is_open = 0');
	$is_fan = false;

	?>
	<div style="text-align:center;">
		<h3>
			<?php echo 'Twoje saldo to ' . intval($result[0]->points) . ' punktów.'; ?>
		</h3>

		<input type="button" value="<?php if ($result[0]->points < 1000) echo 'Odbierz dzienny bonus ('  . $daily[0]->points . 'pkt)'; else echo 'Odbierz nagrodę' ?>" class="points_box" style="margin:10px;" <?php if ($daily[0]->points <= 0) echo 'disabled'; ?> ><p><?//php is_user_like_fb(); ?></p>
		<?php 
			$points = $wpdb->query(
				"
					SELECT text FROM wp_wordpoints_points_logs WHERE user_id = " . intval(get_current_user_id()) . "
					AND (DATE(date) = CURDATE() OR (is_social = 1 AND is_open = 0));
				"
			);
		?>
		<input type="hidden" id="action_adress" value="<?php echo plugins_url( 'points_box.php', __FILE__ ); ?>">
		<div id="gif_loading"></div>
		<div class="prize"></div>
		<input type="hidden" id="prize_adress" value="<?php echo plugins_url( 'get_prize_for_points.php', __FILE__ ); ?>">
	</div>
	
<?php
	if ($is_fan) {
		
	}
}

function is_user_like_fb()
{
	$isFan = file_get_contents("https://api.facebook.com/method/pages.isFan?format=json&access_token=" . USER_TOKEN . "&page_id=" . 293570074557208);
	echo "User " . $isFan;
}

function wpc_register_shortcode() {

	add_shortcode( 'prize-generator', 'wpc_create_form_prize' );
	add_shortcode( 'prize_button', 'get_prize_button' );
	add_shortcode( 'loggedin', 'loggedincheck' );
	add_shortcode( 'loggedout', 'loggedoutcheck' );
	add_shortcode( 'get-prize-for-activity', 'get_prize_for_activity');
}

add_action( 'init', 'wpc_register_shortcode' );


?>
