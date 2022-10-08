/**
 * Handle file uploads
 */

/* Imports */

import {
  mergeObjects,
  request,
  show,
  setLoaders
} from 'Formation/utils'

import DOMPurify from 'dompurify'

/* Module */

const fileUpload = (args) => {
  /* Helpers */

  const disable = (disable = true) => {
    setLoaders(
      [f.loader], // loaders
      [f.selectButton], // buttons
      disable // show
    )
  }

  /* Event callbacks */

  const select = function () {
    const files = this.files

    /* Leave if no files */

    if (files.length === 0) { return }

    /* Not doing multiple uploads for now */

    const file = files[0]
    const type = file.type

    if (type === 'image/svg+xml') {
      disable(true)

      const reader = new window.FileReader()

      reader.onload = function (e) {
        const text = reader.result
        const clean = DOMPurify.sanitize(text)
        const blob = new window.Blob([clean], { type: type })

        if (f.fileInputMeta) {
          const frag = document.createDocumentFragment()
          const div = document.createElement('div')

          div.innerHTML = clean
          div.style.display = 'none'
          div.id = `temp-${file.lastModified}`

          frag.appendChild(div)
          document.body.appendChild(frag)

          const viewBox = document.querySelector(`#temp-${file.lastModified} svg`).getAttribute('viewBox').split(' ')

          if (viewBox) {
            const w = viewBox[2]
            const h = viewBox[3]

            f.fileInputMeta.value = `${w}|${h}`
          }
        }

        upload(new window.File([blob], file.name, {
          lastModified: file.lastModified,
          type: type
        }))
      }

      reader.readAsText(file)
    } else {
      upload(file)
    }
  }

  const remove = (e) => {
    if (e) { e.preventDefault() }

    f.fileName.textContent = ''
    f.fileInput.value = ''

    show(f.fileContainer, false)
    show(f.noFileContainer)
  }

  /* Upload to backend */

  const upload = (file, meta = '') => {
    const formData = new window.FormData()

    formData.append('action', f.action)
    formData.append(f.nonce.name, f.nonce.nonce)
    formData.append('files[]', file)

    disable(true)

    request({
      method: 'POST',
      url: f.url,
      body: formData
    })
      .then(response => {
        const data = JSON.parse(response)

        disable(false)

        if (data.length > 0) {
          const result = data[0]

          f.fileInput.value = result.url
          f.fileName.textContent = `${result.title}.${result.ext}`

          if (f.fileInputMeta) {
            f.fileInputMeta.value = `${f.fileInputMeta.value}|${result.path}`
          }

          if (f.fileType === 'image') { f.fileImage.setAttribute('src', result.url) }

          show(f.noFileContainer, false)
          show(f.fileContainer)
        }
      })
      .catch(xhr => {
        disable(false)
      })
  }

  /* Merge args with defaults */

  mergeObjects({
    selectButton: null,
    removeButton: null,
    fileContainer: null,
    noFileContainer: null,
    fileImage: null,
    fileIcon: null,
    fileName: null,
    fileInput: null,
    fileInputMeta: null,
    fileType: 'file',
    loader: null,
    url: '',
    action: '',
    nonce: {
      nonce: '',
      name: ''
    }
  }, args)

  let error = false

  /* Check for empty elements */

  for (const prop in args) {
    if (!args[prop] && prop !== 'fileInputMeta') {
      error = true
      break
    }
  }

  if (error) { return false }

  const f = args

  /* Event listeners */

  f.selectButton.addEventListener('change', select)
  f.removeButton.addEventListener('click', remove)
}

/* Exports */

export default fileUpload
