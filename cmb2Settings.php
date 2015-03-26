<?php
//define('WP_DEBUG', true);
/**
 * CMB2 Theme Options
 * @version 0.1.0
 */
class email_list_Admin {

	/**
 	 * Option key, and option page slug
 	 * @var string
 	 */
	private $key = 'email_list_options';
	
	private $prefix = 'email_list_admin';

	/**
 	 * Options page metabox id
 	 * @var string
 	 */
	private $metabox_id = 'email_list_option_metabox';
	
	private $subscribers_metabox_id = "email_list_subscribers_metabox";
	
	private $sendemail_metabox_id = "send_email_metabox";

	/**
	 * Array of metaboxes/fields
	 * @var array
	 */
	protected $option_metabox = array();

	/**
	 * Options Page title
	 * @var string
	 */
	protected $title = '';

	/**
	 * Options Page hook
	 * @var string
	 */
	protected $options_page = '';
	
	protected $subscriber_data = array();

	/**
	 * Constructor
	 * @since 0.1.0
	 */
	public function __construct() {
		// Set our title
		$this->title = __( 'List Settings', 'email_list' );
	}

	/**
	 * Initiate our hooks
	 * @since 0.1.0
	 */
	public function hooks() {
		add_action( 'admin_init', array( $this, 'init' ) );
		add_action( 'admin_menu', array( $this, 'add_options_page' ) );
		add_action( 'cmb2_init', array( $this, 'add_options_page_metabox' ) );
		add_action( 'cmb2_init', array( $this, 'add_subscribers_page_metabox' ) );
		add_action( 'cmb2_init', array( $this, 'add_sendemail_page_metabox' ) );
		add_filter('cmb2_override_subscriber_email_meta_save', array( $this, 'save_subscriber_filter_callback'), 10, 3);
		add_filter('cmb2_override_subscriber_name_meta_save', array( $this, 'save_subscriber_filter_callback'), 10, 3);
		add_filter('cmb2_override_meta_save', array( $this, 'override_save_filter_callback'), 9, 3);
		//add_filter('cmb2_override_meta_save', array( $this, 'custom_save'));
		add_filter( 'query_vars', array( $this, 'add_query_vars'));
		add_action('init', array( $this, 'el_add_subscribe_endpoint'));
		add_action('init', array( $this, 'el_send_email_endpoint'));
        	add_action( 'template_redirect', array( $this, 'el_subscribe_template_redirect2'));
        	add_action( 'template_redirect', array( $this, 'send_email'));
        	
        	add_action( "cmb2_save_post_fields", array($this, 'save_consolidated_subscriber'), 10, 4);
        	add_filter( 'cmb2_get_metabox_form_format', array($this, 'myprefix_options_modify_cmb2_metabox_form_format'), 10, 3 );
        	add_action('admin_enqueue_scripts', array($this, 'enqueue_jqueryui'));
        	//add_action('admin_init', array($this, 'enqueue_sendemail_script'));
		
	}


	/**
	 * Register our setting to WP
	 * @since  0.1.0
	 */
	public function init() {
		register_setting( $this->key, $this->key );
	}

	/**
	 * Add menu options page
	 * @since 0.1.0
	 */
	public function add_options_page() {
		//$this->options_page = add_menu_page( $this->title, $this->title, 'manage_options', $this->key, array( $this, 'admin_page_display' ) );
		add_submenu_page( 'edit.php?post_type=emaillist', $this->title, $this->title, 'manage_options', $this->key, array( $this, 'admin_page_display' ) );
		add_submenu_page( 'edit.php?post_type=emaillist', "Subscribers", "Subscribers", 'manage_options', "subscribers", array( $this, 'subscribers_page_display' ) );
		add_submenu_page( 'edit.php?post_type=emaillist', "Send Email", "Send Email", 'manage_options', "sendemail", array( $this, 'sendemail_page_display' ) );
	}
	
	function enqueue_jqueryui(){
		wp_enqueue_script('jquery-ui-dialog');
		wp_enqueue_style('el-jqueryui-css', plugin_dir_url(__FILE__).'css/jquery-ui/jquery-ui.min.css');
	}
	

	/**
	 * Admin page markup. Mostly handled by CMB2
	 * @since  0.1.0
	 */
	public function admin_page_display() {
		?>
		<div class="wrap cmb2_options_page <?php echo $this->key; ?>">
			<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
			<div><a href="http://www.thinklandingpages.com/email_list_pro/">Upgrade to Email List Pro</a></div>
			<?php cmb2_metabox_form( $this->metabox_id, $this->key ); ?>
		</div>
		<?php
	}
	
	
	function save_consolidated_subscriber($object_id, $cmb_id, $updated, $cmb){
	$data = $cmb->data_to_save;
		if(isset($data['object_id']) && $data['object_id'] == 'subscribers'){
			//$GLOBALS['DebugMyPlugin']->panels['main']->addMessage('consolidation test: '.print_r($cmb->data_to_save));
			
			$clean_email = sanitize_email($data['subscriber_email']);
			$name = sanitize_text_field($data['subscriber_name']);
			if(!$clean_email == ''){
				$this->save_subscriber($clean_email, $name);
				$this->subscribe_subscriber($clean_email);
			}
		}
	}
	
	public function subscribers_page_display(){
		echo "<h2>Subscribers</h2>";
		echo '<div><a href="http://www.thinklandingpages.com/email_list_pro/">Upgrade to Email List Pro</a></div>';
		cmb2_metabox_form( $this->subscribers_metabox_id, "subscribers" ); 
		$subscribers = $this->get_all_subscribers();
		include plugin_dir_path( __FILE__ ).'include/admin_subscriber_manage.php';
		
	}
	
	function enqueue_sendemail_script(){
		wp_enqueue_script("sendemail-js", plugin_dir_url(__FILE__).'js/sendEmail.js');
	}
	
	public function sendemail_page_display(){
		//add_action('admin_init', array($this, 'enqueue_sendemail_script'));
		wp_enqueue_script("sendemail-js", plugin_dir_url(__FILE__).'js/sendEmail.js');
		echo "<h2>Send Email</h2>";
		echo '<div><a href="http://www.thinklandingpages.com/email_list_pro/">Upgrade to Email List Pro</a></div>';
		cmb2_metabox_form( $this->sendemail_metabox_id, "sendemail" ); 
		$sent_emails = $this->get_all_sent_emails();
		include plugin_dir_path( __FILE__ ).'include/admin_display_sent_emails.php';
		//$subscribers = $this->get_all_subscribers();
		//include plugin_dir_path( __FILE__ ).'include/admin_subscriber_manage.php';
		
	}
	
	public function get_all_sent_emails(){
		global $wpdb;
		$table_name = $wpdb->prefix . 'el_email_sent';
		$subscribers = $wpdb->get_results("SELECT * FROM $table_name");
		return $subscribers;
	}
	
	public function get_all_subscribers(){
		global $wpdb;
		$table_name = $wpdb->prefix . 'el_subscribers';
		$subscribers = $wpdb->get_results("SELECT * FROM $table_name");
		return $subscribers;
	}
	
	public function get_all_subscribed_subscribers(){
		global $wpdb;
		$table_name = $wpdb->prefix . 'el_subscribers';
		$subscribers = $wpdb->get_results("SELECT email_address,id FROM $table_name WHERE subscribed = 1");
		//print_r($subscribers);
		return $subscribers;
	}
	
	
	public function save_subscriber($email, $name){
		//CMB2_FIELDS->update_data
		//$override = apply_filters( "cmb2_override_{$a['field_id']}_meta_save", $override, $a, $this->args(), $this );
		global $wpdb;
		//$name = "John";
		//$email_address = "jim@jim.com";
		$table_name = $wpdb->prefix . 'el_subscribers';

		$wpdb->insert( 
			$table_name, 
			array( 
				'time_added' => current_time( 'mysql' ), 
				'name' => $name, 
				'email_address' => $email, 
				//'subscribed' => true,
			) 
		);
		
		//echo "inside custom save";
		//die();
	}
	
	function override_save_filter_callback($override, $a, $field_args){
		//print_r($a);
		if($a['id'] == 'sendemail'){
			//print_r($a);
			//$this->el_send_email($a['value']);
			return 'sendemail';
		}
	}
	
	public function save_subscriber_filter_callback($override, $a, $field_args){
	/*
	$GLOBALS['DebugMyPlugin']->panels['main']->addMessage('a[value]: '.print_r($a));
	$GLOBALS['DebugMyPlugin']->panels['main']->addMessage('test');
		//$subscriber_data['email']
		$clean_email = sanitize_email($a['value']);
		if(!$clean_email == ''){
			$this->save_subscriber($clean_email, $name);
			$this->subscribe_subscriber($clean_email);
		}
		//return non-null value to short-circuit cmb2->field->update function
	*/
		return 'not null';
		//$this->save_subscriber($a['value'], '');
	}
	
	public function subscribe_subscriber($email){
		global $wpdb;
		//$name = "John";
		//$email_address = "jim@jim.com";
		$table_name = $wpdb->prefix . 'el_subscribers';

		$wpdb->update( 
			$table_name, 
			array( 
				'subscribe_time' => current_time( 'mysql' ), 
				//'name' => $name, 
				//'email_address' => $email, 
				'subscribed' => true,
			),
			array( 'email_address' => $email ) 
		);
	}
	
	public function unsubscribe_subscriber($email){
		//CMB2_FIELDS->update_data
		//$override = apply_filters( "cmb2_override_{$a['field_id']}_meta_save", $override, $a, $this->args(), $this );
		global $wpdb;
		//$name = "John";
		//$email_address = "jim@jim.com";
		$table_name = $wpdb->prefix . 'el_subscribers';

		$wpdb->update( 
			$table_name, 
			array( 
				'unsubscribe_time' => current_time( 'mysql' ), 
				//'name' => $name, 
				//'email_address' => $email, 
				'subscribed' => false,
			),
			array( 'email_address' => $email ) 
		);
		
		//echo "inside custom save";
		//die();
	}
	
	public function get_all_emails(){
		$args = array(
				'post_type' => 'emaillist',
			);
		$query = new WP_Query( $args );
		//print_r($query);
		return $query;
	}
	
	public function get_all_emails_for_select(){
		$all_emails = array();
		$emails = $this->get_all_emails()->posts;
		$data['empty'] = '';
		foreach($emails as $email) {
		    $data[$email->ID] = $email->post_title;
		}
		return $data;
		/*
		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			echo '<li>' . get_the_title() . '</li>';
		}
		        return array(
		            'tabby'   => __( 'Tabby', 'cmb' ),
		            'siamese' => __( 'Siamese', 'cmb' ),
		            'calico'  => __( 'Calico', 'cmb' ),
		        );
		*/
	}
	
	function send_email(){
		global $wp_query;
		if(isset($wp_query->query_vars['send-email'])){
			if(current_user_can("manage_options")){
				$email_id = isset($_GET['send_email_id']) ? $_GET['send_email_id'] : '';
					if($email_id){
						$this->el_send_email($email_id);
					/*
						$body = get_post_meta($email_id, '_email_list_email_message', true);
						$subject = get_post_meta($email_id, '_email_list_email_subject', true);
						$success = wp_mail( $this->get_all_subscribed_subscribers(), $subject, $body );
						if($success){
							echo 'email sent successfully';
						}
						else{
							echo 'there was a problem sending the email';
						}
					*/
				//get email-id
				//get list of subscribed subscribers
				//send email to every subscribed subscriber
				//update list of send emails table
				
					}
					else{
						echo 'Please choose an email to send';
					}
			}
			else{
				echo 'Access Denied';
			}
			wp_enqueue_script('el-referrer-redirect', plugin_dir_url(__FILE__).'js/referrerRedirect.js');
			echo wp_head();
			echo '<p><a href="#" onClick="window.history.back()">Go Back</a>';
			
			die();
		}
		
	}
	
	function el_send_email($email_id){
		//global $wp_query;
		//if(isset($wp_query->query_vars['send-email'])){
			
			if(current_user_can("manage_options")){
				//$email_id = isset($_POST['send_email_id']) ? $_POST['send_email_id'] : '';
					if($email_id){
						
						add_filter( 'wp_mail_content_type', array($this, 'set_html_content_type' ));
						$body = get_post_meta($email_id, '_email_list_email_message', true);
						$body .= '<br>'.wpautop(cmb2_get_option( $this->key, 'email_footer' ));
						
						$subject = get_post_meta($email_id, '_email_list_email_subject', true);
						$subscribe_subscribers = $this->get_all_subscribed_subscribers();
						$count = 0;
						$failCount = 0;
						$successCount = 0;
						$failures = array();
						$subscribedArray = array();
						$originalBody = $body;
						$sent_email_id = $this->save_sent_email($email_id, get_the_title($email_id));
						foreach($subscribe_subscribers as $subscribed){
							$body .= '<br><a href="'.site_url().'/?email-subscriber='.$subscribed->email_address.'&unsubscribe">Click here to unsubscribe.';
							//$subscribedArray[] = $subscribed->email_address;
							$success = wp_mail( $subscribed->email_address, $subject, $body, $this->get_from_address() );
							$count = ++$count;
							if(!$success){
								$failCount = ++$failCount;
								$failures[] = $subscribed->email_address;
							}
							else{
								$successCount = ++$successCount;
							}
							$body = $originalBody;
							$this->save_email_sent_subscriber($sent_email_id, $subscribed->id, $success);
						}
						//print_r($subscribedArray);
						//$success = wp_mail( $subscribedArray, $subject, $body, $this->get_from_address() );
						// Reset content-type to avoid conflicts -- http://core.trac.wordpress.org/ticket/23578
						remove_filter( 'wp_mail_content_type', 'set_html_content_type' );
						//$this->save_sent_email($email_id, get_the_title($email_id));
						echo $successCount. ' emails sent successfully<br>';
						echo $failCount. ' emails failed<br>';
						echo 'List of email address that failed<br>';
						foreach($failures as $failure){
							echo $failure.'<br>';
						}
						/*
						if($success){
							$this->save_sent_email($email_id, get_the_title($email_id));
							echo 'email sent successfully';
						}
						else{
							echo 'there was a problem sending the email';
						}
						*/
				
					}
					else{
						echo 'Please choose an email to send';
					}
			}
			else{
				echo 'Access Denied';
			}		
	}
	
	function get_from_address(){
		$from_email = cmb2_get_option( $this->key, 'from_email' );
		$from_name = cmb2_get_option( $this->key, 'from_name' );
		if(isset($from_email) && trim($from_email) != ''){
			return 'From: '.$from_name.' <'.$from_email.'>';
		}
	}
	
	public function save_email_sent_subscriber($sent_email_id, $subscriber_id, $sent_success){
		global $wpdb;
		$table_name = $wpdb->prefix . 'el_email_sent_subscribers';

		$wpdb->insert( 
			$table_name, 
			array( 
				'time_sent' => current_time( 'mysql' ), 
				'sent_email_id' => $sent_email_id, 
				'subscriber_id' => $subscriber_id, 
				'sent' => $sent_success,
			) 
		);
		
		//echo "inside custom save";
		//die();
	}
	
	function set_html_content_type() {
		return 'text/html';
	}
	
	function save_sent_email($email_id, $email_title){
		//CMB2_FIELDS->update_data
		//$override = apply_filters( "cmb2_override_{$a['field_id']}_meta_save", $override, $a, $this->args(), $this );
		global $wpdb;
		//$name = "John";
		//$email_address = "jim@jim.com";
		$table_name = $wpdb->prefix . 'el_email_sent';

		$wpdb->insert( 
			$table_name, 
			array( 
				'time_sent' => current_time( 'mysql' ), 
				'email_id' => $email_id, 
				'email_title' => $email_title, 
				//'subscribed' => true,
			) 
		);
		return $wpdb->insert_id;
		//echo "inside custom save";
		//die();
	}
	
	

	/**
	*   Add the 'email-subscriber' query variable so Wordpress
	*   won't mangle it.
	*/
	function add_query_vars($vars){
	    $vars[] = "email-subscriber";
	    $vars[] = "unsubscribe";
	    $vars[] = "send-email";
	    return $vars;
	}
	
	function el_send_email_endpoint() {
		add_rewrite_endpoint( 'send-email', EP_ROOT );
	}
        
        function el_add_subscribe_endpoint() {
		add_rewrite_endpoint( 'email-subscriber', EP_ROOT );
	}

	function el_subscribe_template_redirect() {
		global $wp_query;
		
		// if this is not a request for json or a singular object then bail
		if ( !isset( $wp_query->query_vars['email-subscriber'] )){
			return;
		}
		if ($_SERVER['REQUEST_METHOD'] == 'POST'){
			echo 'is post email address';
			return;
		}
		wp_enqueue_script('el-referrer-redirect', plugin_dir_url(__FILE__).'js/referrerRedirect.js');
		$clean_email = sanitize_email($wp_query->query_vars['email-subscriber']);
		echo wp_head();
		if(isset($wp_query->query_vars['unsubscribe'])){
			$this->unsubscribe_subscriber($clean_email);
			echo $clean_email . " unsubscribed.";
			
		}
		else{
			$name = '';
			if(isset($wp_query->query_vars['name'])){
				$name = sanitize_text_field($wp_query->query_vars['name']);
			}
			
			if(!$clean_email == ''){
				$this->save_subscriber($clean_email, $name);
				$this->subscribe_subscriber($clean_email);
				echo $clean_email . " subscribed.";
			}
			else{
				echo "no subscribo!  Enter a valid email address.";
			}
		}
		echo '<p><a href="#" onClick="window.history.back()">Go Back</a>';
		//wp_enqueue_script('el-referrer-redirect', plugin_dir_url(__FILE__).'js/referrerRedirect.js');
		//echo '<script>setTimeout(function () {window.location.href = document.referrer; }, 1000);</script>';
		//echo '<script>setTimeout(function () {window.history.back(); }, 1000);</script>';
		exit;
	}
	
	
	function el_subscribe_template_redirect2() {
		global $wp_query;
		
		// if this is not a request for json or a singular object then bail
		if ( !isset( $wp_query->query_vars['email-subscriber'] )){
			return;
		}
		if ($_SERVER['REQUEST_METHOD'] == 'POST'){
			//echo 'is post email address';
			$email = $_POST['email'];
			$name = $_POST['firstname'];

		}
		else{
			$email = $wp_query->query_vars['email-subscriber'];
			$name = $wp_query->query_vars['name'];
		}
		wp_enqueue_script('el-referrer-redirect', plugin_dir_url(__FILE__).'js/referrerRedirect.js');
		$clean_email = sanitize_email($email);
		
		echo wp_head();
		if($clean_email != $email){
			echo "no subscribo!  Enter a valid email address.";
			exit;
		}
		if(isset($wp_query->query_vars['unsubscribe'])){
			$this->unsubscribe_subscriber($clean_email);
			echo $clean_email . " unsubscribed.";
			
		}
		else{
			$name = '';
			if(isset($name)){
				$name = sanitize_text_field($name);
			}
			
			if(!$clean_email == ''){
				$this->save_subscriber($clean_email, $name);
				$this->subscribe_subscriber($clean_email);
				echo $clean_email . " subscribed.";
			}
			else{
				echo "no subscribo!  Enter a valid email address.";
			}
		}
		echo '<p><a href="#" onClick="window.history.back()">Go Back</a>';
		//wp_enqueue_script('el-referrer-redirect', plugin_dir_url(__FILE__).'js/referrerRedirect.js');
		//echo '<script>setTimeout(function () {window.location.href = document.referrer; }, 1000);</script>';
		//echo '<script>setTimeout(function () {window.history.back(); }, 1000);</script>';
		exit;
	}

	/**
	 * Add the options metabox to the array of metaboxes
	 * @since  0.1.0
	 * @param  array $meta_boxes
	 * @return array $meta_boxes
	 */
	function add_options_page_metabox() {

		$cmb = new_cmb2_box( array(
			'id'      => $this->metabox_id,
			'hookup'  => false,
			'show_on' => array(
				// These are important, don't remove
				'key'   => 'options-page',
				'value' => array( $this->key, )
			),
		) );

		// Set our CMB2 fields

		$cmb->add_field( array(
			'name' => __( 'From Email', 'email_list' ),
			'desc' => __( 'Email address you want to in the From: header', 'email_list' ),
			'id'   => 'from_email',
			'type' => 'text',
			//'default' => 'Default Text',
		) );

		$cmb->add_field( array(
			'name'    => __( 'Name', 'email_list' ),
			'desc'    => __( 'Name you want went with the from email address.', 'email_list' ),
			'id'      => 'from_name',
			'type'    => 'text',
			//'default' => '#bada55',
		) );
		$cmb->add_field( array(
			'name'    => __( 'Email Footer', 'email_list' ),
			'desc'    => __( 'This will appear on the bottom of every email.', 'email_list' ),
			'id'      => 'email_footer',
			'type'    => 'wysiwyg',
			'options' => array( 'textarea_rows' => 6, ),
		) );

	}
	
	function add_subscribers_page_metabox() {

		$cmb = new_cmb2_box( array(
			'id'      => $this->subscribers_metabox_id,
			'hookup'  => false,
			'show_on' => array(
				// These are important, don't remove
				'key'   => 'subscribers',
				'value' => array( 'subscribers', )
			),
		) );

		// Set our CMB2 fields
		$cmb->add_field( array(
			'name' => __( 'Subscriber Name', 'email_list' ),
			//'desc' => __( 'field description (optional)', 'email_list' ),
			'id'   => 'subscriber_name',
			'type' => 'text',
			'default' => '',
		) );

		$cmb->add_field( array(
			'name' => __( 'Subscriber Email', 'email_list' ),
			//'desc' => __( 'field description (optional)', 'email_list' ),
			'id'   => 'subscriber_email',
			'type' => 'text',
			'default' => '',
		) );
		
	}
	
	
	function add_sendemail_page_metabox() {

		$cmb = new_cmb2_box( array(
			'id'      => $this->sendemail_metabox_id,
			'hookup'  => false,
			'show_on' => array(
				// These are important, don't remove
				'key'   => 'sendemail',
				'value' => array( 'sendemail', )
			),
		) );

		// Set our CMB2 fields

		$cmb->add_field( array(
			'name' => __( 'Email', 'email_list' ),
			//'desc' => __( 'field description (optional)', 'email_list' ),
			'id'   => 'send_email_id',
			'type' => 'select',
			//'default' => '',
			'options' => array( $this, 'get_all_emails_for_select' ),
			//'after_row' =>'<a href="'.site_url().'/?send-email=true">Send Email</a>',
			'after_row' =>'<a href="#" onclick="elConfirmSend(\''.site_url().'\');">Send Email</a>',
		) );	
		
	}
	
	/**
	 * Modify CMB2 Default Form Output
	 *
	 * @param  string  $form_format Form output format
	 * @param  string  $object_id   In the case of an options page, this will be the option key
	 * @param  object  $cmb         CMB2 object. Can use $cmb->cmb_id to retrieve the metabox ID
	 *
	 * @return string               Possibly modified form output
	 */
	function myprefix_options_modify_cmb2_metabox_form_format( $form_format, $object_id, $cmb ) {
	
	    if ( 'sendemail' == $object_id && $this->sendemail_metabox_id == $cmb->cmb_id ) {
	
	        //return '<form class="cmb-form" method="post" id="%1$s" enctype="multipart/form-data" encoding="multipart/form-data"><input type="hidden" name="object_id" value="%2$s">%3$s<div class="submit-wrap"><input type="submit" name="submit-cmb" value="' . __( 'Send', 'send' ) . '" class="button-primary"></div></form>';
	        return '<form class="cmb-form" method="post" id="%1$s" enctype="multipart/form-data" encoding="multipart/form-data"><input type="hidden" name="object_id" value="%2$s">%3$s<div class="submit-wrap"></div></form>';
	    }
	
	    return $form_format;
	}
	
	

	/**
	 * Defines the theme option metabox and field configuration
	 * @since  0.1.0
	 * @return array
	 */
	public function option_metabox() {
		return ;
	}

	/**
	 * Public getter method for retrieving protected/private variables
	 * @since  0.1.0
	 * @param  string  $field Field to retrieve
	 * @return mixed          Field value or exception is thrown
	 */
	public function __get( $field ) {
		// Allowed fields to retrieve
		if ( in_array( $field, array( 'key', 'metabox_id', 'fields', 'title', 'options_page' ), true ) ) {
			return $this->{$field};
		}

		throw new Exception( 'Invalid property: ' . $field );
	}

}

// Get it started
$GLOBALS['email_list_Admin'] = new email_list_Admin();
$GLOBALS['email_list_Admin']->hooks();

/**
 * Helper function to get/return the email_list_Admin object
 * @since  0.1.0
 * @return email_list_Admin object
 */
function email_list_Admin() {
	global $email_list_Admin;
	return $email_list_Admin;
}

/**
 * Wrapper function around cmb2_get_option
 * @since  0.1.0
 * @param  string  $key Options array key
 * @return mixed        Option value
 */
function email_list_get_option( $key = '' ) {
	global $email_list_Admin;
	return cmb2_get_option( $email_list_Admin->key, $key );
	
}
