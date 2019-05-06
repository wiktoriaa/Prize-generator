<?php
require_once( $parse_uri[0] . 'wp-load.php' );
sleep(0);
global $wpdb;
$result = $wpdb->get_results('SELECT count(id) as counter FROM `wp_wordpoints_points_logs` WHERE DATE(date) = CURDATE() AND user_id = ' . get_current_user_id() . ' AND is_open = 0');

$daily = $wpdb->get_results('SELECT sum(points) as points FROM `wp_wordpoints_points_logs` WHERE DATE(date) = CURDATE() AND user_id = ' . get_current_user_id() . ' AND is_open = 0');

$points = $result[0]->counter;

if ($points) {
	
	$wpdb->query( 
		 
			"
				UPDATE `wp_wordpoints_points_logs`
				SET `is_open` = '1'
				WHERE DATE(`date`) = CURDATE() AND `user_id` = '" . get_current_user_id() . "' AND `is_open` = '0'
			"
			
		);
	echo "Gratulacje! Otrzymujesz " . $daily[0]->points . " punktów.";
}

else
	echo "Nie ma dzisiaj już dostępnych punktów";

?>