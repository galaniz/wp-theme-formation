/**
 * Show hidden fields depending on value of option
 */

/* Imports */

import { closest } from 'Formation/utils'

/* Inline event callback */

window.showHiddenFields = function (event) {
  const select = event.currentTarget
  const selectedOption = select.options[select.selectedIndex].value
  const optionsTextarea = closest(select, 'o-form__field').nextElementSibling

  if (selectedOption === 'select' || selectedOption === 'checkbox' || selectedOption === 'radio') {
    optionsTextarea.style.display = 'block'
  } else {
    optionsTextarea.style.display = 'none'
    optionsTextarea.value = ''
  }
}
