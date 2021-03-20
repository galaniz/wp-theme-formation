
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
  PanelBody,
  SelectControl
} = wp.components;

const { 
  InspectorControls,
  InnerBlocks
} = wp.blockEditor;

const { Fragment } = wp.element;
const { registerBlockType } = wp.blocks;

/* Namespace */

const n = getNamespace( true );
const name = n + 'contact-form-group-bottom';

/* Attributes from serverside */

const nO = getNamespaceObj( getNamespace() );
const attr = nO.blocks[name]['attr'];
const def = nO.blocks[name]['default'];

/* Block */

registerBlockType( name, {
  title: 'Field Group Bottom',
  category: 'theme-blocks',
  icon: 'email',
  attributes: attr,
  parent: [n + 'contact-form-group'],
  edit( props ) {
    const { attributes, setAttributes } = props;
    const { gap = def.gap } = attributes;

    return [
      (
        nO.gap_options.length
        ?
        <Fragment>
          <InspectorControls>
            <PanelBody title={ 'Field Group Bottom Options' }>
              <SelectControl
                label="Fields Gap"
                value={ gap }
                options={ nO.gap_options }
                onChange={ ( gap ) => setAttributes( { gap } ) }
              />
            </PanelBody>
          </InspectorControls>
        </Fragment>
        :
        ''
      ),
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
