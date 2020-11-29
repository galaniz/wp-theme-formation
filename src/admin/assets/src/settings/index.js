
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





/*
 * Hide / Show times for hours 
 * ---------------------------
 */

/*window.hideShowTimes = ( checkbox ) => {
	let $times = $( checkbox ).closest( '.c-times' ).find( '.c-times__item:not(:first-child)' );

	if( checkbox.checked ) {
		$times.hide();
	} else {
		$times.show();
	}
};

window.toggleTimes = ( event ) => {
	hideShowTimes( event.target );
};*/

/*
 * Show textarea if select select from dropdown
 * --------------------------------------------
 */

/*window.showHiddenFields = ( event ) => {
	let select = event.currentTarget,
		selectedOptionVal = select.options[select.selectedIndex].value,
		$inlineContainer = $( select ).closest( '.l-inline__item' ).next();
		$optionsTextareaElement = $inlineContainer.find( '.o-form__field' );
		$optionsTextarea = $optionsTextareaElement.find( 'textarea' );
		$valueTextbox = $inlineContainer.next().find( '.o-form__field' );

	if( selectedOptionVal === 'select' ) {
		$optionsTextareaElement.show();
		$valueTextbox.hide();
	} else {
		if( selectedOptionVal === 'checkbox' ) {
			$valueTextbox.show();
		} else {
			$valueTextbox.val( '' );
			$valueTextbox.hide();
		}

		$optionsTextarea.val( '' );
		$optionsTextareaElement.hide();
	}
};*/

// apply toggle to elements

/*(function( $ ) {

	// toggle times
	$( '.js-hours-closed' ).each( function( index ) {
		hideShowTimes( this );
	});

} ( jQuery ));*/

