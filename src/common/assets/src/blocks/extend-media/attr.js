
/*
 * Add container width attribute
 * -----------------------------
 */

/* Dependencies */

import { allowedBlocks, widthOptions } from './vars';

const { getNamespace } = blockUtils;
const { assign } = lodash;
const { addFilter } = wp.hooks;

const n = getNamespace( true );

/* Attributes filter */

addFilter(
  'blocks.registerBlockType',
  n + 'extend-media-attr',
  function( settings, name ) {
    if( allowedBlocks.indexOf( name ) == -1 )
      return settings;

    settings.attributes = assign( settings.attributes, {
      containerWidth: {
        type: 'string',
        default: widthOptions[0].value
      }
    } );

    return settings;
  }
);
