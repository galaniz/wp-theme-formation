/**
 * WP media select
 */

/* Import */

import { mergeObjects, show } from 'Formation/utils'

/* Module */

const wpMedia = (args) => {
  /* Event callbacks */

  const openModal = (e) => {
    e.preventDefault()
    mediaModal.frame.open()
  }

  const remove = (e) => {
    if (e) { e.preventDefault() }

    f.fileName.textContent = ''
    f.fileInput.value = ''

    show(f.noFileContainer)
    show(f.fileContainer, false)
  }

  const closeModal = (e) => {
    const selection = mediaModal.frame.state().get('selection')

    selection.forEach((attachment, i) => {
      const image = true

      if (image) {
        f.fileImage.setAttribute('src', attachment.attributes.url)
        f.fileImage.setAttribute('alt', attachment.attributes.alt)
      } else {
        f.fileIcon.textContent = attachment.attributes.subtype
      }

      f.fileName.textContent = attachment.attributes.filename
      f.fileInput.value = attachment.id

      show(f.noFileContainer, false)
      show(f.fileContainer)
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
    fileType: 'file',
    mediaVars: {}
  }, args)

  let error = false

  /* Check for empty elements */

  for (const prop in args) {
    if (!args[prop]) {
      error = true
      break
    }
  }

  if (error) { return false }

  const f = args
  const wp = window.wp
  const mediaModal = {}

  /* Media args */

  const mediaArgs = {
    frame: 'select',
    title: 'Select Media',
    multiple: false,
    library: {}
  }

  if (f.mediaVars) {
    for (const mediaVar in f.mediaVars) {
      mediaArgs.library[mediaVar] = f.mediaVars[mediaVar]
    }
  }

  /* Media modal */

  const attributes = mediaArgs

  attributes.states = []

  attributes.states.push(
    new wp.media.controller.Library({
      multiple: false,
      title: attributes.title,
      priority: 20,
      filterable: 'all'
    })
  )

  mediaModal.frame = wp.media(attributes)

  /* Event listeners */

  f.selectButton.addEventListener('click', openModal)
  f.removeButton.addEventListener('click', remove)
  mediaModal.frame.on('close', closeModal)
}

/* Exports */

export default wpMedia
