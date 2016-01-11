describe('madison home doc list', function() {
  beforeEach(function() {
    browser.get('/');
  });

  it('should see a list of documents', function() {
    var docList = element.all(by.repeater('doc in docs'));
    expect(docList.count()).toBe(2);
  });

  it('should display a list of most active documents', function() {
    var activeDocs = element.all(by.repeater('doc in mostActive'));
    var mostActiveDoc = activeDocs.first();
    expect(activeDocs.count()).toBe(1);
    expect(mostActiveDoc.getText()).toMatch(/Example Document/);
    expect(mostActiveDoc.getText()).toMatch(/3 Comments/);
    expect(mostActiveDoc.getText()).toMatch(/2 Annotations/);
  });

  it('should have a featured document', function() {
    var featuredDoc = element(by.css('.main-feature'));
    expect(featuredDoc.getText()).toMatch(/Example Document/);
    expect(featuredDoc.getText()).toMatch(/Sponsored by Example Group Display/);
    expect(featuredDoc.getText()).toMatch(/3 Comments/);
    expect(featuredDoc.getText()).toMatch(/2 Annotation/);
  });

  it('takes me to the document page when featured doc title is clicked', function() {
    var featuredDoc = element(by.css('.main-feature'));
    var title = featuredDoc.element(by.css('.entry-title a'));

    // Go to that document page
    title.click();

    var docInfo = element(by.css('.doc-info'));
    var docTitle = docInfo.element(by.css('.heading'));
    expect(docTitle.getText()).toBe('Example Document');
  });

  // TODO: Test that "Recent Activity", "Recent Legislation and "Most Active
  // Documents" show in correct orders. Will require some kind of control over
  // document and activity dates. Currently the database seeding just takes
  // current timestamps, so that will need to be adjusted.

});
