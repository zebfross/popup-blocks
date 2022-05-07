<?php
/**
 * Plugin Name:       Popup Blocks
 * Description:       Dynamic pop-ups and modals for tooltips and everything else
 * Requires at least: 5.8
 * Requires PHP:      7.0
 * Version:           0.1.0
 * Author:            Zeb Fross
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       popup-blocks
 *
 * @package           popupblocks
 */

namespace PopupBlocks;


const PLUGIN_ADDED    = '2021-09-27';
const PLUGIN_PREFIX   = 'popup_blocks';
const PLUGIN_REQUIRES = '5.8';
const PLUGIN_SLUG     = 'popup-blocks';
const PLUGIN_TESTED   = '5.8.2';
const PLUGIN_VERSION  = '1.1.0';
const ROOT_DIR        = __DIR__;
const ROOT_FILE       = __FILE__;

function setup() : void {
	// Enqueue Block Editor Assets.
	add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\\enqueue_block_editor_assets', 10 );

	// Enqueue Block Styles for Frontend and Backend.
	add_action( 'enqueue_block_assets', __NAMESPACE__ . '\\enqueue_block_styles', 10 );

}

/**
 * Enqueue Block Editor Assets
 *
 * @throws \Error Warn if asset dependencies do not exist.
 *
 * @return void
 */
function enqueue_block_editor_assets() : void {

	$asset_path = ROOT_DIR . '/build/index.asset.php';

	if ( ! file_exists( $asset_path ) ) {
		throw new \Error(
			'You need to run `npm start` or `npm run build` in the root of the plugin first.'
		);
	}

	$scripts = '/build/index.js';
	$assets  = include $asset_path;

	wp_enqueue_script(
		PLUGIN_SLUG . '-block-scripts',
		plugins_url( $scripts, ROOT_FILE ),
		$assets['dependencies'],
		$assets['version'],
		false
	);

}

/**
 * Enqueue Block Styles for Frontend and Backend.
 *
 * @return void
 */
function enqueue_block_styles() : void {

	$styles = '/build/style-index.css';

	wp_enqueue_style(
		PLUGIN_SLUG . '-block-styles',
		plugins_url( $styles, ROOT_FILE ),
		array(),
		filemtime( ROOT_DIR . $styles )
	);

	wp_enqueue_script(PLUGIN_SLUG . '-bootstrap-js', plugins_url('/node_modules/bootstrap/dist/js/bootstrap.bundle.min.js', ROOT_FILE), array('jquery'), 1, true);
	wp_enqueue_script(PLUGIN_SLUG . '-popup-blocks-js', plugins_url('/src/index-frontend.js', ROOT_FILE), array(PLUGIN_SLUG . '-bootstrap-js'),
		filemtime(ROOT_DIR . '/src/index-frontend.js'), true);
}

add_action( 'plugins_loaded', __NAMESPACE__ . '\\setup' );
