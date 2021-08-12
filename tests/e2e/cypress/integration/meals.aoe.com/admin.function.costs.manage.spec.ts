import * as data from "../../fixtures/data.json";
import { login } from "../../support/commands/login";

describe("admin.function.costs.manage", () => {
  const checkComponent = (user: string) => {
    // log user in
    login(user);

    // open costs overview
    cy.get("ul[class='navbar']").find("a[href='/print/costsheet']").click();

    // check first cash payment button
    cy.get("[href^='/payment/cash/form/']")
      .first()
      .should("be.visible")
      .as("cashAction");

    // check first settle account button
    cy.get("[href^='/payment/settlement/form/']")
      .first()
      .should("be.visible")
      .as("settleAction");

    // open cash payment option
    cy.get("@cashAction").click();
    cy.get("form[name='cash']").should("be.visible");

    // check inputs
    cy.get("input[id='cash_amount']").should("be.visible");
    cy.get("button[id='cash_submit']").should("be.visible");

    // open settle account option
    cy.get("@settleAction").click();
    cy.get("form[name='settleform']").should("be.visible");

    // check settle account option for correct user profile
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
            expect(text).contain(profile);
          });
      });

    // TODO: make cash payment with random amount and check total value after transaction
    cy.get("@settleRow")
      .children("[class='table-data']")
      .last()
      .invoke("text")
      .then((text) => {
        const regex = /[+-]?\d+(\.\d+)?/g;
        let text2 = text.match(regex)[0];
        cy.log("TOTAL " + (parseFloat(text2) + 1));
      });
  };

  it("is working fine in viewport 'desktop'", () => {
    cy.visitMeals();
    cy.viewportXL();
    checkComponent(data.user.kochomi);
  });
});
