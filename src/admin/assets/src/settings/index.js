
/*
 * Move title to correct section under tabs
 * ----------------------------------------
 */

const initialize = () => {

	let sections = [].slice.call( document.querySelectorAll( '.js-section' ) );

	if( sections.length > 0 ) {
		sections.forEach( ( section, i ) => {
			let lastChild = section.lastElementChild;

			if( lastChild.tagName.toUpperCase() == 'H2' ) {
				let nextSection = section.nextElementSibling;

				nextSection.insertBefore( lastChild, nextSection.firstElementChild );
			}
		} );
	}

}; // end initialize

document.addEventListener( 'DOMContentLoaded', initialize );
