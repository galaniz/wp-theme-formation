
/*
 * Contact form group block
 * ------------------------
 */

/* Dependencies */

const { 
  getNamespace,
  getNamespaceObj
} = blockUtils;

const { 
  Panel,
  PanelBody,
  TextControl
} = wp.components;

const { 
  select,
  withSelect
} = wp.data;

const { 
  InspectorControls,
  InnerBlocks 
} = wp.blockEditor;

const { Fragment } = wp.element;
const { registerBlockType } = wp.blocks;

/* Namespace */

const n = getNamespace( true );
const name = n + 'contact-form-group';

/* Attributes from serverside */

const nO = getNamespaceObj( getNamespace() );
const attr = nO.blocks[name]['attr'];
const def = nO.blocks[name]['default'];

/* Loop through inner blocks */

const recurseInnerBlocks = ( innerBlocks, email_label ) => {
  innerBlocks.forEach( ( b ) => {
    if( b.name == n + 'contact-form-field' )
      b.attributes.email_label = email_label;

    if( b.innerBlocks.length > 0 )
      recurseInnerBlocks( b.innerBlocks, email_label );
  } );
};

/* Add to child field attributes */

const dataSelector = withSelect( ( select, ownProps ) => {
  let { attributes } = ownProps;
  let { email_label = def.email_label } = attributes;
  let blocks = select( 'core/block-editor' ).getBlocks( ownProps.clientId );

  recurseInnerBlocks( blocks, email_label );
} );

/* Block */

registerBlockType( name, {
  title: 'Field Group',
  category: 'theme-blocks',
  icon: 'email',
  attributes: attr,
  parent: [n + 'contact-form'],
  edit: dataSelector( ( props ) => {  
    const { attributes, setAttributes } = props;
    const { email_label = def.email_label } = attributes;

    return [
      <Fragment>
        <InspectorControls>
          <PanelBody title={ 'Field Group Options' }>
            <TextControl
              label="Email Label"
              value={ email_label }
              onChange={ email_label => setAttributes( { email_label } ) }
            />
          </PanelBody>
        </InspectorControls>
      </Fragment>,
      <Panel header="Field Group">
        <PanelBody>
          <InnerBlocks 
            allowedBlocks={ [n + 'contact-form-group-top', n + 'contact-form-group-bottom'] } 
            template={ [ [n + 'contact-form-group-top', {}, []], [n + 'contact-form-group-bottom', {}, []] ] }
          />
        </PanelBody>
      </Panel>   
    ];
  } ),
  save() {
    return <InnerBlocks.Content />; // this block is rendered in php
  }
} );
