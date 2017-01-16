<?php
/*
Plugin Name: WPLMS Course Custom Nav Plugin
Plugin URI: http://www.Vibethemes.com
Description: A simple WordPress plugin to modify WPLMS navs template
Version: 1.2
Author: VibeThemes
Author URI: http://www.vibethemes.com
License: GPL2
Text Domain : wplms-ccn
*/

/*
Copyright GPLv2

WPLMS Course Custom Nav Plugin program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

WPLMS Course Custom Nav Plugin program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with wplms_customizer program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}
if ( in_array( 'vibe-customtypes/vibe-customtypes.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) && in_array( 'vibe-course-module/loader.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
include_once 'classes/course_custom_nav_class.php';
include_once 'classes/init.php';
}

if(class_exists('WPLMS_Course_Custom_Nav_Plugin_Class'))
{	
    // Installation and uninstallation hooks
    register_activation_hook(__FILE__, array('WPLMS_Course_Custom_Nav_Plugin_Class', 'activate'));
    register_deactivation_hook(__FILE__, array('WPLMS_Course_Custom_Nav_Plugin_Class', 'deactivate'));
}

//autoupdate:

add_action( 'init', 'wplms_course_custom_nav_update' );
function wplms_course_custom_nav_update() {
	/* Load Plugin Updater */
	require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'autoupdate/class-plugin-update.php' );
	/* Updater Config */
	$config = array(
		'base'      => plugin_basename( __FILE__ ), //required
		'dashboard' => true,
		'repo_uri'  => 'http://www.vibethemes.com/',  //required
		'repo_slug' => 'wplms-ccn',  //required
	);

	/* Load Updater Class */
	new WPLMS_Course_Custom_Nav_Plugin_Class_Update( $config );
}




