
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

        let insertBlock = false,
            insertBlocks = false;

        if( nO.hasOwnProperty( 'insert_block' ) )
            insertBlock = nO.insert_block;

        if( nO.hasOwnProperty( 'insert_blocks' ) )
            insertBlocks = nO.insert_blocks;

        if( !insertBlock && !insertBlocks )
        	return;

        if( insertBlock ) {
            insertBlocks = [
                {
                    name: insertBlock,
                    defaults: nO.insert_block_defaults
                }
            ];
        }

        if( !insertBlocks.length )
            return;

        let blocksInEditor = wp.data.select( 'core/block-editor' ).getBlocks();

        insertBlocks.forEach( ( bb ) => {
            let blockName = bb.name,
                blockExists = false;

            blocksInEditor.forEach( ( b ) => {
                if( b.name == blockName ) {
                    blockExists = true;
                    return;
                }
            } );

            if( !blockExists ) {
                let block = wp.blocks.createBlock( blockName, bb.defaults );
                wp.data.dispatch( 'core/block-editor' ).insertBlock( block, 0 );
            }
        } );
    } );
} );
