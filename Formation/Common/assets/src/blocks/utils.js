
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

module.exports = {
	getNamespace,
	getNamespaceObj,
	editInnerBlocks,
	saveInnerBlocks
};
