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
  TextControl,
  TextareaControl,
  CheckboxControl
} = window.wp.components

const {
  InspectorControls,
  InnerBlocks
} = window.wp.blockEditor

const { Fragment } = window.wp.element
const { registerBlockType } = window.wp.blocks

/* Namespace */

const n = getNamespace(true)
const name = n + 'contact-form-group'

/* Attributes from serverside */

const nO = getNamespaceObj(getNamespace())
const attr = nO.blocks[name].attr
const def = nO.blocks[name].default

let usesContext = []

if (Object.getOwnPropertyDescriptor(nO.blocks[name], 'uses_context')) {
  usesContext = nO.blocks[name].uses_context
}

/* Block */

registerBlockType(name, {
  title: 'Field Group',
  category: 'theme-blocks',
  icon: 'email',
  attributes: attr,
  usesContext,
  parent: [n + 'contact-form'],
  edit (props) {
    const { attributes, setAttributes } = props

    const {
      legend = def.legend,
      required = def.required,
      empty_message = def.empty_message,
      invalid_message = def.invalid_message
    } = attributes

    return [
      <Fragment key='frag'>
        <InspectorControls>
          <PanelBody title='Field Group Options'>
            <TextControl
              label='Legend'
              value={legend}
              onChange={v => setAttributes({ legend: v })}
            />
            <CheckboxControl
              label='Required'
              value='1'
              checked={!!required}
              onChange={checked => setAttributes({ required: checked })}
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
          </PanelBody>
        </InspectorControls>
      </Fragment>,
      <Panel key='panel'>
        <PanelBody title='Field Group'>
          <InnerBlocks
            allowedBlocks={[n + 'contact-form-field']}
          />
        </PanelBody>
      </Panel>
    ]
  },
  save () {
    return <InnerBlocks.Content /> // Rendered in php
  }
})
