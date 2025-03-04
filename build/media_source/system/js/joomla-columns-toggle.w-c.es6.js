/**
 * Joomla Columns Toggle Web Component
 * With use of <joomla-columns-toggle> you can toggle columns in a table
 */
class JoomlaColumnsToggle extends HTMLElement {
  /**
   * The counter display element selector
   * @type {String}
   * @default 'button'
   */
  // counterDisplay = 'button';

  /**
   * The table selector
   * @type {String}
   * @default 'table'
   */
  // tableSelector = 'table';

  /**
   * The protected columns selector
   * @type {String}
   * @default 'th, .toggle-ignore'
   */
  // protectCol = 'th, .toggle-ignore';

  /**
   * The class to hide columns
   * @type {String}
   * @default 'd-none'
   */
  // hideClass = 'd-none';

  /**
   * The "media query" class list to remove, which may prevent toggling from working.
   * Can be overriden by the attribute "classlist-remove" with a comma separated list of classes.
   * Example:
   *    ['d-none', 'd-xs-table-cell', 'd-sm-table-cell', 'd-md-table-cell', 'd-lg-table-cell', 'd-xl-table-cell', 'd-xxl-table-cell']
   * @type {Array}
  */
  // classlistToRemove = []

  /**
   * The table element, which is the target of the columns toggle
   * @type {HTMLElement}
   */
  // table = null;

  /**
   * The protected columns.
   * Can be overriden by the attribute "protect-col" with a comma separated list of selectors.
   * @type {Array}
   * @default ['th','.toggle-ignore']
   */
  // protectedCols = ['th','.toggle-ignore'];

  /**
   * The total number of columns
   * @type {Number}
   */
  // colsTotal = 0;

  /**
   * The table name, used to store the state in the local storage.
   * Generated from the table dataset name or the page title.
   * @type {String}
   */
  // tableName = '';

  /**
   * The storage key for local storage
   * @type {String}
   */
  // storageKey = 'joomla-tablecolumns-{tableName}';

  /**
   * The class constructor object
   */
  constructor() {
    // Gives element access to the parent class properties
    super();

    // Define attributes
    this.counterDisplay = this.getAttribute('toggle-counter') || this.querySelector('button');
    this.tableSelector = this.getAttribute('table-selector') || 'table';

    // Set the class that should be injected/removed to hide/show columns
    this.hideClass = this.getAttribute('class-to-hide') || 'd-none';

    // Set the "media query" class list to remove, which may prevent toggling from working
    // eslint-disable-next-line prefer-const
    let removeClass = this.getAttribute('classlist-remove');
    // eslint-disable-next-line no-shadow
    this.classlistToRemove = removeClass ? removeClass.split(',').map((removeClass) => removeClass.trim()) : ['d-none', 'd-xs-table-cell', 'd-sm-table-cell', 'd-md-table-cell', 'd-lg-table-cell', 'd-xl-table-cell', 'd-xxl-table-cell'];

    // Set the table element
    this.table = document.querySelector(this.tableSelector);
    if (!this.table) return;

    // Set the protected columns
    // eslint-disable-next-line prefer-const
    let protectCol = this.getAttribute('protect-col') || 'th, .toggle-ignore';
    // eslint-disable-next-line no-shadow
    this.protectedCols = protectCol.split(',').map((protectCol) => protectCol.trim());

    // Set the inital total number of columns to 0
    this.colsTotal = 0;

    // Set the table name and storage key
    this.tableName = this.table.dataset.name ?? document.querySelector('.page-title').textContent.trim().replace(/[^a-z0-9]/gi, '-').toLowerCase();
    this.storageKey = `joomla-tablecolumns-${this.tableName}`;
  }

  /**
   * Runs each time the element is appended to or moved in the DOM
   */
  connectedCallback() {
    // Set the table element
    this.table = document.querySelector(this.tableSelector);
    if (!this.table) return;

    // Remove "media query" classes from table body columns, which may prevent toggling from working.
    this.table.querySelectorAll('tbody td').forEach((tbodyCol) => {
      this.classlistToRemove.forEach((classToRemove) => {
        tbodyCol.classList.remove(classToRemove);
      });
    });

    // Load state from local storage
    const hiddenColsState = this.loadState();

    // Generate the list of columns in dropdown menu
    if (!this.querySelector('ul')) return;
    const list = this.querySelector('ul').cloneNode(true);

    // Loop through the header columns and generate the dropdown list
    if (!this.table.querySelector('thead tr') || this.table.querySelector('thead tr').children.length < 1) return;
    [].slice.call(this.table.querySelector('thead tr').children).forEach((theadCol, index) => {
      // Skip the first column, unless it's a th, as we don't want to display the checkboxes
      if (index === 0 && theadCol.nodeName !== 'TH') return; // TODO use class ???

      // Find the header name
      let titleEl = theadCol.querySelector('span');
      let title = titleEl ? titleEl.textContent.trim() : '';
      if (!title) {
        titleEl = theadCol.querySelector('span.visually-hidden') || theadCol;
        title = titleEl.textContent.trim();
      }
      if (title.includes(':')) {
        title = title.split(':', 2)[1].trim();
      }

      // Set inital values for disabled and checked
      let disabled = '';
      let checked = 'checked';

      // Check if the column should be hidden
      if (window.innerWidth <= 992 || hiddenColsState.indexOf(index) >= 0) {
        checked = '';
      }

      // Check if the column is protected
      if (this.isProtectedColumnByIndex(index)) {
        disabled = 'disabled';
        checked = 'checked';
      }

      list.insertAdjacentHTML(
        'beforeend',
        `<li class="form-check">
          <input id="col-toggle-${index}" type="checkbox" name="table[column][]" ${disabled} class="form-check-input me-1" data-toggle-index="${index}" ${checked} />
            <label for="col-toggle-${index}" class="form-check-label">${title}</label>
          </input>
        </li>`,
      );

      // Remove "media query" classes from table header column, which may prevent toggling from working.
      this.classlistToRemove.forEach((classToRemove) => {
        theadCol.classList.remove(classToRemove);
      });

      // Hide the column if it's not checked
      if (checked !== 'checked') {
        this.hideColumn(index);
      }

      this.colsTotal += 1;
    });

    // Replace the dropdown list with the generated list
    this.querySelector('ul').replaceWith(list);

    // Listen to checkboxes change
    list.addEventListener('change', (event) => {
      if (!event.target.hasAttribute('data-toggle-index')) return;
      this.toggleColumn(parseInt(event.target.getAttribute('data-toggle-index'), 10));
    });

    // Set the text of the counter display element
    this.updateCounter();
  }

  /**
   * Toggle column visibility
   *
   * @param {Number} index  The column index
   * @param {Boolean} force To force hide
   */
  toggleColumn(index) {
    // Skip incorrect index
    if (!this.table.querySelector('thead tr').children[index]) return;
    if (!this.querySelector(`ul input[data-toggle-index="${index}"]`)) return;

    // Skip the protected columns
    if (this.querySelector(`ul input[data-toggle-index="${index}"]`).disabled
        || this.isProtectedColumnByIndex(index)) return;

    // Toggle the column visibility
    if (this.querySelector(`ul input[data-toggle-index="${index}"]`).checked) {
      this.showColumn(index);
    } else {
      this.hideColumn(index);
    }

    // Update the counter display
    this.updateCounter();

    // Save the state in local storage
    this.saveState();
  }

  /**
   * Hide a column by index
   * @param {Number} index The column index
   * @returns {void}
   */
  hideColumn(index) {
    this.table.querySelector('thead tr').children[index].classList.add(this.hideClass);
    this.table.querySelectorAll('tbody tr').forEach(($row) => {
      $row.children[index].classList.add(this.hideClass);
    });
  }

  /**
   * Show a column by index
   * @param {Number} index The column index
   * @returns {void}
   */
  showColumn(index) {
    this.table.querySelector('thead tr').children[index].classList.remove(this.hideClass);

    this.table.querySelectorAll('tbody tr').forEach(($row) => {
      $row.children[index].classList.remove(this.hideClass);
    });
  }

  /**
   * Check if a column is protected by index
   * @param {Number} index The column index
   * @returns {Boolean} If true, the column is protected
   */
  isProtectedColumnByIndex(index) {
    let result = false;
    this.protectedCols.forEach((protectedCol) => {
      if (!result && this.table.querySelector('tbody tr').children[index].matches(protectedCol)) {
        result = true;
      }
    });
    return result;
  }

  /**
   * Update the counter element text to reflect the number of visible/total columns
   * @returns {void}
   */
  updateCounter() {
    const countVisible = this.querySelectorAll('ul input:checked').length;
    this.counterDisplay.textContent = `${countVisible}/${this.colsTotal} ${Joomla.Text._('JGLOBAL_COLUMNS')}`;
  }

  /**
   * Save state, list of hidden columns
   * @returns {void}
   */
  saveState() {
    const hiddenCols = [];

    this.querySelectorAll('ul input[data-toggle-index]').forEach((colToggle) => {
      if (!colToggle.checked) {
        hiddenCols.push(colToggle.getAttribute('data-toggle-index'));
      }
    });

    window.localStorage.setItem(this.storageKey, hiddenCols.join(','));
  }

  /**
   * Load state, list of hidden columns
   * @returns {Array} The list of hidden columns
   */
  loadState() {
    const stored = window.localStorage.getItem(this.storageKey);
    if (stored) {
      return stored.split(',').map((val) => parseInt(val, 10));
    }
    return [];
  }
}

customElements.define('joomla-columns-toggle', JoomlaColumnsToggle);

export default JoomlaColumnsToggle;
