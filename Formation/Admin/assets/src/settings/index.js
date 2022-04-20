/**
 * Hide/show siblings based on value
 */

/* Imports */

import { closest } from 'Formation/utils'

/* DOM loaded handler */

const initialize = () => {
  const toggle = (t) => {
    const toggle = closest(t, 'o-toggle')

    if (toggle) { toggle.setAttribute('data-hide', t.checked) }
  }

  window.toggleSiblings = (event) => {
    toggle(event.target)
  }

  const toggleTriggers = [].slice.call(document.querySelectorAll('.o-toggle__trigger'))

  if (toggleTriggers.length) {
    toggleTriggers.forEach((t) => {
      toggle(t)
    })
  }
}

/* DOM loaded listener */

document.addEventListener('DOMContentLoaded', initialize)
