
/*
 * Contact form field block
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
    BaseControl,
    TextControl,
    TextareaControl,
    SelectControl,
    CheckboxControl,
    RadioControl
} = wp.components;

const { 
    InspectorControls,
    InnerBlocks
} = wp.blockEditor;

const { Fragment } = wp.element;
const { registerBlockType } = wp.blocks;
const { apiFetch } = wp;

/* Namespace */

const n = getNamespace( true );
const nn = getNamespace();
const name = n + 'contact-form-field';

/* Attributes from serverside */

const nO = getNamespaceObj( getNamespace() );
const attr = nO.blocks[name]['attr'];
const def = nO.blocks[name]['default'];

/* Block */

registerBlockType( name, {
    title: 'Field',
    category: 'common',
    parent: [n + 'contact-form', n + 'contact-form-group-bottom'],
    attributes: attr,
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
            value = def.value,
            label_after = def.label_after,
            padding_small = def.padding_small,
            preview = false
        } = attributes;

        setAttributes( { name: name } );

        /* Optional inputs */

        let placeholderInput = '',
            optionsInput = '',
            valueInput = '';

        if( type == 'text' || type == 'email' ) {
            placeholderInput = (
                <TextControl
                    label="Placeholder"
                    value={ placeholder }
                    onChange={ placeholder => setAttributes( { placeholder } ) }
                />
            );
        }

        if( type == 'radio' || type == 'checkbox' ) {
            valueInput = [
                <CheckboxControl
                    label="Label after"
                    value="1"
                    checked={ label_after ? true : false }
                    onChange={ ( checked ) => setAttributes( { label_after: checked } ) }
                />
            ];
        }

        if( type == 'select' || type == 'radio' || type == 'checkbox' || type == 'radio_group' || type == 'checkbox_group' ) {
            optionsInput = [
                <TextControl
                    label="Value"
                    value={ value }
                    onChange={ value => setAttributes( { value } ) }
                />,
                <TextareaControl
                    label="Options (label : value)"
                    value={ options }
                    onChange={ ( options ) => setAttributes( { options } ) }
                />
            ];
        }

        /* Preview form markup */

        let previewContent = ( <h4>{ `Field ${ label ? ': ' + label : '' }` }</h4> );

        if( preview )
            previewContent = ( 
                <div dangerouslySetInnerHTML={ { __html: preview } } /> 
            );

        apiFetch( { 
            path: `/${ nn }/preview-contact-form?type=${ type }&name=${ name }&label=${ label }&placeholder=${ placeholder }&required=${ required }&attr=${ attr }&options=${ options }&width=${ width }`
        } ).then( p => {
            // console.log( p );
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
                                { label: 'Checkbox Group', value: 'checkbox_group' },
                                { label: 'Radio', value: 'radio' },
                                { label: 'Radio Group', value: 'radio_group' },
                                { label: 'Number', value: 'number' },
                                { label: 'Textarea', value: 'textarea' },
                                { label: 'Select', value: 'select' }
                            ] }
                            onChange={ type => setAttributes( { type } ) }
                        />
                        { valueInput }
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
                            label="Padding small"
                            value="1"
                            checked={ padding_small ? true : false }
                            onChange={ ( checked ) => setAttributes( { padding_small: checked } ) }
                        />
                        <RadioControl
                            label="Width"
                            selected={ width }
                            options={ [
                                { label: '100%', value: '100' },
                                { label: '50%', value: '50' },
                                { label: 'Auto', value: 'auto' }
                            ] }
                            onChange={ ( width ) => { setAttributes( { width } ) } }
                        />
                    </PanelBody>
                </InspectorControls>
            </Fragment>,
            <div className="o-disable">
                { previewContent }
            </div>
        ];
    },
    save() {
        return null; // this block is rendered in php
    }
} );
