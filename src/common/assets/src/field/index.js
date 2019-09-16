
/*
 * Imports
 * -------
 */

// polyfills
import 'core-js/es/object/assign';
import 'core-js/es/array/from';
import 'core-js/es/promise';

// functions
import { closest } from '@alanizcreative/formation/utils';

// modules
import fileUpload from './objects/file/upload';
import fileRemove from './objects/file/remove';
import wpMedia from './objects/file/wp-media';

/*
 * DOM loaded
 * ----------
 */

const initialize = () => {
	const namespace = window.namespace;

	if( !window.hasOwnProperty( namespace ) )
		return;

	const n = window[namespace]; 

	/*
	 * File upload
	 * -----------
	 */

	if( n.hasOwnProperty( 'files' ) ) {
		if( n.files.length > 0 ) {
			let fileItems = Array.from( document.querySelectorAll( '.o-asset--upload' ) ),
				nonceName = namespace + '_upload_file_nonce';

			fileItems.forEach( ( item, i ) => {
				new fileUpload( {
					selectButton: item.querySelector( '.o-asset__select input' ),
					removeButton: item.querySelector( '.o-asset__remove' ),
					fileContainer: item.querySelector( '.o-asset__exists' ),
					noFileContainer: item.querySelector( '.o-asset__no' ),
					fileImage: item.querySelector( '.o-asset__image' ),
					fileName: item.querySelector( '.o-asset__name' ),
					fileInput: document.getElementById( n.files[i].id ),
					fileType: n.files[i].file_type,
					loader: item.querySelector( '.js-loader-select' ),
					url: n.ajax_url,
					action: 'upload_file',
					nonce: {
						nonce: n[nonceName],
						name: nonceName
					}
				} );
			} );
		}
	}

	/*
	 * File remove
	 * -----------
	 */

	let fileRemoveItems = Array.from( document.querySelectorAll( '.o-asset--remove' ) );

	if( fileRemoveItems.length > 0 ) {
		fileRemoveItems.forEach( ( item ) => {
			let nonceName = namespace + '_remove_file_nonce';

			fileRemove( {
				item: item,
				button: item.querySelector( '.o-asset__remove' ),
				loader: item.querySelector( '.js-loader-remove' ),
				filePath: item.querySelector( '.o-asset__input' ).value,
				url: n.ajax_url,
				action: 'remove_file',
				nonce: {
					nonce: n[nonceName],
					name: nonceName
				}
			} );
		} );
	}

	/*
	 * Multi fields
	 * ------------
	 */

	if( !n.hasOwnProperty( 'multi' ) ) 
		return;

	window.multi = function( m ) {
		let multiItem = closest( m, 'o-multi__item' ),
			multi = multiItem.parentElement,
			multiItems = Array.from( multi.children );

		let dataType = m.getAttribute( 'data-type' );

		if( dataType === 'add' ) {
			let name = multiItem.getAttribute( 'data-name' );

			/* Get index of current item */

			let itemIndex = multiItems.indexOf( multiItem ),
				newItemIndex = itemIndex !== -1 ? itemIndex + 1 : false;

			/* Insert new item */

			multiItem.insertAdjacentHTML( 'afterend', n.multi[name] );
		} else {
			multi.removeChild( multiItem );
		}

		/* Reindex items */

		multiItems = Array.from( multi.children );

		multiItems.forEach( ( item, i ) => {
			let inputs = Array.from( item.querySelectorAll( '.js-input' ) );

			inputs.forEach( ( input ) => {
				let dataName = input.getAttribute( 'data-name' ),
					dataId = input.getAttribute( 'data-id' );

				input.name = dataName.replace( '%i', i );
				input.id = dataId.replace( '%i', i );
			} );
		} );
	};

}; // end initialize

document.addEventListener( 'DOMContentLoaded', initialize );
