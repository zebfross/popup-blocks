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

import { ColorPalette, RichTextToolbarButton, URLPopover } from '@wordpress/block-editor';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { applyFormat, registerFormatType, toggleFormat, useAnchorRef } from '@wordpress/rich-text';


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

/**
 * Highlighter Colours.
 *
 * Highlighter with a colour selector popover.
 */

// Import WordPress Components.

const name = 'wholesome/highlighter';
const cssClass = 'wholesome-highlight';

// Create Highlighter Button with Colour Selection Popover.
const HighlighterButton = ( props ) => {
	const { contentRef, isActive, onChange, value } = props;
	const { activeFormats } = value;
	const anchorRef = useAnchorRef( { ref: contentRef, value } );

	// State to show popover.
	const [ showPopover, setShowPopover ] = useState( false );
	const [ activeColor, setActiveColor ] = useState( false );

	// Custom highlighter colours.
	const colors = [
		{ name: 'Yellow', color: '#fff300' },
		{ name: 'Green', color: '#79fe0c' },
		{ name: 'Blue', color: '#4af1f2' },
		{ name: 'Purple', color: '#df00ff' },
		{ name: 'Red', color: '#ff2226' },
		{ name: 'Orange', color: '#ff7b19' },
		{ name: 'Pink', color: '#ff70c5' },
	];

	// Function to get active colour from format.
	const getActiveColor = () => {
		const formats = activeFormats.filter( format => name === format['type'] );

		if ( formats.length > 0 ) {
			const format = formats[0];
			const { attributes, unregisteredAttributes } = format;

			let atts = unregisteredAttributes;

			if ( attributes && attributes.length ) {
				atts = attributes;
			}

			// If we have no attributes, use the active colour.
			if ( ! atts ) {
				if ( activeColor ) {
					return { backgroundColor: activeColor };
				}
				return;
			}

			if ( atts.hasOwnProperty('class') ) {
				// If the format has set a colour via the class.
				const parts = atts.class.split( '--' );
				const colorName = parts[ parts.length - 1 ];
				const selectedColor = colors.filter( item => colorName === item.name.toLowerCase() )[0];
				return { backgroundColor: selectedColor.color };
			} else if ( atts.hasOwnProperty('style') ) {
				// If the format has set a colour via an inline style.
				const { style } = atts;
				const parts = style.split( ': ' );
				const selectedColor = parts[ parts.length - 1 ].replace( ';', '' );
				return { backgroundColor: selectedColor };
			}
		}
	};

	// Note that we set a custom icon that has a highlighter colour overlay.
	// We use the build in `text-color` name and key to pin the popover
	// icon to the toolbar once the colour has been selected.
	return (
		<>
			<RichTextToolbarButton
				icon='admin-comments'
				key={ isActive ? 'text-color' : 'text-color-not-active' }
				name={ isActive ? 'text-color' : undefined }
				onClick={ () => {
					setShowPopover( true );
				} }
				title={ __( 'Highlight', 'wholesome-highlighter' ) }
			/>
			{ showPopover && (
				<URLPopover
					anchorRef={ anchorRef }
					className="components-inline-color-popover"
					onClose={ () => setShowPopover( false ) }
				>
					<ColorPalette
						colors={ colors }
						onChange={ ( color ) => {
							setShowPopover( false );
							setActiveColor( color );
							// Set a colour or apply a class if these are custom colours.
							if ( color ) {
								const selectedColor = colors.filter( item => color === item.color );
								const attributes  = {};
								if ( selectedColor.length ) {
									// Colour exists in custom colours, apply a class.
									attributes.class = `${cssClass}--${selectedColor[0].name.toLowerCase()}`;
								} else {
									// Colour does not exist, set a background colour.
									attributes.style = `background-color: ${color};`;
								}
								onChange(
									applyFormat( value, {
											type: name,
											attributes,
										}
									));
							} else {
								onChange( toggleFormat( value, { type: name } ) ); // Remove Format.
							}
						} }
					/>
				</URLPopover>
			) }
		</>
	)
};

// Register the Format.
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


