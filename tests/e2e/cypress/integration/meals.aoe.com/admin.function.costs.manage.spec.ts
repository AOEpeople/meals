import * as data from "../../fixtures/data.json";
import { login } from "../../support/commands/login";

describe("admin.function.costs.manage", () => {
  const checkComponent = (user: string) => {
    // log user in
    login(user);

    // open costs overview
    cy.get("ul[class='navbar']").find("a[href='/print/costsheet']").click();

    // check topmost cash payment button
    cy.get("[href^='/payment/cash/form/']")
      .first()
      .should("be.visible")
      .as("cashAction");

    // check topmost settle account button
    cy.get("[href^='/payment/settlement/form/']")
      .first()
      .should("be.visible")
      .as("settleAction");

    // open cash payment option
    cy.get("@cashAction").click();
    cy.get("form[name='cash']").should("be.visible");

    // check inputs
    cy.get("input[id='cash_amount']").should("be.visible").as("inputAmount");
    cy.get("button[id='cash_submit']").should("be.visible").as("submitAmount");

    // open settle account option
    cy.get("@settleAction").click();
    cy.get("form[name='settleform']").should("be.visible");

    // check correct user profile on settle account option
    cy.get("a[class='settle-account']")
      .invoke("attr", "data-profile")
      .then((profile) => {
        cy.get("@settleAction")
          .parentsUntil("tbody")
          .as("settleRow")
          .children("td")
          .first()
          .invoke("text")
          .then((text) => {
            // compare settle account link to text in column 'name'
            expect(text).contain(profile);
          });
      });

    // make cash payment with random amount and check amount after transaction
    cy.get("@settleRow")
      .children("[class='table-data']")
      .last()
      .invoke("text")
      .then((text: string) => {
        let initialAmount: string;
        let finalAmount: string;
        const regex = /[+-]?\d+(\.\d+)?/g;
        const cashAmount = (Math.floor(Math.random() * 100) + 1) / 100;

        // determine initial amount
        initialAmount = text.match(regex)[0];

        // open cash payment option and submit payment
        cy.get("@cashAction").click();
        cy.get("@inputAmount").clear().type(String(cashAmount));
        cy.get("@submitAmount").click();

        // check success alert
        cy.get("[class='alert alert-success']")
          .should("be.visible")
          .and("contain.text", "Booked cash payment of ")
          .and("contain.text", cashAmount);

        // check total amount after cash payment
        cy.get("@settleRow")
          .children("[class='table-data']")
          .last()
          .invoke("text")
          .then((text: string) => {
            // determine final amount
            finalAmount = text.match(regex)[0];

            // compare final amount to sum of initial amount and cash amount
            expect(parseFloat(finalAmount)).eq(
              parseFloat(initialAmount) + cashAmount
            );
          });
      });
  };

  it("is working fine in viewport 'desktop'", () => {
    cy.visitMeals();
    cy.viewportXL();
    checkComponent(data.user.kochomi);
  });
});
