<?php
/**
 * Plugin Name:       Reported comments
 * Plugin URI:        https://www.tooltipy.com
 * Description:       Advanced comments report plugin
 * Version:           1.0
 * Requires at least: 4.7
 * Author:            Jamel Zarga
 * Author URI         http://www.tooltipy.com/about-us
 * License:           GPL v2 or later
*/

use Lebleut\Plugin\CommentReporter;

// Constants
define( 'REPORTED_COMMENTS_DOMAIN', 'lebleut_comment_reporter' );

define( 'REPORTED_COMMENTS_DIR', plugin_dir_path( __FILE__ ) );
define( 'REPORTED_COMMENTS_URL', plugin_dir_url( __FILE__ ) );

define( 'REPORTED_COMMENTS_DIR_FILE', basename( __DIR__ ) . '/' . basename( __FILE__ ) );

// Requires
require_once REPORTED_COMMENTS_DIR . '/inc/class-comment-report.php';


// Instanciation
new CommentReporter();
