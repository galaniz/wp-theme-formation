
/*
 * Imports
 * -------
 */				

import { mergeObjects, show } from '@alanizcreative/formation/utils';

/*
 * Select link from Insert / edit link modal
 * -----------------------------------------
 */		

const link = ( args ) => {

	/* Event callbacks */

	const openModal = ( e ) => {
		textareaId = 'js-link-' + Date.now();

		document.body.insertAdjacentHTML( 'beforeend', `<textarea id="${ textareaId }" style="display:none;"></textarea>` );

		let href = f.linkUrl.href, 
			text = f.linkText.textContent,
			target = f.linkTarget.textContent;

		// open the link popup
		wpLink.open( 
			textareaId,
			href, 
			text, 
			null 
		); 

		// delay for modal opening
		setTimeout( () => {
			document.getElementById( 'wp-link-url' ).value = href;
			document.getElementById( 'wp-link-text' ).value = text;
			document.getElementById( 'wp-link-target' ).checked = ( target === '_blank' );
		}, 100 );

		current = true;
	};

	const remove = ( e ) => {
		if( e )
			e.preventDefault();

		f.linkText.textContent = '';
		f.linkUrl.href = '';
		f.linkInput.value = '';

		show( f.linkContainer, false );
		show( f.noLinkContainer );
	};

	const closeModal = ( e ) => {
		e.preventDefault();

		if( current ) {
			let linkAttrs = wpLink.getAttrs(),
				text = document.getElementById( 'wp-link-text' ).value,
				href = linkAttrs.href,
				target = linkAttrs.target;

			if( href ) {
				f.linkText.textContent = text;
				f.linkTarget.textContent = target;
				f.linkUrl.textContent = href;
				f.linkUrl.href = href;
				f.linkInput.value = text + '|' +  href + '|' + target;

				show( f.noLinkContainer, false );
				show( f.linkContainer );
			}
		}

		wpLink.close();

		document.body.removeChild( document.getElementById( textareaId ) );

		current = false;
	};
    
    /* Merge args with defaults */

    mergeObjects( {
    	selectButton: null,
		removeButton: null,
		linkContainer: null,
		noLinkContainer: null,
		linkText: null,
		linkUrl: null,
		linkTarget: null,
		linkInput: null,
		reset: false
    }, args );

    let error = false;

    // check for empty elements
    for( let prop in args ) {
    	if( !args[prop] ) {
    		error = true;
    		break;
    	}
    }

    if( error )
    	return false;

    let f = args,
    	current = false,
    	textareaId = '',
    	wp = window.wp;

	/* Reset */

	if( f.reset ) 
		remove();

	/* Event listeners */

	f.selectButton.addEventListener( 'click', openModal );
	f.editButton.addEventListener( 'click', openModal );
	f.removeButton.addEventListener( 'click', remove );

	document.getElementById( 'wp-link-submit' ).addEventListener( 'click', closeModal );

};

export default link;
