describe('Test that content API endpoint', () => {
  afterEach(() => cy.task('queryDB', 'DELETE FROM #__content'));

  it('can deliver a list of articles', () => {
    cy.db_createArticle({ title: 'automated test article' })
      .then(() => cy.api_get('/content/articles'))
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('title')
        .should('include', 'automated test article'));
  });

  it('can deliver a list of articles filtered by the modified start and end filter', () => {
    cy.db_createArticle({ title: 'automated test article before', modified: '2025-03-15 10:00:00' })
      .then(() => cy.db_createArticle({ title: 'automated test article within', modified: '2025-03-15 15:00:00' }))
      .then(() => cy.db_createArticle({ title: 'automated test article after', modified: '2025-03-15 20:00:00' }))
      .then(() => cy.api_get('/content/articles?filter[modified_start]=2025-03-15 14:00:00&filter[modified_end]=2025-03-15 16:00:00'))
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('title')
        .should('include', 'automated test article within')
        .should('not.include', 'automated test article before')
        .should('not.include', 'automated test article after'));
  });

  it('can deliver a single article', () => {
    cy.db_createArticle({ title: 'automated test article' })
      .then((article) => cy.api_get(`/content/articles/${article.id}`))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'automated test article'));
  });

  it('can create an article', () => {
    cy.db_createCategory({ extension: 'com_content' })
      .then((categoryId) => cy.api_post('/content/articles', {
        title: 'automated test article',
        alias: 'test-article',
        catid: categoryId,
        introtext: '',
        fulltext: '',
        state: 1,
        access: 1,
        language: '*',
        created: '2023-01-01 20:00:00',
        modified: '2023-01-01 20:00:00',
        images: '',
        urls: '',
        attribs: '',
        metadesc: '',
        metadata: '',
      }))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'automated test article'));
  });

  it('can update an article', () => {
    cy.db_createArticle({ title: 'automated test article' })
      .then((article) => cy.api_patch(`/content/articles/${article.id}`, { title: 'updated automated test article' }))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'updated automated test article'));
  });

  it('can delete an article', () => {
    cy.db_createArticle({ title: 'automated test article', state: -2 })
      .then((article) => cy.api_delete(`/content/articles/${article.id}`));
  });
});
