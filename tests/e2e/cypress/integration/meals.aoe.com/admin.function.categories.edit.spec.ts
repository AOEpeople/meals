import * as data from "../../fixtures/data.json";
import { login } from "../../support/commands/login";

describe("admin.function.categories.edit", () => {
  const checkEditingElements = (user: string) => {
    // log user in
    login(user);

    // open categories overview
    cy.get("ul[class='navbar']").find("a[href='/category']").click();

    // create new category
    cy.get("[href='/category/form']").click().as("createAction");

    // check visibility of elements
    cy.get("[class='create-form top-form']")
      .should("be.visible")
      .as("createForm");

    cy.get("input[id='category_title_de").should("be.visible").as("titleDE");
    cy.get("input[id='category_title_en").should("be.visible").as("titleEN");

    cy.get("button[id='category_save").should("be.visible").as("saveAction");

    // close and open form
    cy.get("@createAction").click();
    cy.get("@createForm").should("not.be.visible");
  };

  const checkEditingFunctions = () => {
    // open form
    cy.get("@createAction").click();
    cy.get("@createForm").should("be.visible");

    // create category
    cy.get("@titleDE").type("titleDE");
    cy.get("@titleEN").type("titleEN");

    // save category
    cy.get("@saveAction").click();
    cy.get("[class='alert alert-success']")
      .should("be.visible")
      .and("contain.text", "has been added.");

    // check visibility of elements
    cy.get("a[href='/category/form/titleen")
      .should("be.visible")
      .as("editAction");
    cy.get("a[href='/category/titleen/delete")
      .should("be.visible")
      .as("deleteAction");

    // edit category
    cy.get("@editAction").click();
    cy.get("@saveAction").click();
    cy.get("[class='alert alert-success']")
      .should("be.visible")
      .and("contain.text", "has been modified.");

    // delete category
    cy.get("@deleteAction").click();
    cy.get("[class='alert alert-success']")
      .should("be.visible")
      .and("contain.text", "has been deleted.");
  };

  it("is working fine in viewport 'desktop'", () => {
    cy.visitMeals();
    cy.viewportXL();
    checkEditingElements(data.user.kochomi);
    checkEditingFunctions();
  });
});
