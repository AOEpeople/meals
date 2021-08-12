import * as data from "../../fixtures/data.json";
import { login } from "../../support/commands/login";

describe("standard.function.reservation", () => {
  const checkComponent = (user: string) => {
    let participantsAfterJoinAction: number;
    let participantsAfterDeleteAction: number;

    // intercept the join request aka "the reservation"
    cy.intercept("GET", "**/join").as("getJoin");

    // intercept the delete request aka "the cancellation"
    cy.intercept("GET", "**/delete").as("getDelete");

    // log user in
    login(user);

    // check available day
    cy.get("[class='meal is-available']")
      .should("have.length.at.least", 1)
      .and("be.visible");

    // check join action on first available meal
    cy.get("[class='meal is-available']")
      .find("input[class*='join-action']")
      .should("have.length.at.least", 1)
      .first()
      .as("joinAction");

    // join a meal
    cy.get("@joinAction").click({ force: true });

    cy.wait("@getJoin").then(({ response }) => {
      // check response after join
      expect(response && response.body).to.have.property("participantsCount");
      expect(response && response.body).to.have.property("url");

      if (response) {
        participantsAfterJoinAction = response.body.participantsCount;
        cy.reload();

        // check delete action
        cy.get(`input[value='${response.body.url}']`)
          .should("exist")
          .as("deleteAction");
      }

      // delete the joined meal
      cy.get("@deleteAction").click({ force: true });

      cy.wait("@getDelete").then(({ response }) => {
        // check response after delete
        expect(response && response.body).to.have.property("participantsCount");
        expect(response && response.body).to.have.property("url");

        if (response) {
          participantsAfterDeleteAction = response.body.participantsCount;

          // check participants after deletion
          expect(participantsAfterDeleteAction).eq(
            participantsAfterJoinAction - 1
          );

          // check join action after deletion
          cy.get(`input[value='${response.body.url}']`).should("exist");
        }
        cy.reload();

        // check join action on available meal
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
