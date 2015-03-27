<?php
/*
Plugin Name: Email Marketing List
Description: An easy way to place a email list on your Wordpress site.
Version: 1.0
Author: Jeff Bullins
Author URI: http://www.thinklandingpages.com
*/
if ( file_exists( dirname( __FILE__ ) . '/cmb2/init.php' ) ) {
	require_once dirname( __FILE__ ) . '/cmb2/init.php';
} elseif ( file_exists( dirname( __FILE__ ) . '/CMB2/init.php' ) ) {
	require_once dirname( __FILE__ ) . '/CMB2/init.php';
}

/*
function el_subscriber_db_install() {
	global $wpdb;

	$table_name = $wpdb->prefix . 'el_subscribers';
	
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE IF NOT EXISTS " .$table_name. "(
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		time_added datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		subscribe_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		unsubscribe_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		name tinytext,
		email_address varchar(128) NOT NULL UNIQUE,
		subscribed boolean NOT NULL DEFAULT 0,
		hard_bounce_unsubscribe boolean NOT NULL DEFAULT 0,
		PRIMARY KEY  (id)
	)".$charset_collate.";";
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
	
}


function el_email_sent_db_install() {
	global $wpdb;

	$table_name = $wpdb->prefix . 'el_email_sent';
	
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE IF NOT EXISTS " .$table_name. "(
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		time_sent datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		email_id int NOT NULL DEFAULT '0',
		email_title tinytext,
		PRIMARY KEY  (id)
	)".$charset_collate.";";
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
	
}

function el_email_sent_subscribers_db_install() {
	global $wpdb;

	$table_name = $wpdb->prefix . 'el_email_sent_subscribers';
	
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE IF NOT EXISTS " .$table_name. "(
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		time_sent datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		sent_email_id int NOT NULL,
		subscriber_id int NOT NULL,
		sent boolean NOT NULL DEFAULT 0,
		opens mediumint(9) NOT NULL DEFAULT '0',
		clicks mediumint(9) NOT NULL DEFAULT '0',
		soft_bounce mediumint(9) NOT NULL DEFAULT '0',
		hard_bounce boolean NOT NULL DEFAULT 0,
		marked_as_spam boolean NOT NULL DEFAULT 0,
		PRIMARY KEY  (id)
	)".$charset_collate.";";
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
	
}
*/

include_once 'email_list_database.php';

include_once 'cmb2Settings.php';

include_once 'custom-post-type.php';  


function el_activate() {
	$emailListPostType = new EmailListCustomPostType();
	$emailListPostType->create_post_type();
	el_subscriber_db_install();
	el_email_sent_db_install();
	el_email_sent_subscribers_db_install();
	global $wp_rewrite;
	$wp_rewrite->flush_rules();
}


register_activation_hook( __FILE__, 'el_activate');

 