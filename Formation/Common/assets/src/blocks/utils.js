
/*
 * Utilites for blocks
 * -------------------
 */

const getNamespace = ( slash = false ) => {
	if( window.hasOwnProperty( 'namespace' ) )
		return window.namespace + ( slash ? '/' : '' );

	return '';
};

const getNamespaceObj = ( namespace ) => {
	if( !window.hasOwnProperty( namespace ) )
	  return false;

	return window[namespace];
};

const editInnerBlocks = ( allowedBlocks ) => {
	return [
    <div>
      <InnerBlocks allowedBlocks={ allowedBlocks } /> 
    </div>  
  ];
};

const saveInnerBlocks = () => {
	return <InnerBlocks.Content />;
};

const getColorSlug = ( arr = [], color = '' ) => {
	if( !arr.length || !color )
		return '';

  let cs = '';

  arr.forEach( ( c ) => {
    if( c['color'] == color ) {
      cs = c['slug'];
      return;
    }
  } );

  return cs;
};

module.exports = {
	getNamespace,
	getNamespaceObj,
	editInnerBlocks,
	saveInnerBlocks,
	getColorSlug
};
