import * as data from "../../fixtures/data.json";

describe.skip("reservation.function", () => {
  const checkComponent = (user: String) => {
    // log user in
    cy.get("header input[name='_username']").type(`${user}`);
    cy.get("header input[name='_password']").type(`${user}`);
    cy.get("header button[type='submit']").click();

    // at least one day should be available
    cy.get("[class='meal is-available']").should("have.length.at.least", 1);

    // at least one meal should be available
    cy.get("[class='meal is-available']")
      .find("input[class*='join-action']")
      .should("have.length.at.least", 1);

    // https://docs.cypress.io/api/commands/intercept#Request-Response-Modification-with-routeHandler

    cy.get("[class='meal is-available']")
      .find("input[class*='join-action']")
      .first()
      .click({ force: true })
      .invoke("val")
      .then((val) => {
        cy.reload();
        //if (val != undefined) let val2 = val.replace("/join", "/delete");
        //cy.log("CCC " + val2);
        //cy.get(`input[value='${val2}']`).should("be.visible")
      });
  };

  it("is working fine in viewport 'desktop'", () => {
    cy.visitMeals();
    cy.viewportXL();
    checkComponent(data.user.bob);
  });
});
