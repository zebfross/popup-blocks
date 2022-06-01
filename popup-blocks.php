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

namespace {

	$IN_POPUP_MODAL = false;
}

namespace PopupBlocks {


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
	add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\enqueue_block_styles', 10 );

	add_shortcode('popup_modal', __NAMESPACE__ . '\\shortcode_modal');
	add_shortcode('popup_modal_button', __NAMESPACE__ . '\\shortcode_modal_button');
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

	wp_enqueue_style(PLUGIN_SLUG . '-bootstrap-icons',
	plugins_url("/node_modules/bootstrap-icons/font/bootstrap-icons.css", ROOT_FILE));

	$min = "";
	wp_enqueue_script(PLUGIN_SLUG . '-htmx-js', plugins_url('/src/htmx' . $min . '.js', ROOT_FILE), array(),
			filemtime(ROOT_DIR . '/src/htmx' . $min . '.js'), true);
	wp_enqueue_script(PLUGIN_SLUG . '-bootstrap-events-js', plugins_url('/node_modules/bootstrap/js/dist/dom/event-handler.js', ROOT_FILE), array(), 1, true);
	wp_enqueue_script(PLUGIN_SLUG . '-bootstrap-data-js', plugins_url('/node_modules/bootstrap/js/dist/dom/data.js', ROOT_FILE), array(), 1, true);
	wp_enqueue_script(PLUGIN_SLUG . '-bootstrap-selector-js', plugins_url('/node_modules/bootstrap/js/dist/dom/selector-engine.js', ROOT_FILE), array(), 1, true);
	wp_enqueue_script(PLUGIN_SLUG . '-bootstrap-manip-js', plugins_url('/node_modules/bootstrap/js/dist/dom/manipulator.js', ROOT_FILE), array(), 1, true);
	wp_enqueue_script(PLUGIN_SLUG . '-bootstrap-base-js', plugins_url('/node_modules/bootstrap/js/dist/base-component.js', ROOT_FILE), array(PLUGIN_SLUG . '-bootstrap-events-js', PLUGIN_SLUG . '-bootstrap-data-js', PLUGIN_SLUG . '-bootstrap-selector-js', PLUGIN_SLUG . '-bootstrap-manip-js'), 1, true);
	wp_enqueue_script(PLUGIN_SLUG . '-bootstrap-popper-js', plugins_url('/src/popper.js', ROOT_FILE), array(PLUGIN_SLUG . '-bootstrap-base-js'), 2, true);
	wp_enqueue_script(PLUGIN_SLUG . '-bootstrap-alert-js', plugins_url('/node_modules/bootstrap/js/dist/alert.js', ROOT_FILE), array(PLUGIN_SLUG . '-bootstrap-popper-js'), 1, true);
	wp_enqueue_script(PLUGIN_SLUG . '-bootstrap-modal-js', plugins_url('/node_modules/bootstrap/js/dist/modal.js', ROOT_FILE), array(PLUGIN_SLUG . '-bootstrap-popper-js'), 1, true);
	wp_enqueue_script(PLUGIN_SLUG . '-bootstrap-dropdown-js', plugins_url('/node_modules/bootstrap/js/dist/dropdown.js', ROOT_FILE), array(PLUGIN_SLUG . '-bootstrap-popper-js'), 1, true);
	wp_enqueue_script(PLUGIN_SLUG . '-bootstrap-tooltip-js', plugins_url('/node_modules/bootstrap/js/dist/tooltip.js', ROOT_FILE), array(PLUGIN_SLUG . '-bootstrap-popper-js'), 1, true);
	wp_enqueue_script(PLUGIN_SLUG . '-bootstrap-toast-js', plugins_url('/node_modules/bootstrap/js/dist/toast.js', ROOT_FILE), array(), 1, true);
	wp_enqueue_script(PLUGIN_SLUG . '-popup-blocks-js', plugins_url('/src/index-frontend.js', ROOT_FILE), array(PLUGIN_SLUG . '-bootstrap-js'),
		filemtime(ROOT_DIR . '/src/index-frontend.js'), true);
}

$modal_count = random_int(1, 1000000);

function friendly_modal($url, $text=null, $content='', $title=null, $type='link', $size=null, $ajax=null, $swap=true, $isForm='true', $classes='', $id='', $form_button='Save') {
	if (is_callable($content)) {
		$content = $content();
	}
	return shortcode_modal([
		'url' => $url,
		'type' => $type,
		'text' => $text,
		'size' => $size,
		'title' => $title,
		'ajax' => $ajax,
		'swap' => $swap,
		'form' => $isForm,
		'classes' => $classes,
		'id' => $id,
		'form_button' => $form_button
	], $content);
}

function friendly_modal_button($id, $url, $text=null, $type='link', $ajax=null, $classes='') {

	return shortcode_modal_button([
			'id' => $id,
			'url' => $url,
			'type' => $type,
			'text' => $text,
			'ajax' => $ajax,
			'classes' => $classes
	]);
}

function shortcode_modal_button($atts, $content="") {
	if (wp_is_json_request())
		return "";

	$default_atts = [
		'type' => 'link',
		'url' => '',
		'ajax' => false,
		'classes' => '',
		'text' => '[text]',
		'id' => ''
	];

	$atts = shortcode_atts($default_atts, $atts);
	$modal_id = $atts['id'];

	if (!empty($atts['ajax']) && class_exists('\Redify\Integrations\RestApi')) {
		$atts['url'] = \Redify\Integrations\RestApi::redify_ajax_url($atts['ajax']);
	}

	if ($atts['type'] == 'button' && empty($atts['classes']))
		$atts['classes'] .= 'btn btn-primary';

	$attributes = 'class="' . $atts['classes'] . '" ';
	$dynamic = !empty($atts['url']);
	if ($dynamic) {
		$attributes .= 'hx-get="' . $atts['url'] . '"
			hx-target="#' . $modal_id . ' .modal-body"
			hx-trigger="click"
			hx-indicator="#' . $modal_id . '-ind"
			hx-swap="innerHTML"
			';
	}
	$attributes .= ' data-bs-toggle="modal" data-bs-target="#' . $modal_id . '" ';

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
	return ob_get_clean();
}

/**
 * Render pop-up modal
 * [popup_modal type="button|link" title="My Modal Title" size="lg" text="Click to Open"]
 *
 * @param $atts
 * @param $content
 * @return false|string
 */
function shortcode_modal($atts, $content="") {
	if (wp_is_json_request())
		return "";

	global $modal_count;

	$default_atts = [
		'type' => 'link',
		'url' => null,
		'ajax' => null,
		'classes' => '',
		'text' => '[text]',
		'title' => '',
		'size' => '',
		'swap' => true,
		'form' => 'true',
		'id'   => '',
		'form_button' => 'Save'
	];

	global $IN_POPUP_MODAL;
	$IN_POPUP_MODAL = true;

	enqueue_block_styles();

	$atts = shortcode_atts($default_atts, $atts);

	if (empty($atts['id']))
		$atts['id'] = 'popup-modal' . $modal_count;

	$modal_id = $atts['id'];
	$modal_count += 1;

	if (str_starts_with(ltrim($content), '</p>'))
		$content = "<p>$content</p>";

	if ($atts['size'] == 'lg')
		$atts['size'] = 'modal-lg';

	$modal_attributes = "";
	$indicator = '<div class="htmx-indicator indicator-full" id="' . $modal_id . '-ind"><img src="' . plugins_url('/src/spinner.svg', ROOT_FILE) . '"></div>';

	$modal_attributes .= '
			_="on formsaved from body hide_modal(\'#' . $modal_id . '\')"';

	ob_start();
	if ($atts['type'] != 'none') {
		echo shortcode_modal_button($atts);
	}
	?>
	<div class="modal fade" <?php echo $modal_attributes ?> id="<?= $modal_id ?>" tabindex="-1" role="dialog" aria-labelledby="<?= $modal_id ?>Title" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered <?= $atts['size'] ?>" role="document">
			<div class="modal-content">
				<div class="modal-header sticky-modal-header">
					<h5 class="modal-title" id=""><?= $atts['title'] ?></h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
					<?php echo $indicator ?>
				<div class="modal-body">
					<?php echo do_shortcode($content) ?>
				</div>
				<?php if ($atts['form'] === 'true'): ?>
					<div class="modal-footer sticky-modal-footer">
						<button type="button" class="btn btn-secondary modal-btn-cancel" data-bs-dismiss="modal">Close</button>
						<button type="button" class="btn btn-primary modal-btn-save" onclick="saveNearestForm(this)"><?php echo $atts['form_button'] ?></button>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>

<?php
	$content = ob_get_contents();
	ob_end_clean();

	$IN_POPUP_MODAL = false;

	return $content;
}

function friendly_dynamic_load($url='', $ajax='', $content='', $trigger=null, $id=null) {
	$params = [
			'url' => $url,
			'ajax' => $ajax,
			'trigger' => $trigger
	];
	if (!empty($id))
		$params['id'] = $id;
	return shortcode_dynamic_load($params, $content);
}

function shortcode_dynamic_load($atts, $content="") {
	if (wp_is_json_request()) {
		return "";
	}
	$atts = shortcode_atts([
		'url' => "",
		'ajax' => '',
		'id' => 'n' . wp_generate_password(6, false),
		'trigger' => 'load'
	], $atts);

	if (!empty($atts['ajax']) && class_exists('\Redify\Integrations\RestApi')) {
		$atts['url'] = \Redify\Integrations\RestApi::redify_ajax_url($atts['ajax']);
	}

	$indicator = $atts['id'] . '-ind';
	return '<div class="dynamic-container"><div id="' . $indicator . '" class="htmx-indicator indicator-full"><img src="' . plugins_url('/src/spinner.svg', ROOT_FILE) . '"></div>
	<div _="on htmx:afterSwap call reinitCmb()" hx-trigger="' . $atts['trigger'] . '" id="' . $atts['id'] . '" hx-get="' . $atts['url'] . '" hx-indicator="#' . $indicator . '">' . do_shortcode($content) . '</div></div>';
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

function friendly_icon_tooltip($icon, $tooltip) {
	return "<span class='popupblocks-tooltip popupblocks-noicon' data-toggle='tooltip' title='$tooltip'><i class='bi bi-$icon'></i></span>";
}

add_action( 'plugins_loaded', __NAMESPACE__ . '\\setup' );
}
