/**
 * Registers a new block provided a unique name and an object defining its behavior.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
import { registerBlockType } from '@wordpress/blocks';
/**
 * Highlighter.
 *
 * Simple Highlighter that inserts a <mark> into the markup.
 */

// Import WordPress Components.
import { RichTextToolbarButton } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * All files containing `style` keyword are bundled together. The code used
 * gets applied both to the front of your site and to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './style.scss';

/**
 * Internal dependencies
 */
import Edit from './edit';
import save from './save';
import { registerFormatType, toggleFormat } from '@wordpress/rich-text';

// Create Tooltip Button
const HighlighterButton = ( { isActive, onChange, value} ) => {
	return (
		<RichTextToolbarButton
			icon='admin-comments'
			isActive={ isActive }
			onClick={ () => {
				onChange(
					toggleFormat( value, {
						type: 'popupblocks',
					} )
				);
			} }
			title={ 'Tooltip' }
		/>
	)
};

registerFormatType(
	'popupblocks/tooltip', {
		className: 'wholesome-highlight',
		edit: HighlighterButton,
		tagName: 'mark',
		title: __( 'Highlight', 'wholesome-highlighter' ),
	}
);

/**
 * Every block starts by registering a new block type definition.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
registerBlockType( 'popupblocks/modal', {
	/**
	 * @see ./edit.js
	 */
	edit: Edit,
	/**
	 * @see ./save.js
	 */
	save,
	title: "Popup Modal"
} );


