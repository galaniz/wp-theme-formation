/**
 * Contact form group bottom block
 */

/* Dependencies */

const {
  getNamespace,
  getNamespaceObj
} = window.blockUtils

const {
  Panel,
  PanelBody,
  SelectControl
} = window.wp.components

const {
  InspectorControls,
  InnerBlocks
} = window.wp.blockEditor

const { Fragment } = window.wp.element
const { registerBlockType } = window.wp.blocks

/* Namespace */

const n = getNamespace(true)
const name = n + 'contact-form-group-bottom'

/* Attributes from serverside */

const nO = getNamespaceObj(getNamespace())
const attr = nO.blocks[name].attr
const def = nO.blocks[name].default

/* Block */

registerBlockType(name, {
  title: 'Field Group Bottom',
  category: 'theme-blocks',
  icon: 'email',
  attributes: attr,
  parent: [n + 'contact-form-group'],
  edit (props) {
    const { attributes, setAttributes } = props
    const { gap = def.gap } = attributes
    let frag = ''

    if (nO.gap_options.length) {
      frag = (
        <Fragment key='frag'>
          <InspectorControls>
            <PanelBody title='Field Group Bottom Options'>
              <SelectControl
                label='Fields Gap'
                value={gap}
                options={nO.gap_options}
                onChange={gap => setAttributes({ gap })}
              />
            </PanelBody>
          </InspectorControls>
        </Fragment>
      )
    }

    return [
      frag,
      <Panel key='panel'>
        <PanelBody>
          <div className='l-section'>
            <InnerBlocks
              allowedBlocks={[n + 'contact-form-field']}
            />
          </div>
        </PanelBody>
      </Panel>
    ]
  },
  save () {
    return <InnerBlocks.Content /> // this block is rendered in php
  }
})
