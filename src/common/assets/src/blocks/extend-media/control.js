
/*
 * Add control for attr
 * --------------------
 */

/* Dependencies */

import { allowedBlocks, widthOptions } from './vars';

const { getNamespace } = blockUtils;
const { assign } = lodash;
const { addFilter } = wp.hooks;
const { createHigherOrderComponent } = wp.compose;
const { Fragment } = wp.element;
const { InspectorControls } = wp.blockEditor;
const { select } = wp.data;

const { 
  PanelBody, 
  SelectControl 
} = wp.components;

const n = getNamespace( true );

/* Inspector controls filter */

const widthControl = createHigherOrderComponent( ( BlockEdit ) => {
  return ( props ) => {
    if( allowedBlocks.indexOf( props.name ) == -1 ) {
      return (
        <BlockEdit { ...props } />
      );
    }

    const { attributes, setAttributes, clientId } = props;
    const { containerWidth } = attributes;

    return (
      <Fragment>
        <BlockEdit { ...props } />
        <InspectorControls>
          <PanelBody
            title={ 'Container Settings' }
            initialOpen={ true }
          >
            <SelectControl
              label={ 'Container Width' }
              value={ containerWidth }
              options={ widthOptions }
              onChange={ selected => setAttributes( { containerWidth: selected } ) }
            />
          </PanelBody>
        </InspectorControls>
      </Fragment>
    );
  };
}, 'widthControl' );

addFilter( 
  'editor.BlockEdit', 
  n + 'extend-media-control', 
  widthControl 
);
