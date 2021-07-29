import * as data from "../../fixtures/data.json";
import { login } from "../../support/commands/login";

describe("standard.function.balance", () => {
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
    cy.get("tfoot [class='table-row']")
      .should("be.visible")
      .and("contain.text", "Current balance:")
      .find("[class='table-data']")
      .as("balance");

    // check transactions amount and balance amount
    cy.get("@transactions")
      .invoke("text")
      .then((transactionValue) => {
        cy.get("@balance")
          .invoke("text")
          .then((balanceValue) => {
            expect(balanceValue.replace(/\s/g, "")).eq(transactionValue);
          });
      });

    // open payment options
    cy.get("@payment").click();
    cy.get("form[name='ecash']").should("be.visible");
    cy.get("input[id='ecash_amount']").should("be.visible").as("amount");

    // TODO: iframe can not be tested easily (https://www.npmjs.com/package/cypress-iframe)
    //cy.get("[data-funding-source='paypal']").should("be.visible").as("paypal");

    // TODO: carry out a payment
    let randomAmount = (Math.floor(Math.random() * 100) + 1) / 100;
    cy.get("input[id='ecash_amount']").clear().type(String(randomAmount));

    // close payment options
    cy.get("@payment").click();
    cy.get("form[name='ecash']").should("not.be.visible");
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
