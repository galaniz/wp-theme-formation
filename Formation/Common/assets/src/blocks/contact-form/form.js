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
  SelectControl
} = window.wp.components

const {
  withSelect
} = window.wp.data

const {
  InspectorControls,
  InnerBlocks,
  PlainText
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

/* Set data */

const dataSelector = withSelect((select, ownProps) => {
  const clientId = ownProps.clientId
  const args = { clientId: clientId }

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
  edit: dataSelector(props => {
    const { attributes, setAttributes } = props

    const {
      email = def.email,
      subject = def.subject,
      submitText = def.submit_text,
      successMessage = def.success_message,
      gap = def.gap
    } = attributes

    let gapSelect = ''

    if (nO.gap_options.length) {
      gapSelect = (
        <div>
          <SelectControl
            label='Fields Gap'
            value={gap}
            options={nO.gap_options}
            onChange={gap => setAttributes({ gap })}
          />
        </div>
      )
    }

    return [
      <Fragment key='frag'>
        <InspectorControls>
          <PanelBody title='Form Options'>
            <TextControl
              label='To Email'
              value={email}
              onChange={email => setAttributes({ email })}
            />
            <TextControl
              label='Subject'
              value={subject}
              onChange={subject => setAttributes({ subject })}
            />
            <TextControl
              label='Submit Text'
              value={submitText}
              onChange={text => setAttributes({ submit_text: text })}
            />
            {gapSelect}
          </PanelBody>
        </InspectorControls>
      </Fragment>,
      <Panel className='o-form' key='panel'>
        <PanelBody title='Fields'>
          <div className='l-section'>
            <InnerBlocks
              allowedBlocks={[n + 'contact-form-field', n + 'contact-form-group']}
            />
          </div>
          <div className='l-section'>
            <div>
              <PlainText
                value={successMessage}
                onChange={text => setAttributes({ success_message: text })}
                placeholder='Success message...'
              />
            </div>
          </div>
        </PanelBody>
      </Panel>
    ]
  }),
  save () {
    return <InnerBlocks.Content /> // rendered in php
  }
})
