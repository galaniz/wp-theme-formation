/**
 * Initialize field functionality
 */

/* Import */

import 'core-js/es/object/assign'
import 'core-js/es/array/from'
import 'core-js/es/promise'
import { closest } from 'Formation/utils'
import fileUpload from './objects/file/upload'
import fileRemove from './objects/file/remove'
import wpMedia from './objects/file/wp-media'
import link from './objects/link'

/* Init */

const initialize = () => {
  const namespace = window.namespace

  if (!Object.getOwnPropertyDescriptor(window, namespace)) { return }

  const n = window[namespace]

  /**
   * File upload
   */

  if (Object.getOwnPropertyDescriptor(n, 'files')) {
    if (n.files.length > 0) {
      const fileItems = Array.from(document.querySelectorAll('.o-asset--upload'))
      const nonceName = namespace + '_upload_file_nonce'

      fileItems.forEach((item, i) => {
        const wp = item.hasAttribute('data-wp')
        const fileInput = item.querySelector('.o-asset__input')
        const fileMeta = document.querySelector(`input[name="${fileInput.name + '_meta'}"]`)
        const args = {
          selectButton: item.querySelector('.o-asset__select input'),
          removeButton: item.querySelector('.o-asset__remove'),
          fileContainer: item.querySelector('.o-asset__exists'),
          noFileContainer: item.querySelector('.o-asset__no'),
          fileImage: item.querySelector('.o-asset__image'),
          fileName: item.querySelector('.o-asset__name'),
          fileInput: fileInput,
          fileInputMeta: fileMeta,
          fileType: n.files[i].file_type
        }

        if (wp) {
          wpMedia(args)
        } else {
          args.loader = item.querySelector('.js-loader-select')
          args.url = n.ajax_url
          args.action = 'upload_file'
          args.nonce = {
            nonce: n[nonceName],
            name: nonceName
          }

          fileUpload(args)
        }
      })
    }
  }

  /**
   * File remove
   */

  const fileRemoveItems = Array.from(document.querySelectorAll('.o-asset--remove'))

  if (fileRemoveItems.length > 0) {
    fileRemoveItems.forEach((item) => {
      const nonceName = namespace + '_remove_file_nonce'

      fileRemove({
        item: item,
        button: item.querySelector('.o-asset__remove'),
        loader: item.querySelector('.js-loader-remove'),
        filePath: item.querySelector('.o-asset__input').value,
        url: n.ajax_url,
        action: 'remove_file',
        nonce: {
          nonce: n[nonceName],
          name: nonceName
        }
      })
    })
  }

  /**
   * Link select/remove
   */

  if (Object.getOwnPropertyDescriptor(n, 'links')) {
    if (n.links.length > 0) {
      const linkItems = Array.from(document.querySelectorAll('.o-asset--link'))

      if (linkItems.length > 0) {
        linkItems.forEach((item) => {
          link({
            selectButton: item.querySelector('.o-asset__select input'),
            editButton: item.querySelector('.o-asset__edit'),
            removeButton: item.querySelector('.o-asset__remove'),
            linkContainer: item.querySelector('.o-asset__exists'),
            noLinkContainer: item.querySelector('.o-asset__no'),
            linkText: item.querySelector('.o-asset__icon'),
            linkUrl: item.querySelector('.o-asset__name'),
            linkTarget: item.querySelector('.o-asset__target'),
            linkInput: item.querySelector('.o-asset__input')
          })
        })
      }
    }
  }

  /**
   * Multi fields
   */

  if (!Object.getOwnPropertyDescriptor(n, 'multi')) { return }

  window.multi = (m) => {
    const multiItem = closest(m, 'o-multi__item')
    const multi = multiItem.parentElement
    let multiItems = Array.from(multi.children)

    const dataType = m.getAttribute('data-type')

    if (dataType === 'add') {
      const name = multiItem.getAttribute('data-name')

      /* Insert new item */

      multiItem.insertAdjacentHTML('afterend', n.multi[name])
    } else {
      multi.removeChild(multiItem)
    }

    /* Reindex items */

    multiItems = Array.from(multi.children)

    multiItems.forEach((item, i) => {
      const inputs = Array.from(item.querySelectorAll('.js-input'))

      inputs.forEach((input) => {
        const dataName = input.getAttribute('data-name')
        const dataId = input.getAttribute('data-id')

        input.name = dataName.replace('%i', i)
        input.id = dataId.replace('%i', i)
      })
    })
  }

  /**
   * Resize textarea to fit content
   */

  window.textareaFitContent = (t) => {
    const txt = t.value
    const cols = t.cols
    const arraytxt = txt.split('\n')
    let rows = arraytxt.length

    for (let i = 0; i < arraytxt.length; i++) { rows += parseInt(arraytxt[i].length / cols) }

    t.rows = rows
  }

  const textareas = Array.from(document.querySelectorAll('.js-fit-content'))

  if (textareas.length > 0) {
    textareas.forEach((t) => {
      window.textareaFitContent(t)
    })
  }
}

initialize()
