import * as data from "../../fixtures/data.json";
import { login } from "../../support/commands/login";

describe("reservation.function", () => {
  const checkComponent = (user: string) => {
    let participantsAfterJoinAction: number;
    let participantsAfterDeleteAction: number;

    // intercept the join request aka "the reservation"
    cy.intercept("GET", "**/join").as("getJoin");

    // intercept the delete request aka "the cancellation"
    cy.intercept("GET", "**/delete").as("getDelete");

    // log user in
    login(user);

    // at least one day should be available
    cy.get("[class='meal is-available']").should("have.length.at.least", 1);

    // at least one meal should be available
    cy.get("[class='meal is-available']")
      .find("input[class*='join-action']")
      .should("have.length.at.least", 1)
      .as("joinAction");

    cy.get("@joinAction").first().click({ force: true });

    cy.wait("@getJoin").then(({ response }) => {
      expect(response && response.body).to.have.property("participantsCount");
      expect(response && response.body).to.have.property("url");

      if (response) {
        participantsAfterJoinAction = response.body.participantsCount;
        cy.reload();
        cy.get(`input[value='${response.body.url}']`)
          .should("exist")
          .as("deleteAction");
      }

      cy.get("@deleteAction").click({ force: true });

      cy.wait("@getDelete").then(({ response }) => {
        expect(response && response.body).to.have.property("participantsCount");
        expect(response && response.body).to.have.property("url");

        if (response) {
          participantsAfterDeleteAction = response.body.participantsCount;
          expect(participantsAfterDeleteAction).eq(
            participantsAfterJoinAction - 1
          );
          cy.get(`input[value='${response.body.url}']`).should("exist");
        }

        cy.reload();
        cy.get("@joinAction").should("exist");
      });
    });
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
