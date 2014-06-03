<?php

/*
Plugin Name: Glitch Player
Plugin URI: http://www.mZoo.org
Description: Glitch Player Interfaces with PyEchoNest use shortcode: [glitch-player]
Version: 1.0
Author: mikeill
Author URI: http://www.mZoo.org
*/

	//foreach ( glob( plugin_dir_path( __FILE__ )."../wp-miniaudioplayer/*.*" ) as $miniplayer )
	//    include_once $miniplayer;
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

/**
 * Proper way to enqueue scripts and styles
 */
function theme_name_scripts() {
		wp_register_style('glitch_player_fe', glitch_player_URL.'css/front_end.css');
		wp_enqueue_style('glitch_player_fe');
}

add_action( 'wp_enqueue_scripts', 'theme_name_scripts' );		

function ajaxglitch_player_enqueuescripts() {
    wp_enqueue_script('ajaxglitch_player', glitch_player_URL.'js/glitch_player.js', array('jquery'));
    wp_localize_script( 'ajaxglitch_player', 'ajaxglitch_playerajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
}
add_action('wp_enqueue_scripts', 'ajaxglitch_player_enqueuescripts');



add_action( 'wp_ajax_nopriv_ajaxglitch_player_ajaxhandler', 'ajaxglitch_player_ajaxhandler' );
add_action( 'wp_ajax_ajaxglitch_player_ajaxhandler', 'ajaxglitch_player_ajaxhandler' );


function glitch_player_show_make_mix(){
/*<form name="input" action="demo_form_action.asp" method="get">
Username: <input type="text" name="user">
<input type="submit" value="Submit">
</form> */
	$result = "";
    $nonce = wp_create_nonce("ajaxloadpost_nonce");
 	//$arguments =  get_the_ID().",'".$nonce."'";
 	$arguments = "'".$nonce."'";
    $link = ' <div id="make_button"><a onclick="glitch_player_display('.$arguments.');">'. "Make A Mix" .'</a></div>';
	$result .= '<h3>' . $link . '</h3>';
	$result .=  '<div id="showglitchplayer">';
	$result .= '<div id="t1" class="throb">
				<canvas style="width: 34px; height: 34px; display: block;" height="34" width="34"></canvas>
				</div>';
	$result .= '</div>';
	$result .= '<div id="play_button"><a title="The Major Glitch Original Version" href="'.glitch_player_URL.$nonce.'.mp3">First Mix</a></div>';
    return $result;
}
	
add_shortcode( 'glitch-player', 'ajaxglitch_player_shortcode_function' );

function ajaxglitch_player_shortcode_function( $atts ){
    return glitch_player_show_make_mix();
}

function ajaxglitch_player_ajaxhandler(){
	$mix_name = isset( $_POST['mix_name'] )? $_POST['mix_name'] : false;
	var_dump( "The mix_name is $mix_name" ); // this will probably break the AJAX response
	error_log( "The mix_name is $mix_name" ); // write it to the error_log too.
	//$nonce = wp_create_nonce("ajaxloadpost_nonce");
	//$nonce = "illery";
	$my_result = '<div id="play_button"><a title="The Major Glitch Your Mix" href="'.glitch_player_URL.$mix_name.'.mp3">Your Mix</a></div>';
	$my_result .= "<hr/>";
	
	$description = array (     
		0 => array("pipe", "r"),  // stdin
		1 => array("pipe", "w"),  // stdout
		2 => array("pipe", "w")   // stderr
	);
	
	$pipes = array();
 	//pr($_ENV);
 	$env = array(
    'PATH' => 
    '/usr/bin:/bin:/usr/sbin:/sbin:/usr/local/bin',
    'ECHO_NEST_API_KEY' =>
    'TX2IDAM1HXOO99YPB'
);
	$argvs = array();//initialize the array or song track arguments
		
	//make array from all the files in audio folder
	foreach ( glob( plugin_dir_path( __FILE__ )."../../uploads/2014/04/audio/*.mp3" ) as $file )
	    array_push($argvs, substr($file, strlen(plugin_dir_path( __FILE__ ))));
	    
	//dynamically build the sub-process call
	$child_process = "python glitcher/glitchmix.py -v -e -u ".$_POST['mix_name']." ";
	shuffle($argvs);
	$i = 0;
	foreach($argvs as $track){
		if (($i < 2) && (!strpos($track,"12_44"))) // we'll limit the number of tracks and ignore the 12_44 long one
			$child_process .= $track." ";
		$i++;}
	
	$proc = proc_open ( $child_process , $description , $pipes, glitch_player_DIR, $env );

// set all streams to non blockin mode
stream_set_blocking($pipes[1], 0);
stream_set_blocking($pipes[2], 0);

// get PID via get_status call
$status = proc_get_status($proc);
if($status === FALSE) {
    throw new Exception (sprintf(
        'Failed to obtain status information '
    ));
}
$pid = $status['pid'];
// now, poll for childs termination
while(true) {
    // detect if the child has terminated - the php way
    $status = proc_get_status($proc);
    // check retval
    if($status === FALSE) {
        throw new Exception ("Failed to obtain status information for $pid");
    }
    if($status['running'] === FALSE) {
        $exitcode = $status['exitcode'];
        $pid = -1;
        echo "child exited with code: $exitcode\n";
        exit($exitcode);
    }

    // read from childs stdout and stderr
    // avoid *forever* blocking through using a time out (50000usec)
    foreach(array(1, 2) as $desc) {
        // check stdout for data
        $read = array($pipes[$desc]);
        $write = NULL;
        $except = NULL;
        $tv = 0;
        $utv = 50000;

        $n = stream_select($read, $write, $except, $tv, $utv);
        if($n > 0) {
            do {
                $data = fread($pipes[$desc], 8092);
                echo $data . "\n<br/>";
            } while (strlen($data) > 0);
        }
    }
    /*$read = array(STDIN);
    $n = stream_select($read, $write, $except, $tv, $utv);
    if($n > 0) {
        
    }*/
}
	//TEST
	echo $child_process;
	
	echo "<br/>Is ".$child_process." executable? ".is_executable($application_test)." ";
	echo "readable? ".is_readable($child_process)." ";
	echo "writable? ".is_writable($child_process)." ";
	//END TEST
	$my_result .= ' and there is is...<hr/>';
	return $my_result;
} //EOF main/shorcode function

if ( is_admin() ){  //BOF Admin View
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
		'glitch_player_default',
		'Image Snippets Default Account',
		'glitch_player_default',
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
function glitch_player_default() {
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


} //EOF Admin View

		//Format arrays for display in development
			function pr($data)
			{
			    echo "<pre>";
			    print_r($data);
			    echo "</pre>";
			}
			

	?>
