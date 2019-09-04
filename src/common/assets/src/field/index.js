
/*
 * Imports
 * -------
 */

// polyfills
import 'core-js/es/object/assign';
import 'core-js/es/array/from';
import 'core-js/es/promise';

// modules
import fileUpload from './objects/file/file-upload';

// classes
import Multi from '@alanizcreative/formation/objects/multi/multi';

/*
 * DOM loaded ( runs on every page load )
 * -------------------------------------
 */

const initialize = () => {
	const namespace = window.namespace;

	if( !window.hasOwnProperty( namespace ) )
		return;

	const n = window[namespace]; 

	if( n.hasOwnProperty( 'files' ) ) {
		if( n.files.length > 0 ) {
			let fileItems = Array.from( document.querySelectorAll( '.o-file' ) ),
				nonceName = namespace + '_upload_file_nonce';

			fileItems.forEach( ( item, i ) => {
				fileUpload( {
					selectButton: item.querySelector( '.o-file__select input' ),
					removeButton: item.querySelector( '.o-file__remove' ),
					fileContainer: item.querySelector( '.o-file__exists' ),
					noFileContainer: item.querySelector( '.o-file__no' ),
					fileImage: item.querySelector( '.o-file__image' ),
					fileName: item.querySelector( '.o-file__name' ),
					fileInput: document.getElementById( n.files[i].id ),
					fileType: n.files[i].file_type,
					loader: item.querySelector( '.o-loader' ),
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

	if( !n.hasOwnProperty( 'multi' ) ) 
		return;

	for( name in n.multi ) {
		let section = document.querySelector( `.c-section-${ name }` );

		if( section ) {
			let multi = section.querySelectorAll( '.o-multi__item' );

			multi.forEach( ( m ) => {
				let o = new Multi( {
					item: m, 
					itemAsString: n.multi[name],
					buttonSelector: '.o-multi__button',
					inputSelector: '.js-input'
				} );
			} );
		}
	}
}; // end initialize

document.addEventListener( 'DOMContentLoaded', initialize );
