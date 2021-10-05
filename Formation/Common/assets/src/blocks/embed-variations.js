
/*
 * Restrict embed variations
 * -------------------------
 */

/* Dependencies */

const { 
	getNamespace,
	getNamespaceObj
} = blockUtils;

/* Unregister blocks if not in embed array */

window.addEventListener( 'load', () => {
	const n = getNamespace( true );

	if( !n || !wp.hasOwnProperty( 'blocks' ) )
		return;

	const nO = getNamespaceObj( getNamespace() );

	if( !nO )
		return;

	if( !nO.hasOwnProperty( 'embed_variations' ) )
		return;

	const embedVariations = nO.embed_variations;
	const embedBlocks = wp.blocks.getBlockVariations( 'core/embed' );

	if( embedBlocks ) {
		embedBlocks.forEach( block => {
			if( !embedVariations.includes( block.name ) ) {
				wp.blocks.unregisterBlockVariation( 'core/embed', block.name );
			}
		} );
	}
} );
