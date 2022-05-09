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

	add_shortcode('popup_modal', __NAMESPACE__ . '\\shortcode_modal');
	add_shortcode('page_content', __NAMESPACE__ . '\\shortcode_page_content');
	add_shortcode('dynamic_load', __NAMESPACE__ . '\\shortcode_dynamic_load');
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

$modal_count = 1;
/**
 * Render pop-up modal
 * [popup_modal type="button|link" title="My Modal Title" size="lg" text="Click to Open"]
 *
 * @param $atts
 * @param $content
 * @return false|string
 */
function shortcode_modal($atts, $content) {
	$default_atts = [
		'type' => 'link',
		'url' => null,
		'classes' => '',
		'text' => '[text]',
		'title' => '[title]',
		'size' => ''
	];

	global $modal_count;

	enqueue_block_styles();

	$atts = shortcode_atts($default_atts, $atts);

	$modal_id = 'popup-modal' . $modal_count;
	$modal_count += 1;

	if (str_starts_with(ltrim($content), '</p>'))
		$content = "<p>$content</p>";

	if ($atts['size'] == 'lg')
		$atts['size'] = 'modal-lg';

	if ($atts['type'] == 'button' && empty($atts['classes']))
		$atts['classes'] = 'btn btn-primary';

	$attributes = 'class="' . $atts['classes'] . '" ';
	$dynamic = !empty($atts['url']);
	$min = "";
	$modal_attributes = "";
	$indicator = "";
	if ($dynamic) {
		wp_enqueue_script(PLUGIN_SLUG . '-htmx-js', plugins_url('/src/htmx' . $min . '.js', ROOT_FILE), array(),
				filemtime(ROOT_DIR . '/src/htmx' . $min . '.js'), true);

		$indicator = '<img class="htmx-indicator" id="' . $modal_id . '-ind" src="' . plugins_url('/src/spinner.svg', ROOT_FILE) . '">';
		$attributes .= 'hx-get="' . $atts['url'] . '"
			hx-target="#' . $modal_id . ' .modal-body"
			hx-trigger="click"
			hx-indicator="#' . $modal_id . '-ind" ';
	}// else {
		$attributes .= ' data-bs-toggle="modal" data-bs-target="#' . $modal_id . '" ';
	//}

	ob_start();
	if ($atts['type'] == 'button'):
		?>
		<button type="button" <?= $attributes ?>>
			<?= $atts['text'] ?>
		</button>
	<?php
	else:
	?>
		<a href="#<?= $modal_id ?>" <?= $attributes ?>>
			<?= $atts['text'] ?>
		</a>
	<?php
	endif;
	?>
	<div class="modal fade" <?php echo $modal_attributes ?> id="<?= $modal_id ?>" tabindex="-1" role="dialog" aria-labelledby="<?= $modal_id ?>Title" aria-hidden="true">
		<div class="modal-dialog <?= $atts['size'] ?>" role="document">
			<div class="modal-content">
				<div class="modal-header sticky-modal-header">
					<h5 class="modal-title" id=""><?= $atts['title'] ?></h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<?php echo do_shortcode($content) ?>
					<?php echo $indicator ?>
				</div>
			</div>
		</div>
	</div>

<?php
	$content = ob_get_contents();
	ob_end_clean();
	return $content;
}

function shortcode_dynamic_load($atts, $content) {
	$atts = shortcode_atts([
		'url' => "",
		'id' => wp_generate_password(6, false),
		'trigger' => 'load'
	], $atts);

	$indicator = $atts['id'] . '-ind';
	return '<img class="htmx-indicator" id="' . $indicator . '" src="' . plugins_url('/src/spinner.svg', ROOT_FILE) . '">
	<div hx-trigger="' . $atts['trigger'] . '" id="' . $atts['id'] . '" hx-get="' . $atts['url'] . '" hx-indicator="#' . $indicator . '">' . do_shortcode($content) . '</div>';
}

/**
 * Render page content by path or id
 * [page_content page="terms-of-use"]
 * [page_content page="125"]
 * @param $atts
 * @return string
 */
function shortcode_page_content($atts) {

	$atts = shortcode_atts([
		'page' => ''
	], $atts);

	if (is_numeric($atts['page']))
		$page = get_post($atts['page']);
	else
		$page = get_page_by_path($atts['page']);
	if (!empty($page)) {
		return do_shortcode($page->post_content);
	}

	return "";
}

add_action( 'plugins_loaded', __NAMESPACE__ . '\\setup' );
