describe('Test in backend that the content history list', () => {
  beforeEach(() => {
    cy.task('queryDB', "DELETE FROM #__content WHERE title = 'Test article'");
    cy.doAdministratorLogin();
  });

  it('has a title', () => {
    cy.visit('/administrator/index.php?option=com_content&task=article.add');
    cy.get('#jform_title').clear().type('Test article');
    cy.clickToolbarButton('Save');
    cy.get('#toolbar-versions').click(); // @todo remove after https://github.com/joomla-projects/joomla-cypress/pull/40
    // cy.clickToolbarButton('Versions'); // @todo enable after https://github.com/joomla-projects/joomla-cypress/pull/40
    cy.get('.joomla-dialog-header').should('contain.text', 'Versions');
  });

  it('can display a list of content history', () => {
    cy.visit('/administrator/index.php?option=com_content&task=article.add');
    cy.get('#jform_title').clear().type('Test article');
    cy.clickToolbarButton('Save');
    cy.get('#toolbar-versions').click(); // @todo remove after https://github.com/joomla-projects/joomla-cypress/pull/40
    // cy.clickToolbarButton('Versions'); // @todo enable after https://github.com/joomla-projects/joomla-cypress/pull/40
    const currentDate = new Date();
    const formattedDate = `${currentDate.getFullYear()}-${(currentDate.getMonth() + 1).toString().padStart(2, '0')}-${currentDate.getDate().toString().padStart(2, '0')}`;
    cy.log(formattedDate);
    cy.wait(5000);
    cy.get('iframe.iframe-content') // the iframe's selector
      .its('0.contentDocument.body') // Access the iframe's document body
      .should('not.be.empty') // Ensure the body is loaded
      .then(cy.wrap) // Wrap the body for further Cypress commands
      .find('a') // Find the anchor tag containing the string
      .should('contain.text', formattedDate); // The expected text
    cy.get('button.button-close.btn-close').click();
  });

  it('can open the history content item modal', () => {
    cy.visit('/administrator/index.php?option=com_content&task=article.add');
    cy.get('#jform_title').clear().type('Test article');
    cy.clickToolbarButton('Save');
    cy.get('#toolbar-versions').click(); // @todo remove after https://github.com/joomla-projects/joomla-cypress/pull/40
    // cy.clickToolbarButton('Versions'); // @todo enable after https://github.com/joomla-projects/joomla-cypress/pull/40
    cy.wait(5000);

    cy.get('iframe.iframe-content') // the iframe's selector
      .its('0.contentDocument.body') // Access the iframe's document body
      .should('not.be.empty') // Ensure the body is loaded
      .then(cy.wrap) // Wrap the body for further Cypress commands
      .find('a')
      .invoke('attr', 'data-url')
      .then((url) => {
        const baseUrl = Cypress.config('baseUrl');
        // Combine the base URL and the relative URL
        const completeUrl = new URL(url, baseUrl).href;
        // Remove the subdomain (if any) from the base URL
        const basePath = new URL(baseUrl).pathname;
        const modifiedUrl = completeUrl.replace(new URL(baseUrl).origin + basePath, '');

        cy.log('new window url', modifiedUrl);
        // Visit the URL directly
        cy.visit(modifiedUrl);
        // Verify the text on the new page
        cy.contains('Test article').should('be.visible');
      });
  });
});
