<?php
/**
 * Plugin Name:       Quinn
 * Description:       Quinn Shoppable videos
 * Requires at least: 6.1
 * Requires PHP:      7.0
 * Version:           0.1.2
 * Author:            The WordPress Contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       quinn
 *
 * @package CreateBlock
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function quinn_block_init() {
	register_block_type( __DIR__ . '/build/quinn-card' );
	register_block_type( __DIR__ . '/build/quinn-story' );
}
add_action( 'init', 'quinn_block_init' );

/**
 * Include common php file, currenty used to get site domain name
 */
include_once(plugin_dir_path(__FILE__) . 'includes/common.php');


/**
 * Enqueue the common JavaScript files.
 */
function quinn_enqueue_common_scripts() {
	/**
	 * These are essential scripts that are required for the plugin to work when the plugin is activated on the site.
	 */
	$current_domain = quinn_get_domain_name();
	$init_script_url = "https://assets.quinn.live/$current_domain/quinn-init.js";
	wp_enqueue_script(
		'quinn-init',
		$init_script_url,
		array(), // Dependencies, if any.
		"0.1.0", // Version
		array(
		'strategy'  => 'defer',
		) 
	);

	wp_enqueue_script(
		'quinn-vendor',
		"https://assets.quinn.live/woocommerce/quinn-vendor.bundle.js",
		array(),
		"0.1.0", 
		array(
		'strategy'  => 'defer',
		) 
	);
	wp_enqueue_script(
		'quinn-live',
		"https://assets.quinn.live/woocommerce/quinn-live.bundle.js",
		array(), 
		"0.1.0",
		array(
		'strategy'  => 'defer',
		) 
	);
	wp_enqueue_script(
		'quinn-floating',
		"https://assets.quinn.live/woocommerce/quinn-floating.bundle.js",
		array(), 
		"0.1.0",
		array(
		'strategy'  => 'defer',
		) 
	);
}
add_action( 'wp_enqueue_scripts', 'quinn_enqueue_common_scripts' );

/**
 * Add Quinn menu item to the WordPress admin sidebar
 */
function quinn_add_admin_menu() {
	add_menu_page(
		'Quinn Dashboard', // Page title
		'Quinn', // Menu title
		'manage_options', // Capability
		'quinn_dashboard', // Menu slug
		'quinn_render_dashboard_page', // Callback function
		'dashicons-admin-generic', // Icon
		2 // Position
	);
}
add_action( 'admin_menu', 'quinn_add_admin_menu' );



/**
 * Include dashboard file
 */
include_once(plugin_dir_path(__FILE__) . 'includes/dashboard.php');


function quinn_add_inline_script() {
    wp_add_inline_script('quinn-floating', '
        document.addEventListener("DOMContentLoaded", function () {
            window.Quinn.functions.renderQuinn({
                widgetType: "floating",
            });
        });
    ');
}
add_action('wp_enqueue_scripts', 'quinn_add_inline_script');





