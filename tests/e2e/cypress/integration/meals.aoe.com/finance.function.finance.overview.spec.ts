import * as data from "../../fixtures/data.json";
import { login } from "../../support/commands/login";

describe("finance.function.finance.overview", () => {
  const checkComponent = (user: string) => {
    // log user in
    login(user);

    // check visibility of elements
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

    // check visibility of elements
    cy.get("h1[class='headline']").should("be.visible");
    cy.get("a[class$='pdf-export']")
      .should("be.visible")
      .and("have.text", "Export");

    // check table
    cy.get("table[id='accounting-book-table']").should("be.visible");
  };

  it("is working fine in viewport 'desktop'", () => {
    cy.visitMeals();
    cy.viewportXL();
    checkComponent(data.user.finance);
  });
});
