import * as data from "../../fixtures/data.json";
import { login } from "../../support/commands/login";

describe("admin.function.menu.participations", () => {
  const checkComponent = (user: string) => {
    // log user in
    login(user);

    // open menu
    cy.get("ul[class='navbar']").find("a[href='/menu']").click();

    // open first week with meals
    cy.get("[class='week']").first().click();

    // check visibility of elements
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

    // check visibility of elements
    cy.get("h1[class='headline']")
      .should("be.visible")
      .and("contain.text", "Participations");
    cy.get("[class='profile-list']").should("be.visible");

    // check table
    cy.get("table[class='table']").should("be.visible");

    // the table has one date range
    cy.get("[class='table-head wide-cell week-date']").should("have.length", 1);

    // the table has at least one participant
    cy.get("[class='table-row']").should("have.length.at.least", 1);

    // the table has at least one weekday
    cy.get("[class='table-head day']").should("have.length.at.least", 1);

    // the table has at least one meal
    cy.get("[class='table-head meal-title']").should("have.length.at.least", 1);

    // the table has at least one participation
    cy.get("[class='glyphicon glyphicon-ok']").should(
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
