import * as data from "../../fixtures/data.json";
import { login } from "../../support/commands/login";

describe("admin.function.costs.overview", () => {
  const checkComponent = (user: string) => {
    // log user in
    login(user);

    // check visibility of elements
    cy.get("ul[class='navbar']")
      .find("a[href='/print/costsheet']")
      .should("be.visible")
      .and("contain.text", "Costs")
      .as("costs");

    // open costs
    cy.get("@costs").click();
    cy.location().should((loc) => {
      expect(loc.pathname).to.contain("/print/costsheet");
    });

    // check visibility of elements
    cy.get("h1[class='headline']")
      .should("be.visible")
      .and("contain.text", "Cost listing");

    // check table
    cy.get("table[class='table']").should("be.visible");

    // the table has at least one name
    cy.get("[class='table-row']").should("have.length.at.least", 1);

    // at least one cash payment button should be present
    cy.get("[href^='/payment/cash/form/']").should("have.length.at.least", 1);

    // at least one settle account button should be present
    cy.get("[href^='/payment/settlement/form/']").should(
      "have.length.at.least",
      1
    );
  };

  it("is working fine in viewport 'desktop'", () => {
    cy.visitMeals();
    cy.viewportXL();
    checkComponent(data.user.kochomi);
  });
});
