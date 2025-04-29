import('./commands.mjs');
import('joomla-cypress');

before(() => {
  cy.task('startMailServer');
  cy.task('clearLogs');
});

afterEach(() => {
  cy.checkForPhpNoticesOrWarnings();
  cy.task('cleanupDB');
  cy.task('checkForLogs');
});
