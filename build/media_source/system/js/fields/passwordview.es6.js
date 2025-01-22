/**
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

function togglePassword() {
  document.querySelectorAll('input[type="password"]')
    .forEach((input) => {
      const toggleButton = input.parentNode.querySelector('.input-password-toggle');

      if (toggleButton) {
        const hasClickListener = toggleButton.getAttribute('clickListener') === 'true';

        if (!hasClickListener) {
          toggleButton.setAttribute('clickListener', 'true');
          toggleButton.addEventListener('click', () => {
            const icon = toggleButton.firstElementChild;
            const srText = toggleButton.lastElementChild;

            if (input.type === 'password') {
              // Update the icon class or the svg
              if (icon.nodeName === 'SPAN') {
                icon.classList.remove('icon-eye');
                icon.classList.add('icon-eye-slash');
              } else {
                icon.innerHTML = '<use href="#j--eye-slash"></use>';
              }

              // Update the input type
              input.type = 'text';

              // Focus the input field
              input.focus();

              // Update the text for screenreaders
              srText.innerText = Joomla.Text._('JHIDEPASSWORD');
            } else if (input.type === 'text') {
              // Update the icon class or the svg
              if (icon.nodeName === 'SPAN') {
                icon.classList.add('icon-eye');
                icon.classList.remove('icon-eye-slash');
              } else {
                icon.innerHTML = '<use href="#j--eye"></use>';
              }


              // Update the input type
              input.type = 'password';

              // Focus the input field
              input.focus();

              // Update the text for screenreaders
              srText.innerText = Joomla.Text._('JSHOWPASSWORD');
            }
          });
        }
      }
      const modifyButton = input.parentNode.querySelector('.input-password-modify');

      if (modifyButton) {
        modifyButton.addEventListener('click', () => {
          const lock = !modifyButton.classList.contains('locked');

          if (lock === true) {
            // Add lock
            modifyButton.classList.add('locked');

            // Reset value to empty string
            input.value = '';

            // Disable the field
            input.setAttribute('disabled', '');

            // Update the text
            modifyButton.innerText = Joomla.Text._('JMODIFY');
          } else {
            // Remove lock
            modifyButton.classList.remove('locked');

            // Enable the field
            input.removeAttribute('disabled');

            // Focus the input field
            input.focus();

            // Update the text
            modifyButton.innerText = Joomla.Text._('JCANCEL');
          }
        });
      }
    });
}

document.addEventListener('joomla:updated', togglePassword);
togglePassword();
