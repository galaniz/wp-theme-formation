
/*
 * Contact form block
 * ------------------
 */

/* Dependencies */

const { 
    Panel,
    PanelBody,
    TextControl
} = wp.components;

const { InspectorControls } = wp.editor;
const { InnerBlocks } = wp.blockEditor;
const { Fragment } = wp.element;
const { registerBlockType } = wp.blocks;

/* Attributes from serverside */

const namespace = window.namespace;
const blockName = namespace + '/contact-form';

const attr = window[namespace].blocks[blockName]['attr'];
const def = window[namespace].blocks[blockName]['default'];

/* Block */

registerBlockType( blockName, {
    title: 'Contact Form',
    category: 'common',
    edit( props ) {
        const { attributes, setAttributes, clientId } = props;

        let { 
            id = clientId,
            email = def.email,
            subject = def.subject,
            submit_text = def.submit_text
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
            <Panel header="Fields" className="c-panel">
                <PanelBody>
                    <InnerBlocks 
                        allowedBlocks={ [namespace + '/contact-form-field'] } 
                    />  
                </PanelBody>
            </Panel>   
        ];
    },
    save() {
        return <InnerBlocks.Content />; // this block is rendered in php
    }
});
