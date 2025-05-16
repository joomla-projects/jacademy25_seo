describe('Test that contenthistory for content category API endpoint', () => {
  afterEach(() => {
    cy.task('queryDB', "DELETE FROM #__categories WHERE title = 'automated test content category'");
    cy.task('queryDB', 'DELETE FROM #__history');
  });

  it('can get the history of an existing article category', () => {
    cy.api_post('/content/categories', {
      title: 'automated test content category',
      description: 'automated test content category description',
      parent_id: 1,
      extension: 'com_content',
    })
      .then((category) => cy.api_get(`/content/category/${category.body.data.attributes.id}/contenthistory`))
      .then((response) => {
        // Assert response status
        expect(response.status).to.eq(200);

        // Extract the `data` array
        const historyEntries = response.body.data;

        // Iterate through each history entry
        historyEntries.forEach((entry) => {
          const { attributes } = entry;

          // Access top-level attributes
          const historyId = entry.id;
          const saveDate = attributes.save_date;
          const { editor } = attributes;
          const characterCount = attributes.character_count;

          // Access nested `version_data`
          const versionData = attributes.version_data;
          const categoryTitle = versionData.title;
          const { alias } = versionData;
          const createdTime = versionData.created_time;
          const modifiedTime = versionData.modified_time;

          // Perform assertions
          expect(attributes).to.have.property('editor_user_id');
          expect(versionData).to.have.property('title');
          expect(versionData).to.have.property('modified_time');
          expect(categoryTitle).to.eq('automated test content category');
        });

        // Check the total pages from metadata
        const totalPages = response.body.meta['total-pages'];
        expect(totalPages).to.eq(1);
      });
  });
});
