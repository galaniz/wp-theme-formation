
/*
 * Imports
 * -------
 */				

import { 
	mergeObjects, 
	request,
	addClass,
	removeClass,
	show,
	buttonLoader,
	urlEncode
} from '@alanizcreative/formation/utils';

/*
 * Handle file removal
 * -------------------
 */		

const fileRemove = ( args ) => {

	/* Helpers */

	const disable = ( disable = true ) => {
		buttonLoader( 
			f.button, // button 
			f.loader, // loader
			'--show', // class
			disable, // add class
			disable // disable
		);
	};

	/* Event callbacks */

	const remove = () => {
		disable( true );

		let data = {
			action: f.action,
			file_path: f.filePath
		};

		data[f.nonce.name] = f.nonce.nonce;

		console.log(data);

		request( { 
			method: 'POST', 
			url: f.url,
			headers: { 'Content-type': 'application/x-www-form-urlencoded' },
			body: urlEncode( data )
		} )
	    .then( response => {
	    	console.log( 'RESPONSE', response );

	    	disable( false );
	    	f.parent.removeChild( f.item );
	    } )
	    .catch( xhr => {
	        console.log( 'ERROR', xhr, xhr.responseText );
	        disable( false );
	    } );
	};
    
    /* Merge args with defaults */

    mergeObjects( {
    	item: null,
		button: null,
		loader: null,
		filePath: '',
		url: '',
		action: '',
		nonce: {
			nonce: '',
			name: ''
		}
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

    let f = args;

    f['parent'] = f.item.parentElement;

	/* Event listeners */

	f.button.addEventListener( 'click', remove );

};

export default fileRemove;
