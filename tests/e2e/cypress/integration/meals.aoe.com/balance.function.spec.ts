import * as data from "../../fixtures/data.json";
import { login } from "../../support/commands/login";

describe("balance.function", () => {
  const checkComponent = (user: string) => {
    // log user in
    login(user);

    // check visibility of elements
    cy.get("header [class='balance-text']")
      .should("be.visible")
      .and("contain.text", "Balance:");
    cy.get("header a[href='/accounting/transactions']")
      .should("be.visible")
      .as("transactions");

    // open transactions overview
    cy.get("@transactions").click();
    cy.location().should((loc) => {
      expect(loc.pathname).to.contain("/accounting/transactions");
    });

    // check visibility of elements
    cy.get("h1[class='headline']")
      .should("be.visible")
      .and("contain.text", "Account");
    cy.get(`a[href='/payment/ecash/form/${user}']`)
      .should("be.visible")
      .and("contain.text", "ADD FUNDS")
      .as("payment");

    // open payment options
    cy.get("@payment").click();
    cy.get("form[name='ecash']").should("be.visible");
  };

  it("is working fine in viewport 'desktop'", () => {
    cy.visitMeals();
    cy.viewportXL();
    checkComponent(data.user.bob);
  });

  it("is working fine in viewport 'desktop'", () => {
    cy.visitMeals();
    cy.viewportXL();
    checkComponent(data.user.kochomi);
  });
});
