
/*
 * Imports
 * -------
 */				

import { 
	mergeObjects, 
	request,
	addClass,
	removeClass
} from '@alanizcreative/formation/utils';

/*
 * Handle file uploads
 * -------------------
 */		

/* Arguments */

let f = {};	

/* Helpers */

const show = ( item, show = true ) => {
	let display = show ? 'block' : 'none';

	item.style.display = display;
};

const disable = ( disable = true ) => {
	if( disable ) {
		addClass( f.loader, '--show' );
		f.selectButton.disabled = true;
	} else {
		removeClass( f.loader, '--show' );
		f.selectButton.disabled = false;
	}
};

/* Event callbacks */

const selectHandler = function() {
	let files = this.files,
		formData = new FormData();

	formData.append( 'action', f.action );
	formData.append( f.nonce.name, f.nonce.nonce );

	// leave if no files
	if( files.length === 0 ) 
		return;

	// not doing multiple uploads for now
	let file = files[0];

	formData.append( 'files[]', file );

	disable( true );

	request( { 
		method: 'POST', 
		url: f.url,
		body: formData
	} )
    .then( response => {
    	let data = JSON.parse( response );

    	console.log( 'DATA', data );

    	disable( false );

    	if( data.length > 0 ) {
			let result = data[0];

			f.fileInput.value = result.url;
			f.fileName.textContent = result.title + '.' + result.ext;

			if( f.fileType == 'image' )
				f.fileImage.setAttribute( 'src', result.url );

			show( f.noFileContainer, false );
			show( f.fileContainer );
		}
    } )
    .catch( xhr => {
        console.log( 'ERROR', xhr, xhr.responseText );
        disable( false );
    } );
}

const removeImage = ( e ) => {
	if( e ) 
		e.preventDefault();

	f.fileName.textContent = '';
	f.fileInput.value = '';

	show( f.fileContainer, false );
	show( f.noFileContainer );
};

/* Initalize */

const fileUpload = ( args ) => {
    
    /* Merge args with defaults */

    mergeObjects( {
		selectButton: null,
		removeButton: null,
		fileContainer: null,
		noFileContainer: null,
		fileImage: null,
		fileName: null,
		fileInput: null,
		fileType: 'file',
		loader: null,
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
    	return;

    f = args;

	/* Event listeners */

	f.selectButton.addEventListener( 'change', selectHandler );
	f.removeButton.addEventListener( 'click', removeImage );
};

export default fileUpload;
