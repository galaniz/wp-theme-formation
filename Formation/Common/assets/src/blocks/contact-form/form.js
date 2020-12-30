
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
  TextControl
} = wp.components;

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

/* Block */

registerBlockType( name, {
  title: 'Contact Form',
  category: 'theme-blocks',
  attributes: attr,
  edit( props ) {
    const { attributes, setAttributes, clientId } = props;

    let { 
      id = clientId,
      email = def.email,
      subject = def.subject,
      submit_text = def.submit_text,
      success_message = def.success_message
    } = attributes;

    setAttributes( { id: id } );

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
          </PanelBody>
        </InspectorControls>
      </Fragment>,
      <Panel header="Fields">
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
              formattingControls={ ['bold', 'italic', 'link'] }
              placeholder={ 'Success message...' }
            />
          </div>
        </PanelBody>
      </Panel>   
    ];
  },
  save() {
    return <InnerBlocks.Content />; // this block is rendered in php
  }
} );
