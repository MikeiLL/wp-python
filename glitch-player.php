<?php

/*
Plugin Name: Glitch Player
Plugin URI: http://www.mZoo.org
Description: Glitch Player Interfaces with PyEchoNest use shortcode: [glitch-player]
Version: 1.0
Author: mikeill
Author URI: http://www.mZoo.org
*/


//define plugin path and directory
define( 'glitch_player_DIR', plugin_dir_path( __FILE__ ) );
define( 'glitch_player_URL', plugin_dir_url( __FILE__ ) );

//register activation and deactivation hooks
register_activation_hook(__FILE__, 'glitch_player_activation');
register_deactivation_hook(__FILE__, 'glitch_player_deactivation');

function glitch_player_deactivation() {
		delete_option('glitch_player_options');
		delete_transient( 'IS_json_cache' );
}

function glitch_player_activation() {
//might not need to do anything here
}

	function glitch_player_init() {
		wp_register_style('glitch_player_fe', plugins_url('/css/front_end.css',__FILE__ ));
		wp_enqueue_style('glitch_player_fe');
		}
	add_action( 'init','glitch_player_init');

add_shortcode( 'glitch-player', 'glitch_player_request' );

function glitch_player_request( $attributes ){
//echo "The shortcode is working, sir.<br/>";

   // $mystring = system('python hi.py myargs', $retval);
    
    $data = shortcode_atts(
        array(
            'file' => 'hello.py'
        ),
        $attributes
    );
    $path_and_file = glitch_player_DIR . $data['file'];
    $handle = popen( glitch_player_DIR . $data['file'], 'r');
    //var_dump($path_and_file);
    $read   = fread($handle, 2096);
    //var_dump($read);
    pclose($handle);
	//echo '<pre>';

// Outputs all the result of shellcommand "ls", and returns
// the last output line into $last_line. Stores the return value
// of the shell command in $retval.
$last_line = system('ls', $retval);

/* Printing additional info
echo '
</pre>
<hr />Last line of the output: ' . $last_line . '
<hr />Return value: ' . $retval;*/
    return $read;
}

if ( is_admin() ){ // admin actions
add_action ('admin_menu', 'glitch_player_settings_menu');
	function glitch_player_settings_menu() {
		//create submenu under Settings
		add_options_page ('Glitch Player Settings','Glitch Player',
		'manage_options', __FILE__, 'glitch_player_settings_page');
	}

	function glitch_player_settings_page() {
		?>
	<div class="wrap">
		<?php screen_icon(); ?>
		<form action="options.php" method="post">
			<?php settings_fields('glitch_player_options'); ?>
			<?php do_settings_sections('glitch_player'); ?>
			<input name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" />
		</form>
	</div>
	<?php
	}
			// Register and define the settings
	// Register and define the settings
add_action('admin_init', 'glitch_player_admin_init');
function glitch_player_admin_init(){

	register_setting(
		'glitch_player_options',
		'glitch_player_options',
		'glitch_player_validate_options'
	);

	add_settings_section(
		'glitch_player_main',
		'Glitch Player Options',
		'glitch_player_section_text',
		'glitch_player'
	);
	
	add_settings_section(
		'glitch_player_additional',
		'Additional Details',
		'glitch_player_section_two',
		'glitch_player'
	);


	add_settings_field(
		'glitch_player_is_default',
		'Image Snippets Default Account',
		'glitch_player_is_default',
		'glitch_player',
		'glitch_player_main'
	);
		add_settings_field(
		'glitch_player_clear_cache',
		'Force Cache Reset ',
		'glitch_player_clear_cache',
		'glitch_player',
		'glitch_player_main'
	);
		

}


function glitch_player_section_text() {
?><p>Enter the email address associated with your Image Snippets account below.<br/>
Use shortcode [glitch-player] to display gallery on page from default user account.<br/>
Shortcode [glitch-player user="user@email.com"] for alternate user
</p>
<?php
}

function glitch_player_section_two() {
?><p>Contact: <a href="http://www.imagesnippets.com"> www.imagesnippets.com</a></p>
<?php
}


// Display and fill the form field
function glitch_player_is_default() {
	// get option 'sig_default_account' value from the database
	$options = get_option( 'glitch_player_options','Option Not Set' );
	$sig_default_account = (isset($options['sig_default_account'])) ? $options['sig_default_account'] : 'Default Image Snippets Account';
	// echo the field
	echo "<input id='sig_default_account' name='glitch_player_options[sig_default_account]' type='text' value='$sig_default_account' />";
	}

// Display and fill the form field
function glitch_player_clear_cache() {
	$options = get_option( 'glitch_player_options','Option Not Set' );
printf(
    '<input id="%1$s" name="glitch_player_options[%1$s]" type="checkbox" %2$s />',
    'sig_clear_cache',
    checked( isset($options['sig_clear_cache']) , true, false )
);
}

// Validate user input (we want text only)
function glitch_player_validate_options( $input ) {
    foreach ($input as $key => $value)
    {
	$valid[$key] = wp_strip_all_tags(preg_replace( '/\s+/', '', $input[$key] ));
	if( $valid[$key] != $input[$key] ) {
			add_settings_error(
				'glitch_player_text_string',
				'glitch_player_texterror',
				'Does not appear to be valid ',
				'error'
			);
		 }
	}

	return $valid;
}


} //EOF Not Admin View

		//Format arrays for display in development
			function pr($data)
			{
			    echo "<pre>";
			    print_r($data);
			    echo "</pre>";
			}
			

	?>
