// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Print functionality for notes.
 *
 * @module     ltool_note/print
 * @copyright  2025 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery'], ($) => {
  /**
   * Initialize print functionality
   */
  function init() {
    // Print button functionality
    const printBtn = document.getElementById("print-notes-btn")
    if (printBtn) {
      printBtn.addEventListener("click", (e) => {
        e.preventDefault()
        window.print()
      })
    }

    // Return button functionality
    const returnBtn = document.getElementById("return-notes-btn")
    if (returnBtn) {
      returnBtn.addEventListener("click", (e) => {
        e.preventDefault()
        window.close()
      })
    }

    // Keyboard shortcuts
    document.addEventListener("keydown", (e) => {
      // Ctrl+P for print
      if (e.ctrlKey && e.key === "p") {
        e.preventDefault()
        window.print()
      }
      // Escape to close
      if (e.key === "Escape") {
        e.preventDefault()
        window.close()
      }
    })

    // Auto-expand all accordions for print
    const collapseElements = document.querySelectorAll(".collapse")
    collapseElements.forEach((element) => {
      element.classList.add("show")
    })

  }

  return {
    init: init,
  }
})
