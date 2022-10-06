/**
 * Contact form field block
 */

/* Dependencies */

const {
  getNamespace,
  getNamespaceObj
} = window.blockUtils

const {
  PanelBody,
  TextControl,
  TextareaControl,
  SelectControl,
  CheckboxControl,
  RadioControl
} = window.wp.components

const {
  withSelect
} = window.wp.data

const {
  InspectorControls
} = window.wp.blockEditor

const { Fragment } = window.wp.element
const { registerBlockType } = window.wp.blocks
const { apiFetch } = window.wp

/* Namespace */

const n = getNamespace(true)
const nn = getNamespace()
const name = n + 'contact-form-field'

/* Attributes from serverside */

const nO = getNamespaceObj(getNamespace())
const attr = nO.blocks[name].attr
const def = nO.blocks[name].default

/* Set data */

const dataSelector = withSelect((select, ownProps) => {
  const clientID = ownProps.clientId
  const args = { clientID: clientID }

  if (!Object.getOwnPropertyDescriptor(ownProps.attributes, 'name')) {
    ownProps.attributes.name = clientID
  }

  return args
})

/* Block */

registerBlockType(name, {
  title: 'Field',
  category: 'theme-blocks',
  icon: 'email',
  parent: [n + 'contact-form', n + 'contact-form-group-bottom'],
  attributes: attr,
  edit: dataSelector(props => {
    const { attributes, setAttributes, clientID } = props

    const {
      type = def.type,
      name = clientID,
      label = def.label,
      placeholder = def.placeholder,
      required = def.required,
      attr = def.attr,
      options = def.options,
      width = def.width,
      value = def.value,
      labelAfter = def.label_after,
      paddingSmall = def.padding_small,
      preview = false
    } = attributes

    /* Optional inputs */

    let placeholderInput = ''
    let optionsInput = ''
    let valueInput = ''

    if (type === 'text' || type === 'email') {
      placeholderInput = (
        <TextControl
          label='Placeholder'
          value={placeholder}
          onChange={placeholder => setAttributes({ placeholder })}
        />
      )
    }

    if (type === 'radio' || type === 'checkbox') {
      valueInput = (
        <CheckboxControl
          label='Label after'
          value='1'
          checked={!!labelAfter}
          onChange={checked => setAttributes({ label_after: checked })}
        />
      )
    }

    if (type === 'select' || type === 'radio' || type === 'checkbox' || type === 'radio-group' || type === 'checkbox-group') {
      optionsInput = (
        <Fragment>
          <TextControl
            label='Value'
            value={value}
            onChange={value => setAttributes({ value })}
          />
          <TextareaControl
            label='Options (label : value)'
            value={options}
            onChange={options => setAttributes({ options })}
          />
        </Fragment>
      )
    }

    /* Preview form markup */

    let previewContent = (<h4>{`Field ${label ? ': ' + label : ''}`}</h4>)

    if (preview) {
      previewContent = (
        <div dangerouslySetInnerHTML={{ __html: preview }} />
      )
    }

    apiFetch({
      path: `/${nn}/preview-contact-form?type=${type}&name=${name}&label=${label}&placeholder=${placeholder}&required=${required}&attr=${attr}&options=${options}&width=${width}`
    }).then(p => {
      setAttributes({ preview: p })
    }).catch(err => {
      console.log(err)
      setAttributes({ preview: false })
    })

    return [
      <Fragment key='frag'>
        <InspectorControls>
          <PanelBody title='Field Options'>
            <TextControl
              label='Name'
              value={name}
              onChange={name => setAttributes({ name })}
            />
            <TextControl
              label='Label'
              value={label}
              onChange={label => setAttributes({ label })}
            />
            {placeholderInput}
            <SelectControl
              label='Type'
              value={type}
              options={[
                { label: 'Text', value: 'text' },
                { label: 'Email', value: 'email' },
                { label: 'Checkbox', value: 'checkbox' },
                { label: 'Checkbox Group', value: 'checkbox-group' },
                { label: 'Radio', value: 'radio' },
                { label: 'Radio Group', value: 'radio-group' },
                { label: 'Number', value: 'number' },
                { label: 'Textarea', value: 'textarea' },
                { label: 'Select', value: 'select' }
              ]}
              onChange={type => setAttributes({ type })}
            />
            {valueInput}
            {optionsInput}
            <TextareaControl
              label='Attributes (label : value)'
              value={attr}
              onChange={attr => setAttributes({ attr })}
            />
            <CheckboxControl
              label='Required'
              value='1'
              checked={!!required}
              onChange={checked => setAttributes({ required: checked })}
            />
            <CheckboxControl
              label='Padding small'
              value='1'
              checked={!!paddingSmall}
              onChange={checked => setAttributes({ padding_small: checked })}
            />
            <RadioControl
              label='Width'
              selected={width}
              options={[
                { label: '100%', value: '100' },
                { label: '50%', value: '50' },
                { label: 'Auto', value: 'auto' }
              ]}
              onChange={width => { setAttributes({ width }) }}
            />
          </PanelBody>
        </InspectorControls>
      </Fragment>,
      <div className='u-disable' key='div'>
        {previewContent}
      </div>
    ]
  }),
  save () {
    return null // rendered in php
  }
})
