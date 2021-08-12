import * as data from "../../fixtures/data.json";
import { login } from "../../support/commands/login";

describe("admin.function.dishes.edit", () => {
  const checkEditElements = (user: string) => {
    // log user in
    login(user);

    // open dishes overview
    cy.get("ul[class='navbar']").find("a[href='/dish']").click();

    // open form to create new dish
    cy.get("[href='/dish/form']").click().as("createAction");

    // check form
    cy.get("[class='create-form top-form']")
      .should("be.visible")
      .as("createForm");

    // check inputs
    cy.get("input[id='dish_title_de").should("be.visible").as("titleDE");
    cy.get("input[id='dish_description_de")
      .should("be.visible")
      .as("descriptionDE");

    cy.get("input[id='dish_title_en").should("be.visible").as("titleEN");
    cy.get("input[id='dish_description_en")
      .should("be.visible")
      .as("descriptionEN");

    // check category select
    cy.get("select[id='dish_category").should("be.visible").as("category");

    // check save button
    cy.get("button[id='dish_save").should("be.visible").as("saveAction");

    // close form
    cy.get("@createAction").click();
    cy.get("@createForm").should("not.be.visible");
  };

  const checkEditFunctions = () => {
    // open form
    cy.get("@createAction").click();
    cy.get("@createForm").should("be.visible");

    // set inputs
    cy.get("@titleDE").type("titleDE");
    cy.get("@descriptionDE").type("descriptionDE");
    cy.get("@titleEN").type("titleEN");
    cy.get("@descriptionEN").type("descriptionEN");
    cy.get("@category").select("Others");

    // save new dish
    cy.get("@saveAction").click();
    cy.get("[class='alert alert-success']")
      .should("be.visible")
      .and("contain.text", "has been added.");

    // check edit button
    cy.get("a[href='/dish/form/titleen").should("be.visible").as("editAction");

    // check delete button
    cy.get("a[href='/dish/titleen/delete")
      .should("be.visible")
      .as("deleteAction");

    // TODO: add variation to created dish

    // edit created dish
    cy.get("@editAction").click();
    cy.get("@saveAction").click();

    // check success alert
    cy.get("[class='alert alert-success']")
      .should("be.visible")
      .and("contain.text", "has been modified.");

    // delete created dish
    cy.get("@deleteAction").click();

    // check success alert
    cy.get("[class='alert alert-success']")
      .should("be.visible")
      .and("contain.text", "has been deleted.");
  };

  it("is working fine in viewport 'desktop'", () => {
    cy.visitMeals();
    cy.viewportXL();
    checkEditElements(data.user.kochomi);
    checkEditFunctions();
  });
});
