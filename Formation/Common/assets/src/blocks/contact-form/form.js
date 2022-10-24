/**
 * Contact form block
 */

/* Dependencies */

const {
  getNamespace,
  getNamespaceObj
} = window.blockUtils

const {
  Panel,
  PanelBody,
  TextControl,
  TextareaControl,
  SelectControl
} = window.wp.components

const {
  withSelect
} = window.wp.data

const {
  InspectorControls,
  InnerBlocks
} = window.wp.blockEditor

const { Fragment } = window.wp.element
const { registerBlockType } = window.wp.blocks

/* Namespace */

const n = getNamespace(true)
const name = n + 'contact-form'

/* Attributes from serverside */

const nO = getNamespaceObj(getNamespace())
const attr = nO.blocks[name].attr
const def = nO.blocks[name].default

let providesContext = []

if (Object.getOwnPropertyDescriptor(nO.blocks[name], 'provides_context')) {
  providesContext = nO.blocks[name].provides_context
}

/* Set data */

const dataSelector = withSelect((select, ownProps) => {
  const clientId = ownProps.clientId
  const args = { clientId }

  if (!Object.getOwnPropertyDescriptor(ownProps, 'id')) {
    ownProps.attributes.id = clientId
  }

  return args
})

/* Block */

registerBlockType(name, {
  title: 'Contact Form',
  category: 'theme-blocks',
  icon: 'email',
  attributes: attr,
  providesContext,
  edit: dataSelector(props => {
    const { attributes, setAttributes } = props

    const {
      type = def.email,
      email = def.email,
      subject = def.subject,
      submit_label = def.submit_label,
      success_title = def.success_title,
      success_text = def.success_text,
      field_gap = def.field_gap,
      mailchimp_list = def.mailchimp_list // eslint-disable-line camelcase
    } = attributes

    /* Gap */

    let gapInput = ''

    if (nO.field_gap_options.length) {
      gapInput = (
        <div>
          <SelectControl
            label='Field Gap'
            value={field_gap} // eslint-disable-line camelcase
            options={nO.field_gap_options}
            onChange={v => setAttributes({ field_gap: v })}
          />
        </div>
      )
    }

    /* Contact */

    let contactInputs = ''

    if (type === 'contact' || type === 'contact-mailchimp') {
      contactInputs = (
        <Fragment>
          <TextControl
            label='To Email'
            value={email}
            onChange={v => setAttributes({ email: v })}
          />
          <TextControl
            label='Subject'
            value={subject}
            onChange={v => setAttributes({ subject: v })}
          />
        </Fragment>
      )
    }

    /* Mailchimp */

    let mailchimpInput = ''

    if (type === 'mailchimp' || type === 'contact-mailchimp') {
      mailchimpInput = (
        <TextControl
          label='Mailchimp List ID'
          value={mailchimp_list} // eslint-disable-line camelcase
          onChange={v => setAttributes({ mailchimp_list: v })}
        />
      )
    }

    /* Output */

    return [
      <Fragment key='frag'>
        <InspectorControls>
          <PanelBody title='Form Options'>
            <SelectControl
              label='Type'
              value={type}
              options={[
                { label: 'Contact', value: 'contact' },
                { label: 'Mailchimp', value: 'mailchimp' },
                { label: 'Contact + Mailchimp', value: 'contact-mailchimp' }
              ]}
              onChange={v => setAttributes({ type: v })}
            />
            {mailchimpInput}
            {contactInputs}
            <TextControl
              label='Submit Label'
              value={submit_label} // eslint-disable-line camelcase
              onChange={v => setAttributes({ submit_label: v })}
            />
            <TextControl
              label='Success Title'
              value={success_title} // eslint-disable-line camelcase
              onChange={v => setAttributes({ success_title: v })}
            />
            <TextareaControl
              label='Success Text'
              value={success_text} // eslint-disable-line camelcase
              onChange={v => setAttributes({ success_text: v })}
            />
            {gapInput}
          </PanelBody>
        </InspectorControls>
      </Fragment>,
      <Panel key='panel'>
        <PanelBody title='Fields'>
          <InnerBlocks
            allowedBlocks={[n + 'contact-form-field', n + 'contact-form-group']}
          />
        </PanelBody>
      </Panel>
    ]
  }),
  save () {
    return <InnerBlocks.Content /> // Rendered in php
  }
})
