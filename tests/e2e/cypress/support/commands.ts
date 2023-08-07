import { baseUrl } from "./commands/urls";
import {loginAs} from "./commands/login";
import {setCookieInterceptor} from "./interceptors";
import { resetDB } from "./commands/reset";

// add new command to the existing Cypress interface
declare global {
  namespace Cypress {
    interface Chainable {
      /**
       * Perform login as given user
       */
      loginAs: typeof loginAs;
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
       * Navigate to Meals via window object
       */
       visitMealsViaWindowObject: () => Cypress.Chainable<Window>;
      /**
       * Checks if element is in viewport
       */
      isInViewport: (selector: string) => void;
      /**
       * Resets the Database
       */
      resetDB: () => Cypress.Chainable<null>;
    }
  }
}

export const viewportS = () => cy.viewport(320, 800);
export const viewportXL = () => cy.viewport(1344, 800);

export const visitMeals = () => {
  setCookieInterceptor();
  cy.visit(`${baseUrl}`);
};

export const visitMealsViaWindowObject = () => {
  cy.window().then(win => win.location.href = `${baseUrl}`);
};

// add commands to Cypress
Cypress.Commands.add("loginAs", loginAs);
Cypress.Commands.add("viewportS", viewportS);
Cypress.Commands.add("viewportXL", viewportXL);
Cypress.Commands.add("visitMeals", visitMeals);
Cypress.Commands.add("visitMealsViaWindowObject", visitMealsViaWindowObject);
Cypress.Commands.add("resetDB", resetDB);
