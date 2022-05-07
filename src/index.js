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

import { RichTextToolbarButton, PlainText, URLPopover } from '@wordpress/block-editor';
import { useState } from '@wordpress/element';
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

const name = 'popupblocks/tooltip';

// Create Highlighter Button with Colour Selection Popover.
const HighlighterButton = ( props ) => {
	const { contentRef, isActive, onChange, value } = props;
	const { activeFormats } = value;
	const anchorRef = useAnchorRef( { ref: contentRef, value } );

	// State to show popover.
	const [ showPopover, setShowPopover ] = useState( false );
	const [ popupText, setPopupText ] = useState( false );

	// Function to get active colour from format.
	const getTitle = () => {
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
				return "";
			}

			if ( atts.hasOwnProperty('title') ) {
				// If the format has set a colour via the class.
				return atts.title;
			}
			return "";
		}
	};

	// Note that we set a custom icon that has a highlighter colour overlay.
	// We use the build in `text-color` name and key to pin the popover
	// icon to the toolbar once the colour has been selected.
	return (
		<>
			<RichTextToolbarButton
				icon='admin-comments'
				onClick={ () => {
					let title = getTitle();
					if (title)
						setPopupText(title);
					setShowPopover( true );
				} }
				title={ 'Tooltip' }
			/>
			{ showPopover && (
				<URLPopover
					anchorRef={ anchorRef }
					className="components-inline-color-popover"
					onClose={ () => setShowPopover( false ) }
				>
					<PlainText
						className="components-text-control__input"
						value={popupText ? popupText : ""}
						onChange={ ( tooltip ) => {
							setPopupText( tooltip );
							// Set a colour or apply a class if these are custom colours.
							if ( tooltip ) {
								const attributes  = {
									class: 'popupblocks-tooltip-link',
									'data-toggle': 'tooltip',
									title: tooltip
								};
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
		className: 'popupblocks-tooltip',
		edit: HighlighterButton,
		tagName: 'span',
		title: 'Popupblocks Tooltip',
	}
);


