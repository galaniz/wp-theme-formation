/**
 * Restrict embed variations
 */

/* Dependencies */

const {
  getNamespace,
  getNamespaceObj
} = window.blockUtils

/* Unregister blocks if not in embed array */

window.addEventListener('load', () => {
  const n = getNamespace(true)

  if (!n || !Object.getOwnPropertyDescriptor(window.wp, 'blocks')) { return }

  const nO = getNamespaceObj(getNamespace())

  if (!nO) { return }

  if (!Object.getOwnPropertyDescriptor(nO, 'embed_variations')) { return }

  const embedVariations = nO.embed_variations
  const embedBlocks = window.wp.blocks.getBlockVariations('core/embed')

  if (embedBlocks) {
    embedBlocks.forEach(block => {
      if (!embedVariations.includes(block.name)) {
        window.wp.blocks.unregisterBlockVariation('core/embed', block.name)
      }
    })
  }
})
