<?php 
/*
Plugin Name: Custom Plugin
Plugin URI: https://ivan.com
Description: Custom plugin for modals
Version: 1.0
Author: Ivan
Author URI: https://ivan
*/


if (session_id() === "") {
	session_start();
}

// create table
function createTable(){
	global $wpdb;

	$table_name = $wpdb->prefix . 'modal';

	if ($wpdb->get_var('SHOW TABLES LIKE ' . $table_name) != $table_name) {
		$sql = 'CREATE TABLE ' . $table_name . '(id INTEGER(10) AUTO_INCREMENT, post_id INTEGER(10),post_type VARCHAR(50),title VARCHAR(50),text VARCHAR(50), post_status INTEGER(10), PRIMARY KEY   (id));';

		require_once(ABSPATH . 'wp-admin/upgrade.php');

		dbDelta($sql);
	}
}

register_activation_hook(__FILE__, 'createTable');


// import css

function add_css() {
	wp_enqueue_style( 'bootstrap', 'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css');
    wp_enqueue_style( 'custom-style', content_url() . '/plugins/customplugin/css/custom.css');
}

add_action( 'wp_enqueue_scripts', 'add_css', 20);


// import js

function add_js() {
    wp_enqueue_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js');
    wp_enqueue_script('proper', 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js');
    wp_enqueue_script('bootstrap', 'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js');
}

add_action( 'wp_enqueue_scripts', 'add_js');

// add tab to settings


add_action('admin_menu', 'custom_plugin_admin_actions');

function custom_plugin_admin_actions() { 
 
  add_options_page('Custom Plugin', 'Custom Plugin', 'manage_options', __FILE__, 'function_to_display_menu_page');
}


// view
function function_to_display_menu_page(){
?>
<form action="" method="post" accept-charset="utf-8">
	<h3>Title:</h3>
	<input type="text" name="title">
	<h3>Text:</h3>
	<textarea name="text" cols="50" rows="5"> </textarea>

<div id="list-posts">
</div>
	<input type="submit" id="save" name="save" value="Save Changes">
</form>

<div id="categories">
	<h3>Check to list posts/pages:</h3>
	<p>Posts<input type="checkbox" value="post" name="post" id="post" class="post-type"></p>
	<p>Pages<input type="checkbox" value="page" name="page" id="page" class="post-type"></p>
	<?php $post_types = get_post_types(array('_builtin' => false)); ?>
	<?php
		foreach ( $post_types as $post_type ) {
	?>
	<p><?php echo $post_type; ?><input type="checkbox" class="post-type" name="<?php echo $post_type; ?>" value="<?php echo $post_type; ?>"></p>
	<?php
	}
	?>
</div>



<script>
	// ajax request for listing posts
	jQuery('.post-type').click(function(){
		if (jQuery(this).is(':checked')) {


			var dataSend = jQuery(this).val();
			
			if(dataSend.length>0){

				jQuery.ajax({
					type: "POST",
					url: "<?php echo content_url() . '/plugins/customplugin/ajax/listposts.php' ?>",
					data: { post: dataSend},
					beforeSend: function(){ 
						
					}
					}).done(function(data) {

						jQuery('#list-posts').append(data);

				});
				}else{
					jQuery('#list-posts').html('');
				}


		}else{
			jQuery('#list-posts').html('');
		}
	});
</script>

<?php } ?>

<?php 

// insert into database
if (isset($_POST['save'])) {
	$title = $_POST['title'];
	$text = $_POST['text'];

	global $wpdb;
	$table = $wpdb->prefix . 'modal';

	foreach ($_SESSION['info'] as $key => $value) {
		$explode_post_data = explode(',', $value);
		$id = $explode_post_data[0];
		$type = $explode_post_data[1];
		$data = array('post_id' => $id, 'title' => $title, 'text' => $text, 'post_type' => $type, 'post_status' => 1);
		$wpdb->insert($table,$data);
	}
}

 ?>


<!-- add array to session -->
<script>
	jQuery(':checkbox[name=all_posts]').on('change', function() {
    var assignedTo = jQuery(':checkbox[name=all_posts]:checked').map(function() {
    	var info = [];
    	info.push(jQuery(this).attr('post-id') + ',' + jQuery(this).attr('post-type') + ',' + jQuery(this).attr('post-status'));
        return info;
        
    })
    .get();

    jQuery.ajax({
					type: "POST",
					url: "<?php echo content_url() . '/plugins/customplugin/ajax/savetosession.php' ?>",
					data: { info: assignedTo},
					beforeSend: function(){ 
						
					}
					}).done(function(data) {

				});
});
</script>


<?php 
// check on frontend

function openModal(){
	global $wpdb;
	$all_posts = $wpdb->get_results("SELECT * FROM wp_posts");
	$selected_posts = $wpdb->get_results("SELECT * FROM wp_modal");

	foreach ($all_posts as $single_post) {
		foreach ($selected_posts as $selected_post) {
			if ($single_post->ID == $selected_post->post_id) {
				if (is_single ($selected_post->post_id) || is_page($selected_post->post_id) || is_singular($selected_post->post_id)) { 
    			?>
       		
       		<div id="myModal" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
      	<h4 class="modal-title"><?php echo $selected_post->title; ?></h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <p><?php echo $selected_post->text; ?></p>
      </div>
    </div>

  </div>
</div>

<script>
	 jQuery(window).load(function(){        
   jQuery('#myModal').modal('show');
    }); 
</script>

   			 <?php
  			}
			}
		}
	}
	
	
}
add_action ( 'wp_footer', 'openModal' );

// add admin css
function my_admin_theme_style() {
    wp_enqueue_style('my-admin-theme', plugins_url('wp-admin.css', __FILE__));
}
add_action('admin_enqueue_scripts', 'my_admin_theme_style');
add_action('login_enqueue_scripts', 'my_admin_theme_style');

 ?>
 