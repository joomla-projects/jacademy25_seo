describe('Test in backend that the Installer', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
    cy.visit('/administrator/index.php?option=com_installer&view=install');
  });

  it('has a title', () => {
    cy.get('h1.page-title').should('contain.text', 'Extensions: Install');
  });

  it('can install extension from URL tab', () => {
    cy.get('joomla-tab-element#url').should('exist');
    cy.get('joomla-tab-element#url').click({ force: true });
    cy.get('button#installbutton_url').should('contain.text', 'Check & Install');
    cy.get('input#install_url').type('https://github.com/joomla-extensions/patchtester/releases/download/4.4.0/com_patchtester_4.4.0.zip', { force: true }); // Fill in the input field
    cy.get('button#installbutton_url').click({ force: true });
    // Check if the installation was successful
    cy.contains('Installation of the component was successful.');
  });
});
