/**
 * Contact form group block
 */

/* Dependencies */

const {
  getNamespace,
  getNamespaceObj
} = window.blockUtils

const {
  Panel,
  PanelBody,
  TextControl
} = window.wp.components

const {
  InspectorControls,
  InnerBlocks
} = window.wp.blockEditor

const { withSelect } = window.wp.data
const { Fragment } = window.wp.element
const { registerBlockType } = window.wp.blocks

/* Namespace */

const n = getNamespace(true)
const name = n + 'contact-form-group'

/* Attributes from serverside */

const nO = getNamespaceObj(getNamespace())
const attr = nO.blocks[name].attr
const def = nO.blocks[name].default

/* Loop through inner blocks */

const recurseInnerBlocks = (innerBlocks, emailLabel) => {
  innerBlocks.forEach((b) => {
    if (b.name === n + 'contact-form-field') { b.attributes.email_label = emailLabel }

    if (b.innerBlocks.length > 0) { recurseInnerBlocks(b.innerBlocks, emailLabel) }
  })
}

/* Add to child field attributes */

const dataSelector = withSelect((select, ownProps) => {
  const { attributes } = ownProps
  const { email_label = def.email_label } = attributes
  const blocks = select('core/block-editor').getBlocks(ownProps.clientId)

  recurseInnerBlocks(blocks, email_label)
})

/* Block */

registerBlockType(name, {
  title: 'Field Group',
  category: 'theme-blocks',
  icon: 'email',
  attributes: attr,
  parent: [n + 'contact-form'],
  edit: dataSelector(props => {
    const { attributes, setAttributes } = props
    const { emailLabel = def.email_label } = attributes

    return [
      <Fragment key='frag'>
        <InspectorControls>
          <PanelBody title='Field Group Options'>
            <TextControl
              label='Email Label'
              value={emailLabel}
              onChange={text => setAttributes({ email_label: text })}
            />
          </PanelBody>
        </InspectorControls>
      </Fragment>,
      <Panel key='panel'>
        <PanelBody title='Field Group'>
          <div className='l-section'>
            <InnerBlocks
              allowedBlocks={[n + 'contact-form-group-top', n + 'contact-form-group-bottom']}
              template={[[n + 'contact-form-group-top', {}, []], [n + 'contact-form-group-bottom', {}, []]]}
            />
          </div>
        </PanelBody>
      </Panel>
    ]
  }),
  save () {
    return <InnerBlocks.Content /> // this block is rendered in php
  }
})
