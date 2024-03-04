import dayjs = require('dayjs')
var weekday = require('dayjs/plugin/weekday')
dayjs.extend(weekday)

describe("", () => {
  beforeEach(() => {
    cy.resetDB();
    cy.setCookie('locale', 'de');
    cy.loginAs('kochomi');
    cy.visitMeals();
  });

  it ("should close the modal when button is clicked", () => {
    // opens the modal
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
      // closes the modal when button is clicked
      cy.get('title')
      .contains('Teilnahmen am Montag')
      .parent()
      .parent()
      .parent()
      .find('svg')
      .eq(0)
      .click()
})
  it ("should close the modal when outside the modal is clicked", () => {
    // opens the modal
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
      // closes the modal when environment is clicked
      cy.get('title')
      .contains('Teilnahmen am Montag')
      .parent()
      .parent()
      .parent()
      .parent()
      .click()
  })

  it ("should book Kochomi and filter for her", () => {
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

  const nextMonday = dayjs().weekday(7) // next Monday

  it ("should be scrollable for more than 20 participants" ,() => {
    // intercepts the api call
    cy.intercept('GET', '**/api/participations/day/{nextMonday}*', { fixture: 'participantsListFull.json', statusCode: 200 }).as('getListData');
    // opens the modal
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
      .wait(10)
      // scroll doesn't work
      cy.scrollTo('bottom')
  })

  it ("should show message when participant list is empty" ,() => {
    // intercepts the api call
    cy.intercept('GET', '**/api/participations/day/{nextMonday}*', { fixture: 'participantsListEmpty.json', statusCode: 200 }).as('getListData');
    // opens the modal
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
      cy.get('title')
      .contains('Teilnahmen am Montag')
      .parent()
      .parent()
      .find('table')
      .eq(0)
      .find('div')
      .contains('Heute gibt es keine Teilnehmer.')
  })
});