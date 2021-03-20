
/*
 * Contact form block
 * ------------------
 */

/* Dependencies */

const { 
  getNamespace,
  getNamespaceObj
} = blockUtils;

const { 
  Panel,
  PanelBody,
  TextControl,
  SelectControl
} = wp.components;

const { 
  select,
  withSelect
} = wp.data;

const { 
  InspectorControls,
  InnerBlocks,
  RichText
} = wp.blockEditor;

const { Fragment } = wp.element;
const { registerBlockType } = wp.blocks;

/* Namespace */

const n = getNamespace( true );
const name = n + 'contact-form';

/* Attributes from serverside */

const nO = getNamespaceObj( getNamespace() );
const attr = nO.blocks[name]['attr'];
const def = nO.blocks[name]['default'];

/* Set data */

const dataSelector = withSelect( ( select, ownProps ) => {
  let clientID = ownProps.clientId,
      args = { clientID: clientID };

  if( !ownProps.attributes.hasOwnProperty( 'id' ) )
    ownProps.attributes.id = clientID;

  return args;
} );

/* Block */

registerBlockType( name, {
  title: 'Contact Form',
  category: 'theme-blocks',
  icon: 'email',
  attributes: attr,
  edit: dataSelector( ( props ) => {
    const { attributes, setAttributes, clientID } = props;

    let { 
      id = clientID,
      email = def.email,
      subject = def.subject,
      submit_text = def.submit_text,
      success_message = def.success_message,
      gap = def.gap
    } = attributes;

    let gapSelect = '';

    if( nO.gap_options.length ) {
      gapSelect = (
        <div>
          <SelectControl
            label="Fields Gap"
            value={ gap }
            options={ nO.gap_options }
            onChange={ ( gap ) => setAttributes( { gap } ) }
          />
        </div>
      );
    }

    return [
      <Fragment>
        <InspectorControls>
          <PanelBody title={ 'Form Options' }>
            <TextControl
              label="To Email"
              value={ email }
              onChange={ email => setAttributes( { email } ) }
            />
            <TextControl
              label="Subject"
              value={ subject }
              onChange={ subject => setAttributes( { subject } ) }
            />
            <TextControl
              label="Submit Text"
              value={ submit_text }
              onChange={ submit_text => setAttributes( { submit_text } ) }
            />
            { gapSelect }
          </PanelBody>
        </InspectorControls>
      </Fragment>,
      <Panel header="Fields" className="o-form">
        <PanelBody>
          <InnerBlocks 
            allowedBlocks={ [n + 'contact-form-field', n + 'contact-form-group'] } 
          />  
        </PanelBody>
        <PanelBody>
          <div>
            <RichText
              tagName="div"
              multiline="p"
              value={ success_message }
              onChange={ ( success_message ) => setAttributes( { success_message } ) } 
              allowedFormats={ ['bold', 'italic', 'link'] }
              placeholder={ 'Success message...' }
            />
          </div>
        </PanelBody>
      </Panel>   
    ];
  } ),
  save() {
    return <InnerBlocks.Content />; // this block is rendered in php
  }
} );
