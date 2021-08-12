import * as data from "../../fixtures/data.json";
import { login } from "../../support/commands/login";

describe("finance.function.finance.overview", () => {
  const checkComponent = (user: string) => {
    // log user in
    login(user);

    // check navigation for link to 'finance'
    cy.get("ul[class='navbar']")
      .find("a[href='/accounting/book/finance/list']")
      .should("be.visible")
      .and("contain.text", "Finance")
      .as("finance");

    // open finance overview
    cy.get("@finance").click();
    cy.location().should((loc) => {
      expect(loc.pathname).to.contain("/accounting/book/finance/list");
    });

    // check headline
    cy.get("h1[class='headline']").should("be.visible");

    // check date picker
    cy.get("i[class*='date-range-picker']").should("be.visible");

    // check export button
    cy.get("a[class$='pdf-export']")
      .should("be.visible")
      .and("have.text", "Export");

    // check finance tables (2)
    cy.get("table[id='accounting-book-table']")
      .should("have.length", 2)
      .and("be.visible")
      .first()
      .as("table");

    // check table header (4)
    cy.get("@table")
      .find("thead tr th")
      .should("have.length", 4)
      .and("be.visible");

    // check table rows
    cy.get("@table")
      .find("[class*='table-row']")
      .should("have.length.at.least", 1)
      .and("be.visible");
  };

  it("is working fine in viewport 'desktop'", () => {
    cy.visitMeals();
    cy.viewportXL();
    checkComponent(data.user.finance);
  });
});
