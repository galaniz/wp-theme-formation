/**
 * Insert block into editor
 *
 * Note: workaround for gutenberg templates.
 */

/* Dependencies */

const {
  getNamespace,
  getNamespaceObj
} = window.blockUtils

/* Loop through and insert blocks array */

window.addEventListener('load', () => {
  const n = getNamespace(true)

  if (!n) { return }

  const nO = getNamespaceObj(getNamespace())

  if (!nO) { return }

  let insertBlock = false
  let insertBlocks = false

  if (Object.getOwnPropertyDescriptor(nO, 'insert_block')) {
    insertBlock = nO.insert_block
  }

  if (Object.getOwnPropertyDescriptor(nO, 'insert_blocks')) { insertBlocks = nO.insert_blocks }

  if (!insertBlock && !insertBlocks) { return }

  if (insertBlock) {
    insertBlocks = [
      {
        name: insertBlock,
        defaults: nO.insert_block_defaults
      }
    ]
  }

  if (!insertBlocks) { return }

  if (!insertBlocks.length) { return }

  const blocksInEditor = window.wp.data.select('core/block-editor').getBlocks()

  insertBlocks.forEach(bb => {
    const blockName = bb.name
    let blockExists = false

    blocksInEditor.forEach(b => {
      if (b.name === blockName) {
        blockExists = true
      }
    })

    if (!blockExists) {
      const block = window.wp.blocks.createBlock(blockName, bb.defaults)
      const blockAdded = window.wp.data.dispatch('core/block-editor').insertBlock(block, 0)

      console.log(`BLOCK ${blockName} ADDED`, blockAdded)
    }
  })
})
