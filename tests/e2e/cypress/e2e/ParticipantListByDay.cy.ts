describe("", () => {
  beforeEach(() => {
    cy.resetDB();
    cy.setCookie('locale', 'de');
    cy.loginAs('kochomi');
    cy.visitMeals();
  });

  it("should open the modal and click the button", () => {
    // books kochomi in for a meal if not already booked
    cy.get('h2')
      .contains('Nächste Woche')
      .parent()
      .parent()
      .find('[data-cy="mealCheckbox"]')
      .eq(0)
      .then((ele) =>  {
        // if ele has children, checkbox is checked
        if (ele.children().length === 0) {
          cy.get('h2')
            .contains('Nächste Woche')
            .parent()
            .parent()
            .find('[data-cy="mealCheckbox"]')
            .eq(0)
            .click()
        }
      });

    // finds the information button and clicks it
    cy.get('h2')
      .contains('Nächste Woche')
      .parent()
      .parent()
      .find('span')
      .contains('Montag')
      .parent()
      .find('svg')
      .eq(0)
      .click()

    // checks if Kochomi is in the filter when the filter input is 'Kochomi Meals'
    cy.get('title')
      .contains('Teilnahmen am Montag')
      .parent()
      .find('input')
      .type('Kochomi')
      .parent()
      .parent()
      .parent()
      .find('table')
      .find('div')
      .contains('Kochomi Meals')
      .should('exist')
      
    // checks that Kochomi is not in the filter if filter input doesn't include her
    cy.get('title')
      .contains('Teilnahmen am Montag')
      .parent()
      .find('input')
      .type('abylskdfjsll')
      .parent()
      .parent()
      .parent()
      .find('table')
      .find('div')
      .should('not.exist')
  })
})