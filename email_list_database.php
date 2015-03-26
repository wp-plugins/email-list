<?php

function el_subscriber_db_install() {
	global $wpdb;

	$table_name = $wpdb->prefix . 'el_subscribers';
	
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE IF NOT EXISTS " .$table_name. "(
		id int UNSIGNED NOT NULL AUTO_INCREMENT,
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
		id int UNSIGNED NOT NULL AUTO_INCREMENT,
		time_sent datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		email_id bigint(20) UNSIGNED NOT NULL DEFAULT '0',
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
		id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		time_sent datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		sent_email_id int UNSIGNED NOT NULL,
		subscriber_id int UNSIGNED NOT NULL,
		sent boolean NOT NULL DEFAULT 0,
		opens smallint UNSIGNED NOT NULL DEFAULT '0',
		clicks smallint UNSIGNED NOT NULL DEFAULT '0',
		soft_bounce smallint UNSIGNED NOT NULL DEFAULT '0',
		hard_bounce boolean NOT NULL DEFAULT 0,
		marked_as_spam boolean NOT NULL DEFAULT 0,
		PRIMARY KEY  (id)
	)".$charset_collate.";";
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
	
}