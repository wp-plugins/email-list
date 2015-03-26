<?php
global $post;
//get_header(); 
$localPrefix = EmailListCustomPostType::$staticPrefix;
$email = get_post_meta($post->ID, $localPrefix . 'email_message', true);
?>
<a href="#" onClick="window.history.back()">Go Back</a>
<div class="el_emailPreview">
<?php echo wpautop($email); ?>
</div>

<?php //get_footer(); 
?>