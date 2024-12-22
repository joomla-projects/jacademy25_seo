Cypress.Commands.add('config_setParameter', (parameter, value) => {
  const configPath = `${Cypress.env('cmsPath')}/configuration.php`;

  // Return a Cypress chainable for chaining
  return cy.readFile(configPath).then((fileContent) => {
    // Setup the new value
    const newValue = typeof value === 'string' ? `'${value}'` : value;

    // The regex to find the line of the parameter
    const regex = new RegExp(`^.*\\$${parameter}\\s.*$`, 'mg');

    // Replace the whole line with the new value
    const content = fileContent.replace(regex, `public $${parameter} = ${newValue};`);

    /* Write the modified content back to the configuration file relative to the CMS root folder and
     * wait for the task to complete and chain it.
     */
    return cy.task('writeRelativeFile', { path: 'configuration.php', content }).then((result) => {
      cy.log(result); // Log success message for debugging
    });
  });
});
