import * as data from "../../fixtures/data.json";
import { login } from "../../support/commands/login";

describe("admin.function.menu.participations", () => {
  const checkComponent = (user: string) => {
    // log user in
    login(user);

    // open menu overview
    cy.get("ul[class='navbar']").find("a[href='/menu']").click();

    // open first week with meals
    cy.get("[class='week']").first().click();

    // check participations button
    cy.get("[href^='/participations/'][href$='/edit']")
      .should("be.visible")
      .and("contain.text", "Participations")
      .as("participations");

    // open participations
    cy.get("@participations").click();
    cy.location().should((loc) => {
      expect(loc.pathname)
        .to.contain("/participations/")
        .and.to.contain("/edit");
    });

    // check headline
    cy.get("h1[class='headline']")
      .should("be.visible")
      .and("contain.text", "Participations");

    // check participation editor
    cy.get("[class='profile-list']").should("be.visible");

    // check participations table
    cy.get("table[class='table']").should("be.visible").as("table");

    // check date range
    cy.get("@table")
      .find("[class='table-head wide-cell week-date']")
      .should("have.length", 1)
      .and("be.visible");

    // check participants
    cy.get("@table")
      .find("[class='table-row']")
      .should("have.length.at.least", 1)
      .and("be.visible");

    // check weekdays
    cy.get("@table")
      .find("[class='table-head day']")
      .should("have.length.at.least", 1)
      .and("be.visible");

    // check meals
    cy.get("@table")
      .find("[class='table-head meal-title']")
      .should("have.length.at.least", 1)
      .and("be.visible");

    // check participation
    cy.get("@table")
      .find("[class='glyphicon glyphicon-ok']")
      .should("have.length.at.least", 1)
      .and("be.visible");
  };

  // TODO: edit participation

  it("is working fine in viewport 'desktop'", () => {
    cy.visitMeals();
    cy.viewportXL();
    checkComponent(data.user.kochomi);
  });
});
