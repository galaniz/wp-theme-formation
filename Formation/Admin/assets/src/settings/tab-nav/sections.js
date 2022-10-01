/**
 * Move title to correct section under tabs
 */

/* Init */

const initialize = () => {
  const sections = [].slice.call(document.querySelectorAll('.js-section'))

  if (sections.length > 0) {
    sections.forEach((section, i) => {
      const lastChild = section.lastElementChild

      if (lastChild.tagName.toUpperCase() === 'H2') {
        const nextSection = section.nextElementSibling

        nextSection.insertBefore(lastChild, nextSection.firstElementChild)
      }
    })
  }
}

initialize()
