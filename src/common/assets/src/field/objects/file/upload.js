
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
	disableButtonLoader
} from 'Formation/utils/utils';

import DOMPurify from 'dompurify';

/*
 * Handle file uploads
 * -------------------
 */		

const fileUpload = ( args ) => {

	/* Helpers */

	const disable = ( disable = true ) => {
		disableButtonLoader( 
			f.selectButton, // button 
			f.loader, // loader
			'--show', // class
			disable, // add class
			disable // disable
		);
	};

	/* Event callbacks */

	const select = function() {
		let files = this.files;

		// leave if no files
		if( files.length === 0 ) 
			return;

		// not doing multiple uploads for now
		let file = files[0],
			type = file.type;

		if( type == 'image/svg+xml' ) {
			disable( true );

			const reader = new FileReader();

		    reader.onload = function( e ) {
		        let text = reader.result,
		            clean = DOMPurify.sanitize( text ),
		            blob = new Blob( [clean], {type : type} );

		    	upload( new File( [blob], file.name, { 
		    		lastModified: file.lastModified,
		    		type: type 
		    	} ) );
		    }

		    reader.readAsText( file );
		} else {
			upload( file );
		}
	}

	const remove = ( e ) => {
		if( e ) 
			e.preventDefault();

		f.fileName.textContent = '';
		f.fileInput.value = '';

		show( f.fileContainer, false );
		show( f.noFileContainer );
	};

	/* Upload to backend */

	const upload = ( file ) => {
		let formData = new FormData();

		formData.append( 'action', f.action );
		formData.append( f.nonce.name, f.nonce.nonce );
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
	};
    
    /* Merge args with defaults */

    mergeObjects( {
		selectButton: null,
		removeButton: null,
		fileContainer: null,
		noFileContainer: null,
		fileImage: null,
		fileIcon: null,
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
    	return false;

    let f = args;

	/* Event listeners */

	f.selectButton.addEventListener( 'change', select );
	f.removeButton.addEventListener( 'click', remove );

};

export default fileUpload;
