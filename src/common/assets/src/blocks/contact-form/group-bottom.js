
/*
 * Contact form group bottom block
 * -------------------------------
 */

/* Dependencies */

const { 
  getNamespace,
  getNamespaceObj
} = blockUtils;

const { 
  Panel,
  PanelBody
} = wp.components;

const { InnerBlocks } = wp.blockEditor;
const { registerBlockType } = wp.blocks;

/* Namespace */

const n = getNamespace( true );
const name = n + 'contact-form-group-bottom';

/* Block */

registerBlockType( name, {
  title: 'Field Group Bottom',
  category: 'theme-blocks',
  parent: [n + 'contact-form-group'],
  edit( props ) {
    return [
      <Panel>
        <PanelBody>
          <InnerBlocks 
            allowedBlocks={ [n + 'contact-form-field'] } 
          />  
        </PanelBody>
      </Panel>   
    ];
  },
  save() {
    return <InnerBlocks.Content />; // this block is rendered in php
  }
} );
