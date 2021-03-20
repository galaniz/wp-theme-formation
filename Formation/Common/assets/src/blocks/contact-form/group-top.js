
/*
 * Contact form group top block
 * ----------------------------
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
const name = n + 'contact-form-group-top';

/* Blocks */

registerBlockType( name, {
  title: 'Field Group Top',
  category: 'theme-blocks',
  icon: 'email',
  parent: [n + 'contact-form-group'],
  edit( props ) {
    return [
      <Panel>
        <PanelBody>
          <InnerBlocks 
            allowedBlocks={ ['core/paragraph', 'core/heading', 'core/image'] } 
          />  
        </PanelBody>
      </Panel>   
    ];
  },
  save() {
    return <InnerBlocks.Content />; // this block is rendered in php
  }
} );
