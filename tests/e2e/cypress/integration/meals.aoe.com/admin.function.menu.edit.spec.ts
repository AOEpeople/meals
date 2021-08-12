import * as data from "../../fixtures/data.json";
import { login } from "../../support/commands/login";

describe("admin.function.menu.edit", () => {
  const checkEditElements = (user: string) => {
    // log user in
    login(user);

    // open menu overview
    cy.get("ul[class='navbar']").find("a[href='/menu']").click();

    // open first week with meals
    cy.get("[class='week']").first().click();
    cy.location().should((loc) => {
      expect(loc.pathname).to.contain("/menu/").and.to.contain("/edit");
    });

    // check headline
    cy.get("h1[class='headline']")
      .should("be.visible")
      .and("contain.text", "Edit week");

    // check participations button
    cy.get("[href^='/participations/'][href$='/edit']")
      .should("be.visible")
      .and("contain.text", "Participations");

    // check activation button
    cy.get("[class='switchery switchery-default']")
      .should("be.visible")
      .and("have.attr", "aria-checked", "true");

    // check cancel button
    cy.get("button[id='week_form_Cancel']")
      .should("be.visible")
      .as("cancelAction");

    // check save button
    cy.get("button[id='week_form_Save']").should("be.visible").as("saveAction");

    // check limit button
    cy.get("[class='limit-icon']")
      .first()
      .should("be.visible")
      .as("limitAction");

    // check calender button
    cy.get("[class='calendar-icon ']")
      .first()
      .should("be.visible")
      .as("calenderAction");

    // click cancel button
    cy.get("@cancelAction").click();
    cy.location().should((loc) => {
      expect(loc.pathname).to.contain("/menu");
    });
  };

  const checkEditFunctions = () => {
    // open first week with meals
    cy.get("[class='week']").first().click();

    // TODO: deactivate and activate this week

    // TODO: deactivate and activate one day

    // open form to set new limit
    cy.get("@limitAction").click();
    cy.get("[class='limit-box']").should("be.visible");
    cy.get("[class='limit-box-save button small']")
      .should("be.visible")
      .as("saveLimit");

    // TODO: set new limit

    // save form
    cy.get("@saveLimit").click();
    cy.get("[class='limit-box']").should("not.be.visible");

    // open form to set new time
    cy.get("@calenderAction").click();
    cy.get("[class^='xdsoft_datetimepicker']").should("be.visible");

    // TODO: set new time

    // close form
    cy.get("@calenderAction").click();
    cy.get("[class^='xdsoft_datetimepicker']").should("not.be.visible");

    // save week
    cy.get("@saveAction").click();

    // check success alert
    cy.get("[class='alert alert-success']")
      .should("be.visible")
      .and("contain.text", "has been modified.");
  };

  // TODO: create new week with meals

  it("is working fine in viewport 'desktop'", () => {
    cy.visitMeals();
    cy.viewportXL();
    checkEditElements(data.user.kochomi);
    checkEditFunctions();
  });
});
