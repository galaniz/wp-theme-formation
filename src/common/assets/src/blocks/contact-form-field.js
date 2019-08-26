
/*
 * Contact form field block
 * ------------------------
 */

/* Dependencies */

const { 
    Panel,
    PanelBody,
    TextControl,
    TextareaControl,
    SelectControl,
    CheckboxControl
} = wp.components;

const { InspectorControls } = wp.editor;
const { InnerBlocks } = wp.blockEditor;
const { Fragment } = wp.element;
const { registerBlockType } = wp.blocks;
const { apiFetch } = wp;

/* Attributes from serverside */

const namespace = window.namespace;
const blockName = namespace + '/contact-form-field';

const attr = window[namespace].blocks[blockName]['attr'];
const def = window[namespace].blocks[blockName]['default'];

/* Block */

registerBlockType( blockName, {
    title: 'Field',
    category: 'common',
    parent: [namespace + '/contact-form'],
    edit( props ) {
        const { attributes, setAttributes, clientId } = props;

        let { 
            type = def.type,
            name = clientId,
            label = def.label,
            placeholder = def.placeholder,
            required = def.required,
            attr = def.attr,
            options = def.options,
            width = def.width,
            preview = false
        } = attributes;

        setAttributes( { name: name } );

        // optional inputs
        let placeholderInput = '',
            optionsInput = '';

        if( type == 'text' || type == 'email' ) {
            placeholderInput = (
                <TextControl
                    label="Placeholder"
                    value={ placeholder }
                    onChange={ placeholder => setAttributes( { placeholder } ) }
                />
            );
        }

        if( type == 'select' ) {
            optionsInput = (
                <TextareaControl
                    label="Options (label : value)"
                    value={ options }
                    onChange={ ( options ) => setAttributes( { options } ) }
                />
            );
        }

        // preview for form
        let previewContent = ( <h4>{ `Field ${ label ? ': ' + label : '' }` }</h4> );

        if( preview )
            previewContent = ( 
                <div dangerouslySetInnerHTML={ { __html: preview } } /> 
            );

        apiFetch( { 
            path: `/${ namespace }/preview-contact-form?type=${ type }&name=${ name }&label=${ label }&placeholder=${ placeholder }&required=${ required }&attr=${ attr }&options=${ options }&width=${ width }`
        } ).then( p => {
            setAttributes( { preview: p } );
        } ).catch( err => {
            console.log( err );
            setAttributes( { preview: false } );
        } );

        return [
            <Fragment>
                <InspectorControls>
                    <PanelBody title={ 'Field Options' }>
                        <TextControl
                            label="Name"
                            value={ name }
                            onChange={ name => setAttributes( { name } ) }
                        />
                        <TextControl
                            label="Label"
                            value={ label }
                            onChange={ label => setAttributes( { label } ) }
                        />
                        { placeholderInput }
                        <SelectControl
                            label="Type"
                            value={ type }
                            options={ [
                                { label: 'Text', value: 'text' },
                                { label: 'Email', value: 'email' },
                                { label: 'Checkbox', value: 'checkbox' },
                                { label: 'Number', value: 'number' },
                                { label: 'Textarea', value: 'textarea' },
                                { label: 'Select', value: 'select' }
                            ] }
                            onChange={ type => setAttributes( { type } ) }
                        />
                        { optionsInput }
                        <TextareaControl
                            label="Attributes (label : value)"
                            value={ attr }
                            onChange={ ( attr ) => setAttributes( { attr } ) }
                        />
                        <CheckboxControl
                            label="Required"
                            value="1"
                            checked={ required ? true : false }
                            onChange={ ( checked ) => setAttributes( { required: checked } ) }
                        />
                        <CheckboxControl
                            label="Width 50%"
                            value="1"
                            checked={ width == '50' ? true : false }
                            onChange={ ( checked ) => setAttributes( { width: checked ? '50' : '100' } ) }
                        />
                    </PanelBody>
                </InspectorControls>
            </Fragment>,
            <div className="u-disable">
                { previewContent }
            </div>
        ];
    },
    save() {
        return null; // this block is rendered in php
    }
} );
