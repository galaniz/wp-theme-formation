/**
 * Contact form group top block
 */

/* Dependencies */

const { getNamespace } = window.blockUtils

const {
  Panel,
  PanelBody
} = window.wp.components

const { InnerBlocks } = window.wp.blockEditor
const { registerBlockType } = window.wp.blocks

/* Namespace */

const n = getNamespace(true)
const name = n + 'contact-form-group-top'

/* Blocks */

registerBlockType(name, {
  title: 'Field Group Top',
  category: 'theme-blocks',
  icon: 'email',
  parent: [n + 'contact-form-group'],
  edit (props) {
    return (
      <Panel>
        <PanelBody>
          <div className='l-section'>
            <InnerBlocks
              allowedBlocks={['core/paragraph', 'core/heading', 'core/image']}
            />
          </div>
        </PanelBody>
      </Panel>
    )
  },
  save () {
    return <InnerBlocks.Content /> // this block is rendered in php
  }
})
