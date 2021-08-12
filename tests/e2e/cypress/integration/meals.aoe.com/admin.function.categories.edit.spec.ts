import * as data from "../../fixtures/data.json";
import { login } from "../../support/commands/login";

describe("admin.function.categories.edit", () => {
  const checkEditElements = (user: string) => {
    // log user in
    login(user);

    // open categories overview
    cy.get("ul[class='navbar']").find("a[href='/category']").click();

    // open form to create new category
    cy.get("[href='/category/form']").click().as("createAction");

    // check form
    cy.get("[class='create-form top-form']")
      .should("be.visible")
      .as("createForm");

    // check inputs
    cy.get("input[id='category_title_de").should("be.visible").as("titleDE");
    cy.get("input[id='category_title_en").should("be.visible").as("titleEN");

    // check save button
    cy.get("button[id='category_save").should("be.visible").as("saveAction");

    // close form
    cy.get("@createAction").click();
    cy.get("@createForm").should("not.be.visible");
  };

  const checkEditFunctions = () => {
    // open form to create new category
    cy.get("@createAction").click();
    cy.get("@createForm").should("be.visible");

    // set inputs
    cy.get("@titleDE").type("titleDE");
    cy.get("@titleEN").type("titleEN");

    // save new category
    cy.get("@saveAction").click();
    cy.get("[class='alert alert-success']")
      .should("be.visible")
      .and("contain.text", "has been added.");

    // check edit button
    cy.get("a[href='/category/form/titleen")
      .should("be.visible")
      .as("editAction");

    // check delete button
    cy.get("a[href='/category/titleen/delete")
      .should("be.visible")
      .as("deleteAction");

    // edit created category
    cy.get("@editAction").click();
    cy.get("@saveAction").click();

    // check success alert
    cy.get("[class='alert alert-success']")
      .should("be.visible")
      .and("contain.text", "has been modified.");

    // delete created category
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
