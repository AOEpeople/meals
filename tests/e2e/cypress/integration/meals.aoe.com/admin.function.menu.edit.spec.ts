import * as data from "../../fixtures/data.json";
import { login } from "../../support/commands/login";

describe("admin.function.menu.edit", () => {
  const checkEditingElements = (user: string) => {
    // log user in
    login(user);

    // open menu overview
    cy.get("ul[class='navbar']").find("a[href='/menu']").click();

    // open first week with meals
    cy.get("[class='week']").first().click();
    cy.location().should((loc) => {
      expect(loc.pathname).to.contain("/menu/").and.to.contain("/edit");
    });

    // check visibility of elements
    cy.get("h1[class='headline']")
      .should("be.visible")
      .and("contain.text", "Edit week");
    cy.get("[href^='/participations/'][href$='/edit']")
      .should("be.visible")
      .and("contain.text", "Participations");
    cy.get("[class='switchery switchery-default']")
      .should("be.visible")
      .and("have.attr", "aria-checked", "true");

    cy.get("button[id='week_form_Cancel']")
      .should("be.visible")
      .as("cancelAction");
    cy.get("button[id='week_form_Save']").should("be.visible").as("saveAction");

    // check visibility of elements
    cy.get("[class='limit-icon']")
      .first()
      .should("be.visible")
      .as("limitAction");
    cy.get("[class='calendar-icon ']")
      .first()
      .should("be.visible")
      .as("calenderAction");

    cy.get("@cancelAction").click();
    cy.location().should((loc) => {
      expect(loc.pathname).to.contain("/menu");
    });
  };

  const checkEditingFunctions = () => {
    // open first week with meals
    cy.get("[class='week']").first().click();

    // check editing of week - limit
    cy.get("@limitAction").click();
    cy.get("[class='limit-box']").should("be.visible");
    cy.get("[class='limit-box-save button small']")
      .should("be.visible")
      .as("saveLimit");

    cy.get("@saveLimit").click();
    cy.get("[class='limit-box']").should("not.be.visible");

    // check editing of week - calender
    cy.get("@calenderAction").click();
    cy.get("[class^='xdsoft_datetimepicker']").should("be.visible");

    cy.get("@calenderAction").click();
    cy.get("[class^='xdsoft_datetimepicker']").should("not.be.visible");

    // close week via save
    cy.get("@saveAction").click();
    cy.get("[class='alert alert-success']").should("be.visible");
  };

  it("is working fine in viewport 'desktop'", () => {
    cy.visitMeals();
    cy.viewportXL();
    checkEditingElements(data.user.kochomi);
    checkEditingFunctions();
  });
});
