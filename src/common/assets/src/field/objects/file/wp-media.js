
/*
 * Imports
 * -------
 */				

import { mergeObjects, show } from 'Formation/utils';

/*
 * WP media select
 * ---------------
 */		

const wpMedia = ( args ) => {

	/* Event callbacks */

	const openModal = ( e ) => {
		e.preventDefault();
		mediaModal.frame.open();
	};

	const remove = ( e ) => {
		if( e ) 
			e.preventDefault();

		f.fileName.textContent = '';
		f.fileInput.value = '';

		show( f.noFileContainer );
		show( f.fileContainer, false );
	};

	const closeModal = ( e ) => {
		let selection =  mediaModal.frame.state().get( 'selection' );

		selection.forEach( ( attachment, i ) => {
			let image = true;

			if( image ) {
				f.fileImage.setAttribute( 'src', attachment.attributes.url );
				f.fileImage.setAttribute( 'alt', attachment.attributes.alt );
			} else {
				f.fileIcon.textContent = attachment.attributes.subtype;
			}

			f.fileName.textContent = attachment.attributes.filename;
			f.fileInput.value = attachment.id;

			show( f.noFileContainer, false );
			show( f.fileContainer );
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
		mediaVars: {},
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
    	wp = window.wp,
    	mediaModal = {};

    /* Media args */

	let mediaArgs = {
		frame: 'select',
		title: 'Select Media',
		multiple: false,
		library: {}
	};

	if( f.mediaVars ) {
		for( let mediaVar in f.mediaVars ) {
			mediaArgs.library[mediaVar] = f.mediaVars[mediaVar];
		}
	}

	/* Reset */

	if( f.reset ) 
		removeImage();

	/* Media modal */

	let attributes = mediaArgs;
	
	attributes.states = [];

	attributes.states.push(
		new wp.media.controller.Library( {
			multiple: false,
			title: attributes.title,
			priority: 20,
			filterable: 'all'
		} )
	);

	mediaModal.frame = wp.media( attributes );

	/* Event listeners */

	f.selectButton.addEventListener( 'click', openModal );
	f.removeButton.addEventListener( 'click', remove );
	mediaModal.frame.on( 'close', closeModal );

};

export default wpMedia;
