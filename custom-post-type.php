<?php

class EmailListCustomPostType{

private $post_type = 'emaillist';
private $post_label = 'Email List';
private $prefix = '_email_list_';

public static $staticPrefix = '_email_list_';
function __construct() {
	
	
	add_action("init", array(&$this,"create_post_type"));
	add_action('template_include', array(&$this,'email_view_template_include'));
	//add_action( 'init', array(&$this, 'email_list_register_shortcodes'));
	add_action( 'wp_footer', array(&$this, 'enqueue_styles'));
	//add_action( 'wp_footer', array(&$this, 'enqueue_scripts'));
	
	add_action( 'cmb2_init', array(&$this,'emaillist_register_metabox' ));
	
}

function create_post_type(){
	register_post_type($this->post_type, array(
	         'label' => _x($this->post_label, $this->post_type.' label'), 
	         'singular_label' => _x('All '.$this->post_label, $this->post_type.' singular label'), 
	         'public' => true, // These will be public
	         'show_ui' => true, // Show the UI in admin panel
	         '_builtin' => false, // This is a custom post type, not a built in post type
	         '_edit_link' => 'post.php?post=%d',
	         'capability_type' => 'page',
	         'hierarchical' => false,
	         'rewrite' => array("slug" => $this->post_type), // This is for the permalinks
	         'query_var' => $this->post_type, // This goes to the WP_Query schema
	         //'supports' =>array('title', 'editor', 'custom-fields', 'revisions', 'excerpt'),
	         'supports' =>array('title', 'author'),
	         'add_new' => _x('Add New', 'Event')
	         ));
}


function email_view_template_include($template){
	global $wp;
	global $post;
	if (isset($wp->query_vars["post_type"])) {
   		//$post_type_local = $wp->query_vars["post_type"];
		if ($wp->query_vars["post_type"] == $this->post_type){
			if(current_user_can("manage_options")){
				//$email = get_post_meta($post->id, $this->prefix . 'email_message', true);
				// Then use the campaign-template.php file from this plugin directory
				//include plugin_dir_url(__FILE__).'template/emailListAccessDenied.php';
				return plugin_dir_path( __FILE__ ).'template/emailPreview.php';
				//die();
			}
			else{
				return plugin_dir_path( __FILE__ ).'template/emailListAccessDenied.php';
			}
		}
	}
	return $template;
}


function email_list_install() {
//echo "no more";
//die();
	global $wpdb;
	//global $jal_db_version;
	//echo 'calling email_list_install';

	$table_name = $wpdb->prefix . 'el_subscribers';
	
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE IF NOT EXISTS " .$table_name. "(
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		name tinytext,
		email_address varchar(128) NOT NULL UNIQUE,
		subscribed boolean NOT NULL DEFAULT 0,
		PRIMARY KEY  (id)
	)".$charset_collate.";";
	
	
	
	if ( $wpdb->query($sql) === false ) {
	$wpdb->show_errors();
		$wpdb->print_error();
		echo 'trouble in the woods';
		die();
		
	}
    
}


/**************************************************
**********************CMB2*************************
*/


/**
 * Hook in and add a demo metabox. Can only happen on the 'cmb2_init' hook.
 */

function emaillist_register_metabox() {

	// Start with an underscore to hide fields from custom fields list
	//$prefix = '_emaillist_demo_';

	/**
	 * Sample metabox to demonstrate each field type included
	 */
	$cmb_demo = new_cmb2_box( array(
		'id'            => $this->prefix . 'metabox',
		'title'         => __( 'Email Editor', 'cmb2' ),
		'object_types'  => array( $this->post_type, ), // Post type
		'context'       => 'normal',
		'priority'      => 'high',
		'show_names'    => true, // Show field names on the left
		// 'cmb_styles' => false, // false to disable the CMB stylesheet
		// 'closed'     => true, // true to keep the metabox closed by default
	) );
	
	$cmb_demo->add_field( array(
		'name'    => __( 'Email Subject', 'cmb2' ),
		//'desc'    => __( 'field description (optional)', 'cmb2' ),
		'id'      => $this->prefix . 'email_subject',
		'type'    => 'text',
		'before_row' => '<div><a href="http://www.thinklandingpages.com/email_list_pro/">Upgrade to Email List Pro</a></div>',
	) );
	
	$cmb_demo->add_field( array(
		'name'    => __( 'Email', 'cmb2' ),
		//'desc'    => __( 'field description (optional)', 'cmb2' ),
		'id'      => $this->prefix . 'email_message',
		'type'    => 'wysiwyg',
		'options' => array( 'textarea_rows' => 10, ),
	) );

}





/************************************************
*******************End CMB2**********************
*/






/*

function email_list_shortcode($atts){
		extract( shortcode_atts( array(
			'id' => '',
		), $atts ) );
		//$meta_data = get_post_meta( $id, $this->prefix . 'adsense_code', true );
		//$meta_data = get_post_meta($id);
		$dir = plugin_dir_path( __FILE__ );

		
		$background_color = get_post_meta($id, $this->prefix . 'background_color', true);
		$tab1_title = get_post_meta($id, $this->prefix . 'tab1_title', true);
		$tab1_message = get_post_meta($id, $this->prefix . 'tab1_message', true);
		$tab2_title = get_post_meta($id, $this->prefix . 'tab2_title', true);
		$tab2_message = get_post_meta($id, $this->prefix . 'tab2_message', true);
		
		ob_start();
		include $dir.'template/emailListTemplate.php';
		return ob_get_clean();
}



function email_list_register_shortcodes(){
		add_shortcode( 'email_list', array(&$this,'email_list_shortcode' ));
	}
*/
	
function enqueue_styles(){
	wp_register_style( 'email-list-css', plugin_dir_url(__FILE__).'css/emailList.css' );
	wp_enqueue_style('email-list-css');
}

function enqueue_scripts(){
	wp_enqueue_script('email-list-js', plugin_dir_url(__FILE__).'js/emailList.js');
}


function activate() {
	// register taxonomies/post types here
	echo 'in activate()';
	die();
	$this->email_list_install();
	$this->create_post_type();
	global $wp_rewrite;
	$wp_rewrite->flush_rules();
}





}// end EmailListCustomPostType class

new EmailListCustomPostType();


?>