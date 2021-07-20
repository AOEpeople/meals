import { baseUrl } from "./commands/urls";

// add new command to the existing Cypress interface
declare global {
  namespace Cypress {
    interface Chainable {
      /**
       * Change viewport to S
       */
      viewportS: () => Cypress.Chainable<null>;
      /**
       * Change viewport to XL
       */
      viewportXL: () => Cypress.Chainable<null>;
      /**
       * Navigate to Meals
       */
      visitMeals: () => Cypress.Chainable<Window>;
      /**
       * Checks if element is in viewport
       */
      isInViewport: (selector: string) => void;
    }
  }
}

export const viewportS = () => cy.viewport(320, 800);
export const viewportXL = () => cy.viewport(1344, 800);

export const visitMeals = () => {
  cy.visit(`${baseUrl}`);
};

// add commands to Cypress
Cypress.Commands.add("viewportS", viewportS);
Cypress.Commands.add("viewportXL", viewportXL);

Cypress.Commands.add("visitMeals", visitMeals);

Cypress.Commands.add("isInViewport", (element) => {
  cy.get(element).then(($el) => {
    cy.window().then((win) => {
      const bottom = win.innerHeight;
      const rect = $el[0].getBoundingClientRect();
      expect(rect.top).not.to.be.greaterThan(bottom);
    });
  });
});
