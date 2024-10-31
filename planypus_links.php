<?php
/*
Plugin Name: Planypus Links
Version: 1.1
Plugin URI: http://planyp.us/link
Description: Adds a footer link to let users create plans from your event posts.  By default, uses post timestamp as scheduled time, and 'planypus_event_location' custom field as street address.
Author: Planypus
Author URI: http://planyp.us
*/

/*
Change Log

1.0
  * First public release.
1.1 
  * support for subplugin for integration with calendaring plugins in wordpress.
  * open planypus in a new window.
  * support for date/time/location
1.2
  * add support for location name.
  * escape values for html
*/
function add_to_planypus($data){
	global $post;
	$current_options = get_option('add_to_planypus_options');
	$linktext = $current_options['link_text'];
  $date_time = get_date_time_for_planypus($post);

	$data=$data.
    "<form target='_blank' method='post' action='http://planyp.us/plans/new'><input type='hidden' name='url' value='".get_permalink($post->ID). "'/>\n" .
    "<textarea style='display:none' name='description'>". htmlspecialchars($post->post_content) . "</textarea>\n". 
    "<textarea name='title' style='display:none'>". htmlspecialchars($post->post_title) . "</textarea>" .
    "<button type='submit' class='planypus-submit'><img src='http://planyp.us/favicon.ico' style='width:16px; height:16px'/>". $linktext ."</button>\n".
    "<input type='hidden' name='location' value=\"" . htmlspecialchars(get_location_for_planypus($post)) . "\"/>\n" .
    "<input type='hidden' name='location_name' value=\"" . htmlspecialchars(get_location_name_for_planypus($post)) . "\"/>\n" .
    "<input type='hidden' name='time' value=\"" . htmlspecialchars($date_time[1]) . "\"/>\n" .
    "<input type='hidden' name='date' value=\"" . htmlspecialchars($date_time[0]) . "\"/>\n</form>";
	return $data;
}

function get_location_for_planypus($post) {
  global $planypus_subplugin_get_location;
  if (isset($planypus_subplugin_get_location)) {
    return planypus_subplugin_get_location($post);
  }
  
  return get_post_meta($post->ID, 'planypus_event_location', true);
  
}

function get_location_name_for_planypus($post) {
  global $planypus_subplugin_get_location_name;
  if (isset($planypus_subplugin_get_location_name)) {
    return planypus_subplugin_get_location_name($post);
  }
  
  return get_post_meta($post->ID, 'planypus_event_location_name', true);
  
}

function get_date_time_for_planypus($post) { 
  global $planypus_subplugin_get_date_time;
  if (isset($planypus_subplugin_get_date_time)) {
    return planypus_subplugin_get_date_time($post);
  }
  return array($post->post_date);
}

add_filter('the_content', 'add_to_planypus');
add_filter('the_excerpt', 'add_to_planypus');
add_action('wp_head', 'stuff_for_head');

function stuff_for_head() {
?>
	<style type='text/css'>
		.planypus-submit {
			background-color: transparent;
			font-size: 14px;
			text-decoration: underline;
			cursor: pointer;
			cursor: hand;
			border: 0;
			margin-left: -3px;
		}
		.planypus-submit img {
			margin-bottom: -3px;
			margin-right: 3px;
		}
	</style>
<?php
}
// Create the options page
function add_to_planypus_options_page() { 
	$current_options = get_option('add_to_planypus_options');
	$linktext = $current_options["link_text"];
	if ($_POST['action']){ ?>
		<div id="message" class="updated fade"><p><strong>Options saved.</strong></p></div>
	<?php } ?>
	<div class="wrap" id="add-to-facebook-options">
		<h2>Planypus Links Options</h2>
		<p>Here you can set what the text of the link will read.</p>
		<p>You may style your link buttons by editing the css in planypus_links.php at the top. </p>
		<form method="post" action="<?php echo $_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']; ?>">
			<fieldset>
				<h3>Options:</h3>
				<input type="hidden" name="action" value="save_options" />
				<label for="link_text">Link Text:</label>
				<input name='link_text' value ='<?php echo $linktext ?>'/>
			</fieldset>
			<p class="submit">
				<input type="submit" name="Submit" value="Update Options &raquo;" />
			</p>
		</form>
	</div>
<?php 
}

function add_to_planypus_add_options_page() {
	// Add a new menu under Options:
	add_options_page('Planypus Links', 'Planypus Links', 10, __FILE__, 'add_to_planypus_options_page');
}

function add_to_planypus_save_options() {
	// create array
	$add_to_planypus_options["link_text"] = $_POST["link_text"];
	
	update_option('add_to_planypus_options', $add_to_planypus_options);
	$options_saved = true;
}

add_action('admin_menu', 'add_to_planypus_add_options_page');

if (!get_option('add_to_planypus_options')){
	// create default options
	$add_to_planypus_options["link_text"] = 'Plan This on Planypus';
	
	update_option('add_to_planypus_options', $add_to_planypus_options);
}

if ($_POST['action'] == 'save_options'){
	add_to_planypus_save_options();
}

?>
