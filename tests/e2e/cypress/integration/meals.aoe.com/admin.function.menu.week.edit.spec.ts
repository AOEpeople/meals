import * as data from "../../fixtures/data.json";
import { login } from "../../support/commands/login";

describe("admin.function.menu.week.edit", () => {
  const checkComponent = (user: string) => {
    // log user in
    login(user);

    // check visibility of elements
    cy.get("ul[class='navbar']")
      .find("a[href='/menu']")
      .should("be.visible")
      .and("contain.text", "Menu")
      .as("menu");

    // open menu
    cy.get("@menu").click();
    cy.location().should((loc) => {
      expect(loc.pathname).to.contain("/menu");
    });

    // check visibility of elements
    cy.get("h1[class='headline']")
      .should("be.visible")
      .and("contain.text", "List of weeks");

    // at least one week should have meals
    cy.get("[class='week']").should("have.length.at.least", 1);

    // at least one week should have no meals
    cy.get("[class='week week-create']").should("have.length.at.least", 1);

    // open week with meals
    cy.get("[class='week']").first().click();

    cy.get("h1[class='headline']")
      .should("be.visible")
      .and("contain.text", "Edit week");
    cy.get("button[id='week_form_Cancel']")
      .should("be.visible")
      .as("cancelAction");
    cy.get("button[id='week_form_Save']").should("be.visible").as("saveAction");

    cy.get("[class='limit-icon']")
      .first()
      .should("be.visible")
      .as("limitAction");
    cy.get("[class='calendar-icon ']")
      .first()
      .should("be.visible")
      .as("calenderAction");

    // edit week - limit
    cy.get("@limitAction").click();
    cy.get("[class='limit-box']").should("be.visible");

    cy.get("@limitAction").click();
    cy.get("[class='limit-box']").should("not.be.visible");

    // edit week - calender
    cy.get("@calenderAction").click();
    cy.get("[class^='xdsoft_datetimepicker']").should("be.visible");

    cy.get("@calenderAction").click();
    cy.get("[class^='xdsoft_datetimepicker']").should("not.be.visible");

    // close week
    cy.get("@saveAction").click();
    cy.get("[class='alert alert-success']").should("be.visible");
  };

  it("is working fine in viewport 'desktop'", () => {
    cy.visitMeals();
    cy.viewportXL();
    checkComponent(data.user.kochomi);
  });
});
