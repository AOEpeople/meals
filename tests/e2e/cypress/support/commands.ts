import { baseUrl, cookieDomain } from "./commands/urls";
import {loginAs} from "./commands/login";
import {setCookieInterceptor} from "./interceptors";
import { resetDB } from "./commands/reset";
import { visitSettlementLinkFromMail } from "./commands/mailhog";

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
      /**
       * Visits the settlement link from the most recent mail
       */
      visitSettlementLinkFromMail: () => Cypress.Chainable<null>;
    }
  }
}

Cypress.Commands.overwrite('visit', (originalFn, url, options) => {
  // @ts-expect-error
  return originalFn(url, {
    onBeforeLoad(win) {
      Object.defineProperty(win.navigator, 'language', { value: 'de-DE' });
      Object.defineProperty(win.navigator, 'languages', { value: ['de'] });
      Object.defineProperty(win.navigator, 'accept_languages', { value: ['de'] });
  }, ...options });
});

Cypress.Commands.overwrite("log", function(log, ...args) {
  if (Cypress.browser.isHeadless) {
    return cy.task("log", args, { log: false }).then(() => {
      return log(...args);
    });
  } else {
    console.log(...args);
    return log(...args);
  }
});

export const viewportS = () => cy.viewport(320, 800);
export const viewportXL = () => cy.viewport(1344, 800);

export const visitMeals = () => {
  setCookieInterceptor();
  cy.setCookie('locale', 'de-DE', {
    domain: cookieDomain,
    httpOnly: true,
    secure: true,
    hostOnly: true,
    sameSite: 'lax'
  });
  cy.visit(`${baseUrl}`, {
    onBeforeLoad(win) {
      Object.defineProperty(win.navigator, 'language', { value: 'de-DE' });
      Object.defineProperty(win.navigator, 'languages', { value: ['de'] });
      Object.defineProperty(win.navigator, 'accept_languages', { value: ['de'] });
    },
    headers: {
      'Accept-Language': 'de',
    },
  });
  cy.request({
    method: 'POST',
    url: `${Cypress.env('baseUrl')}api/payment/cash/kochomi.meals?amount=1000`
  }).then((response) => {
    expect(response.status).to.eq(200);
  });
  // Hide Symphony's toolbar
  if (Cypress.env('ddev_test')) {
    cy.wait(500)
    cy.get('button[class="hide-button"]').click({ force: true });
  }
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
Cypress.Commands.add("visitSettlementLinkFromMail", visitSettlementLinkFromMail);
