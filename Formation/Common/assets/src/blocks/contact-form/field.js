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

/* Namespace */

const n = getNamespace(true)
const name = n + 'contact-form-field'

/* Attributes from serverside */

const nO = getNamespaceObj(getNamespace())
const attr = nO.blocks[name].attr
const def = nO.blocks[name].default
const contactFormtype = n + 'contact-form/type'

let usesContext = []

if (Object.getOwnPropertyDescriptor(nO.blocks[name], 'uses_context')) {
  usesContext = nO.blocks[name].uses_context
}

/* Set data */

const dataSelector = withSelect((select, ownProps) => {
  const clientID = ownProps.clientId
  const args = { clientID: clientID }

  if (!Object.getOwnPropertyDescriptor(ownProps.attributes, 'id')) {
    ownProps.attributes.id = clientID
  }

  return args
})

/* Block */

registerBlockType(name, {
  title: 'Field',
  category: 'theme-blocks',
  icon: 'email',
  parent: [n + 'contact-form', n + 'contact-form-group'],
  usesContext,
  attributes: attr,
  edit: dataSelector(props => {
    const { attributes, setAttributes, clientID, context } = props

    const {
      id = clientID,
      type = def.type,
      name = def.name,
      label = def.label,
      required = def.required,
      value = def.value,
      options = def.options,
      selected = def.selected,
      placeholder = def.placeholder,
      rows = def.rows,
      width = def.width,
      classes = def.classes,
      empty_message = def.empty_message,
      invalid_message = def.invalid_message,
      conditional = def.conditional,
      mailchimp_consent = def.mailchimp_consent,
      merge_field = def.merge_field,
      tag = def.tag
    } = attributes

    /* Rows */

    let rowsInput = ''

    if (type === 'textarea') {
      rowsInput = (
        <TextControl
          label='Rows'
          type='number'
          value={rows}
          onChange={rows => setAttributes({ rows })}
        />
      )
    }

    /* Placeholder */

    let placeholderInput = ''

    if (type === 'text' || type === 'email') {
      placeholderInput = (
        <TextControl
          label='Placeholder'
          value={placeholder}
          onChange={placeholder => setAttributes({ placeholder })}
        />
      )
    }

    /* Options */

    let optionsInput = ''
    let optionsTextarea = ''

    if (type === 'select' || type === 'radio-select' || type === 'radio-group' || type === 'checkbox-group') {
      optionsTextarea = (
        <TextareaControl
          label='Options'
          help='Format as label : value'
          value={options}
          onChange={options => setAttributes({ options })}
        />
      )

      optionsInput = (
        <Fragment>
          <TextControl
            label='Selected Value'
            value={value}
            onChange={value => setAttributes({ value })}
          />
          {optionsTextarea}
        </Fragment>
      )
    }

    if (type === 'radio' || type === 'radio-select' || type === 'radio-text' || type === 'checkbox') {
      optionsInput = (
        <Fragment>
          <TextControl
            label='Value'
            value={value}
            onChange={value => setAttributes({ value })}
          />
          <CheckboxControl
            label='Selected'
            value='1'
            checked={!!selected}
            onChange={checked => setAttributes({ selected: checked })}
          />
          {optionsTextarea}
        </Fragment>
      )
    }

    /* Width */

    let widthInput = ''

    if (nO.width_options.length) {
      widthInput = (
        <RadioControl
          label='Width'
          selected={width}
          options={nO.width_options}
          onChange={width => { setAttributes({ width }) }}
        />
      )
    }

    /* Mailchimp */

    let mailchimpInputs = ''

    if (Object.getOwnPropertyDescriptor(context, contactFormtype)) {
      const mailchimp = context[contactFormtype] === 'mailchimp' || context[contactFormtype] === 'contact-mailchimp'

      if (mailchimp) {
        mailchimpInputs = (
          <Fragment>
            <CheckboxControl
              label='Mailchimp Consent'
              value='1'
              checked={!!mailchimp_consent} // eslint-disable-line camelcase
              onChange={checked => setAttributes({ mailchimp_consent: checked })}
            />
            <CheckboxControl
              label='Mailchimp Tag'
              value='1'
              checked={!!tag}
              onChange={checked => setAttributes({ tag: checked })}
            />
            <TextControl
              label='Mailchimp Merge Field'
              value={merge_field} // eslint-disable-line camelcase
              onChange={v => setAttributes({ merge_field: v })}
            />
          </Fragment>
        )
      }
    }

    /* Type options */

    const typeLabels = {
      text: 'Text',
      email: 'Email',
      tel: 'Tel',
      checkbox: 'Checkbox',
      'checkbox-group': 'Checkbox Group',
      radio: 'Radio',
      'radio-select': 'Radio Select',
      'radio-text': 'Radio Text',
      'radio-group': 'Radio Group',
      number: 'Number',
      textarea: 'Textarea',
      select: 'Select'
    }

    /* Output */

    return [
      <Fragment key='frag'>
        <InspectorControls>
          <PanelBody title='Field Options'>
            <TextControl
              label='ID'
              value={id}
              onChange={id => setAttributes({ id })}
            />
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
            <SelectControl
              label='Type'
              value={type}
              options={[
                { label: typeLabels.text, value: 'text' },
                { label: typeLabels.email, value: 'email' },
                { label: typeLabels.checkbox, value: 'checkbox' },
                { label: typeLabels.radio, value: 'radio' },
                { label: typeLabels['radio-select'], value: 'radio-select' },
                { label: typeLabels['radio-text'], value: 'radio-text' },
                { label: typeLabels.number, value: 'number' },
                { label: typeLabels.tel, value: 'tel' },
                { label: typeLabels.textarea, value: 'textarea' },
                { label: typeLabels.select, value: 'select' }
              ]}
              onChange={type => setAttributes({ type })}
            />
            {rowsInput}
            {placeholderInput}
            {optionsInput}
            <CheckboxControl
              label='Required'
              value='1'
              checked={!!required}
              onChange={checked => setAttributes({ required: checked })}
            />
            {widthInput}
            <TextControl
              label='Classes'
              value={classes}
              onChange={classes => setAttributes({ classes })}
            />
            {required && (
              <Fragment>
                <TextareaControl
                  label='Empty Error Message'
                  value={empty_message} // eslint-disable-line camelcase
                  onChange={v => setAttributes({ empty_message: v })}
                />
                <TextareaControl
                  label='Invalid Error Message'
                  value={invalid_message} // eslint-disable-line camelcase
                  onChange={v => setAttributes({ invalid_message: v })}
                />
              </Fragment>
            )}
            <TextareaControl
              label='Conditional'
              help='Comma separated ids'
              value={conditional}
              onChange={conditional => setAttributes({ conditional })}
            />
            {mailchimpInputs}
          </PanelBody>
        </InspectorControls>
      </Fragment>,
      <div key='panel' className='components-panel'>
        <div className='components-panel__body'>
          <h2
            className='components-panel__body-title'
            style={{
              padding: '1.25rem',
              height: 'auto'
            }}
          >
            {`${typeLabels[type]} Field: ${label}`}
          </h2>
        </div>
      </div>
    ]
  }),
  save () {
    return null // Rendered in php
  }
})
