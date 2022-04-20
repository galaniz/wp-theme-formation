/**
 * Utilites for blocks
 */

/* Dependencies */

const { InnerBlocks } = window.wp.blockEditor

/* Functions */

const getNamespace = (slash = false) => {
  if (Object.getOwnPropertyDescriptor(window, 'namespace')) {
    return window.namespace + (slash ? '/' : '')
  }

  return ''
}

const getNamespaceObj = namespace => {
  if (!Object.getOwnPropertyDescriptor(window, namespace)) {
    return false
  }

  return window[namespace]
}

const editInnerBlocks = allowedBlocks => {
  return (
    <div>
      <InnerBlocks allowedBlocks={allowedBlocks} />
    </div>
  )
}

const saveInnerBlocks = () => {
  return <InnerBlocks.Content />
}

const getColorSlug = (arr = [], color = '') => {
  if (!arr.length || !color) { return '' }

  let cs = ''

  arr.forEach((c) => {
    if (c.color === color) {
      cs = c.slug
    }
  })

  return cs
}

/* Exports */

module.exports = {
  getNamespace,
  getNamespaceObj,
  editInnerBlocks,
  saveInnerBlocks,
  getColorSlug
}
