
/*
 * Insert block into editor 
 * ------------------------
 *
 * Note: workaround for gutenberg templates.
 */

document.addEventListener( 'DOMContentLoaded', () => {
    window.addEventListener( 'load', () => {
        const { 
        	getNamespace,
        	getNamespaceObj
       	} = blockUtils;

        const n = getNamespace( true );

        if( !n )
        	return;

        const nO = getNamespaceObj( getNamespace() );

        if( !nO )
        	return;

        if( !nO.hasOwnProperty( 'insert_block' ) )
        	return;

        let blockName = nO.insert_block,
        	blocksInEditor = wp.data.select( 'core/block-editor' ).getEditorBlocks(),
            blockExists = false;

        blocksInEditor.forEach( ( b ) => {
            if( b.name == blockName ) {
                blockExists = true;
                return;
            }
        } );

        if( !blockExists ) {
            let block = wp.blocks.createBlock( blockName, nO.insert_block_defaults );
            wp.data.dispatch( 'core/block-editor' ).insertBlock( block, 0 );
        }
    } );
} );
