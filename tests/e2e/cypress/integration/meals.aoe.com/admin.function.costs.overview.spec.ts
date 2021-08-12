import * as data from "../../fixtures/data.json";
import { login } from "../../support/commands/login";

describe("admin.function.costs.overview", () => {
  const checkComponent = (user: string) => {
    // log user in
    login(user);

    // check navigation for link to 'costs'
    cy.get("ul[class='navbar']")
      .find("a[href='/print/costsheet']")
      .should("be.visible")
      .and("contain.text", "Costs")
      .as("costs");

    // open costs overview
    cy.get("@costs").click();
    cy.location().should((loc) => {
      expect(loc.pathname).to.contain("/print/costsheet");
    });

    // check headline
    cy.get("h1[class='headline']")
      .should("be.visible")
      .and("contain.text", "Cost listing");

    // check costs table
    cy.get("table[class='table']").should("be.visible").as("table");

    // check table rows
    cy.get("@table")
      .find("[class='table-row']")
      .should("have.length.at.least", 1)
      .should("be.visible");

    // check cash payment buttons
    cy.get("a[class*='payment-form'][href^='/payment/cash/form/']")
      .should("have.length.at.least", 1)
      .should("be.visible");

    // check settle account buttons
    cy.get("a[class*='settlement-form'][href^='/payment/settlement/form/']")
      .should("have.length.at.least", 1)
      .should("be.visible");
  };

  it("is working fine in viewport 'desktop'", () => {
    cy.visitMeals();
    cy.viewportXL();
    checkComponent(data.user.kochomi);
  });
});
