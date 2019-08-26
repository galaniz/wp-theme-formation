
/*
 * Imports
 * -------
 */

// polyfills
import 'core-js/fn/object/assign';
import 'core-js/fn/array/from';
import 'core-js/fn/promise';

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

	if( !window[namespace].hasOwnProperty( 'multi' ) ) 
		return;

	for( name in window[namespace].multi ) {
		let section = document.querySelector( `.c-section-${ name }` );

		if( section ) {
			let multi = section.querySelectorAll( '.o-multi__item' );

			multi.forEach( ( m ) => {
				let o = new Multi( {
					item: m, 
					itemAsString: window[namespace].multi[name],
					buttonSelector: '.o-multi__button',
					inputSelector: '.js-input'
				} );
			} );
		}
	}
}; // end initialize

document.addEventListener( 'DOMContentLoaded', initialize );
