describe('Test in frontend that the articles module', () => {
  it('can display the title of the article', () => {
    cy.db_createCategory({ extension: 'com_content' })
      .then(async (categoryId) => {
        await cy.db_createArticle({ title: 'automated test article', catid: categoryId });
        await cy.db_createModule({
          module: 'mod_articles',
          params: JSON.stringify({
            mode: 'normal',
            show_on_article_page: 1,
            count: 5,
            category_filtering_type: 1,
            catid: categoryId,
            show_child_category_articles: 0,
            levels: 1,
            exclude_current: 1,
            ex_or_include_articles: 0,
            excluded_articles: [],
            included_articles: [],
            title_only: 0,
            articles_layout: 1,
            layout_columns: 3,
            item_title: 1,
            item_heading: 'h4',
            link_titles: 1,
            show_author: 0,
            show_category: 0,
            show_category_link: 0,
            show_date: 0,
            show_date_field: 'created',
            show_date_format: 'Y-m-d H:i:s',
            show_hits: 0,
            info_layout: 0,
            show_tags: 0,
            trigger_events: 0,
            show_introtext: 0,
            introtext_limit: 100,
            image: 0,
            img_intro_full: 'none',
            show_readmore: 0,
            show_readmore_title: 1,
            readmore_limit: 15,
            show_featured: 'show',
            show_archived: 'hide',
            author_filtering_type: 1,
            author_alias_filtering_type: 1,
            date_filtering: 'off',
            date_field: 'a.created',
            start_date_range: '',
            end_date_range: '',
            relative_date: 30,
            article_ordering: 'publish_up',
            article_ordering_direction: 'DESC',
            article_grouping: 'none',
            date_grouping_field: 'created',
            month_year_format: 'F Y',
            article_grouping_direction: 'ksort',
            layout: '_:default',
            moduleclass_sfx: '',
            owncache: 1,
            cache_time: 900,
            module_tag: 'div',
            bootstrap_size: '0',
            header_tag: 'h3',
            header_class: '',
            style: '0',
          }),
        });
      })
      .then(() => {
        cy.visit('/');

        cy.contains('li', 'automated test article');
      });
  });
});
