/**
 * Handle file removal
 */

/* Imports */

import {
  mergeObjects,
  request,
  setLoaders,
  urlEncode
} from 'Formation/utils'

/* Module */

const fileRemove = (args) => {
  /* Helpers */

  const disable = (disable = true) => {
    setLoaders(
      [f.loader], // loaders
      [f.button], // buttons
      disable // show
    )
  }

  /* Event callbacks */

  const remove = () => {
    disable(true)

    const data = {
      action: f.action,
      file_path: f.filePath
    }

    data[f.nonce.name] = f.nonce.nonce

    request({
      method: 'POST',
      url: f.url,
      headers: { 'Content-type': 'application/x-www-form-urlencoded' },
      body: urlEncode(data)
    })
      .then(response => {
        disable(false)
        f.parent.removeChild(f.item)
      })
      .catch(xhr => {
        disable(false)
      })
  }

  /* Merge args with defaults */

  mergeObjects({
    item: null,
    button: null,
    loader: null,
    filePath: '',
    url: '',
    action: '',
    nonce: {
      nonce: '',
      name: ''
    }
  }, args)

  /* Check for empty elements */

  let error = false

  for (const prop in args) {
    if (!args[prop]) {
      error = true
      break
    }
  }

  if (error) { return false }

  const f = args

  f.parent = f.item.parentElement

  /* Event listeners */

  f.button.addEventListener('click', remove)
}

/* Exports */

export default fileRemove
