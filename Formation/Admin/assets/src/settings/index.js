
/*
 * Imports
 * -------
 */

import { closest } from 'Formation/utils/utils';

/*
 * DOM Loaded
 * ----------
 */

const initialize = () => {

 /*
	* Hide / show siblings based on value
	* -----------------------------------
	*/

	const toggle = ( t ) => {
		let toggle = closest( t, 'o-toggle' );

		toggle.setAttribute( 'data-hide', t.checked );
	};

	window.toggleSiblings = ( event ) => {
		toggle( event.target );
	};

	let toggleTriggers = [].slice.call( document.querySelectorAll( '.o-toggle__trigger' ) );

	if( toggleTriggers.length ) {
		toggleTriggers.forEach( ( t ) => {
			toggle( t );
		} );
	}

}; // end initialize

document.addEventListener( 'DOMContentLoaded', initialize );
