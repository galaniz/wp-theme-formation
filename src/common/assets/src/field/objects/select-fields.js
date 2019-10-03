
/*
 * Imports
 * -------
 */

import { closest } from 'Formation/utils/utils';

/*
 * Show hidden fields depending on value of option
 * -----------------------------------------------
 */

window.showHiddenFields = function( event ) {
	let select = event.currentTarget,
		selectedOption = select.options[select.selectedIndex].value,
		optionsTextarea = closest( select, 'o-field' ).nextElementSibling;

	if( selectedOption == 'select' || 
		selectedOption == 'checkbox' || 
		selectedOption == 'radio' ) {
		optionsTextarea.style.display = 'block';
	} else {
		optionsTextarea.style.display = 'none';
		optionsTextarea.value = '';
	}
};
