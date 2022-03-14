/**
 * Select link from Insert/Edit link modal
 */

import { mergeObjects, show } from 'Formation/utils'

/* Module */

const link = (args) => {
  /* Event callbacks */

  const openModal = (e) => {
    textareaId = 'js-link-' + Date.now()

    document.body.insertAdjacentHTML('beforeend', `<textarea id="${textareaId}" style="display:none;"></textarea>`)

    const href = f.linkUrl.href
    const text = f.linkText.textContent
    const target = f.linkTarget.textContent

    /* Open the link popup */

    window.wpLink.open(
      textareaId,
      href,
      text,
      null
    )

    /* Delay for modal opening */

    setTimeout(() => {
      document.getElementById('wp-link-url').value = href
      document.getElementById('wp-link-text').value = text
      document.getElementById('wp-link-target').checked = (target === '_blank')
    }, 100)

    current = true
  }

  const remove = (e) => {
    if (e) { e.preventDefault() }

    f.linkText.textContent = ''
    f.linkUrl.href = ''
    f.linkInput.value = ''

    show(f.linkContainer, false)
    show(f.noLinkContainer)
  }

  const closeModal = (e) => {
    e.preventDefault()

    if (current) {
      const linkAttrs = window.wpLink.getAttrs()
      const text = document.getElementById('wp-link-text').value
      const href = linkAttrs.href
      const target = linkAttrs.target

      if (href) {
        f.linkText.textContent = text
        f.linkTarget.textContent = target
        f.linkUrl.textContent = href
        f.linkUrl.href = href
        f.linkInput.value = text + '|' + href + '|' + target

        show(f.noLinkContainer, false)
        show(f.linkContainer)
      }
    }

    window.wpLink.close()

    document.body.removeChild(document.getElementById(textareaId))

    current = false
  }

  /* Merge args with defaults */

  mergeObjects({
    selectButton: null,
    removeButton: null,
    linkContainer: null,
    noLinkContainer: null,
    linkText: null,
    linkUrl: null,
    linkTarget: null,
    linkInput: null,
    reset: false
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
  let current = false
  let textareaId = ''

  /* Reset */

  if (f.reset) { remove() }

  /* Event listeners */

  f.selectButton.addEventListener('click', openModal)
  f.editButton.addEventListener('click', openModal)
  f.removeButton.addEventListener('click', remove)

  document.getElementById('wp-link-submit').addEventListener('click', closeModal)
}

/* Exports */

export default link
